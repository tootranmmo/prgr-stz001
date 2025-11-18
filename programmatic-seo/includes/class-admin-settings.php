<?php
/**
 * Admin Settings Handler Class
 */

class Programmatic_SEO_Admin_Settings {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_settings_submenu'));
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('programmatic_seo_group', 'programmatic_seo_settings');
    }

    /**
     * Add settings submenu
     */
    public function add_settings_submenu() {
        add_submenu_page(
            'programmatic-seo',
            __('Settings', 'programmatic-seo'),
            __('Settings', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'programmatic-seo',
            __('Tools', 'programmatic-seo'),
            __('Tools', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo-tools',
            array($this, 'render_tools_page')
        );
    }

    /**
     * Render main admin page
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div class="programmatic-seo-dashboard">
                <div class="dashboard-cards">
                    <div class="card">
                        <h3><?php _e('Features Enabled', 'programmatic-seo'); ?></h3>
                        <p><?php echo $this->count_enabled_features(); ?>/5</p>
                    </div>
                    <div class="card">
                        <h3><?php _e('Meta Tags', 'programmatic-seo'); ?></h3>
                        <p><?php echo $this->is_feature_enabled('enable_meta_tags') ? '✓' : '✗'; ?></p>
                    </div>
                    <div class="card">
                        <h3><?php _e('XML Sitemap', 'programmatic-seo'); ?></h3>
                        <p><?php echo $this->is_feature_enabled('enable_sitemap') ? '✓' : '✗'; ?></p>
                    </div>
                    <div class="card">
                        <h3><?php _e('Schema', 'programmatic-seo'); ?></h3>
                        <p><?php echo $this->is_feature_enabled('enable_schema') ? '✓' : '✗'; ?></p>
                    </div>
                </div>
                <div class="dashboard-actions">
                    <a href="<?php echo admin_url('admin.php?page=programmatic-seo-settings'); ?>" class="button button-primary">
                        <?php _e('Go to Settings', 'programmatic-seo'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'programmatic-seo'));
        }

        $settings = get_option('programmatic_seo_settings', array());
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('programmatic_seo_group'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="enable_meta_tags">
                                    <?php _e('Enable Meta Tags', 'programmatic-seo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_meta_tags" name="programmatic_seo_settings[enable_meta_tags]" value="1"
                                    <?php checked(!empty($settings['enable_meta_tags'])); ?> />
                                <p class="description">
                                    <?php _e('Automatically generate and output SEO meta tags (description, keywords, canonical)', 'programmatic-seo'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="enable_sitemap">
                                    <?php _e('Enable XML Sitemap', 'programmatic-seo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_sitemap" name="programmatic_seo_settings[enable_sitemap]" value="1"
                                    <?php checked(!empty($settings['enable_sitemap'])); ?> />
                                <p class="description">
                                    <?php _e('Generate XML sitemaps for posts, pages, categories, and tags', 'programmatic-seo'); ?><br />
                                    <?php printf(__('Access at: <code>%s</code>', 'programmatic-seo'), esc_html(home_url('/sitemap.xml'))); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="enable_schema">
                                    <?php _e('Enable Structured Data (Schema)', 'programmatic-seo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_schema" name="programmatic_seo_settings[enable_schema]" value="1"
                                    <?php checked(!empty($settings['enable_schema'])); ?> />
                                <p class="description">
                                    <?php _e('Output JSON-LD structured data for better search engine understanding', 'programmatic-seo'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="enable_social_meta">
                                    <?php _e('Enable Social Media Meta Tags', 'programmatic-seo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_social_meta" name="programmatic_seo_settings[enable_social_meta]" value="1"
                                    <?php checked(!empty($settings['enable_social_meta'])); ?> />
                                <p class="description">
                                    <?php _e('Output Open Graph, Twitter Card, and other social media meta tags', 'programmatic-seo'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="enable_robots_txt">
                                    <?php _e('Enable Robots.txt Management', 'programmatic-seo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_robots_txt" name="programmatic_seo_settings[enable_robots_txt]" value="1"
                                    <?php checked(!empty($settings['enable_robots_txt'])); ?> />
                                <p class="description">
                                    <?php _e('Automatically add sitemap to robots.txt', 'programmatic-seo'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="twitter_handle">
                                    <?php _e('Twitter Handle', 'programmatic-seo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="twitter_handle" name="programmatic_seo_twitter_handle" value="<?php echo esc_attr(get_option('programmatic_seo_twitter_handle', '')); ?>" placeholder="@your_twitter_handle" />
                                <p class="description">
                                    <?php _e('Your Twitter handle for attribution in Twitter cards', 'programmatic-seo'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render tools page
     */
    public function render_tools_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'programmatic-seo'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="card">
                <h2><?php _e('Sitemap', 'programmatic-seo'); ?></h2>
                <p><?php _e('View and test your XML sitemaps:', 'programmatic-seo'); ?></p>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank">
                        <?php _e('Main Sitemap', 'programmatic-seo'); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(home_url('/sitemap-1.xml')); ?>" target="_blank">
                        <?php _e('Posts Sitemap', 'programmatic-seo'); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(home_url('/sitemap-pages.xml')); ?>" target="_blank">
                        <?php _e('Pages Sitemap', 'programmatic-seo'); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(home_url('/sitemap-categories.xml')); ?>" target="_blank">
                        <?php _e('Categories Sitemap', 'programmatic-seo'); ?>
                    </a></li>
                    <li><a href="<?php echo esc_url(home_url('/sitemap-tags.xml')); ?>" target="_blank">
                        <?php _e('Tags Sitemap', 'programmatic-seo'); ?>
                    </a></li>
                </ul>
            </div>

            <div class="card">
                <h2><?php _e('Test Tools', 'programmatic-seo'); ?></h2>
                <p><?php _e('External tools for testing your SEO:', 'programmatic-seo'); ?></p>
                <ul>
                    <li><a href="https://developers.google.com/search/mobile-friendly" target="_blank">
                        <?php _e('Google Mobile-Friendly Test', 'programmatic-seo'); ?>
                    </a></li>
                    <li><a href="https://search.google.com/structured-data/testing-tool" target="_blank">
                        <?php _e('Google Structured Data Testing Tool', 'programmatic-seo'); ?>
                    </a></li>
                    <li><a href="https://www.seomoz.org/tools/keyword-explorer" target="_blank">
                        <?php _e('Keyword Research Tool', 'programmatic-seo'); ?>
                    </a></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Check if feature is enabled
     */
    private function is_feature_enabled($feature) {
        $settings = get_option('programmatic_seo_settings', array());
        return isset($settings[$feature]) ? $settings[$feature] : false;
    }

    /**
     * Count enabled features
     */
    private function count_enabled_features() {
        $settings = get_option('programmatic_seo_settings', array());
        return count(array_filter($settings));
    }
}
