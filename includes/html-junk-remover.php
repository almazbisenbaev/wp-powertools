<?php
function powertools_html_junk_remover_page() {

    if ( isset($_POST['save']) ) {
        if( isset($_POST['enable_html_junk_remover']) ){
            update_option('powertools_remove_html_junk', 1);
        } else {
            update_option('powertools_remove_html_junk', 0);
        }
    }

    $is_junk_remover_enabled = get_option('powertools_remove_html_junk');

?>

    <div class="ptools-settings">

        <div class="ptools-settings-header">
            <h2 class="ptools-settings-title">HTML Junk Remover</h2>
            <div class="ptools-settings-descr">This tool removes the useless lines of code from HTML (such as WordPress version, emojis, etc.)</div>
        </div>

        <form class="ptools-metabox" method="post">

            <label for="enable_html_junk_remover">
                <input type="checkbox" id="enable_html_junk_remover" name="enable_html_junk_remover" <?php checked(1, $is_junk_remover_enabled); ?> />
                Remove HTML junk
            </label>
            <div>Remove version tags, emojis and stuff from HEAD</div>

            <div class="ptools-metabox-footer">
                <input type="submit" name="save" value="Save Changes" class="button-primary">
            </div>

        </form>

    </div>

<?php
}


function powertools_remove_html_junk() {

    $is_junk_remover_enabled = get_option('powertools_remove_html_junk');

    if ($is_junk_remover_enabled == 1) {

        // Remove WordPress version
        function bruteforce_remove_version() {
            return '';
        }
        add_filter('the_generator', 'bruteforce_remove_version');

        // I have no idea what these things are but let's remove them too
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);
        remove_action ('wp_head', 'rsd_link');
        remove_action( 'wp_head', 'wlwmanifest_link');
        remove_action( 'wp_head', 'wp_shortlink_wp_head');

        // Remove WP Emoji
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );

    }
    
}
