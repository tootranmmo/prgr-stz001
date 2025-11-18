<?php
/**
 * Database Migration and Management
 */

class Programmatic_SEO_Database {
    private static $instance = null;
    private $wpdb;
    private $charset_collate;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $this->wpdb->get_charset_collate();
    }

    /**
     * Create all necessary database tables
     */
    public function create_tables() {
        $this->create_templates_table();
        $this->create_data_sources_table();
        $this->create_generated_pages_table();
        $this->create_analytics_table();
        $this->create_internal_links_table();
    }

    /**
     * Create templates table
     */
    private function create_templates_table() {
        $table_name = $this->wpdb->prefix . 'programmatic_seo_templates';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description LONGTEXT,
            post_type VARCHAR(50) DEFAULT 'post',
            title_template LONGTEXT,
            description_template LONGTEXT,
            keywords_template LONGTEXT,
            content_template LONGTEXT,
            schema_template LONGTEXT,
            featured_image_field VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slug (slug)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create data sources table
     */
    private function create_data_sources_table() {
        $table_name = $this->wpdb->prefix . 'programmatic_seo_data_sources';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            type ENUM('csv', 'json', 'api') NOT NULL,
            source_url LONGTEXT,
            csv_file_path LONGTEXT,
            json_data LONGTEXT,
            api_endpoint VARCHAR(500),
            api_method VARCHAR(10) DEFAULT 'GET',
            api_headers LONGTEXT,
            api_params LONGTEXT,
            field_mapping LONGTEXT,
            last_synced DATETIME,
            sync_frequency INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create generated pages table
     */
    private function create_generated_pages_table() {
        $table_name = $this->wpdb->prefix . 'programmatic_seo_generated_pages';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            template_id BIGINT UNSIGNED NOT NULL,
            data_source_id BIGINT UNSIGNED,
            source_data LONGTEXT,
            status VARCHAR(50) DEFAULT 'published',
            view_count BIGINT UNSIGNED DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY template_id (template_id),
            FOREIGN KEY (post_id) REFERENCES " . $this->wpdb->prefix . "posts(ID) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create analytics table
     */
    private function create_analytics_table() {
        $table_name = $this->wpdb->prefix . 'programmatic_seo_analytics';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED,
            metric_type VARCHAR(100),
            metric_value INT DEFAULT 0,
            metric_date DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (metric_date),
            KEY metric_type (metric_type)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create internal links table
     */
    private function create_internal_links_table() {
        $table_name = $this->wpdb->prefix . 'programmatic_seo_links';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source_post_id BIGINT UNSIGNED NOT NULL,
            target_post_id BIGINT UNSIGNED NOT NULL,
            anchor_text VARCHAR(500),
            link_type VARCHAR(50) DEFAULT 'internal',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY source_post_id (source_post_id),
            KEY target_post_id (target_post_id),
            UNIQUE KEY unique_link (source_post_id, target_post_id)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Drop all plugin tables
     */
    public function drop_tables() {
        $tables = array(
            $this->wpdb->prefix . 'programmatic_seo_templates',
            $this->wpdb->prefix . 'programmatic_seo_data_sources',
            $this->wpdb->prefix . 'programmatic_seo_generated_pages',
            $this->wpdb->prefix . 'programmatic_seo_analytics',
            $this->wpdb->prefix . 'programmatic_seo_links',
        );

        foreach ($tables as $table) {
            $this->wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Get template table name
     */
    public static function get_templates_table() {
        global $wpdb;
        return $wpdb->prefix . 'programmatic_seo_templates';
    }

    /**
     * Get data sources table name
     */
    public static function get_data_sources_table() {
        global $wpdb;
        return $wpdb->prefix . 'programmatic_seo_data_sources';
    }

    /**
     * Get generated pages table name
     */
    public static function get_generated_pages_table() {
        global $wpdb;
        return $wpdb->prefix . 'programmatic_seo_generated_pages';
    }

    /**
     * Get analytics table name
     */
    public static function get_analytics_table() {
        global $wpdb;
        return $wpdb->prefix . 'programmatic_seo_analytics';
    }

    /**
     * Get internal links table name
     */
    public static function get_links_table() {
        global $wpdb;
        return $wpdb->prefix . 'programmatic_seo_links';
    }
}
