<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Field_Groups')):

class ACFE_Field_Groups{
    
    var $view = '';
    var $sync = '';
    var $old_version = false;
    
    /*
     * Construct
     */
    function __construct(){
        
        // Actions
        add_action('current_screen',        array($this, 'current_screen'));
        add_action('acf/add_meta_boxes',    array($this, 'seamless_metabox'), 10, 3);
        
    }
    
    /*
     * Current Screen
     */
    function current_screen(){
        
        // Bail early if not Field Groups admin page.
        if(!acf_is_screen('edit-acf-field-group'))
            return;
        
        // Old Compatibility
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9'))
            $this->old_version = true;
        
        // ACF < 5.9
        if($this->old_version){
    
            $this->view = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : '';
            $this->sync = $this->get_sync_compatibility();
        
        // ACF >= 5.9
        }else{
    
            $this->view = acf_get_instance('ACF_Admin_Field_Groups')->view;
            $this->sync = acf_get_instance('ACF_Admin_Field_Groups')->sync;
            
        }
        
        // Hooks
        add_filter('manage_edit-acf-field-group_columns',           array($this, 'table_columns'), 20);
        add_action('manage_acf-field-group_posts_custom_column',    array($this, 'table_columns_html'), 10, 2);
    
        add_filter('default_hidden_columns',                        array($this, 'default_hidden_columns'), 10, 2);
        add_filter('edit_acf-field-group_per_page',                 array($this, 'posts_per_page'));
        add_filter('page_row_actions',                              array($this, 'row_actions'), 20, 2);
        add_filter('display_post_states',                           array($this, 'post_states'), 20, 2);
        add_action('admin_footer',                                  array($this, 'admin_footer'));
        
        if($this->view !== 'sync'){
    
            add_filter('bulk_actions-edit-acf-field-group',         array($this, 'bulk_actions'));
            add_filter('handle_bulk_actions-edit-acf-field-group',  array($this, 'handle_bulk_actions'), 10, 3);
            
        }
        
    }
    
    /*
     * Table Columns
     */
    function table_columns($columns){
        
        switch($this->view){
    
            // View: Sync
            case('sync'):
                
                acfe_unset($columns, 'acf-field-group-category');
                
            break;
            
            // View: Local
            case('acfe-local'):
    
                if($this->old_version){
                    
                    $columns = array(
                        'cb'            => '<input type="checkbox" />',
                        'title'         => __('Title', 'acf'),
                        'acfe-source'   => __('Source', 'acf'),
                        'acf-count'     => __('Fields', 'acf'),
                        'acfe-location' => __('Location', 'acf'),
                        'acfe-load'     => __('Load', 'acf'),
                    );
                    
                }else{
    
                    $columns = array(
                        'cb'            => '<input type="checkbox" />',
                        'title'         => __('Title', 'acf'),
                        'acfe-source'   => __('Source', 'acf'),
                        'acf-count'     => __('Fields', 'acf'),
                        'acf-location'  => __('Location', 'acf'),
                        'acfe-load'     => __('Load', 'acf'),
                    );
                    
                }
    
                if(acf_get_setting('acfe/php'))
                    $columns['acfe-autosync-php'] = __('PHP Sync');
    
                if(acf_get_setting('json'))
                    $columns['acfe-autosync-json'] = __('Json Sync', 'acf');
                
            break;
            
            // View: Active/Trash
            default:
    
                // ACF < 5.9
                if($this->old_version){
    
                    acfe_unset($columns, 'acf-fg-status');
                    
                    $columns['acfe-location'] = __('Location', 'acf');
                    $columns['acfe-load'] = __('Load', 'acf');
        
                    if(acf_get_setting('acfe/php'))
                        $columns['acfe-autosync-php'] = __('PHP');
        
                    if(acf_get_setting('json'))
                        $columns['acfe-autosync-json'] = __('Json');
        
                // ACF >= 5.9
                }else{
        
                    // Re-order
                    acfe_unset($columns, 'acf-key');
                    acfe_unset($columns, 'acf-location');
                    acfe_unset($columns, 'acf-count');
                    acfe_unset($columns, 'acf-json');
        
                    $columns['acf-count'] = __('Fields', 'acf');
                    $columns['acf-location'] = __('Location', 'acf');
        
                    $columns['acfe-load'] = __('Load', 'acf');
        
                    if(acf_get_setting('acfe/php'))
                        $columns['acfe-autosync-php'] = __('PHP');
        
                    if(acf_get_setting('json'))
                        $columns['acfe-autosync-json'] = __('Json', 'acf');
        
                }
    
                // Remove Category column if empty
                if(isset($columns['acf-field-group-category'])){
        
                    $categories_count = get_terms(array(
                        'taxonomy'      => 'acf-field-group-category',
                        'hide_empty'    => false,
                        'fields'        => 'count'
                    ));
        
                    if(empty($categories_count))
                        unset($columns['acf-field-group-category']);
        
                }
                
            break;
            
        }
        
        return $columns;
        
    }
    
