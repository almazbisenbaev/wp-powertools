<?php
/**
 * Tool Manager functionality
 *
 * @package PowerTools
 */

namespace PowerTools\Admin;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Tool_Manager
 */
class Tool_Manager {
    /**
     * Option name for storing active tools
     *
     * @var string
     */
    private const OPTION_NAME = 'powertools_active_tools';

    /**
     * List of available tools
     *
     * @var array
     */
    private const AVAILABLE_TOOLS = [
        'cpt_manager' => [
            'name' => 'CPT Manager',
            'description' => 'Easily create and manage custom post types',
            'class' => 'PowerTools\\CPT\\Manager'
        ],
        'toolbar_toggler' => [
            'name' => 'Admin Toolbar Toggler',
            'description' => 'Replaces the admin toolbar with a nice toggler button',
            'class' => 'PowerTools\\Toolbar\\Toolbar_Toggler'
        ],
        'gutenberg_disabler' => [
            'name' => 'Gutenberg Disabler',
            'description' => 'Return the legacy editor for specific post types',
            'class' => 'PowerTools\\Gutenberg\\Gutenberg_Disabler'
        ],
        'html_junk_remover' => [
            'name' => 'HTML Junk Remover',
            'description' => 'This tool removes the useless lines of code from HTML (such as WordPress version, emojis, etc.)',
            'class' => 'PowerTools\\HTML\\Junk_Remover'
        ],
        'junk_cleaner' => [
            'name' => 'Junk Cleaner',
            'description' => 'This tool lets you delete the drafts and revisions that are taking up your disc space',
            'class' => 'PowerTools\\Cleaner\\Junk_Cleaner'
        ],
        'system_info' => [
            'name' => 'System Info',
            'description' => 'View and export system info that can be useful for your IT guy or a tech support agent',
            'class' => 'PowerTools\\System\\Info'
        ]
    ];

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_post_powertools_toggle_tool', array($this, 'handle_tool_toggle'));
    }

    /**
     * Get all available tools
     *
     * @return array
     */
    public function get_available_tools() {
        return self::AVAILABLE_TOOLS;
    }

    /**
     * Get active tools
     *
     * @return array
     */
    public function get_active_tools() {
        $active_tools = get_option(self::OPTION_NAME, array());
        if (!is_array($active_tools)) {
            $active_tools = array();
        }
        return $active_tools;
    }

    /**
     * Check if a tool is active
     *
     * @param string $tool_id
     * @return bool
     */
    public function is_tool_active($tool_id) {
        $active_tools = $this->get_active_tools();
        return isset($active_tools[$tool_id]) && $active_tools[$tool_id];
    }

    /**
     * Handle tool toggle action
     */
    public function handle_tool_toggle() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        if (!isset($_POST['powertools_toggle_nonce']) || !wp_verify_nonce($_POST['powertools_toggle_nonce'], 'powertools_toggle_tool')) {
            wp_die(__('Security check failed.', 'powertools'));
        }

        $tool_id = isset($_POST['tool_id']) ? sanitize_key($_POST['tool_id']) : '';
        if (!isset(self::AVAILABLE_TOOLS[$tool_id])) {
            wp_die(__('Invalid tool.', 'powertools'));
        }

        $active_tools = $this->get_active_tools();
        $active_tools[$tool_id] = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false;
        update_option(self::OPTION_NAME, $active_tools);

        wp_redirect(add_query_arg('page', 'powertools', admin_url('admin.php')));
        exit;
    }
} 