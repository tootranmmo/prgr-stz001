<?php
/**
 * Template Admin UI Handler
 */

class Programmatic_SEO_Template_UI {
    private static $instance = null;
    private $template_manager;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->template_manager = Programmatic_SEO_Template_Manager::get_instance();
        add_action('wp_ajax_prgr_seo_validate_template', array($this, 'ajax_validate_template'));
        add_action('wp_ajax_prgr_seo_preview_template', array($this, 'ajax_preview_template'));
    }

    /**
     * Render templates list page
     */
    public function render_list_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions', 'programmatic-seo'));
        }

        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $templates = $this->template_manager->get_templates(array(
            'offset' => $offset,
            'limit' => $limit,
        ));

        $total = $this->template_manager->count_templates();
        $pages = ceil($total / $limit);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?>
                <a href="<?php echo esc_url(add_query_arg('action', 'new')); ?>" class="page-title-action">
                    <?php _e('Add New Template', 'programmatic-seo'); ?>
                </a>
            </h1>

            <div class="tablenav top">
                <form method="get" id="posts-filter">
                    <input type="hidden" name="page" value="programmatic-seo-templates">
                    <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search templates...', 'programmatic-seo'); ?>">
                    <?php submit_button(__('Search', 'programmatic-seo'), 'button', false, false); ?>
                </form>
            </div>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'programmatic-seo'); ?></th>
                        <th><?php _e('Slug', 'programmatic-seo'); ?></th>
                        <th><?php _e('Post Type', 'programmatic-seo'); ?></th>
                        <th><?php _e('Variables', 'programmatic-seo'); ?></th>
                        <th><?php _e('Created', 'programmatic-seo'); ?></th>
                        <th><?php _e('Actions', 'programmatic-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($templates)): ?>
                        <?php foreach ($templates as $template): ?>
                            <?php $variables = $this->template_manager->get_template_variables($template->id); ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'id' => $template->id))); ?>">
                                            <?php echo esc_html($template->name); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><code><?php echo esc_html($template->slug); ?></code></td>
                                <td><?php echo esc_html($template->post_type); ?></td>
                                <td><?php echo count($variables); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($template->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'id' => $template->id))); ?>">
                                        <?php _e('Edit', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'duplicate', 'id' => $template->id, '_wpnonce' => wp_create_nonce('duplicate_template')))); ?>">
                                        <?php _e('Duplicate', 'programmatic-seo'); ?>
                                    </a> |
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete', 'id' => $template->id, '_wpnonce' => wp_create_nonce('delete_template')))); ?>" onclick="return confirm('<?php _e('Are you sure?', 'programmatic-seo'); ?>');">
                                        <?php _e('Delete', 'programmatic-seo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="center"><?php _e('No templates found. Create one to get started.', 'programmatic-seo'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $pages,
                            'current' => $page,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render template form (create/edit)
     */
    public function render_form_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions', 'programmatic-seo'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'new';
        $template = null;

        if ($action === 'edit' && isset($_GET['id'])) {
            $template = $this->template_manager->get_template(intval($_GET['id']));
            if (!$template) {
                wp_die(__('Template not found', 'programmatic-seo'));
            }
        }

        $post_types = array('post' => 'Post', 'page' => 'Page');
        ?>
        <div class="wrap">
            <h1><?php echo $action === 'edit' ? __('Edit Template', 'programmatic-seo') : __('Create New Template', 'programmatic-seo'); ?></h1>

            <form method="post" id="template-form" class="programmatic-seo-form">
                <?php wp_nonce_field('save_template'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
                <?php if ($template): ?>
                    <input type="hidden" name="template_id" value="<?php echo esc_attr($template->id); ?>">
                <?php endif; ?>

                <div class="form-section">
                    <h2><?php _e('Basic Information', 'programmatic-seo'); ?></h2>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="template_name"><?php _e('Template Name', 'programmatic-seo'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="template_name" name="name" value="<?php echo $template ? esc_attr($template->name) : ''; ?>" required class="regular-text" placeholder="e.g., Product Listing">
                                    <p class="description"><?php _e('A unique name to identify this template', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="template_slug"><?php _e('Template Slug', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="template_slug" name="slug" value="<?php echo $template ? esc_attr($template->slug) : ''; ?>" class="regular-text" placeholder="e.g., product-listing">
                                    <p class="description"><?php _e('Leave empty to auto-generate from name', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="post_type"><?php _e('Post Type', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <select id="post_type" name="post_type">
                                        <?php foreach ($post_types as $key => $label): ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($template ? $template->post_type : 'post', $key); ?>>
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="description"><?php _e('Description', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="description" name="description" rows="3" class="large-text"><?php echo $template ? esc_textarea($template->description) : ''; ?></textarea>
                                    <p class="description"><?php _e('Optional description of what this template is for', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-section">
                    <h2><?php _e('Template Fields', 'programmatic-seo'); ?></h2>
                    <p class="info-box">
                        <strong><?php _e('Tip:', 'programmatic-seo'); ?></strong>
                        <?php _e('Use {{variable}} syntax to insert variables. Example: {{product_name}}, {{price}}, {{description}}', 'programmatic-seo'); ?>
                    </p>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="title_template"><?php _e('Page Title Template', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="title_template" name="title_template" rows="2" class="large-text"><?php echo $template ? esc_textarea($template->title_template) : ''; ?></textarea>
                                    <p class="description"><?php _e('Template for page title (max 60 characters recommended)', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="description_template"><?php _e('Meta Description Template', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="description_template" name="description_template" rows="2" class="large-text"><?php echo $template ? esc_textarea($template->description_template) : ''; ?></textarea>
                                    <p class="description"><?php _e('Template for meta description (120-160 characters)', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="keywords_template"><?php _e('Keywords Template', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="keywords_template" name="keywords_template" rows="2" class="large-text"><?php echo $template ? esc_textarea($template->keywords_template) : ''; ?></textarea>
                                    <p class="description"><?php _e('Comma-separated keywords template', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="content_template"><?php _e('Page Content Template', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $template ? $template->content_template : '',
                                        'content_template',
                                        array(
                                            'textarea_rows' => 15,
                                            'teeny' => false,
                                        )
                                    );
                                    ?>
                                    <p class="description"><?php _e('Full HTML content template for page body', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="schema_template"><?php _e('Schema/JSON Template', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <textarea id="schema_template" name="schema_template" rows="5" class="large-text code"><?php echo $template ? esc_textarea($template->schema_template) : '{}'; ?></textarea>
                                    <p class="description"><?php _e('JSON Schema data (leave empty to auto-generate)', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="featured_image_field"><?php _e('Featured Image Field', 'programmatic-seo'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="featured_image_field" name="featured_image_field" value="<?php echo $template ? esc_attr($template->featured_image_field) : ''; ?>" class="regular-text" placeholder="e.g., image_url">
                                    <p class="description"><?php _e('Name of the data field containing image URL', 'programmatic-seo'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-section">
                    <h2><?php _e('Preview', 'programmatic-seo'); ?></h2>
                    <p><?php _e('See how your template looks with sample variables', 'programmatic-seo'); ?></p>
                    <div id="template-preview" class="preview-box">
                        <p><?php _e('Preview will appear here...', 'programmatic-seo'); ?></p>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <style>
            .form-section {
                background: white;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .form-section h2 {
                margin-top: 0;
                border-bottom: 2px solid #0073aa;
                padding-bottom: 10px;
            }
            .info-box {
                background: #f0f6fc;
                border-left: 4px solid #0073aa;
                padding: 12px;
                margin: 15px 0;
            }
            .preview-box {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 4px;
                min-height: 100px;
            }
            .required {
                color: #d63638;
                font-weight: bold;
            }
        </style>
        <?php
    }

    /**
     * AJAX: Validate template
     */
    public function ajax_validate_template() {
        check_ajax_referer('validate_template');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        $slug = sanitize_title($_POST['slug'] ?? '');

        $errors = array();

        if (empty($name)) {
            $errors['name'] = __('Template name is required', 'programmatic-seo');
        }

        if (empty($slug)) {
            $slug = sanitize_title($name);
        }

        // Check if slug already exists (excluding current template)
        $existing = $this->template_manager->get_template_by_slug($slug);
        if ($existing && (!isset($_POST['template_id']) || $existing->id !== intval($_POST['template_id']))) {
            $errors['slug'] = __('This slug already exists. Please choose a different one.', 'programmatic-seo');
        }

        if (empty($errors)) {
            wp_send_json_success(array(
                'slug' => $slug,
                'message' => __('Template is valid', 'programmatic-seo'),
            ));
        } else {
            wp_send_json_error($errors);
        }
    }

    /**
     * AJAX: Preview template
     */
    public function ajax_preview_template() {
        check_ajax_referer('preview_template');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'programmatic-seo'));
        }

        $title = sanitize_text_field($_POST['title_template'] ?? '');
        $description = sanitize_text_field($_POST['description_template'] ?? '');
        $keywords = sanitize_text_field($_POST['keywords_template'] ?? '');

        // Sample data for preview
        $sample_data = array(
            'product_name' => 'Sample Product',
            'price' => '$99.99',
            'description' => 'This is a sample description',
            'category' => 'Electronics',
        );

        // Process templates
        $preview_title = $this->template_manager->replace_variables($title, $sample_data);
        $preview_description = $this->template_manager->replace_variables($description, $sample_data);
        $preview_keywords = $this->template_manager->replace_variables($keywords, $sample_data);

        wp_send_json_success(array(
            'title' => $preview_title,
            'description' => $preview_description,
            'keywords' => $preview_keywords,
        ));
    }
}
