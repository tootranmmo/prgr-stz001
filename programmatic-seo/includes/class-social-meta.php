<?php
/**
 * Social Media Meta Tags Handler Class
 */

class Programmatic_SEO_Social_Meta {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_social_meta'), 10);
    }

    /**
     * Output social media meta tags
     */
    public function output_social_meta() {
        global $post;

        echo "\n<!-- Programmatic SEO Social Meta Tags -->\n";

        // Open Graph
        $this->output_open_graph();

        // Twitter Card
        $this->output_twitter_card();

        // LinkedIn
        $this->output_linkedin_meta();

        // Pinterest
        $this->output_pinterest_meta();

        echo "<!-- End Programmatic SEO Social Meta Tags -->\n\n";
    }

    /**
     * Output Open Graph meta tags
     */
    private function output_open_graph() {
        global $post;

        $title = get_the_title();
        $description = $this->get_description();
        $image = $this->get_featured_image();
        $url = get_the_permalink();
        $type = is_singular('post') ? 'article' : 'website';

        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        echo '<meta property="og:type" content="' . esc_attr($type) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";

        if (!empty($image)) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1200" />' . "\n";
            echo '<meta property="og:image:height" content="630" />' . "\n";
        }

        echo '<meta property="og:locale" content="' . esc_attr(str_replace('-', '_', get_locale())) . '" />' . "\n";
    }

    /**
     * Output Twitter Card meta tags
     */
    private function output_twitter_card() {
        $title = get_the_title();
        $description = $this->get_description();
        $image = $this->get_featured_image();

        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";

        if (!empty($image)) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
        }

        // Get Twitter handle from settings
        $twitter_handle = get_option('programmatic_seo_twitter_handle', '');
        if (!empty($twitter_handle)) {
            echo '<meta name="twitter:creator" content="' . esc_attr($twitter_handle) . '" />' . "\n";
        }
    }

    /**
     * Output LinkedIn meta tags
     */
    private function output_linkedin_meta() {
        $title = get_the_title();
        $description = $this->get_description();

        echo '<meta property="linkedin:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="linkedin:description" content="' . esc_attr($description) . '" />' . "\n";
    }

    /**
     * Output Pinterest meta tags
     */
    private function output_pinterest_meta() {
        $description = $this->get_description();
        $image = $this->get_featured_image();

        echo '<meta property="pinterest:description" content="' . esc_attr($description) . '" />' . "\n";

        if (!empty($image)) {
            echo '<meta property="pinterest:image" content="' . esc_url($image) . '" />' . "\n";
        }

        // Pinterest save button
        echo '<meta name="pinterest" content="nopin" />' . "\n";
    }

    /**
     * Get post description
     */
    private function get_description() {
        global $post;

        if (is_singular() && !empty($post)) {
            // Check for custom SEO description
            $description = get_post_meta($post->ID, '_programmatic_seo_description', true);

            if (empty($description)) {
                $description = wp_strip_all_tags(get_the_excerpt());
            }

            if (empty($description)) {
                $content = wp_strip_all_tags(get_the_content());
                $description = substr($content, 0, 160);
            }

            return !empty($description) ? $description : get_bloginfo('description');
        }

        return get_bloginfo('description');
    }

    /**
     * Get featured image
     */
    private function get_featured_image() {
        global $post;

        if (is_singular() && has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            return $image[0] ?? '';
        }

        // Return site icon as fallback
        return get_site_icon_url();
    }
}
