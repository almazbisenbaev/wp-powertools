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
    private const OPTION_NAME = 'powertools_gutenberg_settings';

    /**
     * Legacy option name
     *
     * @var string
     */
    private const LEGACY_OPTION_NAME = 'powertools_disable_gutenberg';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_migrate_settings'));
        add_action('init', array($this, 'maybe_disable_gutenberg'));
    }

    /**
     * Migrate settings from legacy option
     */
    public function maybe_migrate_settings() {
        $legacy = get_option(self::LEGACY_OPTION_NAME);
        if ($legacy !== false) {
            $settings = array(
                'enabled'    => ($legacy === '1' || $legacy === 1),
                'mode'       => 'all',
                'post_types' => array()
            );
            update_option(self::OPTION_NAME, $settings);
            delete_option(self::LEGACY_OPTION_NAME);
        }
    }

    /**
     * Get settings
     */
    private function get_settings() {
        $default = array(
            'enabled'    => false,
            'mode'       => 'all',
            'post_types' => array()
        );
        return get_option(self::OPTION_NAME, $default);
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

        $settings = $this->get_settings();
        $post_types = get_post_types(array('public' => true), 'objects');
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
                        <label class="pt-radio-label">
                            <input type="radio" 
                                   name="gutenberg_mode" 
                                   value="all"
                                   id="pt-gutenberg-mode-all"
                                   <?php checked($settings['enabled'] && $settings['mode'] === 'all'); ?> />
                            <div>
                                <div style="font-weight: 600;"><?php esc_html_e('Disable Globally', 'powertools'); ?></div>
                                <div class="pt-text-muted" style="font-size: 14px;"><?php esc_html_e('Completely disable the block editor for all post types and widgets.', 'powertools'); ?></div>
                            </div>
                        </label>

                        <label class="pt-radio-label">
                            <input type="radio" 
                                   name="gutenberg_mode" 
                                   value="selective"
                                   id="pt-gutenberg-mode-selective"
                                   <?php checked($settings['enabled'] && $settings['mode'] === 'selective'); ?> />
                            <div>
                                <div style="font-weight: 600;"><?php esc_html_e('Disable Selectively', 'powertools'); ?></div>
                                <div class="pt-text-muted" style="font-size: 14px;"><?php esc_html_e('Choose specific post types where you want to use the Classic Editor.', 'powertools'); ?></div>
                            </div>
                        </label>

                        <label class="pt-radio-label">
                            <input type="radio" 
                                   name="gutenberg_mode" 
                                   value="none"
                                   id="pt-gutenberg-mode-none"
                                   <?php checked(!$settings['enabled']); ?> />
                            <div>
                                <div style="font-weight: 600;"><?php esc_html_e('Keep Gutenberg Enabled', 'powertools'); ?></div>
                                <div class="pt-text-muted" style="font-size: 14px;"><?php esc_html_e('Do not disable the block editor.', 'powertools'); ?></div>
                            </div>
                        </label>
                    </div>

                    <div id="pt-gutenberg-post-types" style="<?php echo ($settings['enabled'] && $settings['mode'] === 'selective') ? '' : 'display: none;'; ?> border-top: 1px solid var(--pt-border); margin-top: 24px; padding-top: 24px;">
                        <label class="pt-label" style="display: block; margin-bottom: 12px; font-weight: 600;">
                            <?php esc_html_e('Select Post Types to Disable Gutenberg', 'powertools'); ?>
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                            <?php foreach ($post_types as $post_type) : ?>
                                <label class="pt-checkbox-label" style="padding: 8px;">
                                    <input type="checkbox" name="post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $settings['post_types']), true); ?> />
                                    <span style="font-weight: 500;"><?php echo esc_html($post_type->label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modeRadios = document.querySelectorAll('input[name="gutenberg_mode"]');
                const postTypesDiv = document.getElementById('pt-gutenberg-post-types');

                modeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        postTypesDiv.style.display = (this.value === 'selective') ? 'block' : 'none';
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Save the Gutenberg disabler settings
     */
    private function save_settings() {
        $mode = sanitize_text_field($_POST['gutenberg_mode'] ?? 'none');
        
        $settings = array(
            'enabled'    => ($mode !== 'none'),
            'mode'       => ($mode === 'selective') ? 'selective' : 'all',
            'post_types' => isset($_POST['post_types']) ? array_map('sanitize_text_field', $_POST['post_types']) : array()
        );
        
        update_option(self::OPTION_NAME, $settings);
        
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
        $settings = $this->get_settings();
        if (!$settings['enabled']) {
            return;
        }

        // Disable Gutenberg on the back end
        add_filter('use_block_editor_for_post', array($this, 'should_disable_gutenberg'), 10, 2);
        add_filter('use_block_editor_for_post_type', array($this, 'should_disable_gutenberg_for_post_type'), 10, 2);

        // Disable Gutenberg for widgets (only if global)
        if ($settings['mode'] === 'all') {
            add_filter('use_widgets_block_editor', '__return_false');
        }

        // Remove Gutenberg assets on the front end
        add_action('wp_enqueue_scripts', array($this, 'maybe_remove_gutenberg_assets'), 20);
    }

    /**
     * Check if Gutenberg should be disabled for a specific post
     */
    public function should_disable_gutenberg($use_block_editor, $post) {
        $settings = $this->get_settings();
        if (!$settings['enabled']) {
            return $use_block_editor;
        }

        if ($settings['mode'] === 'all') {
            return false;
        }

        if ($settings['mode'] === 'selective' && in_array($post->post_type, $settings['post_types'])) {
            return false;
        }

        return $use_block_editor;
    }

    /**
     * Check if Gutenberg should be disabled for a specific post type
     */
    public function should_disable_gutenberg_for_post_type($use_block_editor, $post_type) {
        $settings = $this->get_settings();
        if (!$settings['enabled']) {
            return $use_block_editor;
        }

        if ($settings['mode'] === 'all') {
            return false;
        }

        if ($settings['mode'] === 'selective' && in_array($post_type, $settings['post_types'])) {
            return false;
        }

        return $use_block_editor;
    }

    /**
     * Conditionally remove Gutenberg assets from the front end
     */
    public function maybe_remove_gutenberg_assets() {
        $should_remove = false;
        $settings = $this->get_settings();

        if ($settings['mode'] === 'all') {
            $should_remove = true;
        } elseif ($settings['mode'] === 'selective') {
            if (is_singular()) {
                $post = get_post();
                if ($post && in_array($post->post_type, $settings['post_types'])) {
                    $should_remove = true;
                }
            } else {
                // For archives, search results, etc., check if the main post type is disabled
                // This is a bit tricky, but we'll check if any of the disabled post types match
                // For simplicity, we'll only disable on singular for selective mode
                // Or we can check the global $post_type if set
                global $post_type;
                if ($post_type && in_array($post_type, $settings['post_types'])) {
                    $should_remove = true;
                }
            }
        }

        if ($should_remove) {
            $this->remove_gutenberg_assets();
        }
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

