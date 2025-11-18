<?php
/**
 * Schema Markup Generator Class
 */

class Programmatic_SEO_Schema_Generator {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', array($this, 'output_custom_schema'), 15);
    }

    /**
     * Output custom schema markup
     */
    public function output_custom_schema() {
        global $post;

        if (!is_singular()) {
            return;
        }

        // Get custom schema from post meta
        $custom_schema = get_post_meta($post->ID, '_programmatic_seo_schema', true);

        if (!empty($custom_schema)) {
            // If it's already JSON, output it
            if (is_string($custom_schema) && substr($custom_schema, 0, 1) === '{') {
                echo "\n<!-- Programmatic SEO Custom Schema -->\n";
                echo '<script type="application/ld+json">' . "\n";
                echo $custom_schema . "\n";
                echo '</script>' . "\n";
                echo "<!-- End Programmatic SEO Custom Schema -->\n\n";
            } else {
                // Generate schema from data
                $schema = $this->generate_schema_from_post($post);
                if (!empty($schema)) {
                    $this->output_schema_json($schema);
                }
            }
        } else {
            // Auto-generate schema
            $schema = $this->generate_schema_from_post($post);
            if (!empty($schema)) {
                $this->output_schema_json($schema);
            }
        }
    }

    /**
     * Generate schema from post
     */
    public function generate_schema_from_post($post) {
        if (!$post) {
            return array();
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->post_title,
            'description' => wp_strip_all_tags($post->post_excerpt ?: substr($post->post_content, 0, 160)),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
        );

        // Add author
        $author = get_the_author_meta('display_name', $post->post_author);
        if (!empty($author)) {
            $schema['author'] = array(
                '@type' => 'Person',
                'name' => $author,
            );
        }

        // Add publisher
        $schema['publisher'] = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_site_icon_url(),
            ),
        );

        // Add image
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            if ($image) {
                $schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                );
            }
        }

        return apply_filters('programmatic_seo_generated_schema', $schema, $post);
    }

    /**
     * Output schema JSON
     */
    private function output_schema_json($schema) {
        echo "\n<!-- Programmatic SEO Auto Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
        echo '</script>' . "\n";
        echo "<!-- End Programmatic SEO Auto Schema -->\n\n";
    }

    /**
     * Generate Product schema
     */
    public function generate_product_schema($post_id, $product_data) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product_data['name'] ?? get_the_title($post_id),
            'description' => $product_data['description'] ?? wp_strip_all_tags(get_the_excerpt($post_id)),
            'sku' => $product_data['sku'] ?? '',
            'brand' => array(
                '@type' => 'Brand',
                'name' => $product_data['brand'] ?? get_bloginfo('name'),
            ),
        );

        // Add price if available
        if (!empty($product_data['price'])) {
            $schema['offers'] = array(
                '@type' => 'Offer',
                'url' => get_permalink($post_id),
                'priceCurrency' => $product_data['currency'] ?? 'USD',
                'price' => $product_data['price'],
                'availability' => 'https://schema.org/' . ($product_data['in_stock'] ? 'InStock' : 'OutOfStock'),
            );
        }

        // Add rating if available
        if (!empty($product_data['rating']) && !empty($product_data['review_count'])) {
            $schema['aggregateRating'] = array(
                '@type' => 'AggregateRating',
                'ratingValue' => $product_data['rating'],
                'reviewCount' => $product_data['review_count'],
            );
        }

        return apply_filters('programmatic_seo_product_schema', $schema, $post_id);
    }

    /**
     * Generate Event schema
     */
    public function generate_event_schema($post_id, $event_data) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event_data['name'] ?? get_the_title($post_id),
            'description' => $event_data['description'] ?? wp_strip_all_tags(get_the_excerpt($post_id)),
            'startDate' => $event_data['start_date'] ?? '',
            'endDate' => $event_data['end_date'] ?? '',
            'url' => get_permalink($post_id),
        );

        // Add location
        if (!empty($event_data['location'])) {
            $schema['location'] = array(
                '@type' => 'Place',
                'name' => $event_data['location'],
                'address' => array(
                    '@type' => 'PostalAddress',
                    'streetAddress' => $event_data['street'] ?? '',
                    'addressLocality' => $event_data['city'] ?? '',
                    'addressRegion' => $event_data['state'] ?? '',
                    'postalCode' => $event_data['postal_code'] ?? '',
                ),
            );
        }

        // Add organizer
        if (!empty($event_data['organizer'])) {
            $schema['organizer'] = array(
                '@type' => 'Organization',
                'name' => $event_data['organizer'],
                'url' => $event_data['organizer_url'] ?? get_home_url(),
            );
        }

        return apply_filters('programmatic_seo_event_schema', $schema, $post_id);
    }

    /**
     * Generate Recipe schema
     */
    public function generate_recipe_schema($post_id, $recipe_data) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => $recipe_data['name'] ?? get_the_title($post_id),
            'description' => $recipe_data['description'] ?? wp_strip_all_tags(get_the_excerpt($post_id)),
            'author' => array(
                '@type' => 'Person',
                'name' => $recipe_data['author'] ?? get_the_author_meta('display_name', get_post_field('post_author', $post_id)),
            ),
            'prepTime' => $recipe_data['prep_time'] ?? '',
            'cookTime' => $recipe_data['cook_time'] ?? '',
            'totalTime' => $recipe_data['total_time'] ?? '',
            'recipeYield' => $recipe_data['yield'] ?? '',
        );

        // Add ingredients
        if (!empty($recipe_data['ingredients']) && is_array($recipe_data['ingredients'])) {
            $schema['recipeIngredient'] = $recipe_data['ingredients'];
        }

        // Add instructions
        if (!empty($recipe_data['instructions']) && is_array($recipe_data['instructions'])) {
            $schema['recipeInstructions'] = array_map(function($instruction) {
                return array(
                    '@type' => 'HowToStep',
                    'text' => $instruction,
                );
            }, $recipe_data['instructions']);
        }

        // Add rating
        if (!empty($recipe_data['rating'])) {
            $schema['aggregateRating'] = array(
                '@type' => 'AggregateRating',
                'ratingValue' => $recipe_data['rating'],
                'reviewCount' => $recipe_data['review_count'] ?? 1,
            );
        }

        return apply_filters('programmatic_seo_recipe_schema', $schema, $post_id);
    }

    /**
     * Generate Article schema
     */
    public function generate_article_schema($post_id, $article_data = array()) {
        $post = get_post($post_id);

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->post_title,
            'alternativeHeadline' => $article_data['alternative_headline'] ?? '',
            'description' => wp_strip_all_tags($post->post_excerpt ?: substr($post->post_content, 0, 160)),
            'articleBody' => wp_strip_all_tags($post->post_content),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
        );

        // Add author
        $author = get_the_author_meta('display_name', $post->post_author);
        if (!empty($author)) {
            $schema['author'] = array(
                '@type' => 'Person',
                'name' => $author,
            );
        }

        // Add image
        if (has_post_thumbnail($post_id)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
            if ($image) {
                $schema['image'] = $image[0];
            }
        }

        return apply_filters('programmatic_seo_article_schema', $schema, $post_id);
    }

    /**
     * Save custom schema for post
     */
    public function save_schema($post_id, $schema_type, $schema_data = array()) {
        $method = 'generate_' . $schema_type . '_schema';

        if (!method_exists($this, $method)) {
            return new WP_Error('invalid_type', 'Invalid schema type: ' . $schema_type);
        }

        $schema = $this->$method($post_id, $schema_data);

        if (empty($schema)) {
            return new WP_Error('generation_failed', 'Failed to generate schema');
        }

        update_post_meta($post_id, '_programmatic_seo_schema_type', $schema_type);
        update_post_meta($post_id, '_programmatic_seo_schema', wp_json_encode($schema));

        return true;
    }

    /**
     * Get available schema types
     */
    public function get_available_schemas() {
        return array(
            'blog_posting' => 'BlogPosting',
            'article' => 'Article',
            'product' => 'Product',
            'event' => 'Event',
            'recipe' => 'Recipe',
        );
    }
}
