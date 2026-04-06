<?php
/**
 * Gutenberg Disabler functionality
 *
 * @package PowerTools
 */

namespace PowerTools\Gutenberg;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Gutenberg_Disabler
 */
class Gutenberg_Disabler {
    /**
     * Option name for storing Gutenberg disabler settings
     *
     * @var string
     */
    private const OPTION_NAME = 'powertools_disable_gutenberg';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_disable_gutenberg'));
    }

    /**
     * Render the Gutenberg disabler settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        // Handle form submission
        if (isset($_POST['save']) && check_admin_referer('powertools_gutenberg_disabler')) {
            $this->save_settings();
        }

        $is_gutenberg_disabled = get_option(self::OPTION_NAME);
        ?>
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-edit" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <h1 class="pt-h1"><?php esc_html_e('Gutenberg Disabler', 'powertools'); ?></h1>
                    <p class="pt-p">
                        <?php esc_html_e('Switch back to the Classic Editor and disable Gutenberg block library assets.', 'powertools'); ?>
                    </p>
                </div>
            </header>

            <form class="pt-settings-container" method="post">
                <?php wp_nonce_field('powertools_gutenberg_disabler'); ?>
                
                <div class="pt-settings-header">
                    <h2 class="pt-h2"><?php esc_html_e('Settings', 'powertools'); ?></h2>
                </div>

                <div class="pt-settings-body">
                    <div class="pt-form-group">
                        <label class="pt-checkbox-label">
                            <input type="checkbox" 
                                   name="disable_gutenberg" 
                                   <?php checked(1, $is_gutenberg_disabled); ?> />
                            <div>
                                <div style="font-weight: 600;"><?php esc_html_e('Disable Block Editor', 'powertools'); ?></div>
                                <div class="pt-text-muted" style="font-size: 14px;"><?php esc_html_e('This will restore the Classic Editor for all post types and disable block-based widgets.', 'powertools'); ?></div>
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
     * Save the Gutenberg disabler settings
     */
    private function save_settings() {
        $is_disabled = isset($_POST['disable_gutenberg']) ? 1 : 0;
        update_option(self::OPTION_NAME, $is_disabled);
        
        add_settings_error(
            'powertools_messages',
            'powertools_message',
            __('Settings Saved', 'powertools'),
            'updated'
        );
    }

    /**
     * Disable Gutenberg if the option is enabled
     */
    public function maybe_disable_gutenberg() {
        if (get_option(self::OPTION_NAME) !== '1') {
            return;
        }

        // Disable Gutenberg on the back end
        add_filter('use_block_editor_for_post', '__return_false');

        // Disable Gutenberg for widgets
        add_filter('use_widgets_block_editor', '__return_false');

        // Remove Gutenberg assets on the front end
        add_action('wp_enqueue_scripts', array($this, 'remove_gutenberg_assets'), 20);
    }

    /**
     * Remove Gutenberg assets from the front end
     */
    public function remove_gutenberg_assets() {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
    }
}

// Initialize the class

