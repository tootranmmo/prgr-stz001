<?php
/**
 * SEO Meta Automation Class
 */

class Programmatic_SEO_Automation {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('save_post', array($this, 'auto_generate_meta'), 20, 1);
    }

    /**
     * Auto-generate SEO meta when post is saved
     */
    public function auto_generate_meta($post_id) {
        // Avoid infinite loops
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $post = get_post($post_id);
        if (!$post || in_array($post->post_status, array('trash', 'auto-draft'))) {
            return;
        }

        // Only auto-generate if not already set
        $existing_title = get_post_meta($post_id, '_programmatic_seo_title', true);
        $existing_description = get_post_meta($post_id, '_programmatic_seo_description', true);

        if (empty($existing_title)) {
            $auto_title = $this->generate_title($post);
            update_post_meta($post_id, '_programmatic_seo_title', $auto_title);
        }

        if (empty($existing_description)) {
            $auto_description = $this->generate_description($post);
            update_post_meta($post_id, '_programmatic_seo_description', $auto_description);
        }

        // Auto-generate keywords
        $auto_keywords = $this->generate_keywords($post);
        if (!empty($auto_keywords)) {
            update_post_meta($post_id, '_programmatic_seo_keywords', $auto_keywords);
        }
    }

    /**
     * Generate SEO title from post data
     */
    public function generate_title($post) {
        $title = $post->post_title;

        // Add category if post has categories
        if ('post' === $post->post_type) {
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $title .= ' | ' . $categories[0]->name;
            }
        }

        // Add site name
        $title .= ' | ' . get_bloginfo('name');

        // Ensure title length is between 50-60 characters
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }

        return apply_filters('programmatic_seo_auto_title', $title, $post);
    }

    /**
     * Generate SEO description from post data
     */
    public function generate_description($post) {
        $description = '';

        // Try to use excerpt first
        if (!empty($post->post_excerpt)) {
            $description = $post->post_excerpt;
        } else {
            // Generate from post content
            $content = strip_tags($post->post_content);
            $description = substr($content, 0, 160);
        }

        // Clean up description
        $description = preg_replace('/\s+/', ' ', trim($description));

        // Ensure description is 150-160 characters
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }

        return apply_filters('programmatic_seo_auto_description', $description, $post);
    }

    /**
     * Generate SEO keywords from post data
     */
    public function generate_keywords($post) {
        $keywords = array();

        // Get from tags
        if ('post' === $post->post_type) {
            $tags = get_the_tags($post->ID);
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    $keywords[] = $tag->name;
                }
            }
        }

        // Get from categories
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $keywords[] = $category->name;
            }
        }

        // Extract keywords from title
        $title_keywords = $this->extract_keywords_from_text($post->post_title);
        $keywords = array_merge($keywords, $title_keywords);

        // Remove duplicates and limit to 5 keywords
        $keywords = array_unique(array_slice(array_filter($keywords), 0, 5));

        return apply_filters('programmatic_seo_auto_keywords', implode(', ', $keywords), $post);
    }

    /**
     * Extract keywords from text
     */
    private function extract_keywords_from_text($text) {
        // Remove common words
        $stop_words = array(
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'be',
            'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will',
            'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that'
        );

        $words = str_word_count(strtolower($text), 1);
        $words = array_filter($words, function($word) use ($stop_words) {
            return !in_array($word, $stop_words) && strlen($word) > 3;
        });

        return array_unique($words);
    }

    /**
     * Batch auto-generate meta for existing posts
     */
    public function batch_generate_meta($post_type = 'post', $limit = 100) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_programmatic_seo_description',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $query = new WP_Query($args);
        $count = 0;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $this->auto_generate_meta($post->ID);
                $count++;
            }
            wp_reset_postdata();
        }

        return $count;
    }

    /**
     * Get keywords from content analysis
     */
    public function analyze_keywords($text) {
        // Count word frequencies
        $words = str_word_count(strtolower($text), 1);

        $word_freq = array_count_values($words);

        // Remove stop words
        $stop_words = array(
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were'
        );

        foreach ($stop_words as $word) {
            unset($word_freq[$word]);
        }

        // Sort by frequency
        arsort($word_freq);

        // Get top 10 keywords
        return array_slice(array_keys($word_freq), 0, 10);
    }

    /**
     * Generate readability score
     */
    public function calculate_readability_score($text) {
        // Simple readability score based on sentence and word length
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count($text);

        if (empty($sentences) || $words === 0) {
            return 0;
        }

        $avg_sentence_length = $words / count($sentences);
        $score = 100 - ($avg_sentence_length * 5);

        return max(0, min(100, $score));
    }

    /**
     * Check meta quality
     */
    public function check_meta_quality($post_id) {
        $post = get_post($post_id);
        $title = get_post_meta($post_id, '_programmatic_seo_title', true) ?: $post->post_title;
        $description = get_post_meta($post_id, '_programmatic_seo_description', true);
        $keywords = get_post_meta($post_id, '_programmatic_seo_keywords', true);

        $issues = array();
        $score = 100;

        // Check title
        if (empty($title)) {
            $issues[] = 'Missing SEO title';
            $score -= 20;
        } elseif (strlen($title) < 30) {
            $issues[] = 'SEO title is too short (less than 30 characters)';
            $score -= 10;
        } elseif (strlen($title) > 60) {
            $issues[] = 'SEO title is too long (more than 60 characters)';
            $score -= 10;
        }

        // Check description
        if (empty($description)) {
            $issues[] = 'Missing SEO description';
            $score -= 20;
        } elseif (strlen($description) < 120) {
            $issues[] = 'SEO description is too short (less than 120 characters)';
            $score -= 10;
        } elseif (strlen($description) > 160) {
            $issues[] = 'SEO description is too long (more than 160 characters)';
            $score -= 10;
        }

        // Check keywords
        if (empty($keywords)) {
            $issues[] = 'Missing keywords';
            $score -= 10;
        }

        return array(
            'score' => max(0, $score),
            'issues' => $issues,
        );
    }
}
