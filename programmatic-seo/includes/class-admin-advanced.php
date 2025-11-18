<?php
/**
 * Admin Pages for Advanced Features
 */

class Programmatic_SEO_Admin_Advanced {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_menus'));
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
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'programmatic-seo'));
        }

        $template_manager = Programmatic_SEO_Template_Manager::get_instance();
        $templates = $template_manager->get_templates();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=programmatic-seo-templates&action=new')); ?>" class="button button-primary">
                    <?php _e('Create New Template', 'programmatic-seo'); ?>
                </a>
            </p>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'programmatic-seo'); ?></th>
                        <th><?php _e('Slug', 'programmatic-seo'); ?></th>
                        <th><?php _e('Post Type', 'programmatic-seo'); ?></th>
                        <th><?php _e('Created', 'programmatic-seo'); ?></th>
                        <th><?php _e('Actions', 'programmatic-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($templates)): ?>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td><?php echo esc_html($template->name); ?></td>
                                <td><code><?php echo esc_html($template->slug); ?></code></td>
                                <td><?php echo esc_html($template->post_type); ?></td>
                                <td><?php echo esc_html($template->created_at); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=programmatic-seo-templates&action=edit&id=' . $template->id)); ?>">
                                        <?php _e('Edit', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=prgr_seo_delete_template&id=' . $template->id), 'delete_template')); ?>" onclick="return confirm('<?php _e('Are you sure?', 'programmatic-seo'); ?>')">
                                        <?php _e('Delete', 'programmatic-seo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5"><?php _e('No templates found. Create one to get started.', 'programmatic-seo'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render data sources page
     */
    public function render_datasources_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'programmatic-seo'));
        }

        $data_source = Programmatic_SEO_Data_Source::get_instance();
        $sources = $data_source->get_sources();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=programmatic-seo-datasources&action=new')); ?>" class="button button-primary">
                    <?php _e('Add New Data Source', 'programmatic-seo'); ?>
                </a>
            </p>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'programmatic-seo'); ?></th>
                        <th><?php _e('Type', 'programmatic-seo'); ?></th>
                        <th><?php _e('Last Synced', 'programmatic-seo'); ?></th>
                        <th><?php _e('Created', 'programmatic-seo'); ?></th>
                        <th><?php _e('Actions', 'programmatic-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sources)): ?>
                        <?php foreach ($sources as $source): ?>
                            <tr>
                                <td><?php echo esc_html($source->name); ?></td>
                                <td><span class="badge badge-<?php echo esc_attr($source->type); ?>"><?php echo esc_html(strtoupper($source->type)); ?></span></td>
                                <td><?php echo $source->last_synced ? esc_html($source->last_synced) : '—'; ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($source->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=programmatic-seo-datasources&action=edit&id=' . $source->id)); ?>">
                                        <?php _e('Edit', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=prgr_seo_sync_datasource&id=' . $source->id), 'sync_datasource')); ?>">
                                        <?php _e('Sync', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=prgr_seo_delete_datasource&id=' . $source->id), 'delete_datasource')); ?>" onclick="return confirm('<?php _e('Are you sure?', 'programmatic-seo'); ?>')">
                                        <?php _e('Delete', 'programmatic-seo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5"><?php _e('No data sources found. Add one to start generating pages.', 'programmatic-seo'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
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
            <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
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
}
