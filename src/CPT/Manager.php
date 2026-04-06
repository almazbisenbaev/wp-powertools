<?php
/**
 * CPT Manager functionality
 *
 * @package PowerTools
 */

namespace PowerTools\CPT;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Manager
 */
class Manager {
    /**
     * Option name for storing custom post types
     *
     * @var string
     */
    private $option_name = 'powertools_cptm_custom_post_types';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_custom_post_types'));
        add_action('admin_post_powertools_cptm_add', array($this, 'handle_add_post_type'));
        add_action('admin_post_powertools_cptm_edit', array($this, 'handle_edit_post_type'));
        add_action('admin_post_powertools_cptm_delete', array($this, 'handle_delete_post_type'));
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        $custom_post_types = get_option($this->option_name, []);
        $edit_mode = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : '';

        if (!is_array($custom_post_types)) {
            $custom_post_types = [];
        }

        $supports         = $edit_mode && isset($custom_post_types[$edit_mode]['supports']) ? $custom_post_types[$edit_mode]['supports'] : ['title', 'editor', 'thumbnail'];
        $support_options  = ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'];
        ?>

        ?>
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-category" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <h1 class="pt-h1"><?php esc_html_e('CPT Manager', 'powertools'); ?></h1>
                    <p class="pt-p">
                        <?php esc_html_e('Create and manage custom post types for your WordPress site with ease.', 'powertools'); ?>
                    </p>
                </div>
            </header>

            <?php if (isset($_GET['error_message'])): ?>
                <div class="pt-badge pt-badge-warning" style="margin-bottom: 24px; width: 100%; box-sizing: border-box;">
                    <span class="dashicons dashicons-dismiss"></span>
                    <?php echo esc_html(urldecode($_GET['error_message'])); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success_message'])): ?>
                <div class="pt-badge pt-badge-success" style="margin-bottom: 24px; width: 100%; box-sizing: border-box;">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php echo esc_html(urldecode($_GET['success_message'])); ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 32px; align-items: flex-start;">
                <!-- Form -->
                <div class="pt-settings-container">
                    <div class="pt-settings-header">
                        <h2 class="pt-h2">
                            <?php echo $edit_mode ? esc_html__('Edit Post Type', 'powertools') : esc_html__('Add New Post Type', 'powertools'); ?>
                        </h2>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'powertools_cptm_edit' : 'powertools_cptm_add'; ?>">
                        <?php wp_nonce_field($edit_mode ? 'powertools_cptm_edit_nonce_action' : 'powertools_cptm_add_nonce_action', $edit_mode ? 'powertools_cptm_edit_nonce' : 'powertools_cptm_add_nonce'); ?>

