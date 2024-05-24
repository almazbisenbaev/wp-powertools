<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PowertoolsSystemInfo {

    public function __construct() {

        // Hook into the admin menu.
        add_action('admin_menu', [$this, 'add_admin_menu']);

    }


    public function add_admin_menu() {

        add_submenu_page(
            'powertools',
            'System Info',
            'System Info',
            'manage_options',
            'powertools_system_info',
            [$this, 'powertools_system_info_page'],
        );

    }



    public function powertools_system_info_page() { 
        global $wpdb;
    ?>
    
        <div class="ptools-settings">
    
            <h2 class="ptools-settings-title">System info</h2>
    
            <div class="ptools-metabox">
                <table class="wp-list-table widefat fixed striped">
    
                    <tr>
                        <td><strong>Site URL:</strong></td>
                        <td><?php echo get_bloginfo('url'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Home URL:</strong></td>
                        <td><?php echo get_bloginfo('wpurl'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>WP language:</strong></td>
                        <td><?php echo get_bloginfo('language'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Active theme:</strong></td>
                        <td><?php echo wp_get_theme(); ?></td>
                    </tr>
    
                    <tr>
                        <td><strong>WordPress version:</strong></td>
                        <td><?php bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server Software:</strong></td>
                        <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>MySQL version:</strong></td>
                        <td><?php echo $wpdb->db_version(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server IP:</strong></td>
                        <td><?php echo $_SERVER['SERVER_ADDR']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server name:</strong></td>
                        <td><?php echo $_SERVER['SERVER_NAME']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server port:</strong></td>
                        <td><?php echo $_SERVER['SERVER_PORT']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Document root:</strong></td>
                        <td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
                    </tr>
                </table>
    
                <div class="wrap">
                    <h2>PHP Configuration</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <tr>
                            <td>Max Execution Time:</td>
                            <td><?php echo ini_get('max_execution_time') . ' seconds'; ?></td>
                        </tr>
                        <tr>
                            <td>Memory Limit:</td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                        <tr>
                            <td>Upload Max Filesize:</td>
                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                    </table>
                </div>
    
                <div class="wrap">
                    <h2>Server Resources</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <tr>
                            <td>Available Disk Space:</td>
                            <td><?php echo round(disk_free_space('/') / (1024 * 1024), 2) . ' MB'; ?></td>
                        </tr>
                        <tr>
                            <td>Total Disk Space:</td>
                            <td><?php echo round(disk_total_space('/') / (1024 * 1024), 2) . ' MB'; ?></td>
                        </tr>
                    </table>
                </div>
    
                <?php
    
                    if (!current_user_can('manage_options')) {
                        echo __('You do not have sufficient permissions to access this page.');
                    } else {
    
                        // Get all plugins
                        $all_plugins = get_plugins();
                        $active_plugins = get_option('active_plugins');
    
                        echo '<div class="wrap">';
                        echo '<h2>Installed Plugins</h2>';
                        echo '<table class="wp-list-table widefat fixed striped">';
                        echo '<thead><tr><th>Name</th><th>Status</th><th>Version</th><th>URL</th></tr></thead>';
                        echo '<tbody>';
    
                        foreach ($all_plugins as $path => $plugin) {
                            // Check if the plugin is active
                            $status = in_array($path, $active_plugins) ? 'Active' : 'Disabled';
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
    
                    }
    
                ?>
    
            </div>
    
        </div>
    
    <?php
    }
    

}


$system_info_module = new PowertoolsSystemInfo();





