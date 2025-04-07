<?php
/**
 * Plugin Name: Power Tools
 * Plugin URI: https://github.com/almazbisenbaev/wp-powertools
 * Description: Useful WordPress utilities to solve common WordPress problems and maximize your productivity 
 * Author: Almaz Bisenbaev
 * Version: 0.1.1
 * Requires at least: 6.0
 * Tested up to: 6.7.1
 * Text Domain: powertools
 * Author URI: https://bruteforce.kz/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package PowerTools
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('POWERTOOLS_VERSION', '0.1.1');
define('POWERTOOLS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POWERTOOLS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('POWERTOOLS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Initialize the plugin
function powertools_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('powertools', false, dirname(POWERTOOLS_PLUGIN_BASENAME) . '/languages');
}
add_action('plugins_loaded', 'powertools_init');

// Include required files
require_once POWERTOOLS_PLUGIN_DIR . 'includes/menu.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/gutenberg-disabler.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/comments-disabler.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/toolbar-toggler.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/html-junk-remover.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/junk-cleaner.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/cpt-manager.php';
require_once POWERTOOLS_PLUGIN_DIR . 'includes/system-info.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'powertools_activate');
register_deactivation_hook(__FILE__, 'powertools_deactivate');

function powertools_activate() {
    // Activation tasks
    flush_rewrite_rules();
}

function powertools_deactivate() {
    // Deactivation tasks
    flush_rewrite_rules();
}

// Enqueue admin styles
function powertools_enqueue_admin_styles() {
    wp_enqueue_style(
        'powertools-admin-styles',
        POWERTOOLS_PLUGIN_URL . 'admin/css/powertools-admin.css',
        array(),
        POWERTOOLS_VERSION
    );
}
add_action('admin_enqueue_scripts', 'powertools_enqueue_admin_styles');

// Homepage content
function powertools_homepage() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
    }
    
    // Include the homepage template
    require_once POWERTOOLS_PLUGIN_DIR . 'admin/views/homepage.php';
}

// Initialize admin menu
function powertools_init_admin_menu() {
    $admin_menu = new PowerTools\Admin\Admin_Menu();
}
add_action('plugins_loaded', 'powertools_init_admin_menu');

// Initialize CPT manager
function powertools_init_cpt_manager() {
    $cpt_manager = new PowerTools\CPT\Manager();
}
add_action('init', 'powertools_init_cpt_manager');

// Initialize system info
function powertools_init_system_info() {
    $system_info = new PowerTools\System\Info();
}
add_action('init', 'powertools_init_system_info');

// Initialize comments disabler
function powertools_init_comments_disabler() {
    $comments_disabler = new PowerTools\Comments\Comments_Disabler();
}
add_action('init', 'powertools_init_comments_disabler');
