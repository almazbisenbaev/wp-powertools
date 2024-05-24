<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CustomPostTypeManager {

    private $option_name = 'powertools_cptm_custom_post_types';

    public function __construct() {

        // Hook into the admin menu.
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Register custom post types on init.
        add_action('init', [$this, 'register_custom_post_types']);

        // Handle form submission to add or delete custom post types.
        add_action('admin_post_powertools_cptm_add', [$this, 'handle_add_post_type']);
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

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="powertools_cptm_add">
                <?php wp_nonce_field('powertools_cptm_add_nonce_action', 'powertools_cptm_add_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Post Type Name</th>
                        <td><input type="text" name="powertools_cptm_post_type_name" value="" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Singular Label</th>
                        <td><input type="text" name="powertools_cptm_singular_label" value="" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Plural Label</th>
                        <td><input type="text" name="powertools_cptm_plural_label" value="" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Public</th>
                        <td><input type="checkbox" name="powertools_cptm_public" value="1" checked /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Has Archive</th>
                        <td><input type="checkbox" name="powertools_cptm_has_archive" value="1" checked /></td>
                    </tr>
                </table>
                <?php submit_button('Add Custom Post Type'); ?>
            </form>

            <h2>Existing Custom Post Types</h2>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>Post Type</th>
                        <th>Singular Label</th>
                        <th>Plural Label</th>
                        <th>Public</th>
                        <th>Has Archive</
                        <th>Action</th>
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
                        <td>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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

        if (!empty($name) && !empty($singular_label) && !empty($plural_label)) {
            $custom_post_types[$name] = [
                'name' => $name,
                'singular_label' => $singular_label,
                'plural_label' => $plural_label,
                'public' => $public,
                'has_archive' => $has_archive,
            ];
            update_option($this->option_name, $custom_post_types);
        } else {
            $this->redirect_with_error('Please fill in all required fields.');
        }

        wp_redirect(admin_url('admin.php?page=powertools_cptm'));
        exit;
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
        } else {
            $this->redirect_with_error('Post type not found.');
        }

        wp_redirect(admin_url('admin.php?page=powertools_cptm'));
        exit;
    }

    private function redirect_with_error($error_message) {
        $redirect_url = add_query_arg('error_message', urlencode($error_message), admin_url('admin.php?page=powertools_cptm'));
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
                'show_ui' => true,
                'show_in_menu' => true,
                'supports' => ['title', 'editor', 'thumbnail'],
            ];
            register_post_type($cpt['name'], $args);
        }

    }
}

// Initialize the plugin
$cpt_manager = new CustomPostTypeManager();