                        <div class="pt-settings-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                                <div class="pt-form-group">
                                    <label class="pt-form-label" for="cptm_post_type_name">
                                        <?php esc_html_e('Post Type Slug', 'powertools'); ?>
                                        <span style="color: var(--pt-danger);">*</span>
                                    </label>
                                    <input class="pt-form-control" id="cptm_post_type_name" type="text"
                                           name="powertools_cptm_post_type_name"
                                           value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['name']) : ''; ?>"
                                           placeholder="e.g. portfolio"
                                           style="font-family: monospace;"
                                           <?php echo $edit_mode ? 'readonly' : 'required'; ?> />
                                    <p class="pt-text-muted" style="font-size: 12px; margin-top: 4px;"><?php esc_html_e('Lowercase, underscores only. Max 20 chars.', 'powertools'); ?></p>
                                </div>

                                <div class="pt-form-group">
                                    <label class="pt-form-label" for="cptm_menu_icon">
                                        <?php esc_html_e('Menu Icon', 'powertools'); ?>
                                    </label>
                                    <input class="pt-form-control" id="cptm_menu_icon" type="text"
                                           name="powertools_cptm_menu_icon"
                                           value="<?php echo $edit_mode && isset($custom_post_types[$edit_mode]['menu_icon']) ? esc_attr($custom_post_types[$edit_mode]['menu_icon']) : ''; ?>"
                                           placeholder="dashicons-admin-post"
                                           style="font-family: monospace;" />
                                    <p class="pt-text-muted" style="font-size: 12px; margin-top: 4px;">
                                        <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" style="color: var(--pt-primary); text-decoration: none;"><?php esc_html_e('Browse Dashicons →', 'powertools'); ?></a>
                                    </p>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                                <div class="pt-form-group">
                                    <label class="pt-form-label" for="cptm_singular_label">
                                        <?php esc_html_e('Singular Label', 'powertools'); ?>
                                        <span style="color: var(--pt-danger);">*</span>
                                    </label>
                                    <input class="pt-form-control" id="cptm_singular_label" type="text"
                                           name="powertools_cptm_singular_label"
                                           value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['singular_label']) : ''; ?>"
                                           placeholder="<?php esc_attr_e('e.g. Project', 'powertools'); ?>"
                                           required />
                                </div>

                                <div class="pt-form-group">
                                    <label class="pt-form-label" for="cptm_plural_label">
                                        <?php esc_html_e('Plural Label', 'powertools'); ?>
                                        <span style="color: var(--pt-danger);">*</span>
                                    </label>
                                    <input class="pt-form-control" id="cptm_plural_label" type="text"
                                           name="powertools_cptm_plural_label"
                                           value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['plural_label']) : ''; ?>"
                                           placeholder="<?php esc_attr_e('e.g. Projects', 'powertools'); ?>"
                                           required />
                                </div>
                            </div>

                            <div style="background: var(--pt-bg-page); padding: 24px; border-radius: var(--pt-radius); margin-bottom: 32px; border: 1px solid var(--pt-border-soft);">
                                <h4 style="margin: 0 0 16px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--pt-text-muted);"><?php esc_html_e('Settings & Capabilities', 'powertools'); ?></h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px;">
                                    <label class="pt-checkbox-label" style="padding: 0;">
                                        <input type="checkbox" name="powertools_cptm_public" value="1"
                                               <?php checked($edit_mode ? $custom_post_types[$edit_mode]['public'] : true); ?> />
                                        <span style="font-weight: 500;"><?php esc_html_e('Public', 'powertools'); ?></span>
                                    </label>
                                    <label class="pt-checkbox-label" style="padding: 0;">
                                        <input type="checkbox" name="powertools_cptm_has_archive" value="1"
                                               <?php checked($edit_mode ? $custom_post_types[$edit_mode]['has_archive'] : true); ?> />
                                        <span style="font-weight: 500;"><?php esc_html_e('Has Archive', 'powertools'); ?></span>
                                    </label>
                                    <label class="pt-checkbox-label" style="padding: 0;">
                                        <input type="checkbox" name="powertools_cptm_hierarchical" value="1"
                                               <?php checked($edit_mode && isset($custom_post_types[$edit_mode]['hierarchical']) ? $custom_post_types[$edit_mode]['hierarchical'] : false); ?> />
                                        <span style="font-weight: 500;"><?php esc_html_e('Hierarchical', 'powertools'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="pt-form-group">
                                <label class="pt-form-label"><?php esc_html_e('Supports', 'powertools'); ?></label>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                                    <?php foreach ($support_options as $option): ?>
                                        <label class="pt-checkbox-label" style="padding: 8px 12px; border: 1px solid var(--pt-border-soft); background: #fff;">
                                            <input type="checkbox" name="powertools_cptm_supports[]"
                                                   value="<?php echo esc_attr($option); ?>"
                                                   <?php checked(in_array($option, $supports)); ?> />
                                            <span style="font-size: 13px; font-weight: 500;"><?php echo esc_html(ucfirst(str_replace('-', ' ', $option))); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="pt-settings-footer">
                            <?php if ($edit_mode): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-cpt-manager')); ?>" class="pt-btn pt-btn-secondary">
                                    <?php esc_html_e('Cancel', 'powertools'); ?>
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="pt-btn pt-btn-primary">
                                <?php echo $edit_mode ? esc_attr__('Update Post Type', 'powertools') : esc_attr__('Create Post Type', 'powertools'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- List -->
                <div class="pt-settings-container">
                    <div class="pt-settings-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 class="pt-h2" style="margin: 0;"><?php esc_html_e('Post Types', 'powertools'); ?></h2>
                        <span style="background: var(--pt-primary); color: #fff; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                            <?php echo count($custom_post_types); ?>
                        </span>
                    </div>

                    <div class="pt-settings-body" style="padding: 0;">
                        <?php if (empty($custom_post_types)): ?>
                            <div style="padding: 48px 32px; text-align: center;">
                                <span class="dashicons dashicons-category" style="font-size: 32px; width: 32px; height: 32px; color: var(--pt-text-light); margin-bottom: 16px;"></span>
                                <p class="pt-text-muted"><?php esc_html_e('No custom post types yet.', 'powertools'); ?></p>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
                                    <thead>
                                        <tr>
                                            <th style="padding: 16px 24px; font-weight: 600; background: var(--pt-bg-page);"><?php esc_html_e('Label', 'powertools'); ?></th>
                                            <th style="padding: 16px 24px; font-weight: 600; background: var(--pt-bg-page); width: 100px; text-align: right;"><?php esc_html_e('Actions', 'powertools'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($custom_post_types as $post_type => $data): ?>
                                            <tr style="<?php echo ($edit_mode === $post_type) ? 'background: var(--pt-primary-soft);' : ''; ?>">
                                                <td style="padding: 16px 24px; vertical-align: middle;">
                                                    <div style="font-weight: 600; color: var(--pt-text-main);"><?php echo esc_html($data['plural_label']); ?></div>
                                                    <code style="font-size: 11px; background: none; padding: 0; color: var(--pt-text-muted);"><?php echo esc_html($post_type); ?></code>
                                                </td>
                                                <td style="padding: 16px 24px; text-align: right; vertical-align: middle;">
                                                    <div style="display: flex; gap: 12px; justify-content: flex-end; align-items: center;">
                                                        <a href="<?php echo esc_url(add_query_arg('edit', $post_type)); ?>" style="color: var(--pt-text-muted); text-decoration: none;" title="Edit">
                                                            <span class="dashicons dashicons-edit"></span>
                                                        </a>
                                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post type?');">
                                                            <input type="hidden" name="action" value="powertools_cptm_delete">
                                                            <input type="hidden" name="powertools_cptm_post_type" value="<?php echo esc_attr($post_type); ?>">
                                                            <?php wp_nonce_field('powertools_cptm_delete_nonce_action', 'powertools_cptm_delete_nonce'); ?>
                                                            <button type="submit" style="background: none; border: none; color: var(--pt-danger); cursor: pointer; padding: 0;">
                                                                <span class="dashicons dashicons-trash"></span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle add post type
     */
    public function handle_add_post_type() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        if (!isset($_POST['powertools_cptm_add_nonce']) || !wp_verify_nonce($_POST['powertools_cptm_add_nonce'], 'powertools_cptm_add_nonce_action')) {
            $this->redirect_with_error(__('Security check failed.', 'powertools'));
        }

        $post_type_name = sanitize_key($_POST['powertools_cptm_post_type_name']);
        $singular_label = sanitize_text_field($_POST['powertools_cptm_singular_label']);
        $plural_label = sanitize_text_field($_POST['powertools_cptm_plural_label']);
        $public = isset($_POST['powertools_cptm_public']);
        $has_archive = isset($_POST['powertools_cptm_has_archive']);
        $hierarchical = isset($_POST['powertools_cptm_hierarchical']);
        $menu_position = isset($_POST['powertools_cptm_menu_position']) ? intval($_POST['powertools_cptm_menu_position']) : null;
        $menu_icon = isset($_POST['powertools_cptm_menu_icon']) ? sanitize_text_field($_POST['powertools_cptm_menu_icon']) : '';
        $supports = isset($_POST['powertools_cptm_supports']) ? array_map('sanitize_text_field', $_POST['powertools_cptm_supports']) : array('title', 'editor');

        if (empty($post_type_name) || empty($singular_label) || empty($plural_label)) {
            $this->redirect_with_error(__('All required fields must be filled.', 'powertools'));
        }

        $custom_post_types = get_option($this->option_name, array());
        $custom_post_types[$post_type_name] = array(
            'name' => $post_type_name,
            'singular_label' => $singular_label,
            'plural_label' => $plural_label,
            'public' => $public,
            'has_archive' => $has_archive,
            'hierarchical' => $hierarchical,
            'menu_position' => $menu_position,
            'menu_icon' => $menu_icon,
            'supports' => $supports
        );

        update_option($this->option_name, $custom_post_types);
        $this->redirect_with_success(__('Custom post type added successfully.', 'powertools'));
    }

    /**
     * Handle edit post type
     */
    public function handle_edit_post_type() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        if (!isset($_POST['powertools_cptm_edit_nonce']) || !wp_verify_nonce($_POST['powertools_cptm_edit_nonce'], 'powertools_cptm_edit_nonce_action')) {
            $this->redirect_with_error(__('Security check failed.', 'powertools'));
        }

        $post_type_name = sanitize_key($_POST['powertools_cptm_post_type_name']);
        $singular_label = sanitize_text_field($_POST['powertools_cptm_singular_label']);
        $plural_label = sanitize_text_field($_POST['powertools_cptm_plural_label']);
        $public = isset($_POST['powertools_cptm_public']);
        $has_archive = isset($_POST['powertools_cptm_has_archive']);
        $hierarchical = isset($_POST['powertools_cptm_hierarchical']);
        $menu_position = isset($_POST['powertools_cptm_menu_position']) ? intval($_POST['powertools_cptm_menu_position']) : null;
        $menu_icon = isset($_POST['powertools_cptm_menu_icon']) ? sanitize_text_field($_POST['powertools_cptm_menu_icon']) : '';
        $supports = isset($_POST['powertools_cptm_supports']) ? array_map('sanitize_text_field', $_POST['powertools_cptm_supports']) : array('title', 'editor');

        if (empty($post_type_name) || empty($singular_label) || empty($plural_label)) {
            $this->redirect_with_error(__('All required fields must be filled.', 'powertools'));
        }

        $custom_post_types = get_option($this->option_name, array());
        if (!isset($custom_post_types[$post_type_name])) {
            $this->redirect_with_error(__('Post type not found.', 'powertools'));
        }

        $custom_post_types[$post_type_name] = array(
            'name' => $post_type_name,
            'singular_label' => $singular_label,
            'plural_label' => $plural_label,
            'public' => $public,
            'has_archive' => $has_archive,
            'hierarchical' => $hierarchical,
            'menu_position' => $menu_position,
            'menu_icon' => $menu_icon,
            'supports' => $supports
        );

        update_option($this->option_name, $custom_post_types);
        $this->redirect_with_success(__('Custom post type updated successfully.', 'powertools'));
    }

    /**
     * Handle delete post type
     */
    public function handle_delete_post_type() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        if (!isset($_POST['powertools_cptm_delete_nonce']) || !wp_verify_nonce($_POST['powertools_cptm_delete_nonce'], 'powertools_cptm_delete_nonce_action')) {
            $this->redirect_with_error(__('Security check failed.', 'powertools'));
        }

        $post_type = sanitize_key($_POST['powertools_cptm_post_type']);
        $custom_post_types = get_option($this->option_name, array());

        if (!isset($custom_post_types[$post_type])) {
            $this->redirect_with_error(__('Post type not found.', 'powertools'));
        }

        unset($custom_post_types[$post_type]);
        update_option($this->option_name, $custom_post_types);
        $this->redirect_with_success(__('Custom post type deleted successfully.', 'powertools'));
    }

    /**
     * Redirect with error
     *
     * @param string $error_message Error message
     */
    private function redirect_with_error($error_message) {
        wp_redirect(add_query_arg('error_message', urlencode($error_message), admin_url('admin.php?page=powertools-cpt-manager')));
        exit;
    }

    /**
     * Redirect with success
     *
     * @param string $success_message Success message
     */
    private function redirect_with_success($success_message) {
        wp_redirect(add_query_arg('success_message', urlencode($success_message), admin_url('admin.php?page=powertools-cpt-manager')));
        exit;
    }

    /**
     * Register custom post types
     */
    public function register_custom_post_types() {
        $custom_post_types = get_option($this->option_name, array());

        foreach ($custom_post_types as $post_type => $data) {
            $labels = array(
                'name'               => $data['plural_label'],
                'singular_name'      => $data['singular_label'],
                'menu_name'          => $data['plural_label'],
                'add_new'            => __('Add New', 'powertools'),
                'add_new_item'       => sprintf(__('Add New %s', 'powertools'), $data['singular_label']),
                'edit_item'          => sprintf(__('Edit %s', 'powertools'), $data['singular_label']),
                'new_item'           => sprintf(__('New %s', 'powertools'), $data['singular_label']),
                'view_item'          => sprintf(__('View %s', 'powertools'), $data['singular_label']),
                'search_items'       => sprintf(__('Search %s', 'powertools'), $data['plural_label']),
                'not_found'          => sprintf(__('No %s found', 'powertools'), $data['plural_label']),
                'not_found_in_trash' => sprintf(__('No %s found in Trash', 'powertools'), $data['plural_label']),
            );

            $args = array(
                'labels'              => $labels,
                'public'              => $data['public'],
                'has_archive'         => $data['has_archive'],
                'hierarchical'        => $data['hierarchical'],
                'menu_position'       => $data['menu_position'],
                'menu_icon'           => $data['menu_icon'],
                'supports'            => $data['supports'],
                'show_in_menu'        => true,
                'show_in_admin_bar'   => true,
                'show_in_nav_menus'   => true,
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'capability_type'     => 'post',
                'show_in_rest'        => true,
            );

            register_post_type($post_type, $args);
        }
    }
}