    /*
     * Table Columns HTML
     */
    function table_columns_html($column, $post_id){
        
        $field_group = acf_get_field_group($post_id);
        
        if(!$field_group)
            return;
        
        $this->render_table_column($column, $field_group);
        
    }
    
    function render_table_column($column, $field_group){
    
        switch($column){
    
            // Source
            case 'acfe-source':
    
                $this->render_admin_table_column_source_html($field_group);
        
            break;
    
            // Count
            case 'acfe-count':
    
                echo esc_html( acf_get_field_count($field_group));
        
            break;
            
            // Location
            case 'acfe-location':
    
                acfe_render_field_group_locations_html($field_group);
        
            break;
    
            // Load
            case 'acfe-load':
    
                $this->render_admin_table_column_load_html($field_group);
        
            break;
    
            // PHP Sync
            case 'acfe-autosync-php':
    
                $this->render_admin_table_column_php_html($field_group);
        
            break;
            
            // New Json Sync
            case 'acfe-autosync-json':
    
                $this->render_admin_table_column_json_html($field_group);
        
            break;
            
        }
        
    }
    
    /*
     * Column: Source
     */
    function render_admin_table_column_source_html($field_group){
    
        $source = __('Theme/Plugin', 'acf');
    
        // ACF Extended
        if(in_array($field_group['key'], acfe_get_setting('reserved_field_groups', array()))){
        
            $source = 'ACF Extended';
            
        // Advanced Forms
        }elseif($field_group['key'] === 'group_form_settings' || $field_group['key'] === 'group_entry_data'){
        
            $source = 'Advanced Forms';
        
        }elseif(acf_maybe_get($field_group, 'acfe_local_source')){
            
            $file = acf_maybe_get($field_group, 'acfe_local_source');
            $file_readable = $this->get_human_readable_file_location($file);
            
            $source = '<span class="acf-js-tooltip" title="' . $file_readable . '">AutoSync</span>';
            
        }
    
        $source = apply_filters('acfe/field_groups_third_party/source', $source, $field_group['key'], $field_group);
    
        echo $source;
        
    }
    
    /*
     * Column: Load
     */
    function render_admin_table_column_load_html($field_group){
    
        $php = acfe_get_local_php_files();
        $local_field_group = acf_get_local_field_group($field_group['key']);
        
        // PHP
        if(isset($php[$field_group['key']]) || acf_maybe_get($local_field_group, 'local') === 'php'){
        
            echo '<span>php</span>';
        
        // Json
        }elseif(acf_maybe_get($local_field_group, 'local') === 'json'){
        
            echo '<span>Json</span>';
        
        // DB
        }else{
        
            echo '<span>DB</span>';
        
        }
        
    }
    
