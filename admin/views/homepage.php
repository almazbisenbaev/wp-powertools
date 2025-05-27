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

$tool_manager = new \PowerTools\Admin\Tool_Manager();
$available_tools = $tool_manager->get_available_tools();
$active_tools = $tool_manager->get_active_tools();
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
        <?php foreach ($available_tools as $tool_id => $tool): ?>
            <div class="ptools-card">
                <div class="ptools-card-header">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ptools-tool-toggle">
                        <input type="hidden" name="action" value="powertools_toggle_tool">
                        <?php wp_nonce_field('powertools_toggle_tool', 'powertools_toggle_nonce'); ?>
                        <input type="hidden" name="tool_id" value="<?php echo esc_attr($tool_id); ?>">
                        <input type="hidden" name="is_active" value="<?php echo isset($active_tools[$tool_id]) && $active_tools[$tool_id] ? '0' : '1'; ?>">
                        <button type="submit" 
                                class="ptools-btn ptools-btn-toggle <?php echo isset($active_tools[$tool_id]) && $active_tools[$tool_id] ? 'ptools-btn--active' : 'ptools-btn--inactive'; ?>"
                                data-status="<?php echo isset($active_tools[$tool_id]) && $active_tools[$tool_id] ? esc_attr__('On', 'powertools') : esc_attr__('Off', 'powertools'); ?>">
                        </button>
                    </form>
                </div>
                <div class="ptools-card-content">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-' . str_replace('_', '-', $tool_id))); ?>">
                        <?php echo esc_html($tool['name']); ?>
                    </a>
                    <br>
                    <?php echo esc_html($tool['description']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="powertools-footer">
        <p>
            <?php
            printf(
                /* translators: %s: Author name with link */
                esc_html__('Author: %s', 'powertools'),
                '<a target="_blank" href="https://almazb.vercel.app">Almaz Bisenbaev</a>'
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