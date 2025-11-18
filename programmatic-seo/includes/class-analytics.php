<?php
/**
 * Performance Analytics Class
 */

class Programmatic_SEO_Analytics {
    private static $instance = null;
    private $wpdb;
    private $analytics_table;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->analytics_table = Programmatic_SEO_Database::get_analytics_table();

        add_action('wp_footer', array($this, 'track_page_view'));
    }

    /**
     * Track page view
     */
    public function track_page_view() {
        if (is_singular('post')) {
            global $post;
            $this->record_metric($post->ID, 'page_view', 1);
        }
    }

    /**
     * Record a metric
     */
    public function record_metric($post_id, $metric_type, $metric_value = 1) {
        $existing = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT id FROM $this->analytics_table
                WHERE post_id = %d AND metric_type = %s AND metric_date = %s",
                $post_id,
                $metric_type,
                current_time('Y-m-d')
            )
        );

        if ($existing) {
            return $this->wpdb->update(
                $this->analytics_table,
                array('metric_value' => $existing->metric_value + $metric_value),
                array('id' => $existing->id)
            );
        } else {
            return $this->wpdb->insert(
                $this->analytics_table,
                array(
                    'post_id' => $post_id,
                    'metric_type' => sanitize_text_field($metric_type),
                    'metric_value' => $metric_value,
                    'metric_date' => current_time('Y-m-d'),
                )
            );
        }
    }

    /**
     * Get page views for a post
     */
    public function get_page_views($post_id, $days = 30) {
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT SUM(metric_value) FROM $this->analytics_table
                WHERE post_id = %d
                AND metric_type = 'page_view'
                AND metric_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)",
                $post_id,
                $days
            )
        );
    }

    /**
     * Get analytics for a post
     */
    public function get_post_analytics($post_id) {
        $analytics = array(
            'post_id' => $post_id,
            'page_views_today' => (int) $this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SELECT SUM(metric_value) FROM $this->analytics_table
                    WHERE post_id = %d AND metric_type = 'page_view' AND metric_date = %s",
                    $post_id,
                    current_time('Y-m-d')
                )
            ),
            'page_views_30days' => $this->get_page_views($post_id, 30),
            'page_views_all_time' => (int) $this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SELECT SUM(metric_value) FROM $this->analytics_table
                    WHERE post_id = %d AND metric_type = 'page_view'",
                    $post_id
                )
            ),
        );

        return $analytics;
    }

    /**
     * Get top posts by views
     */
    public function get_top_posts($limit = 10, $days = 30) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT p.ID, p.post_title, SUM(a.metric_value) as total_views
                FROM {$this->wpdb->posts} p
                LEFT JOIN $this->analytics_table a ON p.ID = a.post_id
                WHERE p.post_status = 'publish'
                AND p.post_type = 'post'
                AND a.metric_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
                GROUP BY p.ID
                ORDER BY total_views DESC
                LIMIT %d",
                $days,
                $limit
            )
        );

        return $results ?: array();
    }

    /**
     * Get engagement rate for a post
     */
    public function get_engagement_rate($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return 0;
        }

        $comments = wp_count_comments($post_id);
        $views = $this->get_page_views($post_id, 30);

        if ($views === 0) {
            return 0;
        }

        return round(($comments->approved / $views) * 100, 2);
    }

    /**
     * Get average time on page (estimated)
     */
    public function estimate_avg_time_on_page($post_id) {
        $content = get_post_field('post_content', $post_id);
        $word_count = str_word_count(strip_tags($content));

        // Average reading speed is ~200 words per minute
        $minutes = $word_count / 200;

        return round($minutes * 60); // Return in seconds
    }

    /**
     * Get SEO score for a post
     */
    public function get_seo_score($post_id) {
        $score = 0;
        $max_score = 100;
        $post = get_post($post_id);

        // Title check (10 points)
        $title = get_post_meta($post_id, '_programmatic_seo_title', true) ?: $post->post_title;
        if (!empty($title) && strlen($title) >= 30 && strlen($title) <= 60) {
            $score += 10;
        } elseif (!empty($title)) {
            $score += 5;
        }

        // Description check (10 points)
        $description = get_post_meta($post_id, '_programmatic_seo_description', true);
        if (!empty($description) && strlen($description) >= 120 && strlen($description) <= 160) {
            $score += 10;
        } elseif (!empty($description)) {
            $score += 5;
        }

        // Keywords check (10 points)
        $keywords = get_post_meta($post_id, '_programmatic_seo_keywords', true);
        if (!empty($keywords)) {
            $score += 10;
        }

        // Featured image check (10 points)
        if (has_post_thumbnail($post_id)) {
            $score += 10;
        }

        // Content length check (15 points)
        $word_count = str_word_count(strip_tags($post->post_content));
        if ($word_count >= 300) {
            $score += 15;
        } elseif ($word_count >= 200) {
            $score += 10;
        }

        // Internal links check (15 points)
        $internal_links = Programmatic_SEO_Internal_Linking::get_instance()->get_post_internal_links($post_id);
        if (count($internal_links) >= 5) {
            $score += 15;
        } elseif (count($internal_links) >= 2) {
            $score += 10;
        }

        // Schema markup check (20 points)
        $schema = get_post_meta($post_id, '_programmatic_seo_schema', true);
        if (!empty($schema)) {
            $score += 20;
        }

        return min($score, $max_score);
    }

    /**
     * Get dashboard stats
     */
    public function get_dashboard_stats() {
        $stats = array(
            'total_posts' => (int) $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
            ),
            'total_page_views' => (int) $this->wpdb->get_var(
                "SELECT SUM(metric_value) FROM $this->analytics_table WHERE metric_type = 'page_view'"
            ),
            'avg_seo_score' => 0,
            'top_posts' => $this->get_top_posts(5),
        );

        // Calculate average SEO score
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'fields' => 'ids',
        ));

        if (!empty($posts)) {
            $total_score = 0;
            foreach ($posts as $post_id) {
                $total_score += $this->get_seo_score($post_id);
            }
            $stats['avg_seo_score'] = round($total_score / count($posts), 2);
        }

        return $stats;
    }

    /**
     * Get analytics data for chart
     */
    public function get_chart_data($post_id, $days = 30, $metric_type = 'page_view') {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT metric_date, SUM(metric_value) as value
                FROM $this->analytics_table
                WHERE post_id = %d
                AND metric_type = %s
                AND metric_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
                GROUP BY metric_date
                ORDER BY metric_date ASC",
                $post_id,
                $metric_type,
                $days
            )
        );

        $labels = array();
        $data = array();

        foreach ($results as $result) {
            $labels[] = $result->metric_date;
            $data[] = (int) $result->value;
        }

        return array(
            'labels' => $labels,
            'data' => $data,
        );
    }

    /**
     * Clean old analytics data
     */
    public function cleanup_old_data($days = 365) {
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM $this->analytics_table
                WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }
}