    /*
     * Column: PHP Sync HTML
     */
    function render_admin_table_column_php_html($field_group){
    
        $return = $this->get_php_data($field_group);
        
        $wrapper = array(
            'class' => ''
        );
        
        if($return['class'])
            $wrapper['class'] = $return['class'];
    
        if($return['message']){
            
            $wrapper['class'] .= ' acf-js-tooltip';
            $wrapper['title'] = $return['message'];
            
        }
        
        $icons = array();
        
        if($return['icon'])
            $icons[] = '<span class="dashicons dashicons-' . $return['icon'] . '"></span>';
    
        if($return['warning'])
            $icons[] = '<span class="dashicons dashicons-warning"></span>';
        
        ?>
        <span <?php echo acf_esc_atts($wrapper); ?>>
            
            <?php if($return['wrapper_start']){ echo $return['wrapper_start']; } ?>
            
            <?php if(!empty($icons)){ ?>
                <?php echo implode('', $icons); ?>
            <?php } ?>

            <?php if($return['wrapper_end']){ echo $return['wrapper_end']; } ?>
            
        </span>
        <?php
        
    }
    
    function get_php_data($field_group){
        
        $return = array(
            'message' => false,
            'file' => false,
            'wrapper_start' => '',
            'wrapper_end' => '',
            'class' => false,
            'warning' => false,
            'icon' => false,
        );
    
        $php = acfe_get_local_php_files();
    
        if(isset($php[$field_group['key']])){
        
            $file = $php[$field_group['key']];
            $file_readable = $this->get_human_readable_file_location($file);
            
            $local_field_group = acf_get_local_field_group($field_group['key']);
            
            if(acf_maybe_get($local_field_group, 'local') === 'php'){
                
                $return['message'] = __('Synchronized', 'acf') . '. ' . $file_readable . '<br/><br/>' . __('Warning: Duplicated PHP code found in theme/plugin.', 'acf');
                $return['file'] = $file_readable . '<br/><br/>' . __('Warning: Duplicated PHP code found in theme/plugin.', 'acf');
                $return['icon'] = 'yes';
                $return['warning'] = true;
            
            }else{
    
                $return['message'] = __('Synchronized', 'acf') . '. ' . $file_readable;
                $return['file'] = $file_readable;
                $return['icon'] = 'yes';
            
            }
        
        }else{
    
            $path = untrailingslashit(acf_get_setting('acfe/php_save'));
    
            $path = apply_filters("acfe/settings/php_save/all",                         $path, $field_group);
            $path = apply_filters("acfe/settings/php_save/ID={$field_group['ID']}",     $path, $field_group);
            $path = apply_filters("acfe/settings/php_save/key={$field_group['key']}",   $path, $field_group);
            
            $found = (bool) is_dir($path) && wp_is_writable($path);
            
            $folder = $this->get_human_readable_file_location($path, $found, false);
            
            if(acfe_has_php_sync($field_group)){
                
                $return['message'] = __('Awaiting save', 'acf') . '. <br />' . __('Save path', 'acf') . ' ' . lcfirst($folder);
                $return['file'] = __('Save path', 'acf') . ' ' . lcfirst($folder);
                $return['class'] = 'secondary';
                $return['icon'] = 'yes';
                $return['warning'] = true;
            
            }else{
                
                $return['file'] = __('Save path', 'acf') . ' ' . lcfirst($folder);
                $return['class'] = 'secondary';
                $return['icon'] = 'no-alt';
                
            }
        
        }
        
        if($this->view === 'acfe-local'){
            
            $return['message'] = false;
            $return['warning'] = false;
            $return['class'] = false;
            
            if($return['icon'] !== 'yes')
                $return['class'] = 'secondary';
            
        }
        
        return $return;
        
    }
    
    /*
     * Column: Json Sync HTML
     */
    function render_admin_table_column_json_html($field_group){
    
        $return = $this->get_json_data($field_group);
    
        $wrapper = array(
            'class' => ''
        );
    
        if($return['class'])
            $wrapper['class'] = $return['class'];
    
        if($return['message']){
        
            $wrapper['class'] .= ' acf-js-tooltip';
            $wrapper['title'] = $return['message'];
        
        }
    
        $icons = array();
    
        if($return['icon'])
            $icons[] = '<span class="dashicons dashicons-' . $return['icon'] . '"></span>';
    
        if($return['warning'])
            $icons[] = '<span class="dashicons dashicons-warning"></span>';
    
        ?>
        <span <?php echo acf_esc_atts($wrapper); ?>>
            
            <?php if($return['wrapper_start']){ echo $return['wrapper_start']; } ?>
            
            <?php if(!empty($icons)){ ?>
                <?php echo implode('', $icons); ?>
            <?php } ?>

            <?php if($return['wrapper_end']){ echo $return['wrapper_end']; } ?>
            
        </span>
        <?php
        
    }
    
