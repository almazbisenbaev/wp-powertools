<?php
/**
 * Insert Code functionality
 *
 * @package PowerTools
 */

namespace PowerTools\Code;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Insert_Code
 */
class Insert_Code {
    /**
     * Post Type name
     *
     * @var string
     */
    private const POST_TYPE = 'powertools_snippet';

    /**
     * Option name for storing code snippets (Legacy)
     *
     * @var string
     */
    private const OPTION_NAME = 'powertools_insert_code_snippets';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'maybe_migrate_data'));

        add_action('wp_head', array($this, 'render_head_snippets'));
        add_action('wp_body_open', array($this, 'render_body_open_snippets'));
        add_action('wp_footer', array($this, 'render_footer_snippets'));
        
        add_action('admin_post_powertools_create_snippet', array($this, 'handle_create_snippet'));
        add_action('admin_post_powertools_save_snippet', array($this, 'handle_save_snippet'));
        add_action('admin_post_powertools_delete_snippet', array($this, 'handle_delete_snippet'));
        add_action('admin_post_powertools_toggle_snippet', array($this, 'handle_toggle_snippet'));
    }

    /**
     * Register Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Code Snippets', 'post type general name', 'powertools'),
            'singular_name'      => _x('Code Snippet', 'post type singular name', 'powertools'),
            'menu_name'          => _x('Code Snippets', 'admin menu', 'powertools'),
            'name_admin_bar'     => _x('Code Snippet', 'add new on admin bar', 'powertools'),
            'add_new'            => _x('Add New', 'snippet', 'powertools'),
            'add_new_item'       => __('Add New Snippet', 'powertools'),
            'new_item'           => __('New Snippet', 'powertools'),
            'edit_item'          => __('Edit Snippet', 'powertools'),
            'view_item'          => __('View Snippet', 'powertools'),
            'all_items'          => __('All Snippets', 'powertools'),
            'search_items'       => __('Search Snippets', 'powertools'),
            'not_found'          => __('No snippets found.', 'powertools'),
            'not_found_in_trash' => __('No snippets found in Trash.', 'powertools')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => false, // We use our own UI
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array('slug' => self::POST_TYPE),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor')
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Migrate data from option to CPT (One-time)
     */
    public function maybe_migrate_data() {
        $legacy_snippets = get_option(self::OPTION_NAME);
        if (empty($legacy_snippets) || !is_array($legacy_snippets)) {
            return;
        }

        foreach ($legacy_snippets as $snippet) {
            $post_id = wp_insert_post(array(
                'post_title'   => $snippet['title'],
                'post_content' => $snippet['code'],
                'post_status'  => !empty($snippet['active']) ? 'publish' : 'draft',
                'post_type'    => self::POST_TYPE,
            ));

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_pt_type', $snippet['type']);
                update_post_meta($post_id, '_pt_location', $snippet['location']);
                update_post_meta($post_id, '_pt_rules', $snippet['rules']);
            }
        }

        // Clear legacy data
        delete_option(self::OPTION_NAME);
    }

    /**
     * Get all snippets
     *
     * @return array
     */
    private function get_snippets() {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => array('publish', 'draft'),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        $posts = get_posts($args);
        $snippets = array();

        foreach ($posts as $post) {
            $snippets[$post->ID] = array(
                'id'       => $post->ID,
                'title'    => $post->post_title,
                'code'     => $post->post_content,
                'type'     => get_post_meta($post->ID, '_pt_type', true),
                'location' => get_post_meta($post->ID, '_pt_location', true),
                'rules'    => get_post_meta($post->ID, '_pt_rules', true),
                'active'   => $post->post_status === 'publish'
            );
        }

        return $snippets;
    }

    /**
     * Render snippets for wp_head
     */
    public function render_head_snippets() {
        $this->render_snippets_by_location('wp_head');
    }

    /**
     * Render snippets for wp_body_open
     */
    public function render_body_open_snippets() {
        $this->render_snippets_by_location('wp_body_open');
    }

    /**
     * Render snippets for wp_footer
     */
    public function render_footer_snippets() {
        $this->render_snippets_by_location('wp_footer');
    }

    /**
     * Render snippets for a specific location
     *
     * @param string $location
     */
    private function render_snippets_by_location($location) {
        $snippets = $this->get_snippets();
        foreach ($snippets as $snippet) {
            if (empty($snippet['active']) || $snippet['location'] !== $location) {
                continue;
            }

            if ($this->should_render_snippet($snippet)) {
                $this->execute_snippet($snippet);
            }
        }
    }

    /**
     * Check if snippet should be rendered based on rules
     *
     * @param array $snippet
     * @return bool
     */
    private function should_render_snippet($snippet) {
        if (empty($snippet['rules']) || !is_array($snippet['rules'])) {
            return true;
        }

        foreach ($snippet['rules'] as $rule) {
            $type = $rule['type'];
            $value = $rule['value'];

            switch ($type) {
                case 'user_role':
                    if ($value === 'guest') {
                        if (is_user_logged_in()) return false;
                    } else {
                        $user = wp_get_current_user();
                        if (!in_array($value, (array) $user->roles)) return false;
                    }
                    break;

                case 'page_id':
                    $ids = array_map('trim', explode(',', $value));
                    if (!is_page($ids) && !is_single($ids)) return false;
                    break;

                case 'post_type':
                    if (!is_singular($value)) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Execute/Output snippet code
     *
     * @param array $snippet
     */
    private function execute_snippet($snippet) {
        $code = $snippet['code'];
        $type = $snippet['type'];

        if ($type === 'php') {
            try {
                // Prepend opening tag to force PHP mode, then evaluate
                eval('?>' . '<?php ' . $code);
            } catch (\Throwable $e) {
                if (is_user_logged_in() && current_user_can('manage_options')) {
                    echo '<!-- PowerTools Error: ' . esc_html($e->getMessage()) . ' -->';
                }
            }
        } elseif ($type === 'js') {
            echo '<script type="text/javascript">' . $code . '</script>';
        } else {
            echo $code;
        }
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        $snippets = $this->get_snippets();
        $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $snippet_to_edit = $edit_id && isset($snippets[$edit_id]) ? $snippets[$edit_id] : null;

        // Get available roles
        $wp_roles = wp_roles();
        $roles = $wp_roles->get_names();

        // Get available post types
        $post_types = get_post_types(array('public' => true), 'objects');

        ?>
        <div class="powertools-wrap pt-fade-in">
            <header class="pt-intro">
                <div class="pt-intro-logo">
                    <span class="dashicons dashicons-code-standards" style="font-size: 48px; width: 48px; height: 48px; color: var(--pt-primary);"></span>
                </div>
                <div class="pt-intro-content">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h1 class="pt-h1"><?php esc_html_e('Insert Code', 'powertools'); ?></h1>
                            <p class="pt-p">
                                <?php esc_html_e('Add custom HTML, CSS, JS or PHP code snippets to your site without editing theme files.', 'powertools'); ?>
                            </p>
                        </div>
                        <?php if (!$edit_id): ?>
                            <button type="button" id="ptools-open-new-snippet-modal" class="pt-btn pt-btn-primary">
                                <span class="dashicons dashicons-plus"></span>
                                <?php esc_html_e('Add New Snippet', 'powertools'); ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-insert-code')); ?>" class="pt-btn pt-btn-secondary">
                                <span class="dashicons dashicons-arrow-left-alt"></span>
                                <?php esc_html_e('Back to List', 'powertools'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="pt-badge pt-badge-success" style="margin-bottom: 24px; width: 100%; box-sizing: border-box;">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Snippet saved successfully.', 'powertools'); ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_id && $snippet_to_edit): ?>
                <!-- Edit View -->
                <div class="pt-settings-container">
                    <div class="pt-settings-header">
                        <h2 class="pt-h2">
                            <?php esc_html_e('Edit Snippet', 'powertools'); ?> 
                            <span style="font-size: 14px; background: var(--pt-primary-soft); color: var(--pt-primary); padding: 4px 12px; border-radius: 20px; vertical-align: middle; margin-left: 8px;">
                                <?php echo esc_html(strtoupper($snippet_to_edit['type'])); ?>
                            </span>
                        </h2>
                    </div>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="powertools_save_snippet">
                        <input type="hidden" name="snippet_id" value="<?php echo esc_attr($edit_id); ?>">
                        <input type="hidden" name="type" value="<?php echo esc_attr($snippet_to_edit['type']); ?>">
                        <?php wp_nonce_field('powertools_save_snippet_nonce'); ?>

                        <div class="pt-settings-body">
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-bottom: 32px;">
                                <div class="pt-form-group">
                                    <label class="pt-form-label"><?php esc_html_e('Snippet Title', 'powertools'); ?></label>
                                    <input type="text" name="title" class="pt-form-control" value="<?php echo esc_attr($snippet_to_edit['title']); ?>" required placeholder="e.g. Google Analytics">
                                </div>
                                <div class="pt-form-group">
                                    <label class="pt-form-label"><?php esc_html_e('Location', 'powertools'); ?></label>
                                    <select name="location" class="pt-form-control">
                                        <option value="wp_head" <?php selected($snippet_to_edit['location'], 'wp_head'); ?>>Site Header (wp_head)</option>
                                        <option value="wp_body_open" <?php selected($snippet_to_edit['location'], 'wp_body_open'); ?>>After Body Tag (wp_body_open)</option>
                                        <option value="wp_footer" <?php selected($snippet_to_edit['location'], 'wp_footer'); ?>>Site Footer (wp_footer)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-form-group" style="margin-bottom: 32px;">
                                <label class="pt-form-label"><?php esc_html_e('Conditional Logic (Optional)', 'powertools'); ?></label>
                                <div id="ptools-rules-container">
                                    <?php 
                                    $rules = !empty($snippet_to_edit['rules']) ? $snippet_to_edit['rules'] : array();
                                    if (empty($rules)) {
                                        $rules = array(array('type' => '', 'value' => ''));
                                    }
                                    foreach ($rules as $index => $rule): 
                                    ?>
                                        <div class="ptools-rule-row" style="display: flex; gap: 16px; margin-bottom: 16px; align-items: center; background: var(--pt-bg-page); padding: 20px; border-radius: var(--pt-radius-sm); border: 1px solid var(--pt-border-soft);">
                                            <select name="rules[<?php echo $index; ?>][type]" class="pt-form-control ptools-rule-type" style="flex: 1;">
                                                <option value=""><?php esc_html_e('No Rule', 'powertools'); ?></option>
                                                <option value="user_role" <?php selected($rule['type'], 'user_role'); ?>><?php esc_html_e('User Role', 'powertools'); ?></option>
                                                <option value="page_id" <?php selected($rule['type'], 'page_id'); ?>><?php esc_html_e('Page/Post ID', 'powertools'); ?></option>
                                                <option value="post_type" <?php selected($rule['type'], 'post_type'); ?>><?php esc_html_e('Post Type', 'powertools'); ?></option>
                                            </select>
                                            
                                            <div class="ptools-rule-value-container" style="flex: 2;">
                                                <input type="text" 
                                                       name="rules[<?php echo $index; ?>][value]" 
                                                       class="pt-form-control ptools-rule-value ptools-rule-value-text" 
                                                       value="<?php echo esc_attr($rule['value']); ?>" 
                                                       placeholder="Value (e.g. 12, 45)"
                                                       style="<?php echo in_array($rule['type'], array('user_role', 'post_type')) ? 'display:none;' : ''; ?>">

                                                <select name="rules[<?php echo $index; ?>][value_role]" 
                                                        class="pt-form-control ptools-rule-value ptools-rule-value-role" 
                                                        style="<?php echo $rule['type'] !== 'user_role' ? 'display:none;' : ''; ?>"
                                                        <?php echo $rule['type'] !== 'user_role' ? 'disabled' : ''; ?>>
                                                    <option value="guest" <?php selected($rule['value'], 'guest'); ?>><?php esc_html_e('Guest (Logged out)', 'powertools'); ?></option>
                                                    <?php foreach ($roles as $role_key => $role_name): ?>
                                                        <option value="<?php echo esc_attr($role_key); ?>" <?php selected($rule['value'], $role_key); ?>><?php echo esc_html($role_name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <select name="rules[<?php echo $index; ?>][value_post_type]" 
                                                        class="pt-form-control ptools-rule-value ptools-rule-value-post-type" 
                                                        style="<?php echo $rule['type'] !== 'post_type' ? 'display:none;' : ''; ?>"
                                                        <?php echo $rule['type'] !== 'post_type' ? 'disabled' : ''; ?>>
                                                    <?php foreach ($post_types as $pt): ?>
                                                        <option value="<?php echo esc_attr($pt->name); ?>" <?php selected($rule['value'], $pt->name); ?>><?php echo esc_html($pt->label); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <button type="button" class="ptools-remove-rule" style="background: none; border: none; color: var(--pt-danger); cursor: pointer; padding: 4px;">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="ptools-add-rule" class="pt-btn pt-btn-secondary" style="margin-top: 12px;">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php esc_html_e('Add Rule', 'powertools'); ?>
                                </button>
                            </div>

                            <div class="pt-form-group">
                                <label class="pt-form-label"><?php esc_html_e('Code Snippet', 'powertools'); ?></label>
                                <div id="ptools-code-editor-container" style="border: 1px solid var(--pt-border); border-radius: var(--pt-radius-sm); overflow: hidden; background: #fff;">
                                    <div id="ptools-tag-top" style="background: var(--pt-bg-page); padding: 8px 16px; font-family: monospace; font-size: 13px; color: var(--pt-text-light); border-bottom: 1px solid var(--pt-border-soft); display: none;"></div>
                                    <input type="hidden" id="ptools-code-type" value="<?php echo esc_attr($snippet_to_edit['type']); ?>">
                                    <textarea name="code" id="ptools-code-editor" class="pt-form-control" style="height: 450px; border: none; border-radius: 0; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;" required><?php echo esc_textarea($snippet_to_edit['code']); ?></textarea>
                                    <div id="ptools-tag-bottom" style="background: var(--pt-bg-page); padding: 8px 16px; font-family: monospace; font-size: 13px; color: var(--pt-text-light); border-top: 1px solid var(--pt-border-soft); display: none;"></div>
                                </div>
                                <p class="pt-text-muted" style="font-size: 13px; margin-top: 12px;">
                                    <?php esc_html_e('Tip: Do not include opening/closing tags (like <?php ?> or <script>) as they are added automatically based on the snippet type.', 'powertools'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="pt-settings-footer">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-insert-code')); ?>" class="pt-btn pt-btn-secondary">
                                <?php esc_html_e('Cancel', 'powertools'); ?>
                            </a>
                            <button type="submit" class="pt-btn pt-btn-primary">
                                <?php esc_attr_e('Save Snippet', 'powertools'); ?>
                            </button>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- List View -->
                <div class="pt-settings-container">
                    <?php if (empty($snippets)): ?>
                        <div style="padding: 80px 40px; text-align: center;">
                            <div style="width: 80px; height: 80px; background: var(--pt-bg-page); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                                <span class="dashicons dashicons-code-standards" style="font-size: 40px; width: 40px; height: 40px; color: var(--pt-text-light);"></span>
                            </div>
                            <h3 class="pt-h2" style="margin-bottom: 8px;"><?php esc_html_e('No snippets found', 'powertools'); ?></h3>
                            <p class="pt-p" style="max-width: 400px; margin: 0 auto 32px;">
                                <?php esc_html_e('Create your first code snippet to start adding custom functionality to your WordPress site.', 'powertools'); ?>
                            </p>
                            <button type="button" onclick="document.getElementById('ptools-open-new-snippet-modal').click()" class="pt-btn pt-btn-primary">
                                <?php esc_html_e('Create First Snippet', 'powertools'); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none; background: transparent;">
                                <thead>
                                    <tr>
                                        <th style="padding: 20px 32px; font-weight: 600; background: var(--pt-bg-page); border-bottom: 1px solid var(--pt-border);"><?php esc_html_e('Snippet Name', 'powertools'); ?></th>
                                        <th style="padding: 20px 32px; font-weight: 600; width: 120px; background: var(--pt-bg-page); border-bottom: 1px solid var(--pt-border);"><?php esc_html_e('Type', 'powertools'); ?></th>
                                        <th style="padding: 20px 32px; font-weight: 600; width: 180px; background: var(--pt-bg-page); border-bottom: 1px solid var(--pt-border);"><?php esc_html_e('Location', 'powertools'); ?></th>
                                        <th style="padding: 20px 32px; font-weight: 600; width: 140px; text-align: right; background: var(--pt-bg-page); border-bottom: 1px solid var(--pt-border);"><?php esc_html_e('Actions', 'powertools'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($snippets as $id => $snippet): ?>
                                        <tr>
                                            <td style="padding: 20px 32px; vertical-align: middle;">
                                                <a href="<?php echo esc_url(add_query_arg('edit', $id)); ?>" style="font-weight: 600; font-size: 15px; text-decoration: none; color: var(--pt-text-main); display: block;">
                                                    <?php echo esc_html($snippet['title']); ?>
                                                </a>
                                            </td>
                                            <td style="padding: 20px 32px; vertical-align: middle;">
                                                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: var(--pt-primary-soft); padding: 4px 10px; border-radius: 4px; color: var(--pt-primary);">
                                                    <?php echo esc_html($snippet['type']); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 20px 32px; vertical-align: middle;">
                                                <span style="color: var(--pt-text-muted); font-size: 14px;">
                                                    <?php echo esc_html(str_replace('wp_', '', $snippet['location'])); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 20px 32px; text-align: right; vertical-align: middle;">
                                                <div style="display: flex; gap: 12px; justify-content: flex-end; align-items: center;">
                                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                                        <input type="hidden" name="action" value="powertools_toggle_snippet">
                                                        <input type="hidden" name="snippet_id" value="<?php echo esc_attr($id); ?>">
                                                        <?php wp_nonce_field('powertools_toggle_snippet_nonce'); ?>
                                                        <label class="pt-toggle" style="transform: scale(0.8);">
                                                            <input type="checkbox" onchange="this.form.submit()" <?php checked(!empty($snippet['active'])); ?>>
                                                            <span class="pt-toggle-slider"></span>
                                                        </label>
                                                    </form>
                                                    <a href="<?php echo esc_url(add_query_arg('edit', $id)); ?>" style="color: var(--pt-text-muted); text-decoration: none;" title="Edit">
                                                        <span class="dashicons dashicons-edit"></span>
                                                    </a>
                                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this snippet?');">
                                                        <input type="hidden" name="action" value="powertools_delete_snippet">
                                                        <input type="hidden" name="snippet_id" value="<?php echo esc_attr($id); ?>">
                                                        <?php wp_nonce_field('powertools_delete_snippet_nonce'); ?>
                                                        <button type="submit" style="background: none; border: none; color: var(--pt-danger); cursor: pointer; padding: 0;">
                                                            <span class="dashicons dashicons-trash"></span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- New Snippet Modal -->
        <div id="ptools-new-snippet-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 100000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div class="pt-fade-in" style="background: #fff; width: 500px; border-radius: var(--pt-radius-lg); border: 1px solid var(--pt-border); overflow: hidden;">
                <div style="padding: 32px; border-bottom: 1px solid var(--pt-border-soft); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 20px; font-weight: 600;"><?php esc_html_e('Create New Snippet', 'powertools'); ?></h3>
                    <button type="button" id="ptools-close-new-snippet-modal" style="background: none; border: none; color: var(--pt-text-light); cursor: pointer;"><span class="dashicons dashicons-no-alt"></span></button>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="padding: 32px;">
                    <input type="hidden" name="action" value="powertools_create_snippet">
                    <?php wp_nonce_field('powertools_create_snippet_nonce'); ?>
                    
                    <div class="pt-form-group" style="margin-bottom: 32px;">
                        <label class="pt-form-label" style="margin-bottom: 16px;"><?php esc_html_e('Snippet Type', 'powertools'); ?></label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <label style="cursor: pointer; text-align: center; border: 1px solid var(--pt-border); padding: 20px; border-radius: var(--pt-radius); transition: var(--pt-transition); display: block;">
                                <input type="radio" name="type" value="html" checked style="display: none;">
                                <div style="font-weight: 700; font-size: 15px;">HTML</div>
                                <div style="font-size: 12px; color: var(--pt-text-light); margin-top: 4px;">Plain Text</div>
                            </label>
                            <label style="cursor: pointer; text-align: center; border: 1px solid var(--pt-border); padding: 20px; border-radius: var(--pt-radius); transition: var(--pt-transition); display: block;">
                                <input type="radio" name="type" value="js" style="display: none;">
                                <div style="font-weight: 700; font-size: 15px;">JS</div>
                                <div style="font-size: 12px; color: var(--pt-text-light); margin-top: 4px;">Scripts</div>
                            </label>
                            <label style="cursor: pointer; text-align: center; border: 1px solid var(--pt-border); padding: 20px; border-radius: var(--pt-radius); transition: var(--pt-transition); display: block;">
                                <input type="radio" name="type" value="php" style="display: none;">
                                <div style="font-weight: 700; font-size: 15px;">PHP</div>
                                <div style="font-size: 12px; color: var(--pt-text-light); margin-top: 4px;">Server Side</div>
                            </label>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 16px;">
                        <button type="button" onclick="document.getElementById('ptools-close-new-snippet-modal').click()" class="pt-btn pt-btn-secondary">
                            <?php esc_html_e('Cancel', 'powertools'); ?>
                        </button>
                        <button type="submit" class="pt-btn pt-btn-primary">
                            <?php esc_html_e('Continue', 'powertools'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            .CodeMirror {
                height: 450px;
                font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                font-size: 13px;
                line-height: 1.6;
            }
            #ptools-code-editor-container .CodeMirror {
                border: none;
            }
            #ptools-new-snippet-modal label:has(input:checked) {
                border-color: var(--pt-primary) !important;
                background: var(--pt-primary-soft) !important;
                color: var(--pt-primary) !important;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Modal Logic
                $('#ptools-open-new-snippet-modal').on('click', function() {
                    $('#ptools-new-snippet-modal').css('display', 'flex');
                });
                $('#ptools-close-new-snippet-modal').on('click', function() {
                    $('#ptools-new-snippet-modal').hide();
                });

                // Initialize CodeMirror
                var editor;
                if (typeof wp !== 'undefined' && wp.codeEditor && typeof ptoolsCodeEditorSettings !== 'undefined' && $('#ptools-code-editor').length) {
                    var editorSettings = $.extend(true, {}, ptoolsCodeEditorSettings);
                    editorSettings.codemirror.lineNumbers = true;
                    editorSettings.codemirror.indentUnit = 4;
                    editorSettings.codemirror.tabSize = 4;
                    
                    editor = wp.codeEditor.initialize($('#ptools-code-editor'), editorSettings);
                    
                    // Add change listener to sync back to textarea
                    editor.codemirror.on('change', function(cm) {
                        cm.save();
                    });
                }

                // Function to update tags and editor mode
                function updateCodeEditorUI() {
                    var type = $('#ptools-code-type').val();
                    if (!type) return;

                    var $top = $('#ptools-tag-top');
                    var $bottom = $('#ptools-tag-bottom');
                    var mode = 'text/html';

                    if (type === 'js') {
                          $top.text('<script type="text/javascript">').show();
                          $bottom.text('</' + 'script>').show();
                          mode = 'javascript';
                      } else if (type === 'php') {
                          $top.text('<' + '?php').show();
                          $bottom.text('?' + '>').show();
                          mode = 'application/x-httpd-php';
                      } else {
                        $top.hide();
                        $bottom.hide();
                        mode = 'text/html';
                    }

                    if (editor && editor.codemirror) {
                        editor.codemirror.setOption('mode', mode);
                    }
                }

                // Initial update
                updateCodeEditorUI();

                // Function to update rule value field based on type
                function updateRuleValueField(row) {
                    var type = row.find('.ptools-rule-type').val();
                    var container = row.find('.ptools-rule-value-container');
                    
                    container.find('.ptools-rule-value').hide().prop('disabled', true);
                    
                    if (type === 'user_role') {
                        container.find('.ptools-rule-value-role').show().prop('disabled', false);
                    } else if (type === 'post_type') {
                        container.find('.ptools-rule-value-post-type').show().prop('disabled', false);
                    } else {
                        container.find('.ptools-rule-value-text').show().prop('disabled', false);
                    }
                }

                // Handle rule type change
                $(document).on('change', '.ptools-rule-type', function() {
                    updateRuleValueField($(this).closest('.ptools-rule-row'));
                });

                $('#ptools-add-rule').on('click', function() {
                    var container = $('#ptools-rules-container');
                    var count = container.find('.ptools-rule-row').length;
                    var newRow = container.find('.ptools-rule-row').first().clone();
                    
                    // Reset fields
                    newRow.find('.ptools-rule-type').attr('name', 'rules[' + count + '][type]').val('');
                    newRow.find('.ptools-rule-value-text').attr('name', 'rules[' + count + '][value]').val('');
                    newRow.find('.ptools-rule-value-role').attr('name', 'rules[' + count + '][value_role]').val('guest');
                    newRow.find('.ptools-rule-value-post-type').attr('name', 'rules[' + count + '][value_post_type]').val('post');
                    
                    container.append(newRow);
                    updateRuleValueField(newRow);
                });

                $(document).on('click', '.ptools-remove-rule', function() {
                    if ($('#ptools-rules-container .ptools-rule-row').length > 1) {
                        $(this).closest('.ptools-rule-row').remove();
                    } else {
                        $(this).closest('.ptools-rule-row').find('select').val('');
                        $(this).closest('.ptools-rule-row').find('input').val('');
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Handle quick creation of a snippet
     */
    public function handle_create_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_create_snippet_nonce');

        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'html';
        
        $post_id = wp_insert_post(array(
            'post_title'   => sprintf(__('New %s Snippet', 'powertools'), strtoupper($type)),
            'post_content' => '',
            'post_type'    => self::POST_TYPE,
            'post_status'  => 'draft'
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_pt_type', $type);
            update_post_meta($post_id, '_pt_location', 'wp_head');
            update_post_meta($post_id, '_pt_rules', array());
            
            wp_redirect(admin_url('admin.php?page=powertools-insert-code&edit=' . $post_id));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=powertools-insert-code&error=1'));
        exit;
    }

    /**
     * Handle saving a snippet
     */
    public function handle_save_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_save_snippet_nonce');

        $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
        if (!$snippet_id) return;

        $title    = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : 'wp_head';
        $code     = isset($_POST['code']) ? $_POST['code'] : ''; // We want to keep the raw code
        $rules    = isset($_POST['rules']) ? $_POST['rules'] : array();

        // Process rules
        $processed_rules = array();
        foreach ($rules as $rule) {
            if (empty($rule['type'])) continue;

            $value = $rule['value'];
            if ($rule['type'] === 'user_role') {
                $value = $rule['value_role'];
            } elseif ($rule['type'] === 'post_type') {
                $value = $rule['value_post_type'];
            }

            $processed_rules[] = array(
                'type'  => sanitize_text_field($rule['type']),
                'value' => sanitize_text_field($value)
            );
        }

        wp_update_post(array(
            'ID'           => $snippet_id,
            'post_title'   => $title,
            'post_content' => $code,
        ));

        update_post_meta($snippet_id, '_pt_location', $location);
        update_post_meta($snippet_id, '_pt_rules', $processed_rules);

        wp_redirect(admin_url('admin.php?page=powertools-insert-code&success=1&edit=' . $snippet_id));
        exit;
    }

    /**
     * Handle deleting a snippet
     */
    public function handle_delete_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_delete_snippet_nonce');

        $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
        if ($snippet_id) {
            wp_delete_post($snippet_id, true);
        }

        wp_redirect(admin_url('admin.php?page=powertools-insert-code'));
        exit;
    }

    /**
     * Handle toggling a snippet
     */
    public function handle_toggle_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_toggle_snippet_nonce');

        $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
        if ($snippet_id) {
            $post = get_post($snippet_id);
            $new_status = $post->post_status === 'publish' ? 'draft' : 'publish';
            wp_update_post(array(
                'ID'          => $snippet_id,
                'post_status' => $new_status
            ));
        }

        wp_redirect(admin_url('admin.php?page=powertools-insert-code'));
        exit;
    }
}
