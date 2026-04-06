<?php
/**
 * System Info functionality
 *
 * @package PowerTools
 */

namespace PowerTools\System;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Info
 */
class Info {
    /**
     * Initialize the class
     */
    public function __construct() {
        // No initialization needed
    }

    /**
     * Render the system info page
     */
    public function render_settings_page() {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'powertools'));
        }
        ?>
        ?>
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-info" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <h1 class="pt-h1"><?php esc_html_e('System Information', 'powertools'); ?></h1>
                    <p class="pt-p">
                        <?php esc_html_e('Detailed overview of your WordPress environment, server configuration, and active plugins.', 'powertools'); ?>
                    </p>
                </div>
            </header>

            <div class="pt-settings-container" style="margin-bottom: 32px;">
                <div class="pt-settings-header">
                    <h2 class="pt-h2"><?php esc_html_e('Environment Overview', 'powertools'); ?></h2>
                </div>
                <div class="pt-settings-body" style="padding: 0;">
                    <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
                        <tr>
                            <td style="padding: 16px 32px;"><strong><?php esc_html_e('Site URL', 'powertools'); ?></strong></td>
                            <td style="padding: 16px 32px;"><?php echo esc_url(get_bloginfo('url')); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 16px 32px;"><strong><?php esc_html_e('WordPress Version', 'powertools'); ?></strong></td>
                            <td style="padding: 16px 32px;">
                                <span class="pt-badge pt-badge-success" style="padding: 4px 12px; font-size: 12px;">
                                    <?php echo esc_html(get_bloginfo('version')); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 16px 32px;"><strong><?php esc_html_e('PHP Version', 'powertools'); ?></strong></td>
                            <td style="padding: 16px 32px;"><?php echo esc_html(phpversion()); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 16px 32px;"><strong><?php esc_html_e('MySQL Version', 'powertools'); ?></strong></td>
                            <td style="padding: 16px 32px;"><?php echo esc_html($wpdb->db_version()); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 16px 32px;"><strong><?php esc_html_e('Server Software', 'powertools'); ?></strong></td>
                            <td style="padding: 16px 32px;"><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
                <div class="pt-settings-container">
                    <div class="pt-settings-header">
                        <h2 class="pt-h2"><?php esc_html_e('PHP Configuration', 'powertools'); ?></h2>
                    </div>
                    <div class="pt-settings-body" style="padding: 0;">
                        <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
                            <tr>
                                <td style="padding: 16px 32px;"><?php esc_html_e('Max Execution Time', 'powertools'); ?></td>
                                <td style="padding: 16px 32px;"><?php echo esc_html(ini_get('max_execution_time')) . 's'; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 16px 32px;"><?php esc_html_e('Memory Limit', 'powertools'); ?></td>
                                <td style="padding: 16px 32px;"><?php echo esc_html(ini_get('memory_limit')); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 16px 32px;"><?php esc_html_e('Upload Max Filesize', 'powertools'); ?></td>
                                <td style="padding: 16px 32px;"><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="pt-settings-container">
                    <div class="pt-settings-header">
                        <h2 class="pt-h2"><?php esc_html_e('Server Resources', 'powertools'); ?></h2>
                    </div>
                    <div class="pt-settings-body" style="padding: 0;">
                        <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
                            <tr>
                                <td style="padding: 16px 32px;"><?php esc_html_e('Available Disk Space', 'powertools'); ?></td>
                                <td style="padding: 16px 32px;"><?php echo esc_html(round(disk_free_space('/') / (1024 * 1024), 2)) . ' MB'; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 16px 32px;"><?php esc_html_e('Total Disk Space', 'powertools'); ?></td>
                                <td style="padding: 16px 32px;"><?php echo esc_html(round(disk_total_space('/') / (1024 * 1024), 2)) . ' MB'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="pt-settings-container">
                <div class="pt-settings-header">
                    <h2 class="pt-h2"><?php esc_html_e('Installed Plugins', 'powertools'); ?></h2>
                </div>
                <div class="pt-settings-body" style="padding: 0;">
                    <?php
                    $all_plugins = get_plugins();
                    $active_plugins = get_option('active_plugins');
                    ?>
                    <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
                        <thead>
                            <tr>
                                <th style="padding: 16px 32px; font-weight: 600; background: var(--pt-bg-page);"><?php esc_html_e('Plugin Name', 'powertools'); ?></th>
                                <th style="padding: 16px 32px; font-weight: 600; background: var(--pt-bg-page); width: 120px;"><?php esc_html_e('Status', 'powertools'); ?></th>
                                <th style="padding: 16px 32px; font-weight: 600; background: var(--pt-bg-page); width: 100px;"><?php esc_html_e('Version', 'powertools'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_plugins as $path => $plugin): 
                                $is_active = in_array($path, $active_plugins);
                                ?>
                                <tr>
                                    <td style="padding: 16px 32px;">
                                        <strong><?php echo esc_html($plugin['Name']); ?></strong>
                                    </td>
                                    <td style="padding: 16px 32px;">
                                        <span class="pt-badge <?php echo $is_active ? 'pt-badge-success' : ''; ?>" style="padding: 4px 10px; font-size: 11px; text-transform: uppercase;">
                                            <?php echo $is_active ? esc_html__('Active', 'powertools') : esc_html__('Inactive', 'powertools'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 32px;">
                                        <code style="background: none; padding: 0; color: var(--pt-text-muted);"><?php echo esc_html($plugin['Version']); ?></code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the class






