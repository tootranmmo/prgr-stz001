<?php
/**
 * Internal Linking System Class
 */

class Programmatic_SEO_Internal_Linking {
    private static $instance = null;
    private $wpdb;
    private $links_table;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->links_table = Programmatic_SEO_Database::get_links_table();

        add_filter('the_content', array($this, 'add_internal_links'), 20);
    }

    /**
     * Add internal links to post content
     */
    public function add_internal_links($content) {
        if (is_singular('post') && !is_admin()) {
            global $post;

            // Get internal links for this post
            $links = $this->get_post_internal_links($post->ID);

            if (!empty($links)) {
                // Add links to content
                foreach ($links as $link) {
                    $target_post = get_post($link->target_post_id);
                    if ($target_post) {
                        $link_html = sprintf(
                            '<a href="%s" title="%s">%s</a>',
                            esc_url(get_permalink($target_post->ID)),
                            esc_attr($target_post->post_title),
                            esc_html($link->anchor_text)
                        );

                        // Replace first occurrence of anchor text
                        $pattern = '/\b' . preg_quote($link->anchor_text, '/') . '\b/i';
                        $content = preg_replace($pattern, $link_html, $content, 1);
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Create internal link
     */
    public function create_link($source_post_id, $target_post_id, $anchor_text = '') {
        if ($source_post_id === $target_post_id) {
            return new WP_Error('same_post', 'Cannot link a post to itself');
        }

        if (empty($anchor_text)) {
            $anchor_text = get_the_title($target_post_id);
        }

        $result = $this->wpdb->insert(
            $this->links_table,
            array(
                'source_post_id' => $source_post_id,
                'target_post_id' => $target_post_id,
                'anchor_text' => sanitize_text_field($anchor_text),
                'link_type' => 'internal',
            )
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get internal links for a post
     */
    public function get_post_internal_links($post_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->links_table WHERE source_post_id = %d",
                $post_id
            )
        );
    }

    /**
     * Get links pointing to a post
     */
    public function get_post_backlinks($post_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->links_table WHERE target_post_id = %d",
                $post_id
            )
        );
    }

    /**
     * Auto-generate internal links based on content similarity
     */
    public function auto_generate_links($source_post_id, $limit = 5) {
        $source_post = get_post($source_post_id);
        if (!$source_post) {
            return false;
        }

        // Get keywords from source post
        $keywords = $this->extract_keywords($source_post->post_content);

        if (empty($keywords)) {
            return 0;
        }

        $links_created = 0;

        // Find similar posts
        foreach ($keywords as $keyword) {
            $related_posts = $this->find_related_posts($keyword, $source_post_id, $limit);

            foreach ($related_posts as $post) {
                // Check if link already exists
                $existing = $this->wpdb->get_row(
                    $this->wpdb->prepare(
                        "SELECT id FROM $this->links_table WHERE source_post_id = %d AND target_post_id = %d",
                        $source_post_id,
                        $post->ID
                    )
                );

                if (!$existing) {
                    $this->create_link($source_post_id, $post->ID, $keyword);
                    $links_created++;

                    if ($links_created >= $limit) {
                        break 2;
                    }
                }
            }
        }

        return $links_created;
    }

    /**
     * Find related posts based on keyword
     */
    private function find_related_posts($keyword, $exclude_id, $limit = 5) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT ID FROM {$this->wpdb->posts}
                WHERE post_status = 'publish'
                AND post_type = 'post'
                AND ID != %d
                AND (post_title LIKE %s OR post_content LIKE %s)
                LIMIT %d",
                $exclude_id,
                '%' . $this->wpdb->esc_like($keyword) . '%',
                '%' . $this->wpdb->esc_like($keyword) . '%',
                $limit
            )
        );
    }

    /**
     * Extract keywords from text
     */
    private function extract_keywords($text, $limit = 10) {
        // Remove HTML tags and special characters
        $text = wp_strip_all_tags($text);

        // Convert to lowercase and split into words
        $words = str_word_count(strtolower($text), 1);

        // Remove stop words
        $stop_words = array(
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'be',
        );

        $keywords = array_filter($words, function($word) use ($stop_words) {
            return !in_array($word, $stop_words) && strlen($word) > 3;
        });

        // Count frequencies
        $frequencies = array_count_values($keywords);

        // Sort by frequency
        arsort($frequencies);

        // Get top keywords
        return array_slice(array_keys($frequencies), 0, $limit);
    }

    /**
     * Delete internal link
     */
    public function delete_link($link_id) {
        return $this->wpdb->delete(
            $this->links_table,
            array('id' => $link_id)
        );
    }

    /**
     * Delete all links for a post
     */
    public function delete_post_links($post_id) {
        return $this->wpdb->delete(
            $this->links_table,
            array('source_post_id' => $post_id)
        );
    }

    /**
     * Get link statistics
     */
    public function get_link_stats() {
        $total_links = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM $this->links_table");

        $avg_links_per_post = (int) $this->wpdb->get_var(
            "SELECT AVG(link_count) FROM (
                SELECT COUNT(*) as link_count FROM $this->links_table GROUP BY source_post_id
            ) as counts"
        );

        return array(
            'total_links' => $total_links,
            'avg_links_per_post' => $avg_links_per_post,
        );
    }

    /**
     * Get posts with most internal links
     */
    public function get_top_linked_posts($limit = 10) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT p.ID, p.post_title, COUNT(*) as link_count
                FROM {$this->wpdb->posts} p
                INNER JOIN $this->links_table l ON p.ID = l.target_post_id
                WHERE p.post_status = 'publish'
                GROUP BY p.ID
                ORDER BY link_count DESC
                LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Update link anchor text
     */
    public function update_link_anchor($link_id, $anchor_text) {
        return $this->wpdb->update(
            $this->links_table,
            array('anchor_text' => sanitize_text_field($anchor_text)),
            array('id' => $link_id)
        );
    }

    /**
     * Batch create links for posts
     */
    public function batch_generate_links($post_ids = array(), $links_per_post = 5) {
        if (empty($post_ids)) {
            // Get all posts
            $posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ));
            $post_ids = $posts;
        }

        $total_generated = 0;

        foreach ($post_ids as $post_id) {
            $generated = $this->auto_generate_links($post_id, $links_per_post);
            $total_generated += $generated;
        }

        return $total_generated;
    }
}
