<?php
/**
 * Main Plugin Class
 *
 * @package PowerTools
 */

namespace PowerTools;

use PowerTools\Admin\Admin_Menu;
use PowerTools\Admin\Tool_Manager;
use PowerTools\CPT\Manager as CPT_Manager;
use PowerTools\System\Info as System_Info;
use PowerTools\Comments\Comments_Disabler;
use PowerTools\Toolbar\Toolbar_Toggler;
use PowerTools\HTML\Junk_Remover;
use PowerTools\Cleaner\Junk_Cleaner;
use PowerTools\Gutenberg\Gutenberg_Disabler;

/**
 * Class Plugin
 */
class Plugin {

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain
        $this->load_textdomain();

        // Initialize admin menu
        add_action('plugins_loaded', array($this, 'init_admin_menu'));

        // Initialize tools
        add_action('init', array($this, 'init_tools'));

        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('powertools', false, dirname(POWERTOOLS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Initialize admin menu
     */
    public function init_admin_menu() {
        new Admin_Menu();
    }

    /**
     * Initialize tools based on their active state
     */
    public function init_tools() {
        $tool_manager = new Tool_Manager();
        $active_tools = $tool_manager->get_active_tools();

        // Initialize CPT manager if active
        if (isset($active_tools['cpt_manager']) && $active_tools['cpt_manager']) {
            new CPT_Manager();
        }

        // Initialize system info if active
        if (isset($active_tools['system_info']) && $active_tools['system_info']) {
            new System_Info();
        }

        // Initialize comments disabler if active
        if (isset($active_tools['comments_disabler']) && $active_tools['comments_disabler']) {
            new Comments_Disabler();
        }

        // Initialize toolbar toggler if active
        if (isset($active_tools['toolbar_toggler']) && $active_tools['toolbar_toggler']) {
            new Toolbar_Toggler();
        }

        // Initialize HTML junk remover if active
        if (isset($active_tools['html_junk_remover']) && $active_tools['html_junk_remover']) {
            new Junk_Remover();
        }

        // Initialize junk cleaner if active
        if (isset($active_tools['junk_cleaner']) && $active_tools['junk_cleaner']) {
            new Junk_Cleaner();
        }

        // Initialize Gutenberg disabler if active
        if (isset($active_tools['gutenberg_disabler']) && $active_tools['gutenberg_disabler']) {
            new Gutenberg_Disabler();
        }
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'powertools-admin-styles',
            POWERTOOLS_PLUGIN_URL . 'admin/css/powertools-admin.css',
            array(),
            POWERTOOLS_VERSION
        );
    }
}
