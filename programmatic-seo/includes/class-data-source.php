<?php
/**
 * Data Source Integration Class
 */

class Programmatic_SEO_Data_Source {
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
        $this->table_name = Programmatic_SEO_Database::get_data_sources_table();
    }

    /**
     * Create a new data source
     */
    public function create_source($data) {
        $defaults = array(
            'name' => '',
            'type' => 'csv',
            'source_url' => '',
            'csv_file_path' => '',
            'json_data' => '',
            'api_endpoint' => '',
            'api_method' => 'GET',
            'api_headers' => '',
            'api_params' => '',
            'field_mapping' => '',
        );

        $data = wp_parse_args($data, $defaults);

        $result = $this->wpdb->insert(
            $this->table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'type' => sanitize_text_field($data['type']),
                'source_url' => esc_url_raw($data['source_url']),
                'csv_file_path' => sanitize_file_name($data['csv_file_path']),
                'json_data' => wp_json_encode($data['json_data']),
                'api_endpoint' => esc_url_raw($data['api_endpoint']),
                'api_method' => sanitize_text_field($data['api_method']),
                'api_headers' => wp_json_encode($data['api_headers']),
                'api_params' => wp_json_encode($data['api_params']),
                'field_mapping' => wp_json_encode($data['field_mapping']),
            )
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get data source by ID
     */
    public function get_source($source_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE id = %d",
                $source_id
            )
        );
    }

    /**
     * Get all data sources
     */
    public function get_sources() {
        return $this->wpdb->get_results(
            "SELECT * FROM $this->table_name ORDER BY created_at DESC"
        );
    }

    /**
     * Fetch data from source
     */
    public function fetch_data($source_id) {
        $source = $this->get_source($source_id);
        if (!$source) {
            return new WP_Error('source_not_found', 'Data source not found');
        }

        switch ($source->type) {
            case 'csv':
                return $this->fetch_csv_data($source);
            case 'json':
                return $this->fetch_json_data($source);
            case 'api':
                return $this->fetch_api_data($source);
            default:
                return new WP_Error('invalid_type', 'Invalid data source type');
        }
    }

    /**
     * Fetch data from CSV file
     */
    private function fetch_csv_data($source) {
        $file_path = $source->csv_file_path;

        // Check if file exists
        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', 'CSV file not found: ' . $file_path);
        }

        $data = array();
        if (($handle = fopen($file_path, 'r')) !== false) {
            $headers = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $item = array_combine($headers, $row);
                    $item = array_map('trim', $item);
                    $data[] = $item;
                }
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * Fetch data from JSON
     */
    private function fetch_json_data($source) {
        $json_data = $source->json_data;

        if (empty($json_data)) {
            return array();
        }

        $data = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_json', 'Invalid JSON data: ' . json_last_error_msg());
        }

        // Ensure data is an array of items
        if (isset($data[0]) && is_array($data[0])) {
            return $data;
        }

        return array($data);
    }

    /**
     * Fetch data from API
     */
    private function fetch_api_data($source) {
        $endpoint = $source->api_endpoint;
        $method = strtoupper($source->api_method);

        if (empty($endpoint)) {
            return new WP_Error('invalid_endpoint', 'API endpoint is required');
        }

        $args = array(
            'method' => $method,
            'timeout' => 30,
            'redirection' => 5,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        );

        // Add headers
        if (!empty($source->api_headers)) {
            $headers = json_decode($source->api_headers, true);
            if (is_array($headers)) {
                $args['headers'] = $headers;
            }
        }

        // Add body for POST requests
        if ($method === 'POST' && !empty($source->api_params)) {
            $params = json_decode($source->api_params, true);
            if (is_array($params)) {
                $args['body'] = wp_json_encode($params);
            }
        }

        $response = wp_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_response', 'Invalid JSON response from API');
        }

        // Handle paginated responses
        if (isset($data['items'])) {
            return $data['items'];
        } elseif (isset($data['data'])) {
            return $data['data'];
        } elseif (is_array($data[0] ?? null)) {
            return $data;
        }

        return array($data);
    }

    /**
     * Apply field mapping to data
     */
    public function apply_mapping($data, $source_id) {
        $source = $this->get_source($source_id);
        if (!$source || empty($source->field_mapping)) {
            return $data;
        }

        $mapping = json_decode($source->field_mapping, true);
        if (!is_array($mapping)) {
            return $data;
        }

        // If data is an array of items
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            return array_map(function($item) use ($mapping) {
                return $this->map_item_fields($item, $mapping);
            }, $data);
        }

        return $this->map_item_fields($data, $mapping);
    }

    /**
     * Map individual item fields
     */
    private function map_item_fields($item, $mapping) {
        $mapped = array();

        foreach ($mapping as $mapped_field => $source_field) {
            if (isset($item[$source_field])) {
                $mapped[$mapped_field] = $item[$source_field];
            }
        }

        return $mapped;
    }

    /**
     * Validate data source
     */
    public function validate_source($data) {
        $errors = array();

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['type']) || !in_array($data['type'], array('csv', 'json', 'api'))) {
            $errors[] = 'Valid type is required';
        }

        if ($data['type'] === 'csv' && empty($data['csv_file_path'])) {
            $errors[] = 'CSV file path is required';
        }

        if ($data['type'] === 'api' && empty($data['api_endpoint'])) {
            $errors[] = 'API endpoint is required';
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Update data source
     */
    public function update_source($source_id, $data) {
        $validation = $this->validate_source($data);
        if ($validation !== true) {
            return new WP_Error('validation_failed', implode(', ', $validation));
        }

        return $this->wpdb->update(
            $this->table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'type' => sanitize_text_field($data['type']),
                'csv_file_path' => sanitize_file_name($data['csv_file_path'] ?? ''),
                'api_endpoint' => esc_url_raw($data['api_endpoint'] ?? ''),
                'api_method' => sanitize_text_field($data['api_method'] ?? 'GET'),
            ),
            array('id' => $source_id)
        );
    }

    /**
     * Delete data source
     */
    public function delete_source($source_id) {
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => $source_id)
        );
    }
}
