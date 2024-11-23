<?php

function powertools_toolbar_toggler_page() {

    if ( isset($_POST['save']) ) {

        if( isset($_POST['enable_toolbar_toggler']) ){
            update_option('powertools_toolbar_toggler_enabled', 1);
        } else {
            update_option('powertools_toolbar_toggler_enabled', 0);
        }

    }

    $is_toolbar_toggler_enabled = get_option('powertools_toolbar_toggler_enabled');

?>

    <div class="ptools-settings">

        <div class="ptools-settings-header">
            <h2 class="ptools-settings-title">Toolbar Toggler</h2>
            <div class="ptools-settings-descr">This setting with replace the admin toolbar with a nice toggler button</div>
        </div>

        <form class="ptools-metabox" method="post">

            <label class="ptools-toggler" for="enable_toolbar_toggler">
                <div class="ptools-toggler-input">
                    <input type="checkbox" id="enable_toolbar_toggler" name="enable_toolbar_toggler" <?php checked(1, $is_toolbar_toggler_enabled); ?> />
                </div>
                <div class="ptools-toggler-content">
                    <div>Enable Toolbar Toggler Button</div>
                </div>
            </label>

            <div class="ptools-metabox-footer">
                <input type="submit" name="save" value="Save Changes" class="button-primary">
            </div>

        </form>

    </div>

<?php
}

function powertools_toolbar_toggler() {

    $is_toolbar_toggler_enabled = get_option('powertools_toolbar_toggler_enabled');

    if ($is_toolbar_toggler_enabled == 1) { ?>

        <style>
            html {
                margin-top: 0 !important;
            }
            #wpadminbar {
                display: none;
            }
            #toolbar-toggle-button {
                position: fixed;
                top: 10px;
                left: 10px;
                background-color: #000;
                color: #fff;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                cursor: pointer;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }
            body.toolbar-visible #wpadminbar {
                display: block;
            }
        </style>

        <div id="toolbar-toggle-button" class="dashicons dashicons-admin-generic"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toggleButton = document.getElementById('toolbar-toggle-button');
                toggleButton.addEventListener('click', function() {
                    document.body.classList.toggle('toolbar-visible');
                });
            });
        </script>

    <?php }
}

