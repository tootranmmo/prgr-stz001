<?php
/**
 * Meta Tags Handler Class
 */

class Programmatic_SEO_Meta_Tags {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_meta_tags'), 1);
    }

    /**
     * Output meta tags in wp_head
     */
    public function output_meta_tags() {
        global $post;

        if (is_singular()) {
            $title = get_the_title($post->ID);
            $description = $this->get_meta_description($post->ID);
            $keywords = $this->get_meta_keywords($post->ID);
            $canonical = get_permalink($post->ID);

            // Output meta tags
            echo "\n<!-- Programmatic SEO Meta Tags -->\n";
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";

            if (!empty($keywords)) {
                echo '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
            }

            echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
            echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";
            echo "<!-- End Programmatic SEO Meta Tags -->\n\n";
        } elseif (is_home() || is_archive()) {
            echo "\n<!-- Programmatic SEO Meta Tags -->\n";
            echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";
            echo "<!-- End Programmatic SEO Meta Tags -->\n\n";
        }

        // Output Open Graph tags
        $this->output_og_tags();
    }

    /**
     * Get meta description
     */
    private function get_meta_description($post_id) {
        $description = get_post_meta($post_id, '_programmatic_seo_description', true);

        if (empty($description)) {
            $description = wp_strip_all_tags(get_the_excerpt($post_id));
        }

        if (empty($description)) {
            $description = wp_strip_all_tags(get_the_content($post_id));
        }

        // Truncate to 160 characters
        $description = substr($description, 0, 160);

        return !empty($description) ? $description : get_bloginfo('description');
    }

    /**
     * Get meta keywords
     */
    private function get_meta_keywords($post_id) {
        return get_post_meta($post_id, '_programmatic_seo_keywords', true);
    }

    /**
     * Output Open Graph tags
     */
    private function output_og_tags() {
        global $post;

        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '" />' . "\n";
        echo '<meta property="og:type" content="' . esc_attr($this->get_og_type()) . '" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($this->get_meta_description($post->ID ?? 0)) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_the_permalink()) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";

        if (has_post_thumbnail($post->ID ?? 0)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID ?? 0), 'full');
            if ($image) {
                echo '<meta property="og:image" content="' . esc_url($image[0]) . '" />' . "\n";
            }
        }

        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title()) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($this->get_meta_description($post->ID ?? 0)) . '" />' . "\n";
    }

    /**
     * Get Open Graph type
     */
    private function get_og_type() {
        global $post;

        if (is_home() || is_archive()) {
            return 'website';
        }

        if (is_singular('post')) {
            return 'article';
        }

        return 'website';
    }

    /**
     * Set meta tags via post meta
     */
    public static function set_post_meta($post_id, $field, $value) {
        update_post_meta($post_id, '_programmatic_seo_' . $field, $value);
    }

    /**
     * Get meta tags via post meta
     */
    public static function get_post_meta($post_id, $field) {
        return get_post_meta($post_id, '_programmatic_seo_' . $field, true);
    }
}
