<?php
function powertools_gutenberg_disabler_page() {

    if ( isset($_POST['save']) ) {

        if( isset($_POST['disable_gutenberg']) ){
            update_option('powertools_disable_gutenberg', 1);
        } else {
            update_option('powertools_disable_gutenberg', 0);
        }

    }

    $is_gutenberg_disabled = get_option('powertools_disable_gutenberg');

?>

    <div class="ptools-settings">

        <div class="ptools-settings-header">
            <h2 class="ptools-settings-title">Disable Gutenberg editor</h2>
            <div class="ptools-settings-descr">This setting disable the new editor and enables the legacy one</div>
        </div>

        <form class="ptools-metabox" method="post">

            <label class="ptools-toggler" for="disable_gutenberg">
                <div class="ptools-toggler-input">
                    <input type="checkbox" id="disable_gutenberg" name="disable_gutenberg" <?php checked(1, $is_gutenberg_disabled); ?> />
                </div>
                <div class="ptools-toggler-content">
                    <div>Use Gutenberg Disabler</div>
                </div>
            </label>

            <!-- <div class="ptools-field">
                <div class="ptools-field-label">Disable Gutenberg for the following post types:</div>
                <div class="ptools-field-instructions">Leave empty to disable for all post types</div>
                <input type="text" value="">
                <div class="ptools-field-hint">Comma-separated list. E.g: <i>post, page, portfolio</i></div>
            </div> -->

            <div class="ptools-metabox-footer">
                <input type="submit" name="save" value="Save Changes" class="button-primary">
            </div>

        </form>

    </div>

<?php
}

function powertools_disable_gutenberg() {

    $is_gutenberg_disabled = get_option('powertools_disable_gutenberg');

    if ($is_gutenberg_disabled == 1) {

        // Disable Gutenberg on the back end.
        add_filter( 'use_block_editor_for_post', '__return_false' );

        // Disable Gutenberg for widgets.
        add_filter( 'use_widgets_block_editor', '__return_false' );

        add_action( 'wp_enqueue_scripts', function() {
            // Remove CSS on the front end.
            wp_dequeue_style( 'wp-block-library' );

            // Remove Gutenberg theme.
            wp_dequeue_style( 'wp-block-library-theme' );

            // Remove inline global CSS on the front end.
            wp_dequeue_style( 'global-styles' );

            // Remove classic-themes CSS for backwards compatibility for button blocks.
            wp_dequeue_style( 'classic-theme-styles' );
        }, 20 );

    }
    
}
