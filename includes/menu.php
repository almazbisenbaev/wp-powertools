<?php
/**
 * Menu setup for Power Tools plugin
 *
 * @package PowerTools
 */

namespace PowerTools\Admin;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Admin_Menu
 */
class Admin_Menu {
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'setup_menu'));
    }

    /**
     * Setup the admin menu
     */
    public function setup_menu() {
        // Add main menu page
        add_menu_page(
            __('Power Tools', 'powertools'),
            __('Power Tools', 'powertools'),
            'manage_options',
            'powertools',
            array($this, 'render_homepage'),
            'dashicons-hammer',
            100
        );

        // Add submenu pages
        $this->add_submenu_pages();
    }

    /**
     * Add submenu pages
     */
    private function add_submenu_pages() {
        // Add CPT Manager submenu
        add_submenu_page(
            'powertools',
            __('CPT Manager', 'powertools'),
            __('CPT Manager', 'powertools'),
            'manage_options',
            'powertools-cpt-manager',
            array($this, 'render_cpt_manager')
        );

        // Add Gutenberg Disabler submenu
        add_submenu_page(
            'powertools',
            __('Gutenberg Disabler', 'powertools'),
            __('Gutenberg Disabler', 'powertools'),
            'manage_options',
            'powertools-gutenberg-disabler',
            array($this, 'render_gutenberg_disabler')
        );

        // Add Comments Disabler submenu
        add_submenu_page(
            'powertools',
            __('Comments Disabler', 'powertools'),
            __('Comments Disabler', 'powertools'),
            'manage_options',
            'powertools-comments-disabler',
            array($this, 'render_comments_disabler')
        );

        add_submenu_page(
            'powertools',
            __('Toolbar Toggler', 'powertools'),
            __('Toolbar Toggler', 'powertools'),
            'manage_options',
            'powertools-toolbar-toggler',
            array($this, 'render_toolbar_toggler')
        );

        add_submenu_page(
            'powertools',
            __('HTML Junk Remover', 'powertools'),
            __('HTML Junk Remover', 'powertools'),
            'manage_options',
            'powertools-html-junk-remover',
            array($this, 'render_html_junk_remover')
        );

        add_submenu_page(
            'powertools',
            __('Junk Cleaner', 'powertools'),
            __('Junk Cleaner', 'powertools'),
            'manage_options',
            'powertools-junk-cleaner',
            array($this, 'render_junk_cleaner')
        );

        add_submenu_page(
            'powertools',
            __('System Info', 'powertools'),
            __('System Info', 'powertools'),
            'manage_options',
            'powertools-system-info',
            array($this, 'render_system_info')
        );
    }

    /**
     * Render the homepage
     */
    public function render_homepage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }
        require_once POWERTOOLS_PLUGIN_DIR . 'admin/views/homepage.php';
    }

    /**
     * Render the CPT manager page
     */
    public function render_cpt_manager() {
        $cpt_manager = new \PowerTools\CPT\Manager();
        $cpt_manager->render_settings_page();
    }

    /**
     * Render the Gutenberg disabler page
     */
    public function render_gutenberg_disabler() {
        $gutenberg_disabler = new \PowerTools\Gutenberg\Gutenberg_Disabler();
        $gutenberg_disabler->render_settings_page();
    }

    /**
     * Render the comments disabler page
     */
    public function render_comments_disabler() {
        $comments_disabler = new \PowerTools\Comments\Comments_Disabler();
        $comments_disabler->render_settings_page();
    }

    /**
     * Render the toolbar toggler page
     */
    public function render_toolbar_toggler() {
        $toolbar_toggler = new \PowerTools\Toolbar\Toolbar_Toggler();
        $toolbar_toggler->render_settings_page();
    }

    /**
     * Render the HTML junk remover page
     */
    public function render_html_junk_remover() {
        $html_junk_remover = new \PowerTools\HTML\Junk_Remover();
        $html_junk_remover->render_settings_page();
    }

    /**
     * Render the junk cleaner page
     */
    public function render_junk_cleaner() {
        $junk_cleaner = new \PowerTools\Cleaner\Junk_Cleaner();
        $junk_cleaner->render_settings_page();
    }

    /**
     * Render the system info page
     */
    public function render_system_info() {
        $system_info = new \PowerTools\System\Info();
        $system_info->render_settings_page();
    }
}
