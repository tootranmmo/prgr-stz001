<?php
/**
 * Data Source Admin UI Handler
 */

class Programmatic_SEO_DataSource_UI {
    private static $instance = null;
    private $data_source;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->data_source = Programmatic_SEO_Data_Source::get_instance();
        add_action('wp_ajax_prgr_seo_test_datasource', array($this, 'ajax_test_datasource'));
        add_action('wp_ajax_prgr_seo_preview_datasource', array($this, 'ajax_preview_datasource'));
    }

    /**
     * Render data sources list page
     */
    public function render_list_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions', 'programmatic-seo'));
        }

        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $sources = $this->data_source->get_sources();
        $total = count($sources);
        $pages = ceil($total / $limit);
        $sources = array_slice($sources, $offset, $limit);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?>
                <a href="<?php echo esc_url(add_query_arg('action', 'new')); ?>" class="page-title-action">
                    <?php _e('Add New Data Source', 'programmatic-seo'); ?>
                </a>
            </h1>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'programmatic-seo'); ?></th>
                        <th><?php _e('Type', 'programmatic-seo'); ?></th>
                        <th><?php _e('Endpoint', 'programmatic-seo'); ?></th>
                        <th><?php _e('Last Synced', 'programmatic-seo'); ?></th>
                        <th><?php _e('Created', 'programmatic-seo'); ?></th>
                        <th><?php _e('Actions', 'programmatic-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sources)): ?>
                        <?php foreach ($sources as $source): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'id' => $source->id))); ?>">
                                            <?php echo esc_html($source->name); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo esc_attr($source->type); ?>">
                                        <?php echo esc_html(strtoupper($source->type)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $endpoint = '';
                                    if ($source->type === 'api') {
                                        $endpoint = parse_url($source->api_endpoint, PHP_URL_HOST);
                                    } elseif ($source->type === 'csv') {
                                        $endpoint = basename($source->csv_file_path);
                                    } else {
                                        $endpoint = '(inline)';
                                    }
                                    echo esc_html($endpoint);
                                    ?>
                                </td>
                                <td>
                                    <?php echo $source->last_synced ? esc_html(date_i18n(get_option('date_format'), strtotime($source->last_synced))) : '—'; ?>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($source->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'id' => $source->id))); ?>">
                                        <?php _e('Edit', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'test', 'id' => $source->id))); ?>">
                                        <?php _e('Test', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete', 'id' => $source->id, '_wpnonce' => wp_create_nonce('delete_datasource')))); ?>" onclick="return confirm('<?php _e('Are you sure?', 'programmatic-seo'); ?>');">
                                        <?php _e('Delete', 'programmatic-seo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="center"><?php _e('No data sources found. Create one to get started.', 'programmatic-seo'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $pages,
                            'current' => $page,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            .badge-csv { background: #e1f5f7; color: #00838f; }
            .badge-json { background: #f3e5f5; color: #6a1b9a; }
            .badge-api { background: #e8f5e9; color: #2e7d32; }
        </style>
        <?php
    }

    /**
     * Render data source form (create/edit)
     */
    public function render_form_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions', 'programmatic-seo'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'new';
        $source = null;

        if ($action === 'edit' && isset($_GET['id'])) {
            $source = $this->data_source->get_source(intval($_GET['id']));
            if (!$source) {
                wp_die(__('Data source not found', 'programmatic-seo'));
            }
        }

        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : ($source ? $source->type : 'csv');
        ?>
        <div class="wrap">
            <h1><?php echo $action === 'edit' ? __('Edit Data Source', 'programmatic-seo') : __('Create New Data Source', 'programmatic-seo'); ?></h1>

            <form method="post" action="<?php echo esc_url(admin_url('admin.php?action=save_datasource')); ?>" id="datasource-form" class="programmatic-seo-form" enctype="multipart/form-data">
                <?php wp_nonce_field('save_datasource'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
                <?php if ($source): ?>
                    <input type="hidden" name="datasource_id" value="<?php echo esc_attr($source->id); ?>">
                    <input type="hidden" name="type" value="<?php echo esc_attr($source->type); ?>">
                <?php endif; ?>

                <div class="form-section">
                    <h2><?php _e('Basic Information', 'programmatic-seo'); ?></h2>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="ds_name"><?php _e('Data Source Name', 'programmatic-seo'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="ds_name" name="name" value="<?php echo $source ? esc_attr($source->name) : ''; ?>" required class="regular-text" placeholder="e.g., Product CSV Import">
                                    <p class="description"><?php _e('A unique name to identify this data source', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="ds_type"><?php _e('Data Source Type', 'programmatic-seo'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <?php if ($source): ?>
                                        <input type="text" disabled class="regular-text" value="<?php echo esc_attr(strtoupper($source->type)); ?>">
                                        <p class="description"><?php _e('Cannot change type after creation', 'programmatic-seo'); ?></p>
                                    <?php else: ?>
                                        <select id="ds_type" name="type" onchange="showTypeOptions(this.value)" required>
                                            <option value="">— <?php _e('Select Type', 'programmatic-seo'); ?> —</option>
                                            <option value="csv" <?php selected($type, 'csv'); ?>><?php _e('CSV File', 'programmatic-seo'); ?></option>
                                            <option value="json" <?php selected($type, 'json'); ?>><?php _e('JSON Data', 'programmatic-seo'); ?></option>
                                            <option value="api" <?php selected($type, 'api'); ?>><?php _e('API Endpoint', 'programmatic-seo'); ?></option>
                                        </select>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- CSV Options -->
                <div id="csv-options" class="form-section" style="<?php echo $type !== 'csv' ? 'display: none;' : ''; ?>">
                    <h2><?php _e('CSV File Configuration', 'programmatic-seo'); ?></h2>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="csv_file"><?php _e('CSV File Path', 'programmatic-seo'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <?php if ($source): ?>
                                        <input type="text" id="csv_file" value="<?php echo esc_attr($source->csv_file_path); ?>" disabled class="regular-text">
                                    <?php else: ?>
                                        <input type="text" id="csv_file" name="csv_file_path" class="regular-text" placeholder="/path/to/file.csv">
                                    <?php endif; ?>
                                    <p class="description"><?php _e('Full path to the CSV file (e.g., /home/user/data.csv)', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- JSON Options -->
                <div id="json-options" class="form-section" style="<?php echo $type !== 'json' ? 'display: none;' : ''; ?>">
                    <h2><?php _e('JSON Data Configuration', 'programmatic-seo'); ?></h2>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="json_data"><?php _e('JSON Data', 'programmatic-seo'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <textarea id="json_data" name="json_data" rows="10" class="large-text code"><?php echo $source ? esc_textarea($source->json_data) : '[]'; ?></textarea>
                                    <p class="description"><?php _e('Paste your JSON data here. Should be an array of objects.', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- API Options -->
                <div id="api-options" class="form-section" style="<?php echo $type !== 'api' ? 'display: none;' : ''; ?>">
                    <h2><?php _e('API Configuration', 'programmatic-seo'); ?></h2>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="api_endpoint"><?php _e('API Endpoint URL', 'programmatic-seo'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="url" id="api_endpoint" name="api_endpoint" value="<?php echo $source ? esc_attr($source->api_endpoint) : ''; ?>" class="large-text" placeholder="https://api.example.com/data">
                                    <p class="description"><?php _e('Full URL to the API endpoint', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="api_method"><?php _e('HTTP Method', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <select id="api_method" name="api_method">
                                        <option value="GET" <?php selected($source ? $source->api_method : 'GET', 'GET'); ?>>GET</option>
                                        <option value="POST" <?php selected($source ? $source->api_method : 'GET', 'POST'); ?>>POST</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="api_headers"><?php _e('Headers (JSON)', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="api_headers" name="api_headers" rows="4" class="large-text code" placeholder='{"Authorization": "Bearer YOUR_TOKEN"}'><?php echo $source ? esc_textarea($source->api_headers) : '{}'; ?></textarea>
                                    <p class="description"><?php _e('Custom HTTP headers as JSON object', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="api_params"><?php _e('Parameters (JSON)', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="api_params" name="api_params" rows="4" class="large-text code" placeholder='{"limit": 100, "page": 1}'><?php echo $source ? esc_textarea($source->api_params) : '{}'; ?></textarea>
                                    <p class="description"><?php _e('Request parameters as JSON object', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Field Mapping -->
                <div class="form-section">
                    <h2><?php _e('Field Mapping', 'programmatic-seo'); ?></h2>
                    <p><?php _e('Map data fields to standard names for use in templates. Leave blank to use original field names.', 'programmatic-seo'); ?></p>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="field_mapping"><?php _e('Field Mapping (JSON)', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="field_mapping" name="field_mapping" rows="6" class="large-text code" placeholder='{"name": "product_name", "url": "product_url"}'><?php echo $source ? esc_textarea($source->field_mapping) : '{}'; ?></textarea>
                                    <p class="description">
                                        <?php _e('Map original fields to new names. Example: ', 'programmatic-seo'); ?>
                                        <code>{"target_field": "source_field"}</code>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Preview -->
                <div class="form-section">
                    <h2><?php _e('Data Preview', 'programmatic-seo'); ?></h2>
                    <button type="button" class="button button-secondary" onclick="testDataSource()">
                        <?php _e('Test & Preview Data', 'programmatic-seo'); ?>
                    </button>
                    <div id="preview-result" class="preview-box" style="display: none; margin-top: 15px;">
                        <h3><?php _e('Preview Result', 'programmatic-seo'); ?></h3>
                        <pre id="preview-content" style="background: #f5f5f5; padding: 10px; border-radius: 4px;"></pre>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <style>
            .form-section {
                background: white;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .form-section h2 {
                margin-top: 0;
                border-bottom: 2px solid #0073aa;
                padding-bottom: 10px;
            }
            .preview-box {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 4px;
            }
            .required {
                color: #d63638;
                font-weight: bold;
            }
            .code {
                font-family: 'Courier New', monospace;
            }
        </style>

        <script>
            function showTypeOptions(type) {
                document.getElementById('csv-options').style.display = type === 'csv' ? 'block' : 'none';
                document.getElementById('json-options').style.display = type === 'json' ? 'block' : 'none';
                document.getElementById('api-options').style.display = type === 'api' ? 'block' : 'none';
            }

            function testDataSource() {
                const type = document.getElementById('ds_type') ? document.getElementById('ds_type').value : '<?php echo esc_attr($type); ?>';
                const previewBox = document.getElementById('preview-result');
                const previewContent = document.getElementById('preview-content');

                previewContent.textContent = '<?php _e('Testing...', 'programmatic-seo'); ?>';
                previewBox.style.display = 'block';

                // This would be handled via AJAX in production
                previewContent.textContent = '<?php _e('Preview feature requires saving the data source first.', 'programmatic-seo'); ?>';
            }
        </script>
        <?php
    }

    /**
     * AJAX: Test data source
     */
    public function ajax_test_datasource() {
        check_ajax_referer('test_datasource');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $source_id = intval($_POST['source_id'] ?? 0);
        if (empty($source_id)) {
            wp_send_json_error(__('Source ID is required', 'programmatic-seo'));
        }

        $source = $this->data_source->get_source($source_id);
        if (!$source) {
            wp_send_json_error(__('Data source not found', 'programmatic-seo'));
        }

        // Fetch data
        $data = $this->data_source->fetch_data($source_id);

        if (is_wp_error($data)) {
            wp_send_json_error($data->get_error_message());
        }

        // Limit preview to 5 items
        $preview = array_slice($data, 0, 5);

        wp_send_json_success(array(
            'total_items' => count($data),
            'preview_count' => count($preview),
            'sample_data' => $preview,
        ));
    }

    /**
     * AJAX: Preview data source
     */
    public function ajax_preview_datasource() {
        check_ajax_referer('preview_datasource');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        // Similar to ajax_test_datasource but with different response format
        $this->ajax_test_datasource();
    }
}
