<?php
/**
 * AJAX and Form Handlers
 */

class Programmatic_SEO_AJAX_Handlers {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Template AJAX handlers
        add_action('wp_ajax_prgr_seo_validate_template', array($this, 'validate_template'));
        add_action('wp_ajax_prgr_seo_preview_template', array($this, 'preview_template'));
        add_action('wp_ajax_prgr_seo_delete_template', array($this, 'delete_template'));

        // Data source AJAX handlers
        add_action('wp_ajax_prgr_seo_test_datasource', array($this, 'test_datasource'));
        add_action('wp_ajax_prgr_seo_preview_datasource', array($this, 'preview_datasource'));
        add_action('wp_ajax_prgr_seo_delete_datasource', array($this, 'delete_datasource'));
        add_action('wp_ajax_prgr_seo_sync_datasource', array($this, 'sync_datasource'));

        // Page generation AJAX
        add_action('wp_ajax_prgr_seo_generate_pages', array($this, 'generate_pages'));

        // Delete hooks for non-AJAX
        add_action('admin_post_prgr_seo_delete_template', array($this, 'delete_template_post'));
        add_action('admin_post_prgr_seo_delete_datasource', array($this, 'delete_datasource_post'));
    }

    /**
     * Validate template
     */
    public function validate_template() {
        check_ajax_referer('validate_template', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        $errors = array();

        if (empty($name)) {
            $errors['name'] = __('Template name is required', 'programmatic-seo');
        }

        if (!empty($errors)) {
            wp_send_json_error($errors);
        }

        wp_send_json_success(array(
            'message' => __('Template is valid', 'programmatic-seo'),
        ));
    }

    /**
     * Preview template
     */
    public function preview_template() {
        check_ajax_referer('preview_template', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $template_manager = Programmatic_SEO_Template_Manager::get_instance();

        $title = sanitize_text_field($_POST['title_template'] ?? '');
        $description = sanitize_text_field($_POST['description_template'] ?? '');
        $keywords = sanitize_text_field($_POST['keywords_template'] ?? '');

        // Sample data
        $sample_data = array(
            'product_name' => 'Sample Product',
            'price' => '$99.99',
            'category' => 'Electronics',
            'description' => 'This is a sample description',
        );

        $preview_title = $template_manager->replace_variables($title, $sample_data);
        $preview_description = $template_manager->replace_variables($description, $sample_data);
        $preview_keywords = $template_manager->replace_variables($keywords, $sample_data);

        wp_send_json_success(array(
            'title' => $preview_title ?: __('(Empty)', 'programmatic-seo'),
            'description' => $preview_description ?: __('(Empty)', 'programmatic-seo'),
            'keywords' => $preview_keywords ?: __('(Empty)', 'programmatic-seo'),
        ));
    }

    /**
     * Delete template via AJAX
     */
    public function delete_template() {
        check_ajax_referer('delete_template', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $template_id = intval($_POST['id'] ?? 0);
        if (empty($template_id)) {
            wp_send_json_error(__('Template ID is required', 'programmatic-seo'));
        }

        $template_manager = Programmatic_SEO_Template_Manager::get_instance();
        if ($template_manager->delete_template($template_id)) {
            wp_send_json_success(array(
                'message' => __('Template deleted successfully', 'programmatic-seo'),
            ));
        } else {
            wp_send_json_error(__('Failed to delete template', 'programmatic-seo'));
        }
    }

    /**
     * Test data source
     */
    public function test_datasource() {
        check_ajax_referer('test_datasource', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $source_id = intval($_POST['id'] ?? 0);
        if (empty($source_id)) {
            wp_send_json_error(__('Source ID is required', 'programmatic-seo'));
        }

        $data_source = Programmatic_SEO_Data_Source::get_instance();
        $data = $data_source->fetch_data($source_id);

        if (is_wp_error($data)) {
            wp_send_json_error($data->get_error_message());
        }

        $preview = array_slice($data, 0, 5);

        wp_send_json_success(array(
            'total_items' => count($data),
            'preview_count' => count($preview),
            'sample_data' => $preview,
        ));
    }

    /**
     * Preview data source
     */
    public function preview_datasource() {
        $this->test_datasource();
    }

    /**
     * Sync data source
     */
    public function sync_datasource() {
        check_ajax_referer('sync_datasource', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $source_id = intval($_POST['id'] ?? 0);
        if (empty($source_id)) {
            wp_send_json_error(__('Source ID is required', 'programmatic-seo'));
        }

        $data_source = Programmatic_SEO_Data_Source::get_instance();
        $data = $data_source->fetch_data($source_id);

        if (is_wp_error($data)) {
            wp_send_json_error($data->get_error_message());
        }

        // Update last_synced timestamp
        global $wpdb;
        $table_name = Programmatic_SEO_Database::get_data_sources_table();
        $wpdb->update(
            $table_name,
            array('last_synced' => current_time('mysql')),
            array('id' => $source_id)
        );

        wp_send_json_success(array(
            'message' => sprintf(__('%d items synced successfully', 'programmatic-seo'), count($data)),
            'total_items' => count($data),
        ));
    }

    /**
     * Delete data source
     */
    public function delete_datasource() {
        check_ajax_referer('delete_datasource', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $source_id = intval($_POST['id'] ?? 0);
        if (empty($source_id)) {
            wp_send_json_error(__('Source ID is required', 'programmatic-seo'));
        }

        $data_source = Programmatic_SEO_Data_Source::get_instance();
        if ($data_source->delete_source($source_id)) {
            wp_send_json_success(array(
                'message' => __('Data source deleted successfully', 'programmatic-seo'),
            ));
        } else {
            wp_send_json_error(__('Failed to delete data source', 'programmatic-seo'));
        }
    }

    /**
     * Generate pages
     */
    public function generate_pages() {
        check_ajax_referer('prgr_seo_generate_pages', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        $source_id = intval($_POST['source_id'] ?? 0);

        if (empty($template_id) || empty($source_id)) {
            wp_send_json_error(__('Template and data source are required', 'programmatic-seo'));
        }

        $page_generator = Programmatic_SEO_Page_Generator::get_instance();
        $result = $page_generator->generate_pages($template_id, $source_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => sprintf(
                __('%d pages generated successfully, %d failed', 'programmatic-seo'),
                $result['success'],
                $result['failed']
            ),
            'success' => $result['success'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ));
    }

    /**
     * Delete template via admin_post
     */
    public function delete_template_post() {
        check_admin_referer('delete_template');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'programmatic-seo'));
        }

        $template_id = intval($_GET['id'] ?? 0);
        if (empty($template_id)) {
            wp_die(__('Template ID is required', 'programmatic-seo'));
        }

        $template_manager = Programmatic_SEO_Template_Manager::get_instance();
        if ($template_manager->delete_template($template_id)) {
            wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=programmatic-seo-templates')));
        } else {
            wp_redirect(add_query_arg('error', 'failed', admin_url('admin.php?page=programmatic-seo-templates')));
        }
        exit;
    }

    /**
     * Delete data source via admin_post
     */
    public function delete_datasource_post() {
        check_admin_referer('delete_datasource');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'programmatic-seo'));
        }

        $source_id = intval($_GET['id'] ?? 0);
        if (empty($source_id)) {
            wp_die(__('Source ID is required', 'programmatic-seo'));
        }

        $data_source = Programmatic_SEO_Data_Source::get_instance();
        if ($data_source->delete_source($source_id)) {
            wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=programmatic-seo-datasources')));
        } else {
            wp_redirect(add_query_arg('error', 'failed', admin_url('admin.php?page=programmatic-seo-datasources')));
        }
        exit;
    }
}
