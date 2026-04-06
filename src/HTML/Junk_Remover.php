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
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-html" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <h1 class="pt-h1"><?php esc_html_e('HTML Junk Remover', 'powertools'); ?></h1>
                    <p class="pt-p">
                        <?php esc_html_e('Clean up your website source code by removing unnecessary tags, scripts, and links from the HEAD section.', 'powertools'); ?>
                    </p>
                </div>
            </header>

            <form class="pt-settings-container" method="post">
                <?php wp_nonce_field('powertools_html_junk_remover'); ?>
                
                <div class="pt-settings-header">
                    <h2 class="pt-h2"><?php esc_html_e('Settings', 'powertools'); ?></h2>
                </div>

                <div class="pt-settings-body">
                    <div class="pt-form-group">
                        <label class="pt-checkbox-label">
                            <input type="checkbox" 
                                   name="enable_html_junk_remover" 
                                   <?php checked(1, $is_junk_remover_enabled); ?> />
                            <div>
                                <div style="font-weight: 600;"><?php esc_html_e('Remove HTML Junk', 'powertools'); ?></div>
                                <div class="pt-text-muted" style="font-size: 14px;"><?php esc_html_e('This will remove WordPress version tags, emojis, REST API links, and other discovery links from your site\'s head.', 'powertools'); ?></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-settings-footer">
                    <input type="submit" 
                           name="save" 
                           value="<?php esc_attr_e('Save Settings', 'powertools'); ?>" 
                           class="pt-btn pt-btn-primary">
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

