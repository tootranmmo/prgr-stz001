<?php
/**
 * Plugin Name: Programmatic SEO
 * Plugin URI: https://github.com/tootranmmo/programmatic-seo
 * Description: Advanced SEO plugin with template management, automatic page generation, meta automation, schema markup, internal linking, and analytics
 * Version: 2.0.0
 * Author: Programmatic SEO Team
 * Author URI: https://github.com/tootranmmo
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: programmatic-seo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PROGRAMMATIC_SEO_VERSION', '2.0.0');
define('PROGRAMMATIC_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PROGRAMMATIC_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PROGRAMMATIC_SEO_INCLUDES_DIR', PROGRAMMATIC_SEO_PLUGIN_DIR . 'includes/');

/**
 * Main Plugin Class
 */
class Programmatic_SEO {
    /**
     * Instance of the class
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_textdomain();
        $this->require_classes();
        $this->init_hooks();
    }

    /**
     * Load plugin text domain for translations
     */
    private function load_textdomain() {
        load_plugin_textdomain(
            'programmatic-seo',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Require all plugin classes
     */
    private function require_classes() {
        // Core classes
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-database.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-meta-tags.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-sitemap.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-schema.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-social-meta.php';

        // Advanced features
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-template-manager.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-data-source.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-page-generator.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-seo-automation.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-schema-generator.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-internal-linking.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-analytics.php';

        // Admin & UI
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-admin-settings.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-template-ui.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-datasource-ui.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-ajax-handlers.php';
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-admin-advanced.php';
    }

    /**
     * Initialize plugin hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Load plugin features
        add_action('wp_loaded', array($this, 'load_features'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Init features
        add_action('init', array($this, 'init_features'));
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        // Load database class
        require_once PROGRAMMATIC_SEO_INCLUDES_DIR . 'class-database.php';

        // Create database tables
        $db = Programmatic_SEO_Database::get_instance();
        $db->create_tables();

        // Create necessary plugin options
        if (!get_option('programmatic_seo_settings')) {
            add_option('programmatic_seo_settings', array(
                'enable_meta_tags' => true,
                'enable_sitemap' => true,
                'enable_schema' => true,
                'enable_social_meta' => true,
                'enable_robots_txt' => true,
                'enable_templates' => true,
                'enable_page_generator' => true,
                'enable_internal_linking' => true,
                'enable_analytics' => true,
            ));
        }

        // Flush rewrite rules for sitemap
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }

    /**
     * Load plugin features
     */
    public function load_features() {
        // Initialize Meta Tags handler
        if ($this->is_feature_enabled('enable_meta_tags')) {
            Programmatic_SEO_Meta_Tags::get_instance();
        }

        // Initialize Sitemap handler
        if ($this->is_feature_enabled('enable_sitemap')) {
            Programmatic_SEO_Sitemap::get_instance();
        }

        // Initialize Schema handler
        if ($this->is_feature_enabled('enable_schema')) {
            Programmatic_SEO_Schema::get_instance();
        }

        // Initialize Social Meta handler
        if ($this->is_feature_enabled('enable_social_meta')) {
            Programmatic_SEO_Social_Meta::get_instance();
        }

        // Initialize advanced features
        if ($this->is_feature_enabled('enable_templates')) {
            Programmatic_SEO_Template_Manager::get_instance();
        }

        if ($this->is_feature_enabled('enable_page_generator')) {
            Programmatic_SEO_Page_Generator::get_instance();
        }

        if ($this->is_feature_enabled('enable_seo_automation')) {
            Programmatic_SEO_Automation::get_instance();
        }

        if ($this->is_feature_enabled('enable_schema_generator')) {
            Programmatic_SEO_Schema_Generator::get_instance();
        }

        if ($this->is_feature_enabled('enable_internal_linking')) {
            Programmatic_SEO_Internal_Linking::get_instance();
        }

        if ($this->is_feature_enabled('enable_analytics')) {
            Programmatic_SEO_Analytics::get_instance();
        }

        // Always initialize admin advanced
        if (is_admin()) {
            Programmatic_SEO_Admin_Advanced::get_instance();
            Programmatic_SEO_AJAX_Handlers::get_instance();
        }
    }

    /**
     * Initialize features
     */
    public function init_features() {
        // Add rewrite rules
        if ($this->is_feature_enabled('enable_sitemap')) {
            add_rewrite_rule('^sitemap\.xml$', 'index.php?programmatic_seo_sitemap=1', 'top');
            add_rewrite_rule('^sitemap-([^/]+)\.xml$', 'index.php?programmatic_seo_sitemap=$matches[1]', 'top');
        }

        // Query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
    }

    /**
     * Add query variables
     */
    public function add_query_vars($vars) {
        $vars[] = 'programmatic_seo_sitemap';
        return $vars;
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Programmatic SEO', 'programmatic-seo'),
            __('Programmatic SEO', 'programmatic-seo'),
            'manage_options',
            'programmatic-seo',
            array($this, 'render_admin_page'),
            'dashicons-chart-line',
            25
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        Programmatic_SEO_Admin_Settings::get_instance()->render_page();
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'programmatic-seo') !== false) {
            wp_enqueue_style(
                'programmatic-seo-admin',
                PROGRAMMATIC_SEO_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                PROGRAMMATIC_SEO_VERSION
            );
            wp_enqueue_script(
                'programmatic-seo-admin',
                PROGRAMMATIC_SEO_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                PROGRAMMATIC_SEO_VERSION,
                true
            );
        }
    }

    /**
     * Check if feature is enabled
     */
    private function is_feature_enabled($feature) {
        $settings = get_option('programmatic_seo_settings', array());
        return isset($settings[$feature]) ? $settings[$feature] : false;
    }
}

/**
 * Initialize plugin
 */
function programmatic_seo_init() {
    return Programmatic_SEO::get_instance();
}

// Start plugin
programmatic_seo_init();
