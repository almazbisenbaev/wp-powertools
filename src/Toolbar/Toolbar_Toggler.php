<?php
/**
 * Toolbar Toggler functionality
 *
 * @package PowerTools
 */

namespace PowerTools\Toolbar;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Toolbar_Toggler
 */
class Toolbar_Toggler {
    /**
     * Option name for storing toolbar toggler settings
     *
     * @var string
     */
    private const OPTION_NAME = 'powertools_toolbar_toggler_enabled';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('wp_footer', array($this, 'maybe_add_toolbar_toggler'));
    }

    /**
     * Render the toolbar toggler settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        // Handle form submission
        if (isset($_POST['save']) && check_admin_referer('powertools_toolbar_toggler')) {
            $this->save_settings();
        }

        $is_toolbar_toggler_enabled = get_option(self::OPTION_NAME);
        ?>
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-admin-generic" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <h1 class="pt-h1"><?php esc_html_e('Toolbar Toggler', 'powertools'); ?></h1>
                    <p class="pt-p">
                        <?php esc_html_e('Minimize distractions by hiding the admin toolbar behind a sleek toggle button.', 'powertools'); ?>
                    </p>
                </div>
            </header>

            <form class="pt-settings-container" method="post">
                <?php wp_nonce_field('powertools_toolbar_toggler'); ?>
                
                <div class="pt-settings-header">
                    <h2 class="pt-h2"><?php esc_html_e('Settings', 'powertools'); ?></h2>
                </div>

                <div class="pt-settings-body">
                    <div class="pt-form-group">
                        <label class="pt-checkbox-label">
                            <input type="checkbox" 
                                   name="enable_toolbar_toggler" 
                                   <?php checked(1, $is_toolbar_toggler_enabled); ?> />
                            <div>
                                <div style="font-weight: 600;"><?php esc_html_e('Enable Toolbar Toggler', 'powertools'); ?></div>
                                <div class="pt-text-muted" style="font-size: 14px;"><?php esc_html_e('This will hide the default WordPress toolbar and show a small toggle button in the top-left corner instead.', 'powertools'); ?></div>
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
     * Save the toolbar toggler settings
     */
    private function save_settings() {
        $is_enabled = isset($_POST['enable_toolbar_toggler']) ? 1 : 0;
        update_option(self::OPTION_NAME, $is_enabled);
        
        add_settings_error(
            'powertools_messages',
            'powertools_message',
            __('Settings Saved', 'powertools'),
            'updated'
        );
    }

    /**
     * Add the toolbar toggler if enabled and user is admin
     */
    public function maybe_add_toolbar_toggler() {
        if (!current_user_can('manage_options') || get_option(self::OPTION_NAME) !== '1') {
            return;
        }

        $this->add_toolbar_toggler();
    }

    /**
     * Add the toolbar toggler HTML, CSS, and JavaScript
     */
    private function add_toolbar_toggler() {
        ?>
        <style>
            html {
                margin-top: 0 !important;
            }
            #wpadminbar {
                display: none;
            }
            #toolbar-toggle-button {
                position: fixed;
                top: 10px;
                left: 10px;
                background-color: #000;
                color: #fff;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                cursor: pointer;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }
            body.toolbar-visible #wpadminbar {
                display: block;
            }
        </style>

        <div id="toolbar-toggle-button" class="dashicons dashicons-admin-generic"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toggleButton = document.getElementById('toolbar-toggle-button');
                toggleButton.addEventListener('click', function() {
                    document.body.classList.toggle('toolbar-visible');
                });
            });
        </script>
        <?php
    }
}

