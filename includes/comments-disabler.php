<?php
/**
 * Comments Disabler functionality
 *
 * @package PowerTools
 */

namespace PowerTools\Comments;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Comments_Disabler
 */
class Comments_Disabler {
    /**
     * Option name for storing comments disabler settings
     *
     * @var string
     */
    private const OPTION_NAME = 'powertools_disable_comments';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_disable_comments'));
    }

    /**
     * Render the comments disabler settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        // Handle form submission
        if (isset($_POST['save']) && check_admin_referer('powertools_comments_disabler')) {
            $this->save_settings();
        }

        $is_comments_disabled = get_option(self::OPTION_NAME);
        ?>
        <div class="ptools-settings">
            <div class="ptools-settings-header">
                <h2 class="ptools-settings-title"><?php esc_html_e('Disable Comments', 'powertools'); ?></h2>
                <div class="ptools-settings-descr">
                    <?php esc_html_e('This setting completely disables the WordPress comments feature', 'powertools'); ?>
                </div>
            </div>

            <form class="ptools-metabox" method="post">
                <?php wp_nonce_field('powertools_comments_disabler'); ?>

                <label class="ptools-toggler" for="disable_comments">
                    <div class="ptools-toggler-input">
                        <input type="checkbox" 
                               id="disable_comments" 
                               name="disable_comments" 
                               <?php checked(1, $is_comments_disabled); ?> />
                    </div>
                    <div class="ptools-toggler-content">
                        <div><?php esc_html_e('Disable Comments', 'powertools'); ?></div>
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
     * Save the comments disabler settings
     */
    private function save_settings() {
        $is_disabled = isset($_POST['disable_comments']) ? 1 : 0;
        update_option(self::OPTION_NAME, $is_disabled);
        
        add_settings_error(
            'powertools_messages',
            'powertools_message',
            __('Settings Saved', 'powertools'),
            'updated'
        );
    }

    /**
     * Disable comments if the option is enabled
     */
    public function maybe_disable_comments() {
        if (get_option(self::OPTION_NAME) !== '1') {
            return;
        }

        // Disable support for comments and trackbacks in post types
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);

        // Hide existing comments
        add_filter('comments_array', '__return_empty_array', 10, 2);

        // Remove comments page from admin menu
        add_action('admin_menu', array($this, 'remove_comments_menu'));

        // Remove comments links from admin bar
        add_action('admin_bar_menu', array($this, 'remove_comments_admin_bar'), 999);

        // Remove comments metabox from dashboard
        add_action('admin_init', array($this, 'remove_comments_dashboard'));

        // Remove comments from admin bar
        add_action('wp_before_admin_bar_render', array($this, 'remove_comments_admin_bar_render'));

        // Disable comments widget
        add_action('widgets_init', array($this, 'disable_comments_widget'));

        // Redirect any user trying to access comments page
        add_action('admin_init', array($this, 'disable_comments_page'));

        // Remove comments metabox from post types
        add_action('admin_init', array($this, 'remove_comments_metabox'));
    }

    /**
     * Remove comments menu from admin
     */
    public function remove_comments_menu() {
        remove_menu_page('edit-comments.php');
    }

    /**
     * Remove comments links from admin bar
     */
    public function remove_comments_admin_bar() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }

    /**
     * Remove comments metabox from dashboard
     */
    public function remove_comments_dashboard() {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    /**
     * Remove comments from admin bar
     */
    public function remove_comments_admin_bar_render() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }

    /**
     * Disable comments widget
     */
    public function disable_comments_widget() {
        unregister_widget('WP_Widget_Recent_Comments');
        add_filter('show_recent_comments_widget_style', '__return_false');
    }

    /**
     * Redirect any user trying to access comments page
     */
    public function disable_comments_page() {
        if (is_admin() && get_current_screen()->id === 'edit-comments' && !isset($_GET['action'])) {
            wp_redirect(admin_url()); 
            exit;
        }
    }

    /**
     * Remove comments metabox from post types
     */
    public function remove_comments_metabox() {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
}

// Initialize the class
$comments_disabler = new Comments_Disabler(); 