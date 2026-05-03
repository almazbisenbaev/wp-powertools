<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following:
 *
 * - This method should be static.
 * - Check if the $_wp_column_names variable is set.
 * - Check if the plugin is actually being uninstalled.
 *
 * @package PowerTools
 */

// If uninstall not called from WordPress, die.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

/**
 * List of options used by PowerTools
 */
$powertools_options = [
    'powertools_insert_code_snippets',
    'powertools_remove_html_junk',
    'powertools_toolbar_toggler_enabled',
    'powertools_disable_gutenberg',
    'powertools_disable_comments',
    'powertools_active_tools',
    'powertools_cptm_custom_post_types',
];

/**
 * Delete options
 */
foreach ($powertools_options as $option) {
    delete_option($option);
}

// For any site in a multisite network
/*
if (is_multisite()) {
    global $wpdb;
    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach ($blogs as $blog_id) {
        switch_to_blog($blog_id);
        foreach ($powertools_options as $option) {
            delete_option($option);
        }
        restore_current_blog();
    }
}
*/
