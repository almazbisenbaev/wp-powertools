<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


class CustomPostTypeManager {
    private $option_name = 'powertools_cptm_custom_post_types';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('init', [$this, 'register_custom_post_types']);
        add_action('admin_post_powertools_cptm_add', [$this, 'handle_add_post_type']);
        add_action('admin_post_powertools_cptm_edit', [$this, 'handle_edit_post_type']);
        add_action('admin_post_powertools_cptm_delete', [$this, 'handle_delete_post_type']);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'powertools',
            'CPT Manager',
            'Custom Post Types Manager',
            'manage_options',
            'powertools_cptm',
            [$this, 'settings_page'],
        );
    }

    public function settings_page() {
        $custom_post_types = get_option($this->option_name, []);
        $edit_mode = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : '';

        if (!is_array($custom_post_types)) {
            $custom_post_types = [];
        }
        ?>

        <div class="wrap">
            <h1>Custom Post Type Manager</h1>

            <?php if (isset($_GET['error_message'])): ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($_GET['error_message']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success_message'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($_GET['success_message']); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'powertools_cptm_edit' : 'powertools_cptm_add'; ?>">
                <?php wp_nonce_field($edit_mode ? 'powertools_cptm_edit_nonce_action' : 'powertools_cptm_add_nonce_action', $edit_mode ? 'powertools_cptm_edit_nonce' : 'powertools_cptm_add_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Post Type Name</th>
                        <td><input type="text" name="powertools_cptm_post_type_name" value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['name']) : ''; ?>" <?php echo $edit_mode ? 'readonly' : 'required'; ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Singular Label</th>
                        <td><input type="text" name="powertools_cptm_singular_label" value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['singular_label']) : ''; ?>" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Plural Label</th>
                        <td><input type="text" name="powertools_cptm_plural_label" value="<?php echo $edit_mode ? esc_attr($custom_post_types[$edit_mode]['plural_label']) : ''; ?>" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Public</th>
                        <td><input type="checkbox" name="powertools_cptm_public" value="1" <?php checked($edit_mode ? $custom_post_types[$edit_mode]['public'] : true); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Has Archive</th>
                        <td><input type="checkbox" name="powertools_cptm_has_archive" value="1" <?php checked($edit_mode ? $custom_post_types[$edit_mode]['has_archive'] : true); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Hierarchical</th>
                        <td><input type="checkbox" name="powertools_cptm_hierarchical" value="1" <?php checked($edit_mode && isset($custom_post_types[$edit_mode]['hierarchical']) ? $custom_post_types[$edit_mode]['hierarchical'] : false); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Menu Position</th>
                        <td><input type="number" name="powertools_cptm_menu_position" value="<?php echo $edit_mode && isset($custom_post_types[$edit_mode]['menu_position']) ? esc_attr($custom_post_types[$edit_mode]['menu_position']) : ''; ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Menu Icon</th>
                        <td><input type="text" name="powertools_cptm_menu_icon" value="<?php echo $edit_mode && isset($custom_post_types[$edit_mode]['menu_icon']) ? esc_attr($custom_post_types[$edit_mode]['menu_icon']) : ''; ?>" placeholder="dashicons-admin-post" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Supports</th>
                        <td>
                            <?php
                            $supports = $edit_mode && isset($custom_post_types[$edit_mode]['supports']) ? $custom_post_types[$edit_mode]['supports'] : ['title', 'editor', 'thumbnail'];
                            $support_options = ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'];
                            foreach ($support_options as $option) {
                                echo '<label><input type="checkbox" name="powertools_cptm_supports[]" value="' . esc_attr($option) . '" ' . (in_array($option, $supports) ? 'checked' : '') . ' /> ' . ucfirst($option) . '</label><br>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button($edit_mode ? 'Update Custom Post Type' : 'Add Custom Post Type'); ?>
            </form>

            <h2>Existing Custom Post Types</h2>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>Post Type</th>
                        <th>Singular Label</th>
                        <th>Plural Label</th>
                        <th>Public</th>
                        <th>Has Archive</th>
                        <th>Hierarchical</th>
                        <th>Menu Position</th>
                        <th>Menu Icon</th>
                        <th>Supports</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custom_post_types as $cpt) : ?>
                    <tr>
                        <td><?php echo esc_html($cpt['name']); ?></td>
                        <td><?php echo esc_html($cpt['singular_label']); ?></td>
                        <td><?php echo esc_html($cpt['plural_label']); ?></td>
                        <td><?php echo $cpt['public'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $cpt['has_archive'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo isset($cpt['hierarchical']) && $cpt['hierarchical'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo isset($cpt['menu_position']) ? esc_html($cpt['menu_position']) : ''; ?></td>
                        <td><?php echo isset($cpt['menu_icon']) ? esc_html($cpt['menu_icon']) : ''; ?></td>
                        <td><?php echo isset($cpt['supports']) ? esc_html(implode(', ', $cpt['supports'])) : ''; ?></td>
                        <td>
                            <a href="<?php echo add_query_arg(['page' => 'powertools_cptm', 'edit' => $cpt['name']], admin_url('admin.php')); ?>" class="button">Edit</a>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                <input type="hidden" name="action" value="powertools_cptm_delete">
                                <?php wp_nonce_field('powertools_cptm_delete_nonce_action', 'powertools_cptm_delete_nonce'); ?>
                                <input type="hidden" name="powertools_cptm_post_type_name" value="<?php echo esc_attr($cpt['name']); ?>" />
                                <?php submit_button('Delete', 'delete', 'submit', false); ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function handle_add_post_type() {
        if (!isset($_POST['powertools_cptm_add_nonce']) || !wp_verify_nonce($_POST['powertools_cptm_add_nonce'], 'powertools_cptm_add_nonce_action')) {
            $this->redirect_with_error('Nonce verification failed for adding post type.');
        }

        $custom_post_types = get_option($this->option_name, []);
        if (!is_array($custom_post_types)) {
            $custom_post_types = [];
        }

        $name = sanitize_text_field($_POST['powertools_cptm_post_type_name']);
        $singular_label = sanitize_text_field($_POST['powertools_cptm_singular_label']);
        $plural_label = sanitize_text_field($_POST['powertools_cptm_plural_label']);
        $public = isset($_POST['powertools_cptm_public']) ? true : false;
        $has_archive = isset($_POST['powertools_cptm_has_archive']) ? true : false;
        $hierarchical = isset($_POST['powertools_cptm_hierarchical']) ? true : false;
        $menu_position = !empty($_POST['powertools_cptm_menu_position']) ? intval($_POST['powertools_cptm_menu_position']) : null;
        $menu_icon = !empty($_POST['powertools_cptm_menu_icon']) ? sanitize_text_field($_POST['powertools_cptm_menu_icon']) : 'dashicons-admin-post';
        $supports = isset($_POST['powertools_cptm_supports']) ? array_map('sanitize_text_field', $_POST['powertools_cptm_supports']) : ['title', 'editor', 'thumbnail'];

        if (!empty($name) && !empty($singular_label) && !empty($plural_label)) {
            $custom_post_types[$name] = [
                'name' => $name,
                'singular_label' => $singular_label,
                'plural_label' => $plural_label,
                'public' => $public,
                'has_archive' => $has_archive,
                'hierarchical' => $hierarchical,
                'menu_position' => $menu_position,
                'menu_icon' => $menu_icon,
                'supports' => $supports,
            ];
            update_option($this->option_name, $custom_post_types);
            $this->redirect_with_success('Custom Post Type added successfully.');
        } else {
            $this->redirect_with_error('Please fill in all required fields.');
        }
    }

    public function handle_edit_post_type() {
        if (!isset($_POST['powertools_cptm_edit_nonce']) || !wp_verify_nonce($_POST['powertools_cptm_edit_nonce'], 'powertools_cptm_edit_nonce_action')) {
            $this->redirect_with_error('Nonce verification failed for editing post type.');
        }

        $custom_post_types = get_option($this->option_name, []);
        if (!is_array($custom_post_types)) {
            $custom_post_types = [];
        }

        $name = sanitize_text_field($_POST['powertools_cptm_post_type_name']);
        $singular_label = sanitize_text_field($_POST['powertools_cptm_singular_label']);
        $plural_label = sanitize_text_field($_POST['powertools_cptm_plural_label']);
        $public = isset($_POST['powertools_cptm_public']) ? true : false;
        $has_archive = isset($_POST['powertools_cptm_has_archive']) ? true : false;
        $hierarchical = isset($_POST['powertools_cptm_hierarchical']) ? true : false;
        $menu_position = !empty($_POST['powertools_cptm_menu_position']) ? intval($_POST['powertools_cptm_menu_position']) : null;
        $menu_icon = !empty($_POST['powertools_cptm_menu_icon']) ? sanitize_text_field($_POST['powertools_cptm_menu_icon']) : 'dashicons-admin-post';
        $supports = isset($_POST['powertools_cptm_supports']) ? array_map('sanitize_text_field', $_POST['powertools_cptm_supports']) : ['title', 'editor', 'thumbnail'];

        if (!empty($name) && !empty($singular_label) && !empty($plural_label)) {
            $custom_post_types[$name] = [
                'name' => $name,
                'singular_label' => $singular_label,
                'plural_label' => $plural_label,
                'public' => $public,
                'has_archive' => $has_archive,
                'hierarchical' => $hierarchical,
                'menu_position' => $menu_position,
                'menu_icon' => $menu_icon,
                'supports' => $supports,
            ];
            update_option($this->option_name, $custom_post_types);
            $this->redirect_with_success('Custom Post Type updated successfully.');
        } else {
            $this->redirect_with_error('Please fill in all required fields.');
        }
    }


    public function handle_delete_post_type() {
        if (!isset($_POST['powertools_cptm_delete_nonce']) || !wp_verify_nonce($_POST['powertools_cptm_delete_nonce'], 'powertools_cptm_delete_nonce_action')) {
            $this->redirect_with_error('Nonce verification failed for deleting post type.');
        }

        $custom_post_types = get_option($this->option_name, []);
        if (!is_array($custom_post_types)) {
            $custom_post_types = [];
        }

        $name = sanitize_text_field($_POST['powertools_cptm_post_type_name']);

        if (isset($custom_post_types[$name])) {
            unset($custom_post_types[$name]);
            update_option($this->option_name, $custom_post_types);
            $this->redirect_with_success('Custom Post Type deleted successfully.');
        } else {
            $this->redirect_with_error('Custom Post Type not found.');
        }
    }

    private function redirect_with_error($error_message) {
        $redirect_url = add_query_arg([
            'page' => 'powertools_cptm',
            'error_message' => urlencode($error_message)
        ], admin_url('admin.php'));
        wp_redirect($redirect_url);
        exit;
    }

    private function redirect_with_success($success_message) {
        $redirect_url = add_query_arg([
            'page' => 'powertools_cptm',
            'success_message' => urlencode($success_message)
        ], admin_url('admin.php'));
        wp_redirect($redirect_url);
        exit;
    }

    public function register_custom_post_types() {
        $custom_post_types = get_option($this->option_name, []);

        if (!is_array($custom_post_types)) {
            $custom_post_types = [];
        }

        foreach ($custom_post_types as $cpt) {
            $args = [
                'label' => $cpt['plural_label'],
                'labels' => [
                    'name' => $cpt['plural_label'],
                    'singular_name' => $cpt['singular_label'],
                ],
                'public' => $cpt['public'],
                'has_archive' => $cpt['has_archive'],
                'hierarchical' => isset($cpt['hierarchical']) ? $cpt['hierarchical'] : false,
                'menu_position' => isset($cpt['menu_position']) ? $cpt['menu_position'] : null,
                'menu_icon' => isset($cpt['menu_icon']) ? $cpt['menu_icon'] : 'dashicons-admin-post',
                'supports' => isset($cpt['supports']) ? $cpt['supports'] : ['title', 'editor', 'thumbnail'],
                'show_ui' => true,
                'show_in_menu' => true,
            ];
            register_post_type($cpt['name'], $args);
        }
    }

}

// Initialize the plugin
$cpt_manager = new CustomPostTypeManager();