    function get_json_data($field_group){
        
        $return = array(
            'message' => false,
            'file' => false,
            'wrapper_start' => false,
            'wrapper_end' => false,
            'class' => false,
            'warning' => false,
            'icon' => false,
        );
    
        $json = acf_get_local_json_files();
    
        if(isset($json[$field_group['key']])){
        
            $file = $json[$field_group['key']];
            $file_readable = $this->get_human_readable_file_location($file);
        
            if(isset($this->sync[$field_group['key']])){
                
                // vars
                $nonce = wp_create_nonce('bulk-posts');
                
                if($this->old_version){
                    
                    $url = admin_url('edit.php?post_type=acf-field-group&post_status=sync&acfsync=' . $field_group['key'] . '&_wpnonce=' . $nonce );
                    $text = $field_group['ID'] ? __('Sync', 'acf') : __('Import', 'acf');
                    
                    $wrapper_start = '<a href="' . esc_url($url) . '">';
                    $wrapper_end = '<div class="row-actions"><span class="review" style="color:#006799;">' . $text . '</span></div></a>';
        
                }else{
    
                    $url = admin_url('edit.php?post_type=acf-field-group&acfsync=' . $field_group['key'] . '&_wpnonce=' . $nonce);
                    $text = $field_group['ID'] ? __('Review', 'acf') : __('Import', 'acf');
    
                    $wrapper_start = '<a href="#" data-event="review-sync" data-id="' . esc_attr($field_group['ID']) . '" data-href="' . esc_url($url) . '">';
                    $wrapper_end = '<div class="row-actions"><span class="review" style="color:#006799;">' . $text . '</span></div></a>';
                    
                }
            
                if($field_group['ID']){
    
                    $return['message'] = __('Sync available', 'acf') . '. ' . $file_readable;
                    $return['file'] = $file_readable;
                    $return['icon'] = 'update';
                    $return['wrapper_start'] = $wrapper_start;
                    $return['wrapper_end'] = $wrapper_end;
                
                }else{
    
                    $return['message'] = __('Sync available', 'acf') . '. ' . $file_readable;
                    $return['file'] = $file_readable;
                    $return['icon'] = 'update';
                    $return['wrapper_start'] = $wrapper_start;
                    $return['wrapper_end'] = $wrapper_end;
                
                }
            
            }else{
    
                $return['message'] = __('Synchronized', 'acf') . '. ' . $file_readable;
                $return['file'] = $file_readable;
                $return['icon'] = 'yes';
            
            }
        
        }else{
    
            $path = untrailingslashit(acf_get_setting('save_json'));
    
            $path = apply_filters("acfe/settings/json_save/all",                        $path, $field_group);
            $path = apply_filters("acfe/settings/json_save/ID={$field_group['ID']}",    $path, $field_group);
            $path = apply_filters("acfe/settings/json_save/key={$field_group['key']}",  $path, $field_group);
    
            $found = (bool) is_dir($path) && wp_is_writable($path);
    
            $folder = $this->get_human_readable_file_location($path, $found, false);
        
            if(acfe_has_json_sync($field_group)){
    
                $return['message'] = __('Awaiting save', 'acf') . '. <br />' . __('Save path', 'acf') . ' ' . lcfirst($folder);
                $return['file'] = __('Save path', 'acf') . ' ' . lcfirst($folder);
                $return['class'] = 'secondary';
                $return['icon'] = 'yes';
                $return['warning'] = 'true';
            
            }else{
    
                $return['file'] = __('Save path', 'acf') . ' ' . lcfirst($folder);
                $return['class'] = 'secondary';
                $return['icon'] = 'no-alt';
            
            }
        
        }
    
        if($this->view === 'acfe-local'){
        
            $return['message'] = false;
            $return['warning'] = false;
            $return['class'] = false;
            
            if($return['icon'] !== 'yes')
                $return['class'] = 'secondary';
        
        }
        
        return $return;
        
    }
    
