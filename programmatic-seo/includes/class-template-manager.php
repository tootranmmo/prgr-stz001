<?php
/**
 * Template Management Class
 */

class Programmatic_SEO_Template_Manager {
    private static $instance = null;
    private $wpdb;
    private $table_name;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = Programmatic_SEO_Database::get_templates_table();
    }

    /**
     * Create a new template
     */
    public function create_template($data) {
        $defaults = array(
            'name' => '',
            'slug' => '',
            'description' => '',
            'post_type' => 'post',
            'title_template' => '',
            'description_template' => '',
            'keywords_template' => '',
            'content_template' => '',
            'schema_template' => '',
            'featured_image_field' => '',
        );

        $data = wp_parse_args($data, $defaults);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        }

        $result = $this->wpdb->insert(
            $this->table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'slug' => sanitize_title($data['slug']),
                'description' => wp_kses_post($data['description']),
                'post_type' => sanitize_text_field($data['post_type']),
                'title_template' => wp_kses_post($data['title_template']),
                'description_template' => wp_kses_post($data['description_template']),
                'keywords_template' => wp_kses_post($data['keywords_template']),
                'content_template' => wp_kses_post($data['content_template']),
                'schema_template' => wp_kses_post($data['schema_template']),
                'featured_image_field' => sanitize_text_field($data['featured_image_field']),
            )
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update a template
     */
    public function update_template($template_id, $data) {
        $template = $this->get_template($template_id);
        if (!$template) {
            return false;
        }

        $update_data = array();
        $allowed_fields = array(
            'name', 'slug', 'description', 'post_type',
            'title_template', 'description_template', 'keywords_template',
            'content_template', 'schema_template', 'featured_image_field'
        );

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = sanitize_text_field($data[$field]);
            }
        }

        if (empty($update_data)) {
            return false;
        }

        return $this->wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $template_id)
        );
    }

    /**
     * Get template by ID
     */
    public function get_template($template_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE id = %d",
                $template_id
            )
        );
    }

    /**
     * Get template by slug
     */
    public function get_template_by_slug($slug) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE slug = %s",
                $slug
            )
        );
    }

    /**
     * Get all templates
     */
    public function get_templates($args = array()) {
        $defaults = array(
            'offset' => 0,
            'limit' => 20,
            'order_by' => 'created_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM $this->table_name ORDER BY {$args['order_by']} {$args['order']}";

        if (!empty($args['limit'])) {
            $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        return $this->wpdb->get_results($query);
    }

    /**
     * Delete a template
     */
    public function delete_template($template_id) {
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => $template_id)
        );
    }

    /**
     * Process template with data
     */
    public function process_template($template_id, $data) {
        $template = $this->get_template($template_id);
        if (!$template) {
            return false;
        }

        $processed = array();

        // Process each template field
        $fields = array('title_template', 'description_template', 'keywords_template', 'content_template', 'schema_template');

        foreach ($fields as $field) {
            $processed[$field] = $this->replace_variables($template->$field, $data);
        }

        return $processed;
    }

    /**
     * Replace variables in template text
     */
    public function replace_variables($text, $data) {
        if (empty($text)) {
            return $text;
        }

        // Find all variables in format {{variable}}
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);

        if (empty($matches[1])) {
            return $text;
        }

        foreach ($matches[1] as $variable) {
            $variable = trim($variable);

            // Check if variable exists in data
            if (isset($data[$variable])) {
                $value = $data[$variable];

                // Handle array values (join with separator)
                if (is_array($value)) {
                    $value = implode(', ', array_map('sanitize_text_field', $value));
                }

                $value = sanitize_text_field($value);
                $text = str_replace('{{' . $variable . '}}', $value, $text);
            }
        }

        return $text;
    }

    /**
     * Get template variables
     */
    public function get_template_variables($template_id) {
        $template = $this->get_template($template_id);
        if (!$template) {
            return array();
        }

        $variables = array();
        $fields = array('title_template', 'description_template', 'keywords_template', 'content_template', 'schema_template');

        foreach ($fields as $field) {
            $field_vars = $this->extract_variables($template->$field);
            $variables = array_merge($variables, $field_vars);
        }

        return array_unique($variables);
    }

    /**
     * Extract variables from template text
     */
    private function extract_variables($text) {
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
        return !empty($matches[1]) ? array_map('trim', $matches[1]) : array();
    }

    /**
     * Duplicate a template
     */
    public function duplicate_template($template_id, $new_name = '') {
        $template = $this->get_template($template_id);
        if (!$template) {
            return false;
        }

        $new_template_data = array(
            'name' => $new_name ? $new_name : $template->name . ' (Copy)',
            'slug' => '',
            'description' => $template->description,
            'post_type' => $template->post_type,
            'title_template' => $template->title_template,
            'description_template' => $template->description_template,
            'keywords_template' => $template->keywords_template,
            'content_template' => $template->content_template,
            'schema_template' => $template->schema_template,
            'featured_image_field' => $template->featured_image_field,
        );

        return $this->create_template($new_template_data);
    }

    /**
     * Count templates
     */
    public function count_templates() {
        return (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $this->table_name"
        );
    }
}
