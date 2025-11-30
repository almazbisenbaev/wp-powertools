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
        <div class="ptools-settings">
            <h2 class="ptools-settings-title"><?php esc_html_e('System Info', 'powertools'); ?></h2>

            <div class="ptools-metabox">
                <table class="wp-list-table widefat fixed striped">
                    <tr>
                        <td><strong><?php esc_html_e('Site URL:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_url(get_bloginfo('url')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Home URL:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_url(get_bloginfo('wpurl')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('WP language:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html(get_bloginfo('language')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Active theme:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html(wp_get_theme()); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('WordPress version:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Server Software:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('PHP Version:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html(phpversion()); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('MySQL version:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html($wpdb->db_version()); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Server IP:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html($_SERVER['SERVER_ADDR']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Server name:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html($_SERVER['SERVER_NAME']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Server port:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html($_SERVER['SERVER_PORT']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Document root:', 'powertools'); ?></strong></td>
                        <td><?php echo esc_html($_SERVER['DOCUMENT_ROOT']); ?></td>
                    </tr>
                </table>

                <div class="wrap">
                    <h2><?php esc_html_e('PHP Configuration', 'powertools'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <tr>
                            <td><?php esc_html_e('Max Execution Time:', 'powertools'); ?></td>
                            <td><?php echo esc_html(ini_get('max_execution_time')) . ' ' . esc_html__('seconds', 'powertools'); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Memory Limit:', 'powertools'); ?></td>
                            <td><?php echo esc_html(ini_get('memory_limit')); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Upload Max Filesize:', 'powertools'); ?></td>
                            <td><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="wrap">
                    <h2><?php esc_html_e('Server Resources', 'powertools'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <tr>
                            <td><?php esc_html_e('Available Disk Space:', 'powertools'); ?></td>
                            <td><?php echo esc_html(round(disk_free_space('/') / (1024 * 1024), 2)) . ' ' . esc_html__('MB', 'powertools'); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Total Disk Space:', 'powertools'); ?></td>
                            <td><?php echo esc_html(round(disk_total_space('/') / (1024 * 1024), 2)) . ' ' . esc_html__('MB', 'powertools'); ?></td>
                        </tr>
                    </table>
                </div>

                <?php
                // Get all plugins
                $all_plugins = get_plugins();
                $active_plugins = get_option('active_plugins');

                echo '<div class="wrap">';
                echo '<h2>' . esc_html__('Installed Plugins', 'powertools') . '</h2>';
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>' . esc_html__('Name', 'powertools') . '</th><th>' . esc_html__('Status', 'powertools') . '</th><th>' . esc_html__('Version', 'powertools') . '</th><th>' . esc_html__('URL', 'powertools') . '</th></tr></thead>';
                echo '<tbody>';

                foreach ($all_plugins as $path => $plugin) {
                    // Check if the plugin is active
                    $status = in_array($path, $active_plugins) ? __('Active', 'powertools') : __('Disabled', 'powertools');
                    $url = isset($plugin['PluginURI']) ? $plugin['PluginURI'] : 'N/A';

                    echo '<tr>';
                    echo '<td>' . esc_html($plugin['Name']) . '</td>';
                    echo '<td>' . esc_html($status) . '</td>';
                    echo '<td>' . esc_html($plugin['Version']) . '</td>';
                    echo '<td>' . esc_url($url) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';
                ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
$system_info = new Info();





