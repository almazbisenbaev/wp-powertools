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
        <div class="ptools-settings">
            <div class="ptools-settings-header">
                <h2 class="ptools-settings-title"><?php esc_html_e('Disable Gutenberg editor', 'powertools'); ?></h2>
                <div class="ptools-settings-descr">
                    <?php esc_html_e('This setting disables the new editor and enables the legacy one', 'powertools'); ?>
                </div>
            </div>

            <form class="ptools-metabox" method="post">
                <?php wp_nonce_field('powertools_gutenberg_disabler'); ?>

                <label class="ptools-toggler" for="disable_gutenberg">
                    <div class="ptools-toggler-input">
                        <input type="checkbox" 
                               id="disable_gutenberg" 
                               name="disable_gutenberg" 
                               <?php checked(1, $is_gutenberg_disabled); ?> />
                    </div>
                    <div class="ptools-toggler-content">
                        <div><?php esc_html_e('Use Gutenberg Disabler', 'powertools'); ?></div>
                    </div>
                </label>

                <!-- <div class="ptools-field">
                    <div class="ptools-field-label">Disable Gutenberg for the following post types:</div>
                    <div class="ptools-field-instructions">Leave empty to disable for all post types</div>
                    <input type="text" value="">
                    <div class="ptools-field-hint">Comma-separated list. E.g: <i>post, page, portfolio</i></div>
                </div> -->

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