    /*
     * Hide Default Columns
     */
    function default_hidden_columns($hidden, $screen){
        
        $hidden[] = 'acf-description';
        
        return $hidden;
        
    }
    
    /*
     * Posts Per Page
     */
    function posts_per_page(){
        
        return 999;
        
    }
    
    /*
     * Row Actions
     */
    function row_actions($actions, $post){
        
        // bail early
        if($post->post_type !== 'acf-field-group'){
            return $actions;
        }
        
        $field_group = acf_get_field_group($post->ID);
        
        $actions['acfe-export-php'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=php&keys=' . $field_group['key']) . '">PHP</a>';
        $actions['acfe-export-json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=json&keys=' . $field_group['key']) . '">Json</a>';
        $actions['acfe-key'] = '<code>' . $field_group['key'] . '</code>';
        //$actions['acfe-id'] = '<span style="color:#555;">ID: ' . $field_group['ID'] . '</span>';
        
        return $actions;
        
    }
    
    /*
     * Post States
     */
    function post_states($states, $post){
        
        // Vars
        $field_group = acf_get_field_group($post->ID);
        
        // ACF < 5.9
        if($this->old_version){
            
            // Disabled
            if($post->post_status === 'acf-disabled'){
    
                $states['acf-disabled'] = '<span class="dashicons dashicons-hidden acf-js-tooltip" title="' . _x('Disabled', 'post status', 'acf') . '"></span>';
                
            }
            
        // ACF > 5.9
        }else{
            
            if(isset($states['acf-disabled'])){
    
                unset($states['acf-disabled']);
                $states['acf-disabled'] = '<span class="dashicons dashicons-hidden acf-js-tooltip" title="' . _x('Disabled', 'post status', 'acf') . '"></span>';
                
            }
            
        }
        
        // Alternative Title
        $display_title = acf_maybe_get($field_group, 'acfe_display_title');
        
        if(!empty($display_title)){
    
            $states['acfe-title'] = '<span class="acf-js-tooltip" title="' . __('Alternative title', 'acf') . '">' . $display_title . '</span>';
            
        }
        
        return $states;
        
    }
    
    /*
     * Admin Footer
     */
    function admin_footer(){
        ?>

        <!-- ACFE: Label -->
        <script type="text/html" id="tmpl-acfe-label">
            <span style="word-wrap: break-word;padding: 2px 6px;margin-left:1px;border-radius:2px;background:#ca4a1f;color: #fff; font-size: 14px;vertical-align: text-bottom;font-style: italic;">Extended</span>
        </script>

        <!-- ACFE: Debug -->
        <script type="text/html" id="tmpl-acfe-debug">
            <div class="acf-box">
            
            </div>
        </script>

        <script type="text/javascript">
            (function($){

                // ACFE: Label
                $('.acf-column-2 > .acf-box > .inner > h2').append($('#tmpl-acfe-label').html());

                // ACFE: Debug
                //$('#posts-filter').append($('#tmpl-acfe-debug').html());

                // Fix no field groups found
                $('#the-list tr.no-items td').attr('colspan', $('.wp-list-table > thead > tr > .manage-column:visible').length);

            })(jQuery);
        </script>
        <?php
    }
    
    /*
     * Bulk Actions
     */
    function bulk_actions($actions){
        
        $actions['acfe_php'] = __( 'Export PHP', 'acf' );
        $actions['acfe_json'] = __( 'Export Json', 'acf' );
        
        return $actions;
        
    }
    
    /*
     * Handle Bulk Actions
     */
    function handle_bulk_actions($redirect, $action, $post_ids){
        
        if(!isset($_REQUEST['post']) || empty($_REQUEST['post']))
            return $redirect;
        
        // PHP
        if($action === 'acfe_php'){
            
            $post_ids = $_REQUEST['post'];
            
            foreach($post_ids as &$post_id){
                
                $field_group = acf_get_field_group($post_id);
                $post_id = $field_group['key'];
                
            }
            
            $url = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=php&keys=' . implode('+', $post_ids));
            wp_redirect($url);
            exit;
            
        }
        
        // Json
        elseif($action === 'acfe_json'){
            
            $post_ids = $_REQUEST['post'];
    
            foreach($post_ids as &$post_id){
                
                $field_group = acf_get_field_group($post_id);
                $post_id = $field_group['key'];
                
            }
            
            $url = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=json&keys=' . implode('+', $post_ids));
            wp_redirect($url);
            exit;
            
        }
        
        return $redirect;
        
    }
    
    /*
     * Helper: Human Readable File
     */
    function get_human_readable_file_location($file, $found = true, $br = true, $prefix = true){
        
        // Generate friendly file path.
        $theme_path = get_stylesheet_directory();
        $located = '';
        
        if( strpos($file, $theme_path) !== false ) {
            $rel_file = str_replace( $theme_path, '', $file );
            
            if($prefix){
                $located .= __($found ? 'Located' : 'Not found', 'acf');
            }
            
            $located .= ' ' . __('in theme:', 'acf') . (($br) ? "<br/>" : ' ');
            $located .= $rel_file;
            
        } elseif( strpos($file, WP_PLUGIN_DIR) !== false ) {
            $rel_file = str_replace( WP_PLUGIN_DIR, '', $file );
            
            if($prefix){
                $located .= __($found ? 'Located' : 'Not found', 'acf');
            }
            
            $located .= ' ' . __('in plugin:', 'acf') . (($br) ? "<br/>" : ' ');
            $located .= $rel_file;
            
        } else {
            $rel_file = str_replace( ABSPATH, '', $file );
            
            if($prefix){
                $located .= __($found ? 'Located' : 'Not found', 'acf');
            }
            
            $located .= ' ' . __('in:', 'acf') . (($br) ? "<br/>" : ' ');
            $located .= $rel_file;
        }
        
        return $located;
        
    }
    
    /*
     * Seamless Metabox
     */
    function seamless_metabox($post_type, $post, $field_groups){
        
        // check gutenberg
        $is_gutenberg = acfe_is_block_editor();
        
        foreach($field_groups as $field_group){
            
            add_filter("postbox_classes_{$post_type}_acf-{$field_group['key']}", function($classes) use($field_group, $is_gutenberg){
                
                // default
                $classes[] = 'acf-postbox';
                
                // seamless
                if(!$is_gutenberg && $field_group['style'] === 'seamless'){
                    $classes[] = 'seamless';
                }
                
                // left
                if($field_group['label_placement'] === 'left'){
                    $classes[] = 'acfe-postbox-left';
                }
                
                // top
                if($field_group['label_placement'] === 'top'){
                    $classes[] = 'acfe-postbox-top';
                }
                
                // return
                return $classes;
                
            });
            
        }
        
    }
    
    function get_sync_compatibility(){
        
        $sync = array();
        
        if(!acf_get_local_json_files())
            return $sync;
        
        $field_groups = acf_get_field_groups();
    
        foreach($field_groups as $field_group){
        
            // Vars
            $local = acf_maybe_get($field_group, 'local');
            $modified = acf_maybe_get($field_group, 'modified');
            $private = acf_maybe_get($field_group, 'private');
            
            // Bail early
            if($private || $local !== 'json')
                continue;
            
            // If field group doesn't exists in DB or modified file date more recent than DB
            if(!$field_group['ID'] || ($modified && $modified > get_post_modified_time('U', true, $field_group['ID']))){
    
                $sync[$field_group['key']] = $field_group;
                
            }
        
        }
    
        return $sync;
        
    }
    
}

acf_new_instance('ACFE_Field_Groups');

endif;