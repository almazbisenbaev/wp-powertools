<?php
/**
 * @package Power_Tools
 * @version 0.1.0
 */
/*
Plugin Name: Power Tools
Plugin URI: https://bruteforce.kz/
Description: Useful WordPress utilities to solve common WordPress problems and maximize your productivity 
Author: Almaz Bisenbaev
Version: 0.1.0
Requires at least: 6.0
Tested up to: 6.5
Text Domain: powertools
Author URI: https://bruteforce.kz/
*/


// Menu items
require_once plugin_dir_path( __FILE__ ) . 'includes/menu.php';
add_action('admin_menu', 'powertools_setup_menu');



// Tools
require_once plugin_dir_path( __FILE__ ) . 'includes/gutenberg-disabler.php';
add_action('init', 'powertools_disable_gutenberg');

require_once plugin_dir_path( __FILE__ ) . 'includes/hide-admin-bar.php';
add_action('init', 'powertools_hide_admin_bar');

require_once plugin_dir_path( __FILE__ ) . 'includes/html-junk-remover.php';
add_action('init', 'powertools_remove_html_junk');

require_once plugin_dir_path( __FILE__ ) . 'includes/junk-cleaner.php';
// add_action('init', 'powertools_junk_cleaner');

require_once plugin_dir_path( __FILE__ ) . 'includes/system-info.php';




// Enqueue styles and javascript

function powertools_enqueue_styles() {

    // CSS for dashboard
    wp_enqueue_style('powertools-styles', plugin_dir_url(__FILE__) . 'admin/css/powertools-admin.css', array(), '0.1.2');

}

add_action('admin_enqueue_scripts', 'powertools_enqueue_styles');


// Homepage
function powertools_homepage() {

    echo '<div class="ptools-intro">';
      echo '<h1 class="ptools-intro-title">Welcome to Power Tools!</h1>';
      echo '<div class="ptools-intro-descr">Simple tools that solve common problems during WordPress development and maximize your productivity</div>';
    echo '</div>';

    echo '<div class="ptools-cards" style="margin: 30px 0;">';
      echo '<div class="ptools-card">
              <a href="/wp-admin/admin.php?page=powertools-hide-admin-bar">Hide Admin Bar</a>
              <br>Hide the black bar on top of your website or remove the 32px margin that is added to &#60;html&#62; tag
            </div>';
      echo '<div class="ptools-card">
              <a href="/wp-admin/admin.php?page=powertools-gutenberg-disabler">Gutenberg Disabler</a>
              <br>Return the legacy editor for specific post types
            </div>';
      echo '<div class="ptools-card">
              <a href="/wp-admin/admin.php?page=powertools-html-junk-remover">HTML Junk Remover</a>
              <br>This tool removes the useless lines of code from HTML (such as WordPress version, emojis, etc.)
            </div>';
      echo '<div class="ptools-card">
              <a href="/wp-admin/admin.php?page=powertools-junk-cleaner">Junk Cleaner</a>
              <br>This tool removes the useless lines of code from HTML (such as WordPress version, emojis, etc.)
            </div>';
      echo '<div class="ptools-card">
              <a href="/wp-admin/admin.php?page=powertools-system-info">System Info</a>
              <br>View and export system info that can be useful for your IT guy or a tech support agent
            </div>';
    echo '</div>';

    echo '<hr />';
    echo '<div>Author: Almaz Bisenbaev (<a target="_blank" href="https://github.com/almazbisenbaev">https://github.com/almazbisenbaev</a>)</div>';
    echo '<hr />';
    echo '<div>Github repository: <a target="_blank" href="https://github.com/almazbisenbaev/wp-powertools">https://github.com/almazbisenbaev/wp-powertools</a></div>';

}
