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
        $drafts_count    = (int) $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft'");
        $revisions_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");
        $is_clean        = ( $drafts_count === 0 && $revisions_count === 0 );

        ?>
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-trash" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <h1 class="pt-h1"><?php esc_html_e('Junk Cleaner', 'powertools'); ?></h1>
                    <p class="pt-p">
                        <?php esc_html_e('Free up database space by removing unnecessary drafts and post revisions.', 'powertools'); ?>
                    </p>
                </div>
            </header>

            <?php settings_errors('powertools_messages'); ?>

            <div class="pt-settings-container">
                <div class="pt-settings-header">
                    <h2 class="pt-h2"><?php esc_html_e('Database Overview', 'powertools'); ?></h2>
                </div>

                <div class="pt-settings-body">
                    <div class="pt-stats">
                        <div class="pt-stat-item">
                            <div class="pt-stat-value"><?php echo esc_html( $drafts_count ); ?></div>
                            <div class="pt-stat-label"><?php esc_html_e('Draft Posts', 'powertools'); ?></div>
                        </div>
                        <div class="pt-stat-item">
                            <div class="pt-stat-value"><?php echo esc_html( $revisions_count ); ?></div>
                            <div class="pt-stat-label"><?php esc_html_e('Post Revisions', 'powertools'); ?></div>
                        </div>
                    </div>

                    <?php if ( $is_clean ) : ?>
                    <div class="pt-badge pt-badge-success">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Your database is clean! No drafts or revisions found.', 'powertools'); ?>
                    </div>
                    <?php else : ?>
                    <div class="pt-badge pt-badge-warning">
                        <span class="dashicons dashicons-warning"></span>
                        <?php printf(
                            esc_html__('You have %1$d draft(s) and %2$d revision(s) that can be removed to free up database space.', 'powertools'),
                            $drafts_count,
                            $revisions_count
                        ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pt-settings-footer">
                    <form method="post">
                        <?php wp_nonce_field('powertools_junk_cleaner'); ?>
                        <input type="hidden" name="delete_revisions_drafts" value="1" />
                        <button type="submit"
                                class="pt-btn pt-btn-primary"
                                <?php disabled( $is_clean ); ?>>
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_attr_e('Clean Database Now', 'powertools'); ?>
                        </button>
                    </form>
                </div>
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


