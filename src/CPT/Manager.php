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

        <div class="ptools-settings">
            <div class="ptools-settings-header">
                <h2 class="ptools-settings-title"><?php esc_html_e('CPT Manager', 'powertools'); ?></h2>
                <div class="ptools-settings-descr">
                    <?php esc_html_e('Create and manage custom post types for your WordPress site.', 'powertools'); ?>
                </div>
            </div>

            <?php if (isset($_GET['error_message'])): ?>
                <div class="ptools-notice ptools-notice--error">
                    <span class="dashicons dashicons-dismiss"></span>
                    <?php echo esc_html(urldecode($_GET['error_message'])); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success_message'])): ?>
                <div class="ptools-notice ptools-notice--success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php echo esc_html(urldecode($_GET['success_message'])); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="ptools-metabox">
                <h3 class="ptools-metabox-title">
                    <?php echo $edit_mode ? esc_html__('Edit Post Type', 'powertools') : esc_html__('Add New Post Type', 'powertools'); ?>
                </h3>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="<?php echo $edit_mode ? 'powertools_cptm_edit' : 'powertools_cptm_add'; ?>">
                    <?php wp_nonce_field($edit_mode ? 'powertools_cptm_edit_nonce_action' : 'powertools_cptm_add_nonce_action', $edit_mode ? 'powertools_cptm_edit_nonce' : 'powertools_cptm_add_nonce'); ?>

                    <div class="ptools-form-grid">
                        <div class="ptools-form-group">
                            <label class="ptools-form-label" for="cptm_post_type_name">
                                <?php esc_html_e('Post Type Name', 'powertools'); ?>
                                <span class="ptools-form-required">*</span>
                            </label>
                            <div class="ptools-form-input-wrap">
                                <span class="dashicons dashicons-tag"></span>
                                <input class="ptools-form-input ptools-form-input--mono" id="cptm_post_type_name" type="text"
                                       name="powertools_cptm_post_type_name"
                                       value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['name']) : ''; ?>"
                                       placeholder="e.g. my_portfolio"
                                       <?php echo $edit_mode ? 'readonly' : 'required'; ?> />
                            </div>
                            <span class="ptools-form-hint"><?php esc_html_e('Lowercase, underscores only. Max 20 chars.', 'powertools'); ?></span>
                        </div>

                        <div class="ptools-form-group">
                            <label class="ptools-form-label" for="cptm_singular_label">
                                <?php esc_html_e('Singular Label', 'powertools'); ?>
                                <span class="ptools-form-required">*</span>
                            </label>
                            <div class="ptools-form-input-wrap">
                                <span class="dashicons dashicons-admin-users"></span>
                                <input class="ptools-form-input" id="cptm_singular_label" type="text"
                                       name="powertools_cptm_singular_label"
                                       value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['singular_label']) : ''; ?>"
                                       placeholder="<?php esc_attr_e('e.g. Project', 'powertools'); ?>"
                                       required />
                            </div>
                        </div>

                        <div class="ptools-form-group">
                            <label class="ptools-form-label" for="cptm_plural_label">
                                <?php esc_html_e('Plural Label', 'powertools'); ?>
                                <span class="ptools-form-required">*</span>
                            </label>
                            <div class="ptools-form-input-wrap">
                                <span class="dashicons dashicons-admin-users"></span>
                                <input class="ptools-form-input" id="cptm_plural_label" type="text"
                                       name="powertools_cptm_plural_label"
                                       value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['plural_label']) : ''; ?>"
                                       placeholder="<?php esc_attr_e('e.g. Projects', 'powertools'); ?>"
                                       required />
                            </div>
                        </div>

                        <div class="ptools-form-group">
                            <label class="ptools-form-label" for="cptm_menu_icon">
                                <?php esc_html_e('Menu Icon', 'powertools'); ?>
                            </label>
                            <div class="ptools-form-input-wrap">
                                <span class="dashicons dashicons-admin-appearance"></span>
                                <input class="ptools-form-input ptools-form-input--mono" id="cptm_menu_icon" type="text"
                                       name="powertools_cptm_menu_icon"
                                       value="<?php echo $edit_mode && isset($custom_post_types[$edit_mode]['menu_icon']) ? esc_attr($custom_post_types[$edit_mode]['menu_icon']) : ''; ?>"
                                       placeholder="dashicons-admin-post" />
                            </div>
                            <span class="ptools-form-hint">
                                <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank"><?php esc_html_e('Browse Dashicons →', 'powertools'); ?></a>
                            </span>
                        </div>

                        <div class="ptools-form-group">
                            <label class="ptools-form-label" for="cptm_menu_position">
                                <?php esc_html_e('Menu Position', 'powertools'); ?>
                            </label>
                            <div class="ptools-form-input-wrap">
                                <span class="dashicons dashicons-menu"></span>
                                <input class="ptools-form-input ptools-form-input--short" id="cptm_menu_position" type="number"
                                       name="powertools_cptm_menu_position"
                                       value="<?php echo $edit_mode && isset($custom_post_types[$edit_mode]['menu_position']) ? esc_attr($custom_post_types[$edit_mode]['menu_position']) : ''; ?>"
                                       min="1" placeholder="25" />
                            </div>
                            <span class="ptools-form-hint"><?php esc_html_e('WordPress default is 25 (below Comments).', 'powertools'); ?></span>
                        </div>
                    </div>

                    <div class="ptools-form-toggles">
                        <label class="ptools-toggle-label">
                            <input type="checkbox" name="powertools_cptm_public" value="1"
                                   <?php checked($edit_mode ? $custom_post_types[$edit_mode]['public'] : true); ?> />
                            <span class="ptools-toggle-text"><?php esc_html_e('Public', 'powertools'); ?></span>
                        </label>
                        <label class="ptools-toggle-label">
                            <input type="checkbox" name="powertools_cptm_has_archive" value="1"
                                   <?php checked($edit_mode ? $custom_post_types[$edit_mode]['has_archive'] : true); ?> />
                            <span class="ptools-toggle-text"><?php esc_html_e('Has Archive', 'powertools'); ?></span>
                        </label>
                        <label class="ptools-toggle-label">
                            <input type="checkbox" name="powertools_cptm_hierarchical" value="1"
                                   <?php checked($edit_mode && isset($custom_post_types[$edit_mode]['hierarchical']) ? $custom_post_types[$edit_mode]['hierarchical'] : false); ?> />
                            <span class="ptools-toggle-text"><?php esc_html_e('Hierarchical', 'powertools'); ?></span>
                        </label>
                    </div>

                    <div class="ptools-form-group">
                        <span class="ptools-form-label"><?php esc_html_e('Supports', 'powertools'); ?></span>
                        <div class="ptools-form-supports">
                            <?php foreach ($support_options as $option): ?>
                                <label class="ptools-toggle-label">
                                    <input type="checkbox" name="powertools_cptm_supports[]"
                                           value="<?php echo esc_attr($option); ?>"
                                           <?php checked(in_array($option, $supports)); ?> />
                                    <span class="ptools-toggle-text"><?php echo esc_html(ucfirst(str_replace('-', ' ', $option))); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="ptools-metabox-footer">
                        <?php if ($edit_mode): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-cpt-manager')); ?>" class="button ptools-btn ptools-btn--secondary">
                                <?php esc_html_e('Cancel', 'powertools'); ?>
                            </a>
                        <?php endif; ?>
                        <input type="submit" class="button button-primary"
                               value="<?php echo $edit_mode ? esc_attr__('Update Post Type', 'powertools') : esc_attr__('Add Post Type', 'powertools'); ?>" />
                    </div>
                </form>
            </div>

            <!-- List -->
            <div class="ptools-metabox">
                <h3 class="ptools-metabox-title">
                    <?php esc_html_e('Registered Post Types', 'powertools'); ?>
                    <span class="ptools-cpt-count"><?php echo count($custom_post_types); ?></span>
                </h3>

                <?php if (empty($custom_post_types)): ?>
                    <div class="ptools-cpt-empty">
                        <span class="dashicons dashicons-category"></span>
                        <p><?php esc_html_e('No custom post types registered yet. Use the form above to create your first one.', 'powertools'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="ptools-cpt-table-wrap">
                        <table class="ptools-cpt-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Name', 'powertools'); ?></th>
                                    <th><?php esc_html_e('Singular', 'powertools'); ?></th>
                                    <th><?php esc_html_e('Plural', 'powertools'); ?></th>
                                    <th><?php esc_html_e('Flags', 'powertools'); ?></th>
                                    <th><?php esc_html_e('Actions', 'powertools'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($custom_post_types as $post_type => $data): ?>
                                    <tr class="<?php echo ($edit_mode === $post_type) ? 'ptools-row--editing' : ''; ?>">
                                        <td>
                                            <code class="ptools-cpt-slug"><?php echo esc_html($post_type); ?></code>
                                        </td>
                                        <td><?php echo esc_html($data['singular_label']); ?></td>
                                        <td><?php echo esc_html($data['plural_label']); ?></td>
                                        <td class="ptools-cpt-flags">
                                            <?php if (!empty($data['public'])): ?>
                                                <span class="ptools-flag ptools-flag--on" title="<?php esc_attr_e('Public', 'powertools'); ?>">Public</span>
                                            <?php endif; ?>
                                            <?php if (!empty($data['has_archive'])): ?>
                                                <span class="ptools-flag ptools-flag--on" title="<?php esc_attr_e('Has Archive', 'powertools'); ?>">Archive</span>
                                            <?php endif; ?>
                                            <?php if (!empty($data['hierarchical'])): ?>
                                                <span class="ptools-flag ptools-flag--on" title="<?php esc_attr_e('Hierarchical', 'powertools'); ?>">Hierarchical</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="ptools-cpt-actions">
                                            <a href="<?php echo esc_url(add_query_arg('edit', $post_type)); ?>" class="ptools-action-btn ptools-action-btn--edit">
                                                <span class="dashicons dashicons-edit"></span>
                                                <?php esc_html_e('Edit', 'powertools'); ?>
                                            </a>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                                <input type="hidden" name="action" value="powertools_cptm_delete">
                                                <input type="hidden" name="powertools_cptm_post_type" value="<?php echo esc_attr($post_type); ?>">
                                                <?php wp_nonce_field('powertools_cptm_delete_nonce_action', 'powertools_cptm_delete_nonce'); ?>
                                                <button type="submit" class="ptools-action-btn ptools-action-btn--delete"
                                                        onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this post type?', 'powertools'); ?>');">
                                                    <span class="dashicons dashicons-trash"></span>
                                                    <?php esc_html_e('Delete', 'powertools'); ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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


