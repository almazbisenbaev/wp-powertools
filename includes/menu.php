<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Add menu item and submenu
function powertools_setup_menu() {

    add_menu_page(
        'Power Tools',
        'Power Tools',
        'manage_options',
        'powertools',
        'powertools_homepage',
        'dashicons-hammer',
        100
    );
    
    add_submenu_page(
        'powertools',
        'Hide Admin Bar',
        'Hide Admin Bar',
        'manage_options',
        'powertools-hide-admin-bar',
        'powertools_hide_admin_bar_page'
    );
    
    add_submenu_page(
        'powertools',
        'Gutenberg Disabler',
        'Gutenberg Disabler',
        'manage_options',
        'powertools-gutenberg-disabler',
        'powertools_gutenberg_disabler_page'
    );
    
    add_submenu_page(
        'powertools',
        'HTML Junk Remover',
        'HTML Junk Remover',
        'manage_options',
        'powertools-html-junk-remover',
        'powertools_html_junk_remover_page'
    );
    
    add_submenu_page(
        'powertools',
        'Junk Cleaner',
        'Junk Cleaner',
        'manage_options',
        'powertools-junk-cleaner',
        'powertools_junk_cleaner_page'
    );
    
}
