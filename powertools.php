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
require_once POWERTOOLS_PLUGIN_DIR . 'includes/tool-manager.php';
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

// Initialize tools based on their active state
function powertools_init_tools() {
    $tool_manager = new PowerTools\Admin\Tool_Manager();
    $active_tools = $tool_manager->get_active_tools();

    // Initialize CPT manager if active
    if (isset($active_tools['cpt_manager']) && $active_tools['cpt_manager']) {
        $cpt_manager = new PowerTools\CPT\Manager();
    }

    // Initialize system info if active
    if (isset($active_tools['system_info']) && $active_tools['system_info']) {
        $system_info = new PowerTools\System\Info();
    }

    // Initialize comments disabler if active
    if (isset($active_tools['comments_disabler']) && $active_tools['comments_disabler']) {
        $comments_disabler = new PowerTools\Comments\Comments_Disabler();
    }

    // Initialize toolbar toggler if active
    if (isset($active_tools['toolbar_toggler']) && $active_tools['toolbar_toggler']) {
        $toolbar_toggler = new PowerTools\Toolbar\Toolbar_Toggler();
    }

    // Initialize HTML junk remover if active
    if (isset($active_tools['html_junk_remover']) && $active_tools['html_junk_remover']) {
        $html_junk_remover = new PowerTools\HTML\Junk_Remover();
    }

    // Initialize junk cleaner if active
    if (isset($active_tools['junk_cleaner']) && $active_tools['junk_cleaner']) {
        $junk_cleaner = new PowerTools\Cleaner\Junk_Cleaner();
    }

    // Initialize Gutenberg disabler if active
    if (isset($active_tools['gutenberg_disabler']) && $active_tools['gutenberg_disabler']) {
        $gutenberg_disabler = new PowerTools\Gutenberg\Gutenberg_Disabler();
    }
}
add_action('init', 'powertools_init_tools');
