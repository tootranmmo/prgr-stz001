<?php
/**
 * Schema/Structured Data Handler Class
 */

class Programmatic_SEO_Schema {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_schema'), 5);
    }

    /**
     * Output schema markup
     */
    public function output_schema() {
        global $post;

        if (!is_singular()) {
            $this->output_organization_schema();
            return;
        }

        if (is_singular('post')) {
            $this->output_article_schema($post->ID);
        } elseif (is_singular('page')) {
            $this->output_webpage_schema($post->ID);
        }

        $this->output_breadcrumb_schema();
    }

    /**
     * Output article schema
     */
    private function output_article_schema($post_id) {
        $post = get_post($post_id);
        $author = get_the_author_meta('display_name', $post->post_author);
        $image_url = '';

        if (has_post_thumbnail($post_id)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
            $image_url = $image[0] ?? '';
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => get_the_title($post_id),
            'description' => wp_strip_all_tags(get_the_excerpt($post_id)),
            'articleBody' => wp_strip_all_tags(get_the_content('', false, $post_id)),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => array(
                '@type' => 'Person',
                'name' => $author,
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url(),
                ),
            ),
        );

        if (!empty($image_url)) {
            $schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $image_url,
            );
        }

        $this->output_schema_markup($schema);
    }

    /**
     * Output webpage schema
     */
    private function output_webpage_schema($post_id) {
        $post = get_post($post_id);

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => get_the_title($post_id),
            'description' => wp_strip_all_tags(get_the_excerpt($post_id)),
            'url' => get_permalink($post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url(),
                ),
            ),
        );

        $this->output_schema_markup($schema);
    }

    /**
     * Output organization schema
     */
    private function output_organization_schema() {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
            'description' => get_bloginfo('description'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_site_icon_url(),
            ),
        );

        $this->output_schema_markup($schema);
    }

    /**
     * Output breadcrumb schema
     */
    private function output_breadcrumb_schema() {
        global $post;

        if (!is_singular() && !is_home()) {
            return;
        }

        $breadcrumbs = array(
            array(
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => home_url(),
            ),
        );

        if (is_home()) {
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Blog',
                'item' => get_permalink(get_option('page_for_posts')),
            );
        } elseif (is_singular('post')) {
            $categories = get_the_category($post->ID);

            if (!empty($categories)) {
                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $categories[0]->name,
                    'item' => get_category_link($categories[0]->term_id),
                );

                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => get_the_title($post->ID),
                    'item' => get_permalink($post->ID),
                );
            }
        } elseif (is_singular('page')) {
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title($post->ID),
                'item' => get_permalink($post->ID),
            );
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs,
        );

        $this->output_schema_markup($schema);
    }

    /**
     * Output schema markup as JSON-LD
     */
    private function output_schema_markup($schema) {
        echo "\n<!-- Programmatic SEO Schema Markup -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
        echo '</script>' . "\n";
        echo "<!-- End Programmatic SEO Schema Markup -->\n\n";
    }
}
