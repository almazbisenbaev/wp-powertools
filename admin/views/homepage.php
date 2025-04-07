<?php
/**
 * Homepage template for Power Tools plugin
 *
 * @package PowerTools
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap powertools-homepage">
    <div class="ptools-intro">
        <div class="ptools-intro-logo">
            <img src="<?php echo esc_url(POWERTOOLS_PLUGIN_URL . 'images/logo-icon.png'); ?>" alt="<?php esc_attr_e('Power Tools Logo', 'powertools'); ?>">
        </div>
        <div class="ptools-intro-content">
            <h1 class="ptools-intro-title"><?php esc_html_e('Welcome to Power Tools!', 'powertools'); ?></h1>
            <div class="ptools-intro-descr">
                <?php esc_html_e('Simple tools that solve common problems during WordPress development and maximize your productivity', 'powertools'); ?>
            </div>
        </div>
    </div>

    <div class="ptools-cards">
        <div class="ptools-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-cpt-manager')); ?>">
                <?php esc_html_e('CPT Manager', 'powertools'); ?>
            </a>
            <br>
            <?php esc_html_e('Easily create and manage custom post types', 'powertools'); ?>
        </div>

        <div class="ptools-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-toolbar-toggler')); ?>">
                <?php esc_html_e('Admin Toolbar Toggler', 'powertools'); ?>
            </a>
            <br>
            <?php esc_html_e('Replaces the admin toolbar with a nice toggler button', 'powertools'); ?>
        </div>

        <div class="ptools-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-gutenberg-disabler')); ?>">
                <?php esc_html_e('Gutenberg Disabler', 'powertools'); ?>
            </a>
            <br>
            <?php esc_html_e('Return the legacy editor for specific post types', 'powertools'); ?>
        </div>

        <div class="ptools-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-html-junk-remover')); ?>">
                <?php esc_html_e('HTML Junk Remover', 'powertools'); ?>
            </a>
            <br>
            <?php esc_html_e('This tool removes the useless lines of code from HTML (such as WordPress version, emojis, etc.)', 'powertools'); ?>
        </div>

        <div class="ptools-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-junk-cleaner')); ?>">
                <?php esc_html_e('Junk Cleaner', 'powertools'); ?>
            </a>
            <br>
            <?php esc_html_e('This tool lets you delete the drafts and revisions that are taking up your disc space', 'powertools'); ?>
        </div>

        <div class="ptools-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-system-info')); ?>">
                <?php esc_html_e('System Info', 'powertools'); ?>
            </a>
            <br>
            <?php esc_html_e('View and export system info that can be useful for your IT guy or a tech support agent', 'powertools'); ?>
        </div>
    </div>

    <hr />
    <div class="powertools-footer">
        <p>
            <?php
            printf(
                /* translators: %s: Author name with link */
                esc_html__('Author: %s', 'powertools'),
                '<a target="_blank" href="https://github.com/almazbisenbaev">Almaz Bisenbaev</a>'
            );
            ?>
        </p>
        <p>
            <?php
            printf(
                /* translators: %s: Github repository URL */
                esc_html__('Github repository: %s', 'powertools'),
                '<a target="_blank" href="https://github.com/almazbisenbaev/wp-powertools">https://github.com/almazbisenbaev/wp-powertools</a>'
            );
            ?>
        </p>
    </div>
</div> 