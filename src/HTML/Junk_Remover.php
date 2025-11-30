<?php
/**
 * HTML Junk Remover functionality
 *
 * @package PowerTools
 */

namespace PowerTools\HTML;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Junk_Remover
 */
class Junk_Remover {
    /**
     * Option name for storing HTML junk remover settings
     *
     * @var string
     */
    private const OPTION_NAME = 'powertools_remove_html_junk';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_remove_html_junk'));
    }

    /**
     * Render the HTML junk remover settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        // Handle form submission
        if (isset($_POST['save']) && check_admin_referer('powertools_html_junk_remover')) {
            $this->save_settings();
        }

        $is_junk_remover_enabled = get_option(self::OPTION_NAME);
        ?>
        <div class="ptools-settings">
            <div class="ptools-settings-header">
                <h2 class="ptools-settings-title"><?php esc_html_e('HTML Junk Remover', 'powertools'); ?></h2>
                <div class="ptools-settings-descr">
                    <?php esc_html_e('This tool removes the useless lines of code from HTML (such as WordPress version, emojis, etc.)', 'powertools'); ?>
                </div>
            </div>

            <form class="ptools-metabox" method="post">
                <?php wp_nonce_field('powertools_html_junk_remover'); ?>

                <label class="ptools-toggler">
                    <div class="ptools-toggler-input">
                        <input type="checkbox" 
                               id="enable_html_junk_remover" 
                               name="enable_html_junk_remover" 
                               <?php checked(1, $is_junk_remover_enabled); ?> />
                    </div>
                    <div class="ptools-toggler-content">
                        <div><?php esc_html_e('Remove HTML junk', 'powertools'); ?></div>
                        <div><i><?php esc_html_e('Remove version tags, emojis and stuff from HEAD', 'powertools'); ?></i></div>
                    </div>
                </label>

                <div class="ptools-metabox-footer">
                    <input type="submit" 
                           name="save" 
                           value="<?php esc_attr_e('Save Changes', 'powertools'); ?>" 
                           class="button-primary">
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Save the HTML junk remover settings
     */
    private function save_settings() {
        $is_enabled = isset($_POST['enable_html_junk_remover']) ? 1 : 0;
        update_option(self::OPTION_NAME, $is_enabled);
        
        add_settings_error(
            'powertools_messages',
            'powertools_message',
            __('Settings Saved', 'powertools'),
            'updated'
        );
    }

    /**
     * Remove HTML junk if enabled
     */
    public function maybe_remove_html_junk() {
        if (get_option(self::OPTION_NAME) !== '1') {
            return;
        }

        $this->remove_html_junk();
    }

    /**
     * Remove various HTML junk from WordPress
     */
    private function remove_html_junk() {
        // Remove WordPress version
        add_filter('the_generator', array($this, 'remove_version'));

        // Remove REST API links
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');

        // Remove WP Emoji
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }

    /**
     * Remove WordPress version
     *
     * @return string
     */
    public function remove_version() {
        return '';
    }
}

// Initialize the class

