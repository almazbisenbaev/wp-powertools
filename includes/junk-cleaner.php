<?php
/**
 * Junk Cleaner functionality
 *
 * @package PowerTools
 */

namespace PowerTools\Cleaner;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Junk_Cleaner
 */
class Junk_Cleaner {
    /**
     * Initialize the class
     */
    public function __construct() {
        // No initialization needed
    }

    /**
     * Render the junk cleaner settings page
     */
    public function render_settings_page() {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }

        // Handle deletion
        if (isset($_POST['delete_revisions_drafts']) && check_admin_referer('powertools_junk_cleaner')) {
            $this->delete_junk();
        }

        // Get the number of drafts and revisions
        $drafts_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft'");
        $revisions_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");

        ?>
        <div class="ptools-settings">
            <div class="ptools-settings-header">
                <h2 class="ptools-settings-title"><?php esc_html_e('Junk Cleaner', 'powertools'); ?></h2>
                <div class="ptools-settings-descr">
                    <?php esc_html_e('This tool lets you delete the drafts and revisions that are taking up your disc space', 'powertools'); ?>
                </div>
            </div>

            <div class="ptools-metabox">
                <div><?php printf(esc_html__('Total Drafts: %d', 'powertools'), $drafts_count); ?></div>
                <div><?php printf(esc_html__('Total Revisions: %d', 'powertools'), $revisions_count); ?></div>

                <hr>

                <form method="post">
                    <?php wp_nonce_field('powertools_junk_cleaner'); ?>
                    <input type="hidden" name="delete_revisions_drafts" value="1" />
                    <input type="submit" 
                           class="button button-primary" 
                           value="<?php esc_attr_e('Delete All Revisions & Drafts', 'powertools'); ?>" />
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Delete all drafts and revisions
     */
    private function delete_junk() {
        global $wpdb;

        // Delete drafts and revisions
        $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'draft' OR post_type = 'revision'");

        // Show success message
        add_settings_error(
            'powertools_messages',
            'powertools_message',
            __('All revisions and drafts have been deleted.', 'powertools'),
            'updated'
        );
    }
}

// Initialize the class
$junk_cleaner = new Junk_Cleaner();

