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
        <div class="ptools-settings">
            <div class="ptools-settings-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 class="ptools-settings-title"><?php esc_html_e('Insert Code', 'powertools'); ?></h2>
                    <div class="ptools-settings-descr">
                        <?php esc_html_e('Add custom HTML, CSS, JS or PHP code snippets to your site.', 'powertools'); ?>
                    </div>
                </div>
                <?php if (!$edit_id): ?>
                    <button type="button" id="ptools-open-new-snippet-modal" class="button button-primary" style="height: 40px; padding: 0 24px; border-radius: 12px;">
                        <span class="dashicons dashicons-plus" style="margin-top: 8px; margin-right: 4px;"></span>
                        <?php esc_html_e('Add New Snippet', 'powertools'); ?>
                    </button>
                <?php else: ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-insert-code')); ?>" class="button" style="height: 40px; line-height: 38px; padding: 0 24px; border-radius: 12px;">
                        <span class="dashicons dashicons-arrow-left-alt" style="margin-top: 8px; margin-right: 4px;"></span>
                        <?php esc_html_e('Back to List', 'powertools'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="ptools-notice ptools-notice--success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Settings saved successfully.', 'powertools'); ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_id && $snippet_to_edit): ?>
                <!-- Edit View -->
                <div class="ptools-metabox" style="margin-top: 0;">
                    <h3 class="ptools-metabox-title">
                        <?php esc_html_e('Edit Snippet', 'powertools'); ?> (<?php echo esc_html(strtoupper($snippet_to_edit['type'])); ?>)
                    </h3>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="powertools_save_snippet">
                        <input type="hidden" name="snippet_id" value="<?php echo esc_attr($edit_id); ?>">
                        <input type="hidden" name="type" value="<?php echo esc_attr($snippet_to_edit['type']); ?>">
                        <?php wp_nonce_field('powertools_save_snippet_nonce'); ?>

                        <div class="ptools-form-grid" style="grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div class="ptools-form-group">
                                <label class="ptools-form-label"><?php esc_html_e('Snippet Title', 'powertools'); ?></label>
                                <input type="text" name="title" class="ptools-form-input" value="<?php echo esc_attr($snippet_to_edit['title']); ?>" required placeholder="e.g. Google Analytics">
                            </div>
                            <div class="ptools-form-group">
                                <label class="ptools-form-label"><?php esc_html_e('Location', 'powertools'); ?></label>
                                <select name="location" class="ptools-form-input">
                                    <option value="wp_head" <?php selected($snippet_to_edit['location'], 'wp_head'); ?>>Site Header (wp_head)</option>
                                    <option value="wp_body_open" <?php selected($snippet_to_edit['location'], 'wp_body_open'); ?>>After Body Tag (wp_body_open)</option>
                                    <option value="wp_footer" <?php selected($snippet_to_edit['location'], 'wp_footer'); ?>>Site Footer (wp_footer)</option>
                                </select>
                            </div>
                        </div>

                        <div class="ptools-form-group" style="margin-bottom: 24px;">
                            <label class="ptools-form-label"><?php esc_html_e('Conditional Logic (Optional)', 'powertools'); ?></label>
                            <div id="ptools-rules-container">
                                <?php 
                                $rules = !empty($snippet_to_edit['rules']) ? $snippet_to_edit['rules'] : array();
                                if (empty($rules)) {
                                    $rules = array(array('type' => '', 'value' => ''));
                                }
                                foreach ($rules as $index => $rule): 
                                ?>
                                    <div class="ptools-rule-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                        <select name="rules[<?php echo $index; ?>][type]" class="ptools-form-input ptools-rule-type" style="flex: 1;">
                                            <option value=""><?php esc_html_e('No Rule', 'powertools'); ?></option>
                                            <option value="user_role" <?php selected($rule['type'], 'user_role'); ?>><?php esc_html_e('User Role', 'powertools'); ?></option>
                                            <option value="page_id" <?php selected($rule['type'], 'page_id'); ?>><?php esc_html_e('Page/Post ID', 'powertools'); ?></option>
                                            <option value="post_type" <?php selected($rule['type'], 'post_type'); ?>><?php esc_html_e('Post Type', 'powertools'); ?></option>
                                        </select>
                                        
                                        <div class="ptools-rule-value-container" style="flex: 2;">
                                            <!-- Default Input (Hidden by JS if needed) -->
                                            <input type="text" 
                                                   name="rules[<?php echo $index; ?>][value]" 
                                                   class="ptools-form-input ptools-rule-value ptools-rule-value-text" 
                                                   value="<?php echo esc_attr($rule['value']); ?>" 
                                                   placeholder="Value (e.g. 12, 45)"
                                                   style="<?php echo in_array($rule['type'], array('user_role', 'post_type')) ? 'display:none;' : ''; ?>">

                                            <!-- User Role Select -->
                                            <select name="rules[<?php echo $index; ?>][value_role]" 
                                                    class="ptools-form-input ptools-rule-value ptools-rule-value-role" 
                                                    style="<?php echo $rule['type'] !== 'user_role' ? 'display:none;' : ''; ?>"
                                                    <?php echo $rule['type'] !== 'user_role' ? 'disabled' : ''; ?>>
                                                <option value="guest" <?php selected($rule['value'], 'guest'); ?>><?php esc_html_e('Guest (Logged out)', 'powertools'); ?></option>
                                                <?php foreach ($roles as $role_key => $role_name): ?>
                                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected($rule['value'], $role_key); ?>><?php echo esc_html($role_name); ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                            <!-- Post Type Select -->
                                            <select name="rules[<?php echo $index; ?>][value_post_type]" 
                                                    class="ptools-form-input ptools-rule-value ptools-rule-value-post-type" 
                                                    style="<?php echo $rule['type'] !== 'post_type' ? 'display:none;' : ''; ?>"
                                                    <?php echo $rule['type'] !== 'post_type' ? 'disabled' : ''; ?>>
                                                <?php foreach ($post_types as $pt): ?>
                                                    <option value="<?php echo esc_attr($pt->name); ?>" <?php selected($rule['value'], $pt->name); ?>><?php echo esc_html($pt->label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <button type="button" class="button ptools-remove-rule" style="color: #d63638; border-color: #d63638; padding: 4px 8px; height: 34px;"><span class="dashicons dashicons-trash" style="margin-top: 0;"></span></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="ptools-add-rule" class="button button-secondary" style="margin-top: 5px;"><?php esc_html_e('Add Rule', 'powertools'); ?></button>
                        </div>

                        <div class="ptools-form-group" style="margin-bottom: 24px;">
                            <label class="ptools-form-label"><?php esc_html_e('Code', 'powertools'); ?></label>
                            
                            <div id="ptools-code-editor-container" style="border: 1px solid #c3c4c7; border-radius: 8px; overflow: hidden; background: #fff;">
                                <!-- Top Visual Tag -->
                                <div id="ptools-tag-top" style="background: #f0f0f1; padding: 4px 12px; font-family: monospace; font-size: 13px; color: #8c8f94; border-bottom: 1px solid #c3c4c7; display: none;"></div>
                                
                                <input type="hidden" id="ptools-code-type" value="<?php echo esc_attr($snippet_to_edit['type']); ?>">
                                <textarea name="code" id="ptools-code-editor" class="ptools-form-input ptools-form-input--mono" style="height: 400px; border: none; border-radius: 0;" required><?php echo esc_textarea($snippet_to_edit['code']); ?></textarea>
                                
                                <!-- Bottom Visual Tag -->
                                <div id="ptools-tag-bottom" style="background: #f0f0f1; padding: 4px 12px; font-family: monospace; font-size: 13px; color: #8c8f94; border-top: 1px solid #c3c4c7; display: none;"></div>
                            </div>
                            
                            <span class="ptools-form-hint"><?php esc_html_e('Enter your HTML, CSS, JS or PHP code. The opening/closing tags are automatically added for you based on the type.', 'powertools'); ?></span>
                        </div>

                        <div class="ptools-metabox-footer">
                            <input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Snippet', 'powertools'); ?>">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=powertools-insert-code')); ?>" class="button" style="margin-left: 10px;"><?php esc_html_e('Cancel', 'powertools'); ?></a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- List View -->
                <div class="ptools-metabox" style="margin-top: 0; padding: 0; overflow: hidden;">
                    <?php if (empty($snippets)): ?>
                        <div style="padding: 48px; text-align: center;">
                            <span class="dashicons dashicons-code-standards" style="font-size: 48px; width: 48px; height: 48px; color: #c3c4c7; margin-bottom: 16px;"></span>
                            <p style="color: #646970; font-size: 16px; margin: 0;"><?php esc_html_e('No snippets added yet. Create your first one!', 'powertools'); ?></p>
                        </div>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
                            <thead>
                                <tr>
                                    <th style="padding: 16px 24px; font-weight: 600;"><?php esc_html_e('Snippet Title', 'powertools'); ?></th>
                                    <th style="padding: 16px 24px; font-weight: 600; width: 120px;"><?php esc_html_e('Type', 'powertools'); ?></th>
                                    <th style="padding: 16px 24px; font-weight: 600; width: 150px;"><?php esc_html_e('Location', 'powertools'); ?></th>
                                    <th style="padding: 16px 24px; font-weight: 600; width: 100px; text-align: right;"><?php esc_html_e('Actions', 'powertools'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($snippets as $id => $snippet): ?>
                                    <tr>
                                        <td style="padding: 16px 24px;">
                                            <a href="<?php echo esc_url(add_query_arg('edit', $id)); ?>" style="font-weight: 600; font-size: 14px; text-decoration: none; color: #2271b1;">
                                                <?php echo esc_html($snippet['title']); ?>
                                            </a>
                                        </td>
                                        <td style="padding: 16px 24px;">
                                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; background: #f0f0f1; padding: 4px 8px; border-radius: 4px; color: #646970;">
                                                <?php echo esc_html($snippet['type']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 24px;">
                                            <span style="color: #646970; font-size: 13px;">
                                                <?php echo esc_html(str_replace('wp_', '', $snippet['location'])); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 24px; text-align: right;">
                                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                                    <input type="hidden" name="action" value="powertools_toggle_snippet">
                                                    <input type="hidden" name="snippet_id" value="<?php echo esc_attr($id); ?>">
                                                    <?php wp_nonce_field('powertools_toggle_snippet_nonce'); ?>
                                                    <button type="submit" class="button-link" style="color: <?php echo !empty($snippet['active']) ? '#6b46c1' : '#c3c4c7'; ?>; text-decoration: none;" title="<?php echo !empty($snippet['active']) ? 'Deactivate' : 'Activate'; ?>">
                                                        <span class="dashicons dashicons-<?php echo !empty($snippet['active']) ? 'visibility' : 'hidden'; ?>"></span>
                                                    </button>
                                                </form>
                                                <a href="<?php echo esc_url(add_query_arg('edit', $id)); ?>" class="button-link" style="color: #2271b1; text-decoration: none;" title="Edit">
                                                    <span class="dashicons dashicons-edit"></span>
                                                </a>
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="action" value="powertools_delete_snippet">
                                                    <input type="hidden" name="snippet_id" value="<?php echo esc_attr($id); ?>">
                                                    <?php wp_nonce_field('powertools_delete_snippet_nonce'); ?>
                                                    <button type="submit" class="button-link" style="color: #d63638; text-decoration: none;" title="Delete">
                                                        <span class="dashicons dashicons-trash"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- New Snippet Modal -->
        <div id="ptools-new-snippet-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100000; align-items: center; justify-content: center;">
            <div style="background: #fff; width: 450px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden;">
                <div style="padding: 24px; border-bottom: 1px solid #f0f0f1; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600;"><?php esc_html_e('Create New Snippet', 'powertools'); ?></h3>
                    <button type="button" id="ptools-close-new-snippet-modal" class="button-link" style="color: #646970;"><span class="dashicons dashicons-no-alt"></span></button>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="padding: 24px;">
                    <input type="hidden" name="action" value="powertools_create_snippet">
                    <?php wp_nonce_field('powertools_create_snippet_nonce'); ?>
                    
                    <div class="ptools-form-group" style="margin-bottom: 24px;">
                        <label class="ptools-form-label" style="margin-bottom: 8px;"><?php esc_html_e('Choose Language', 'powertools'); ?></label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                            <label style="cursor: pointer; text-align: center; border: 2px solid #f0f0f1; padding: 16px; border-radius: 12px; transition: all 0.2s;">
                                <input type="radio" name="type" value="html" checked style="display: none;">
                                <div style="font-weight: 600; font-size: 14px;">HTML</div>
                                <div style="font-size: 11px; color: #8c8f94; margin-top: 4px;">Universal</div>
                            </label>
                            <label style="cursor: pointer; text-align: center; border: 2px solid #f0f0f1; padding: 16px; border-radius: 12px; transition: all 0.2s;">
                                <input type="radio" name="type" value="js" style="display: none;">
                                <div style="font-weight: 600; font-size: 14px;">JS</div>
                                <div style="font-size: 11px; color: #8c8f94; margin-top: 4px;">JavaScript</div>
                            </label>
                            <label style="cursor: pointer; text-align: center; border: 2px solid #f0f0f1; padding: 16px; border-radius: 12px; transition: all 0.2s;">
                                <input type="radio" name="type" value="php" style="display: none;">
                                <div style="font-weight: 600; font-size: 14px;">PHP</div>
                                <div style="font-size: 11px; color: #8c8f94; margin-top: 4px;">Scripting</div>
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" class="button ptools-close-new-snippet-modal" style="flex: 1; height: 44px; border-radius: 10px;"><?php esc_html_e('Cancel', 'powertools'); ?></button>
                        <button type="submit" class="button button-primary" style="flex: 2; height: 44px; border-radius: 10px;"><?php esc_html_e('Create Snippet', 'powertools'); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            .CodeMirror {
                height: 400px;
                font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                font-size: 13px;
                line-height: 1.6;
            }
            #ptools-code-editor-container .CodeMirror {
                border: none;
            }
            #ptools-new-snippet-modal label:has(input:checked) {
                border-color: #6b46c1 !important;
                background: #f8f7ff;
                color: #6b46c1;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Modal Logic
                $('#ptools-open-new-snippet-modal').on('click', function() {
                    $('#ptools-new-snippet-modal').css('display', 'flex');
                });
                $('.ptools-close-new-snippet-modal, #ptools-close-new-snippet-modal').on('click', function() {
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
            
            wp_redirect(add_query_arg('edit', $post_id, admin_url('admin.php?page=powertools-insert-code')));
        } else {
            wp_redirect(admin_url('admin.php?page=powertools-insert-code'));
        }
        exit;
    }

    /**
     * Handle saving snippet
     */
    public function handle_save_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_save_snippet_nonce');

        $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
        
        $rules = array();
        if (isset($_POST['rules']) && is_array($_POST['rules'])) {
            foreach ($_POST['rules'] as $rule) {
                if (!empty($rule['type'])) {
                    $value = '';
                    if ($rule['type'] === 'user_role') {
                        $value = isset($rule['value_role']) ? sanitize_text_field($rule['value_role']) : '';
                    } elseif ($rule['type'] === 'post_type') {
                        $value = isset($rule['value_post_type']) ? sanitize_text_field($rule['value_post_type']) : '';
                    } else {
                        $value = isset($rule['value']) ? sanitize_text_field($rule['value']) : '';
                    }

                    $rules[] = array(
                        'type' => sanitize_text_field($rule['type']),
                        'value' => $value
                    );
                }
            }
        }

        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['title']),
            'post_content' => stripslashes($_POST['code']),
            'post_type'    => self::POST_TYPE,
            'post_status'  => 'publish' // Default to active on save/update
        );

        if ($snippet_id) {
            $post_data['ID'] = $snippet_id;
            // Preserve status if updating
            $post_data['post_status'] = get_post_status($snippet_id);
            wp_update_post($post_data);
        } else {
            $snippet_id = wp_insert_post($post_data);
        }

        if ($snippet_id) {
            // Type is immutable on edit, but we update meta for new ones
            if (!isset($_POST['snippet_id'])) {
                update_post_meta($snippet_id, '_pt_type', sanitize_text_field($_POST['type']));
            }
            update_post_meta($snippet_id, '_pt_location', sanitize_text_field($_POST['location']));
            update_post_meta($snippet_id, '_pt_rules', $rules);
        }

        wp_redirect(add_query_arg(array('page' => 'powertools-insert-code', 'success' => 1), admin_url('admin.php')));
        exit;
    }

    /**
     * Handle deleting snippet
     */
    public function handle_delete_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_delete_snippet_nonce');

        $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
        if ($snippet_id && get_post_type($snippet_id) === self::POST_TYPE) {
            wp_delete_post($snippet_id, true);
        }

        wp_redirect(add_query_arg('page', 'powertools-insert-code', admin_url('admin.php')));
        exit;
    }

    /**
     * Handle toggling snippet active status
     */
    public function handle_toggle_snippet() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'powertools'));
        }

        check_admin_referer('powertools_toggle_snippet_nonce');

        $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
        if ($snippet_id && get_post_type($snippet_id) === self::POST_TYPE) {
            $status = get_post_status($snippet_id);
            $new_status = ($status === 'publish') ? 'draft' : 'publish';
            
            wp_update_post(array(
                'ID'          => $snippet_id,
                'post_status' => $new_status
            ));
        }

        wp_redirect(add_query_arg('page', 'powertools-insert-code', admin_url('admin.php')));
        exit;
    }
}
