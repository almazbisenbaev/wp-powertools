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

<div class="powertools-wrap pt-fade-in">
    <header class="pt-intro">
        <div class="pt-intro-logo">
            <img src="<?php echo esc_url(POWERTOOLS_PLUGIN_URL . 'images/logo-icon.png'); ?>" alt="<?php esc_attr_e('Power Tools Logo', 'powertools'); ?>">
        </div>
        <div class="pt-intro-content">
            <h1 class="pt-h1"><?php esc_html_e('Power Tools', 'powertools'); ?></h1>
            <p class="pt-p">
                <?php esc_html_e('Modern developer utilities to maximize your WordPress productivity.', 'powertools'); ?>
            </p>
        </div>
    </header>

    <div class="pt-grid">
        <?php foreach ($available_tools as $tool_id => $tool): 
            $is_active = isset($active_tools[$tool_id]) && $active_tools[$tool_id];
            $tool_slug = str_replace('_', '-', $tool_id);
            ?>
            <div class="pt-card">
                <div class="pt-card-header">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-' . $tool_slug)); ?>" class="pt-card-title">
                        <?php echo esc_html($tool['name']); ?>
                    </a>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pt-tool-toggle">
                        <input type="hidden" name="action" value="powertools_toggle_tool">
                        <?php wp_nonce_field('powertools_toggle_tool', 'powertools_toggle_nonce'); ?>
                        <input type="hidden" name="tool_id" value="<?php echo esc_attr($tool_id); ?>">
                        <input type="hidden" name="is_active" value="<?php echo $is_active ? '0' : '1'; ?>">
                        
                        <label class="pt-toggle">
                            <input type="checkbox" onchange="this.form.submit()" <?php checked($is_active); ?>>
                            <span class="pt-toggle-slider"></span>
                        </label>
                    </form>
                </div>
                
                <p class="pt-card-desc">
                    <?php echo esc_html($tool['description']); ?>
                </p>
                
                <div class="pt-card-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-' . $tool_slug)); ?>" 
                       class="pt-btn pt-btn-secondary <?php echo !$is_active ? 'disabled' : ''; ?>"
                       <?php echo !$is_active ? 'style="opacity: 0.5; pointer-events: none;"' : ''; ?>>
                        <?php esc_html_e('Configure', 'powertools'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <footer class="pt-footer">
        <p>
            <?php
            printf(
                /* translators: %s: Author name with link */
                esc_html__('Developed by %s', 'powertools'),
                '<a target="_blank" href="https://almazb.vercel.app">Almaz Bisenbaev</a>'
            );
            ?>
            &bull;
            <?php
            printf(
                /* translators: %s: Github repository URL */
                esc_html__('View on %s', 'powertools'),
                '<a target="_blank" href="https://github.com/almazbisenbaev/wp-powertools">GitHub</a>'
            );
            ?>
        </p>
    </footer>
</div>
