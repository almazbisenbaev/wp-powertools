<?php

function powertools_junk_cleaner_page() {

    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Get all revisions and drafts
    $revisions = wp_get_post_revisions();
    $drafts = get_posts(array('post_status' => 'draft'));

    ?>

    <div class="ptools-settings">

        <div class="ptools-settings-header">
            <h2 class="ptools-settings-title">Junk Cleaner</h2>
            <div class="ptools-settings-descr">This tool lets you delete the drafts and revisions that are taking up your disc space</div>
        </div>

        <div class="ptools-metabox">

            <?php
                echo '<div>Total Revisions: ' . count($revisions) . '</div>';
                echo '<div>Total Drafts: ' . count($drafts) . '</div>';
            ?>

            <hr>

            <form method="post">
                <input type="hidden" name="delete_revisions_drafts" value="1" />
                <input type="submit" class="button button-primary" value="Delete All Revisions & Drafts" />
            </form>

        </div>

    </div>


    <?php

    // Handle deletion
    if (isset($_POST['delete_revisions_drafts'])) {
        foreach ($revisions as $revision) {
            wp_delete_post($revision->ID, true);
        }
        foreach ($drafts as $draft) {
            wp_delete_post($draft->ID, true);
        }
        echo '<p>All revisions and drafts have been deleted.</p>';
    }

}

