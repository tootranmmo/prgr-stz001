<?php
/**
 * XML Sitemap Handler Class
 */

class Programmatic_SEO_Sitemap {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_sitemap_request'));
        add_filter('robots_txt', array($this, 'add_sitemap_to_robots'));
    }

    /**
     * Add rewrite rules for sitemap
     */
    public function add_rewrite_rules() {
        // Already handled in main plugin class
    }

    /**
     * Handle sitemap requests
     */
    public function handle_sitemap_request() {
        $sitemap_type = get_query_var('programmatic_seo_sitemap');

        if ($sitemap_type) {
            // Set response headers
            header('Content-Type: application/xml; charset=UTF-8');
            header('Content-Disposition: inline; filename="sitemap.xml"');

            // Output sitemap
            $this->generate_sitemap($sitemap_type);
            exit;
        }
    }

    /**
     * Generate sitemap
     */
    private function generate_sitemap($type = '1') {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        if ($type === '1') {
            // Posts sitemap
            $this->add_posts_to_sitemap();
            // Add generated pages
            $this->add_generated_pages_to_sitemap();
        } elseif ($type === 'pages') {
            // Pages sitemap
            $this->add_pages_to_sitemap();
        } elseif ($type === 'categories') {
            // Categories sitemap
            $this->add_categories_to_sitemap();
        } elseif ($type === 'tags') {
            // Tags sitemap
            $this->add_tags_to_sitemap();
        } elseif ($type === 'generated') {
            // Generated pages sitemap
            $this->add_generated_pages_to_sitemap();
        }

        echo '</urlset>';
    }

    /**
     * Add posts to sitemap
     */
    private function add_posts_to_sitemap() {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->add_url_to_sitemap(get_the_permalink(), get_the_modified_date('c'));
            }
            wp_reset_postdata();
        }
    }

    /**
     * Add pages to sitemap
     */
    private function add_pages_to_sitemap() {
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->add_url_to_sitemap(get_the_permalink(), get_the_modified_date('c'));
            }
            wp_reset_postdata();
        }
    }

    /**
     * Add categories to sitemap
     */
    private function add_categories_to_sitemap() {
        $categories = get_categories(array(
            'hide_empty' => true,
        ));

        foreach ($categories as $category) {
            $this->add_url_to_sitemap(get_category_link($category->term_id));
        }
    }

    /**
     * Add tags to sitemap
     */
    private function add_tags_to_sitemap() {
        $tags = get_tags(array(
            'hide_empty' => true,
        ));

        foreach ($tags as $tag) {
            $this->add_url_to_sitemap(get_tag_link($tag->term_id));
        }
    }

    /**
     * Add single URL to sitemap
     */
    private function add_url_to_sitemap($url, $lastmod = '') {
        echo '  <url>' . "\n";
        echo '    <loc>' . esc_url($url) . '</loc>' . "\n";

        if (!empty($lastmod)) {
            echo '    <lastmod>' . esc_attr($lastmod) . '</lastmod>' . "\n";
        }

        echo '    <changefreq>weekly</changefreq>' . "\n";
        echo '    <priority>0.8</priority>' . "\n";
        echo '  </url>' . "\n";
    }

    /**
     * Add generated pages to sitemap
     */
    private function add_generated_pages_to_sitemap() {
        global $wpdb;
        $generated_pages_table = $wpdb->prefix . 'programmatic_seo_generated_pages';

        $results = $wpdb->get_results(
            "SELECT p.ID, p.post_modified FROM {$wpdb->posts} p
            INNER JOIN $generated_pages_table gp ON p.ID = gp.post_id
            WHERE p.post_status = 'publish'
            ORDER BY p.post_modified DESC"
        );

        if ($results) {
            foreach ($results as $post) {
                $this->add_url_to_sitemap(get_permalink($post->ID), get_date_from_gmt($post->post_modified, 'c'));
            }
        }
    }

    /**
     * Add sitemap to robots.txt
     */
    public function add_sitemap_to_robots($robots) {
        $robots .= "Sitemap: " . home_url('/sitemap.xml') . "\n";
        return $robots;
    }
}
