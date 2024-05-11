<?php

function powertools_junk_cleaner_page() {

    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Handle deletion
    if (isset($_POST['delete_revisions_drafts'])) {
        $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'draft' OR post_type = 'revision'");
        echo '<p>All revisions and drafts have been deleted.</p>';
    }

    // Get the number of drafts and revisions
    $drafts_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft'");
    $revisions_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");

    ?>

    <div class="ptools-settings">

        <div class="ptools-settings-header">
            <h2 class="ptools-settings-title">Junk Cleaner</h2>
            <div class="ptools-settings-descr">This tool lets you delete the drafts and revisions that are taking up your disc space</div>
        </div>

        <div class="ptools-metabox">

            <?php
                echo '<div>Total Drafts: ' . $drafts_count . '</div>';
                echo '<div>Total Revisions: ' . $revisions_count . '</div>';
            ?>

            <hr>

            <form method="post">
                <input type="hidden" name="delete_revisions_drafts" value="1" />
                <input type="submit" class="button button-primary" value="Delete All Revisions & Drafts" />
            </form>

        </div>

    </div>


<?php
}

