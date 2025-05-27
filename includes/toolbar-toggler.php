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
        <div class="ptools-settings">
            <div class="ptools-settings-header">
                <h2 class="ptools-settings-title"><?php esc_html_e('Toolbar Toggler', 'powertools'); ?></h2>
                <div class="ptools-settings-descr">
                    <?php esc_html_e('This setting will replace the admin toolbar with a nice toggler button', 'powertools'); ?>
                </div>
            </div>

            <form class="ptools-metabox" method="post">
                <?php wp_nonce_field('powertools_toolbar_toggler'); ?>

                <label class="ptools-toggler" for="enable_toolbar_toggler">
                    <div class="ptools-toggler-input">
                        <input type="checkbox" 
                               id="enable_toolbar_toggler" 
                               name="enable_toolbar_toggler" 
                               <?php checked(1, $is_toolbar_toggler_enabled); ?> />
                    </div>
                    <div class="ptools-toggler-content">
                        <div><?php esc_html_e('Enable Toolbar Toggler Button', 'powertools'); ?></div>
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

