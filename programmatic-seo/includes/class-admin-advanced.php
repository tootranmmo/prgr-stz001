<?php
/**
 * Admin Pages for Advanced Features
 */

class Programmatic_SEO_Admin_Advanced {
    private static $instance = null;
    private $template_ui;
    private $datasource_ui;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->template_ui = Programmatic_SEO_Template_UI::get_instance();
        $this->datasource_ui = Programmatic_SEO_DataSource_UI::get_instance();

        add_action('admin_menu', array($this, 'add_menus'));
        add_action('admin_post_save_template', array($this, 'handle_template_save'));
        add_action('admin_post_save_datasource', array($this, 'handle_datasource_save'));
    }

    /**
     * Add admin menus
     */
    public function add_menus() {
        // Templates submenu
        add_submenu_page(
            'programmatic-seo',
            __('Templates', 'programmatic-seo'),
            __('Templates', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo-templates',
            array($this, 'render_templates_page')
        );

        // Data Sources submenu
        add_submenu_page(
            'programmatic-seo',
            __('Data Sources', 'programmatic-seo'),
            __('Data Sources', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo-datasources',
            array($this, 'render_datasources_page')
        );

        // Page Generator submenu
        add_submenu_page(
            'programmatic-seo',
            __('Page Generator', 'programmatic-seo'),
            __('Page Generator', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo-generator',
            array($this, 'render_generator_page')
        );

        // Analytics submenu
        add_submenu_page(
            'programmatic-seo',
            __('Analytics', 'programmatic-seo'),
            __('Analytics', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo-analytics',
            array($this, 'render_analytics_page')
        );
    }

    /**
     * Render templates page
     */
    public function render_templates_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        if ($action === 'new' || $action === 'edit') {
            $this->template_ui->render_form_page();
        } else {
            $this->template_ui->render_list_page();
        }
    }

    /**
     * Render data sources page
     */
    public function render_datasources_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        if ($action === 'new' || $action === 'edit') {
            $this->datasource_ui->render_form_page();
        } else {
            $this->datasource_ui->render_list_page();
        }
    }

    /**
     * Handle template save
     */
    public function handle_template_save() {
        check_admin_referer('save_template');

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions', 'programmatic-seo'));
        }

        $template_manager = Programmatic_SEO_Template_Manager::get_instance();
        $action = sanitize_text_field($_POST['action'] ?? '');
        $template_id = intval($_POST['template_id'] ?? 0);

        $data = array(
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'slug' => sanitize_title($_POST['slug'] ?? ''),
            'description' => wp_kses_post($_POST['description'] ?? ''),
            'post_type' => sanitize_text_field($_POST['post_type'] ?? 'post'),
            'title_template' => wp_kses_post($_POST['title_template'] ?? ''),
            'description_template' => wp_kses_post($_POST['description_template'] ?? ''),
            'keywords_template' => wp_kses_post($_POST['keywords_template'] ?? ''),
            'content_template' => wp_kses_post($_POST['content_template'] ?? ''),
            'schema_template' => wp_kses_post($_POST['schema_template'] ?? ''),
            'featured_image_field' => sanitize_text_field($_POST['featured_image_field'] ?? ''),
        );

        // Validate
        if (empty($data['name'])) {
            $this->redirect_with_error(__('Template name is required', 'programmatic-seo'));
            return;
        }

        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        }

        // Auto-generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        }

        try {
            if ($action === 'edit' && $template_id) {
                $template_manager->update_template($template_id, $data);
                $this->redirect_with_message(__('Template updated successfully', 'programmatic-seo'));
            } else {
                $template_id = $template_manager->create_template($data);
                if ($template_id) {
                    $this->redirect_with_message(__('Template created successfully', 'programmatic-seo'), $template_id);
                } else {
                    $this->redirect_with_error(__('Failed to create template', 'programmatic-seo'));
                }
            }
        } catch (Exception $e) {
            $this->redirect_with_error($e->getMessage());
        }
    }

    /**
     * Handle data source save
     */
    public function handle_datasource_save() {
        check_admin_referer('save_datasource');

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions', 'programmatic-seo'));
        }

        $data_source = Programmatic_SEO_Data_Source::get_instance();
        $action = sanitize_text_field($_POST['action'] ?? '');
        $source_id = intval($_POST['datasource_id'] ?? 0);
        $type = sanitize_text_field($_POST['type'] ?? 'csv');

        $data = array(
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'type' => $type,
            'csv_file_path' => sanitize_file_name($_POST['csv_file_path'] ?? ''),
            'json_data' => wp_kses_post($_POST['json_data'] ?? '[]'),
            'api_endpoint' => esc_url_raw($_POST['api_endpoint'] ?? ''),
            'api_method' => sanitize_text_field($_POST['api_method'] ?? 'GET'),
            'api_headers' => wp_kses_post($_POST['api_headers'] ?? '{}'),
            'api_params' => wp_kses_post($_POST['api_params'] ?? '{}'),
            'field_mapping' => wp_kses_post($_POST['field_mapping'] ?? '{}'),
        );

        // Validate
        $validation = $data_source->validate_source($data);
        if ($validation !== true) {
            $this->redirect_with_error(implode(', ', $validation));
            return;
        }

        try {
            if ($action === 'edit' && $source_id) {
                $data_source->update_source($source_id, $data);
                $this->redirect_with_message(__('Data source updated successfully', 'programmatic-seo'));
            } else {
                $source_id = $data_source->create_source($data);
                if ($source_id) {
                    $this->redirect_with_message(__('Data source created successfully', 'programmatic-seo'));
                } else {
                    $this->redirect_with_error(__('Failed to create data source', 'programmatic-seo'));
                }
            }
        } catch (Exception $e) {
            $this->redirect_with_error($e->getMessage());
        }
    }

    /**
     * Render page generator page
     */
    public function render_generator_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'programmatic-seo'));
        }

        $page_generator = Programmatic_SEO_Page_Generator::get_instance();
        $template_manager = Programmatic_SEO_Template_Manager::get_instance();
        $data_source = Programmatic_SEO_Data_Source::get_instance();

        $templates = $template_manager->get_templates();
        $sources = $data_source->get_sources();
        $stats = $page_generator->get_stats();
        $generated_pages = $page_generator->get_generated_pages(array('limit' => 20));
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="programmatic-seo-dashboard">
                <div class="dashboard-cards">
                    <div class="card">
                        <h3><?php _e('Total Generated Pages', 'programmatic-seo'); ?></h3>
                        <p><?php echo esc_html($stats['total_generated_pages']); ?></p>
                    </div>
                    <div class="card">
                        <h3><?php _e('Templates', 'programmatic-seo'); ?></h3>
                        <p><?php echo esc_html($stats['total_templates']); ?></p>
                    </div>
                    <div class="card">
                        <h3><?php _e('Data Sources', 'programmatic-seo'); ?></h3>
                        <p><?php echo esc_html($stats['total_data_sources']); ?></p>
                    </div>
                </div>
            </div>

            <h2><?php _e('Generate Pages', 'programmatic-seo'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" class="programmatic-seo-form">
                <?php wp_nonce_field('prgr_seo_generate_pages'); ?>
                <input type="hidden" name="action" value="prgr_seo_generate_pages">

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="template_id"><?php _e('Select Template', 'programmatic-seo'); ?></label>
                            </th>
                            <td>
                                <select id="template_id" name="template_id" required>
                                    <option value=""><?php _e('Choose a template...', 'programmatic-seo'); ?></option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo esc_attr($template->id); ?>">
                                            <?php echo esc_html($template->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="source_id"><?php _e('Select Data Source', 'programmatic-seo'); ?></label>
                            </th>
                            <td>
                                <select id="source_id" name="source_id" required>
                                    <option value=""><?php _e('Choose a data source...', 'programmatic-seo'); ?></option>
                                    <?php foreach ($sources as $source): ?>
                                        <option value="<?php echo esc_attr($source->id); ?>">
                                            <?php echo esc_html($source->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Generate Pages', 'programmatic-seo')); ?>
            </form>

            <h2><?php _e('Generated Pages', 'programmatic-seo'); ?></h2>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Title', 'programmatic-seo'); ?></th>
                        <th><?php _e('Status', 'programmatic-seo'); ?></th>
                        <th><?php _e('Views', 'programmatic-seo'); ?></th>
                        <th><?php _e('Created', 'programmatic-seo'); ?></th>
                        <th><?php _e('Actions', 'programmatic-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($generated_pages)): ?>
                        <?php foreach ($generated_pages as $page): ?>
                            <tr>
                                <td><a href="<?php echo esc_url(get_permalink($page->post_id)); ?>" target="_blank"><?php echo esc_html($page->post->post_title); ?></a></td>
                                <td><?php echo esc_html(ucfirst($page->status)); ?></td>
                                <td><?php echo esc_html($page->view_count); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($page->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($page->post_id)); ?>">
                                        <?php _e('Edit', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(get_delete_post_link($page->post_id)); ?>">
                                        <?php _e('Delete', 'programmatic-seo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5"><?php _e('No generated pages yet. Create a template and data source to get started.', 'programmatic-seo'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'programmatic-seo'));
        }

        $analytics = Programmatic_SEO_Analytics::get_instance();
        $stats = $analytics->get_dashboard_stats();
        $top_posts = $stats['top_posts'];
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="programmatic-seo-dashboard">
                <div class="dashboard-cards">
                    <div class="card">
                        <h3><?php _e('Total Posts', 'programmatic-seo'); ?></h3>
                        <p><?php echo esc_html($stats['total_posts']); ?></p>
                    </div>
                    <div class="card">
                        <h3><?php _e('Total Page Views', 'programmatic-seo'); ?></h3>
                        <p><?php echo esc_html($stats['total_page_views'] ?: '0'); ?></p>
                    </div>
                    <div class="card">
                        <h3><?php _e('Avg SEO Score', 'programmatic-seo'); ?></h3>
                        <p><?php echo esc_html($stats['avg_seo_score']); ?>/100</p>
                    </div>
                </div>
            </div>

            <h2><?php _e('Top Performing Posts', 'programmatic-seo'); ?></h2>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Post Title', 'programmatic-seo'); ?></th>
                        <th><?php _e('Page Views (30 days)', 'programmatic-seo'); ?></th>
                        <th><?php _e('SEO Score', 'programmatic-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_posts)): ?>
                        <?php foreach ($top_posts as $post): ?>
                            <tr>
                                <td><a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank"><?php echo esc_html($post->post_title); ?></a></td>
                                <td><?php echo esc_html($post->total_views ?: '0'); ?></td>
                                <td>
                                    <div class="seo-score-bar">
                                        <div class="seo-score-fill" style="width: <?php echo esc_attr($analytics->get_seo_score($post->ID)); ?>%"></div>
                                    </div>
                                    <?php echo esc_html($analytics->get_seo_score($post->ID)); ?>/100
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3"><?php _e('No analytics data available yet.', 'programmatic-seo'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Redirect with message
     */
    private function redirect_with_message($message, $template_id = null) {
        $redirect_url = add_query_arg('message', urlencode($message), admin_url('admin.php?page=programmatic-seo-templates'));
        wp_safe_remote_post($redirect_url);
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Redirect with error
     */
    private function redirect_with_error($error) {
        $redirect_url = add_query_arg('error', urlencode($error), admin_url('admin.php?page=programmatic-seo-templates'));
        wp_safe_remote_post($redirect_url);
        wp_redirect($redirect_url);
        exit;
    }
}
