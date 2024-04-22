<?php

function powertools_hide_admin_bar_page() {

    if ( isset($_POST['save']) ) {

        if( isset($_POST['hide_admin_bar']) ){
            update_option('powertools_hide_admin_bar', 1);
        } else {
            update_option('powertools_hide_admin_bar', 0);
        }

        if( isset($_POST['remove_admin_bar_margin']) ){
            update_option('powertools_remove_admin_bar_margin', 1);
        } else {
            update_option('powertools_remove_admin_bar_margin', 0);
        }

    }

    $hide_admin_bar = get_option('powertools_hide_admin_bar');
    $remove_admin_bar_margin = get_option('powertools_remove_admin_bar_margin');

?>

    <div class="ptools-settings">

        <h2 class="ptools-settings-title">Hide admin bar</h2>
        <div>This option lets you hide the annoying admin bar and remove the 32px margin that appears when you're logged in</div>

        <form class="ptools-metabox" method="post">

            <label class="powertools-toggler" for="hide_admin_bar">
                <input type="checkbox" id="hide_admin_bar" name="hide_admin_bar" <?php checked(1, $hide_admin_bar); ?> />
                Hide admin bar
            </label>

            <label class="powertools-toggler" for="remove_admin_bar_margin">
                <input type="checkbox" id="remove_admin_bar_margin" name="remove_admin_bar_margin" <?php checked(1, $remove_admin_bar_margin); ?> />
                Keep admin bar, only remove 32px margin from &lt;html&gt;
            </label>

            <div class="ptools-metabox-footer">
                <input type="submit" name="save" value="Save Changes" class="button-primary">
            </div>

        </form>

    </div>

<?php
}


function powertools_hide_admin_bar() {

    $hide_admin_bar = get_option('powertools_hide_admin_bar');
    $remove_admin_bar_margin = get_option('powertools_remove_admin_bar_margin');

    if ($hide_admin_bar == 1) {
        add_filter('show_admin_bar', '__return_false');
    }
    
    if ($remove_admin_bar_margin == 1) {
        add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );
    }

}
