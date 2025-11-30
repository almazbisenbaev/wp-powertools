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
 * Author URI: https://helloalmaz.com/
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

// Include autoloader
require_once POWERTOOLS_PLUGIN_DIR . 'includes/autoloader.php';

// Initialize the plugin
function powertools_init() {
    $plugin = new PowerTools\Plugin();
    $plugin->init();
}
powertools_init();

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
