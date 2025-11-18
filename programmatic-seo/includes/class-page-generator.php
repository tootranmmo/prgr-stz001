<?php
/**
 * Auto Page Generation Class
 */

class Programmatic_SEO_Page_Generator {
    private static $instance = null;
    private $wpdb;
    private $template_manager;
    private $data_source;
    private $generated_pages_table;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->template_manager = Programmatic_SEO_Template_Manager::get_instance();
        $this->data_source = Programmatic_SEO_Data_Source::get_instance();
        $this->generated_pages_table = Programmatic_SEO_Database::get_generated_pages_table();
    }

    /**
     * Generate pages from template and data source
     */
    public function generate_pages($template_id, $source_id, $user_id = null) {
        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }

        $template = $this->template_manager->get_template($template_id);
        if (!$template) {
            return new WP_Error('template_not_found', 'Template not found');
        }

        // Fetch data from source
        $data = $this->data_source->fetch_data($source_id);
        if (is_wp_error($data)) {
            return $data;
        }

        // Apply field mapping
        $data = $this->data_source->apply_mapping($data, $source_id);

        if (empty($data)) {
            return new WP_Error('no_data', 'No data found from source');
        }

        // Generate pages
        $results = array(
            'success' => 0,
            'failed' => 0,
            'errors' => array(),
        );

        // Ensure $data is array of items
        if (!isset($data[0]) || !is_array($data[0])) {
            $data = array($data);
        }

        foreach ($data as $index => $item_data) {
            $result = $this->generate_single_page($template, $item_data, $user_id, $source_id);

            if (is_wp_error($result)) {
                $results['failed']++;
                $results['errors'][] = sprintf('Item %d: %s', $index + 1, $result->get_error_message());
            } else {
                $results['success']++;
            }
        }

        return $results;
    }

    /**
     * Generate a single page
     */
    private function generate_single_page($template, $item_data, $user_id, $source_id) {
        // Check for duplicates
        $existing = $this->find_duplicate_page($template->id, $item_data);
        if ($existing) {
            return new WP_Error('duplicate', 'A page with this data already exists');
        }

        // Process template with data
        $processed = $this->template_manager->process_template($template->id, $item_data);

        if (!$processed) {
            return new WP_Error('processing_failed', 'Template processing failed');
        }

        // Create post
        $post_data = array(
            'post_title' => $processed['title_template'],
            'post_content' => $processed['content_template'],
            'post_status' => 'publish',
            'post_type' => $template->post_type,
            'post_author' => $user_id,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id) || $post_id === 0) {
            return new WP_Error('post_creation_failed', 'Failed to create post');
        }

        // Save post meta
        update_post_meta($post_id, '_programmatic_seo_description', $processed['description_template']);
        update_post_meta($post_id, '_programmatic_seo_keywords', $processed['keywords_template']);

        // Save schema data
        if (!empty($processed['schema_template'])) {
            update_post_meta($post_id, '_programmatic_seo_schema', $processed['schema_template']);
        }

        // Save source data for reference
        update_post_meta($post_id, '_programmatic_seo_source_data', $item_data);

        // Record in generated pages table
        $this->record_generated_page($post_id, $template->id, $source_id, $item_data);

        // Handle featured image if specified
        if (!empty($template->featured_image_field) && isset($item_data[$template->featured_image_field])) {
            $this->set_featured_image($post_id, $item_data[$template->featured_image_field]);
        }

        return $post_id;
    }

    /**
     * Find duplicate pages
     */
    private function find_duplicate_page($template_id, $item_data) {
        $source_data_json = wp_json_encode($item_data);
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT post_id FROM $this->generated_pages_table
                WHERE template_id = %d AND source_data = %s LIMIT 1",
                $template_id,
                $source_data_json
            )
        );

        return $result ? $result->post_id : null;
    }

    /**
     * Record generated page in database
     */
    private function record_generated_page($post_id, $template_id, $source_id, $source_data) {
        return $this->wpdb->insert(
            $this->generated_pages_table,
            array(
                'post_id' => $post_id,
                'template_id' => $template_id,
                'data_source_id' => $source_id,
                'source_data' => wp_json_encode($source_data),
                'status' => 'published',
            )
        );
    }

    /**
     * Set featured image for post
     */
    private function set_featured_image($post_id, $image_url) {
        if (empty($image_url)) {
            return;
        }

        // Download image
        $upload_dir = wp_upload_dir();
        $image_data = wp_remote_get($image_url);

        if (is_wp_error($image_data)) {
            return;
        }

        $image_name = basename($image_url);
        $image_path = $upload_dir['path'] . '/' . $image_name;

        // Save image file
        file_put_contents($image_path, wp_remote_retrieve_body($image_data));

        // Create attachment
        $attachment = array(
            'post_mime_type' => mime_content_type($image_path),
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($image_url)),
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attach_id = wp_insert_attachment($attachment, $image_path, $post_id);

        if (!is_wp_error($attach_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            set_post_thumbnail($post_id, $attach_id);
        }
    }

    /**
     * Get generated pages count
     */
    public function count_generated_pages($template_id = null) {
        $query = "SELECT COUNT(*) FROM $this->generated_pages_table WHERE 1=1";

        if (!empty($template_id)) {
            $query .= $this->wpdb->prepare(" AND template_id = %d", $template_id);
        }

        return (int) $this->wpdb->get_var($query);
    }

    /**
     * Get generated pages
     */
    public function get_generated_pages($args = array()) {
        $defaults = array(
            'offset' => 0,
            'limit' => 20,
            'template_id' => null,
        );

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM $this->generated_pages_table WHERE 1=1";

        if (!empty($args['template_id'])) {
            $query .= $this->wpdb->prepare(" AND template_id = %d", $args['template_id']);
        }

        $query .= " ORDER BY created_at DESC";

        if (!empty($args['limit'])) {
            $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        $results = $this->wpdb->get_results($query);

        // Attach post info
        if ($results) {
            foreach ($results as &$result) {
                $result->post = get_post($result->post_id);
            }
        }

        return $results;
    }

    /**
     * Delete generated pages by template
     */
    public function delete_pages_by_template($template_id) {
        $pages = $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT post_id FROM $this->generated_pages_table WHERE template_id = %d",
                $template_id
            )
        );

        foreach ($pages as $post_id) {
            wp_delete_post($post_id, true);
        }

        return $this->wpdb->delete(
            $this->generated_pages_table,
            array('template_id' => $template_id)
        );
    }

    /**
     * Get generation stats
     */
    public function get_stats() {
        return array(
            'total_generated_pages' => (int) $this->wpdb->get_var("SELECT COUNT(*) FROM $this->generated_pages_table"),
            'total_templates' => (int) $this->template_manager->count_templates(),
            'total_data_sources' => count($this->data_source->get_sources()),
        );
    }
}
