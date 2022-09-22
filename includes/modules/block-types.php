<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dynamic_block_types')):

class acfe_dynamic_block_types extends acfe_dynamic_module{
    
    /*
     * Initialize
     */
    function initialize(){
    
        $this->name = 'block_type';
        $this->active = acf_get_setting('acfe/modules/block_types');
        $this->settings = 'modules.block_types';
        $this->post_type = 'acfe-dbt';
        $this->label = 'Block Type Title';
        $this->textdomain = 'ACF Extended: Block Types';
        
        $this->tool = 'acfe_dynamic_block_types_export';
        $this->tools = array('php', 'json');
        $this->columns = array(
            'name'          => __('Name', 'acf'),
            'category'      => __('Category', 'acf'),
            'post_types'    => __('Post Types', 'acf'),
            'render'        => __('Render', 'acf'),
        );
        
    }
    
    /*
     * Actions
     */
    function actions(){
        
        // Validate
        add_filter('acf/validate_value/key=field_acfe_dbt_name',    array($this, 'validate_name'), 10, 4);
        add_filter('acf/update_value/key=field_acfe_dbt_name',      array($this, 'update_name'), 10, 3);
        
        // Register
        add_filter('acfe/block_type/register',                      array($this, 'register'), 10, 2);
        
        // Save
        add_filter('acfe/block_type/save_args',                     array($this, 'save_args'), 10, 3);
        add_action('acfe/block_type/save',                          array($this, 'save'), 10, 3);
        
        // Import
        add_action('acfe/block_type/import_fields',                 array($this, 'import_fields'), 10, 3);
        add_action('acfe/block_type/import',                        array($this, 'after_import'), 10, 2);
    
        // Multilang
        add_action('acfe/block_type/save',                          array($this, 'l10n_save'), 10, 3);
        add_filter('acfe/block_type/register',                      array($this, 'l10n_register'), 10, 2);
    
        $this->register_user_block_types();
        
    }
    
    /*
     * Get Name
     */
    function get_name($post_id){
        
        return get_field('name', $post_id);
        
    }
    
    /*
     * Init
     */
    function init(){
    
        $this->register_post_type();
        
    }
    
    /*
     * Register Post Type
     */
    function register_post_type(){
    
        $capability = acf_get_setting('capability');
        
        if(!acf_get_setting('show_admin'))
            $capability = false;
        
        register_post_type($this->post_type, array(
            'label'                 => 'Block Type',
            'description'           => 'Block Type',
            'labels'                => array(
                'name'          => 'Block Types',
                'singular_name' => 'Block Type',
                'menu_name'     => 'Block Types',
                'edit_item'     => 'Edit Block Type',
                'add_new_item'  => 'New Block Type',
            ),
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=acf-field-group',
            'menu_icon'             => 'dashicons-layout',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'rewrite'               => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capabilities'          => array(
                'publish_posts'         => $capability,
                'edit_posts'            => $capability,
                'edit_others_posts'     => $capability,
                'delete_posts'          => $capability,
                'delete_others_posts'   => $capability,
                'read_private_posts'    => $capability,
                'edit_post'             => $capability,
                'delete_post'           => $capability,
                'read_post'             => $capability,
            ),
            'acfe_admin_orderby'    => 'title',
            'acfe_admin_order'      => 'ASC',
            'acfe_admin_ppp'        => 999,
        ));
        
    }
    
    /*
     * Register User Block Types
     */
    function register_user_block_types(){
        
        $settings = apply_filters('acfe/block_type/prepare_register', acfe_get_settings($this->settings));
    
        if(empty($settings))
            return;
    
        foreach($settings as $name => $args){
            
            // Bail early
            if(empty($name) || acf_has_block_type('acf/' . $name))
                continue;
            
            // Filters
            $args = apply_filters("acfe/block_type/register",                 $args, $name);
            $args = apply_filters("acfe/block_type/register/name={$name}",    $args, $name);
            
            if($args === false)
                continue;
            
            // Register
            acf_register_block_type($args);
            
        }
    
    }
    
    /*
     * Post Head
     */
    function post_head(){
        
        global $post;
    
        $post_id = $post->ID;
        $name = $this->get_name($post->ID);
    
        $field_groups = acf_get_field_groups(array(
            'block' => "acf/{$name}"
        ));
        
        if($field_groups){
    
            add_meta_box('acfe-dbt-field-groups', __('Field Groups', 'acf'), array($this, 'metabox_render'), $this->post_type, 'normal', 'default', $field_groups);
            
        }
        
        $prepend = acfe_get_setting('theme_folder') ? trailingslashit(acfe_get_setting('theme_folder')) : '';
    
        add_filter('acf/prepare_field/name=render_template', function($field) use($name, $prepend){
        
            $prepend = apply_filters("acfe/block_type/prepend/template",                $prepend, $name);
            $prepend = apply_filters("acfe/block_type/prepend/template/name={$name}",   $prepend, $name);
            $field['prepend'] = $prepend;
            return $field;
        
        });
    
        add_filter('acf/prepare_field/name=enqueue_style', function($field) use($name, $prepend){
        
            $prepend = apply_filters("acfe/block_type/prepend/style",               $prepend, $name);
            $prepend = apply_filters("acfe/block_type/prepend/style/name={$name}",  $prepend, $name);
            $field['prepend'] = $prepend;
            return $field;
        
        });
    
        add_filter('acf/prepare_field/name=enqueue_script', function($field) use($name, $prepend){
        
            $prepend = apply_filters("acfe/block_type/prepend/script",              $prepend, $name);
            $prepend = apply_filters("acfe/block_type/prepend/script/name={$name}", $prepend, $name);
            $field['prepend'] = $prepend;
            return $field;
        
        });
        
    }
    
    /*
     * Metabox Render
     */
    function metabox_render($array, $data){
    
        $data = $data['args'];
    
        foreach($data as $field_group){ ?>

            <div class="acf-field">

                <div class="acf-label">
                    <label for="acf-_post_title"><a href="<?php echo admin_url('post.php?post=' . $field_group['ID'] . '&action=edit'); ?>"><?php echo $field_group['title']; ?></a></label>
                    <p class="description"><?php echo $field_group['key']; ?></p>
                </div>

                <div class="acf-input">
                    <?php $fields = acf_get_fields($field_group); ?>
                
                    <?php if(!empty($fields)){ ?>

                        <table class="acf-table">
                            <thead>
                                <th class="acf-th" width="25%"><strong>Label</strong></th>
                                <th class="acf-th" width="25%"><strong>Name</strong></th>
                                <th class="acf-th" width="25%"><strong>Key</strong></th>
                                <th class="acf-th" width="25%"><strong>Type</strong></th>
                            </thead>

                            <tbody>
                            <?php
                        
                            $array = array();
                            foreach($fields as $field){
    
                                $this->get_fields_labels_recursive($array, $field);
                            
                            }
                        
                            foreach($array as $field_key => $field_label){
                            
                                $field = acf_get_field($field_key);
                                if(!$field) continue;
                                
                                $type = acf_get_field_type($field['type']);
                                $type_label = acfe_maybe_get($type, 'label', '-');
                                ?>

                                <tr class="acf-row">
                                    <td width="25%"><?php echo $field_label; ?></td>
                                    <td width="25%"><?php echo $field['name']; ?></td>
                                    <td width="25%"><code><?php echo $field_key; ?></code></td>
                                    <td width="25%"><?php echo $type_label; ?></td>
                                </tr>
                        
                            <?php } ?>
                            </tbody>
                        </table>
                
                    <?php } ?>
                </div>

            </div>
    
        <?php } ?>

        <script type="text/javascript">
        (function($){

            if(typeof acf === 'undefined')
                return;

            acf.newPostbox(<?php echo wp_json_encode(array(
                'id'    => 'acfe-dbt-field-groups',
                'key'   => '',
                'style' => 'default',
                'label' => 'left',
                'edit'  => false
            )); ?>);

        })(jQuery);
        </script>
        <?php
    
    }
    
    /*
     * Edit Columns HTML
     */
    function edit_columns_html($column, $post_id){
        
        switch($column){
            
            // Name
            case 'name':
                
                echo '<code style="font-size: 12px;">' . $this->get_name($post_id) . '</code>';
                break;
                
            // Category
            case 'category':
                
                $cat = '—';
                $category = get_field('category', $post_id);
                
                if(!empty($category))
                    $cat = ucfirst($category);
                
                echo $cat;
                break;
            
            // Post Types
            case 'post_types':
                
                $pt = '—';
                
                $get_post_types = acf_get_array(get_field('post_types', $post_id));
                
                if(!empty($get_post_types)){
                    
                    $post_types = array();
                    
                    foreach($get_post_types as $post_type){
                        
                        if(!post_type_exists($post_type))
                            continue;
                        
                        $post_types[] = $post_type;
                        
                    }
                    
                    if(!empty($post_types)){
                        
                        $post_types_labels = acf_get_pretty_post_types($post_types);
                        
                        if(!empty($post_types_labels)){
                            $pt = implode(', ', $post_types_labels);
                        }
                        
                    }
                    
                }
                
                echo $pt;
                break;
            
            // Render
            case 'render':
                
                // vars
                $render = '—';
                $render_template = get_field('render_template', $post_id);
                $render_callback = get_field('render_callback', $post_id);
                
                if(!empty($render_template)){
                    
                    $render = '<code style="font-size: 12px;">' . $render_template . '</code>';
                    
                }elseif(!empty($render_callback)){
                    
                    $render = '<code style="font-size: 12px;">' . $render_callback . '</code>';
                    
                }
                
                echo $render;
                
                break;
            
        }
        
    }
    
    /*
     * Validate Name
     */
    function validate_name($valid, $value, $field, $input){
    
        if(!$valid)
            return $valid;
    
        // Editing Current Block Type
        $current_post_id = acf_maybe_get_POST('post_ID');
    
        if(!empty($current_post_id)){
        
            $current_name = get_field($field['name'], $current_post_id);
        
            if($value === $current_name)
                return $valid;
        
        }
    
        // Check existing ACF Block Types
        if(acf_has_block_type('acf/' . $value)){
        
            $valid = 'This block type name already exists';
        
        }
    
        return $valid;
        
    }
    
    /*
     * Update Name
     */
    function update_name($value, $post_id, $field){
    
        // Previous value
        $_value = get_field($field['name'], $post_id);
    
        // Value Changed. Delete option
        if($_value !== $value){
            acfe_delete_settings("{$this->settings}.{$_value}");
        }
    
        return $value;
        
    }
    
    /*
     * Register
     */
    function register($args, $name){
        
        // Template
        if(acf_maybe_get($args, 'render_template')){
            $template = acfe_locate_file_path($args['render_template']);
            
            if(!empty($template)){
                $args['render_template'] = $template;
            }
        }
        
        // Style
        if(acf_maybe_get($args, 'enqueue_style')){
            $style = acfe_locate_file_url($args['enqueue_style']);
            
            if(!empty($style)){
                $args['enqueue_style'] = $style;
            }
        }
        
        // Script
        if(acf_maybe_get($args, 'enqueue_script')){
            $script = acfe_locate_file_url($args['enqueue_script']);
            
            if(!empty($script)){
                $args['enqueue_script'] = $script;
            }
        }
        
        return $args;
        
    }
    
    /*
     * ACF Save post
     */
    function save_post($post_id){
        
        // vars
        $args = array();
        $name = $this->get_name($post_id);
        
        // Filters
        $args = apply_filters("acfe/block_type/save_args",                  $args, $name, $post_id);
        $args = apply_filters("acfe/block_type/save_args/name={$name}",     $args, $name, $post_id);
        $args = apply_filters("acfe/block_type/save_args/id={$post_id}",    $args, $name, $post_id);
        
        if($args === false)
            return;
        
        // Actions
        do_action("acfe/block_type/save",               $name, $args, $post_id);
        do_action("acfe/block_type/save/name={$name}",  $name, $args, $post_id);
        do_action("acfe/block_type/save/id={$post_id}", $name, $args, $post_id);
        
    }
    
    /*
     * Save Args
     */
    function save_args($args, $name, $post_id){
        
        $label = get_post_field('post_title', $post_id);
        $name = get_field('name', $post_id);
        $description = get_field('description', $post_id);
        $category = get_field('category', $post_id);
        $keywords = acf_decode_choices(get_field('keywords', $post_id), true);
        $post_types = acf_get_array(get_field('post_types', $post_id));
        $mode = get_field('mode', $post_id);
        $align = get_field('align', $post_id);
        $align_content = get_field('align_content', $post_id);
        $render_template = get_field('render_template', $post_id);
        $render_callback = get_field('render_callback', $post_id);
        $enqueue_style = get_field('enqueue_style', $post_id);
        $enqueue_script = get_field('enqueue_script', $post_id);
        $enqueue_assets = get_field('enqueue_assets', $post_id);
        
        // Register: Args
        $args = array(
            'name'              => $name,
            'title'             => $label,
            'description'       => $description,
            'category'          => $category,
            'keywords'          => $keywords,
            'post_types'        => $post_types,
            'mode'              => $mode,
            'align'             => $align,
            'align_content'     => $align_content,
            'render_template'   => $render_template,
            'render_callback'   => $render_callback,
            'enqueue_style'     => $enqueue_style,
            'enqueue_script'    => $enqueue_script,
            'enqueue_assets'    => $enqueue_assets,
        );
        
        // Align
        if($align === 'none')
            $args['align'] = '';
        
        // Icon
        $icon_type = get_field('icon_type', $post_id);
        
        // Icon: Simple
        if($icon_type === 'simple'){
            
            $icon_text = get_field('icon_text', $post_id);
            $args['icon'] = $icon_text;
            
        }
        
        // Icon: Colors
        elseif($icon_type == 'colors'){
            
            $icon_background = get_field('icon_background', $post_id);
            $icon_foreground = get_field('icon_foreground', $post_id);
            $icon_src = get_field('icon_src', $post_id);
            
            $args['icon'] = array(
                'background'    => $icon_background,
                'foreground'    => $icon_foreground,
                'src'           => $icon_src,
            );
            
        }
        
        // Supports: Align
        $supports_align = get_field('supports_align', $post_id);
        $supports_align_args = acf_decode_choices(get_field('supports_align_args', $post_id), true);
        
        $args['supports']['align'] = false;
        if(!empty($supports_align)){
            
            $args['supports']['align'] = true;
            
            if(!empty($supports_align_args))
                $args['supports']['align'] = $supports_align_args;
            
        }
        
        // Supports: Mode
        $supports_mode = get_field('supports_mode', $post_id);
        
        $args['supports']['mode'] = false;
        if(!empty($supports_mode))
            $args['supports']['mode'] = true;
        
        // Supports: Multiple
        $supports_multiple = get_field('supports_multiple', $post_id);
        
        $args['supports']['multiple'] = false;
        if(!empty($supports_multiple))
            $args['supports']['multiple'] = true;
        
        // Supports: Experimental JSX
        $experimental_jsx = get_field('supports_experimental_jsx', $post_id);
        
        $args['supports']['jsx'] = false;
        if(!empty($experimental_jsx))
            $args['supports']['jsx'] = true;
        
        // Supports: Align Content
        $supports_align_content = get_field('supports_align_content', $post_id);
        
        $args['supports']['align_content'] = false;
        if(!empty($supports_align_content))
            $args['supports']['align_content'] = true;
        
        // Supports: Anchor
        $supports_anchor = get_field('supports_anchor', $post_id);
        
        $args['supports']['anchor'] = false;
        if(!empty($supports_anchor))
            $args['supports']['anchor'] = true;
        
        return $args;
        
    }
    
    /*
     * Save
     */
    function save($name, $args, $post_id){
        
        // Get ACFE option
        $settings = acfe_get_settings($this->settings);
        
        // Create ACFE option
        $settings[$name] = $args;
        
        // Sort keys ASC
        ksort($settings);
        
        // Update ACFE option
        acfe_update_settings($this->settings, $settings);
        
        // Update post
        wp_update_post(array(
            'ID'            => $post_id,
            'post_name'     => $name,
            'post_status'   => 'publish',
        ));
        
    }
    
    /*
     * Trashed Post Type
     */
    function trashed_post($post_id){
        
        $name = $this->get_name($post_id);
        
        // Get ACFE option
        $settings = acfe_get_settings($this->settings);
        
        // Unset ACFE option
        acfe_unset($settings, $name);
        
        // Update ACFE option
        acfe_update_settings($this->settings, $settings);
        
    }
    
    /*
     * Import: Data
     */
    function import($name, $args){
    
        // Vars
        $settings = acfe_get_settings($this->settings);
        $title = $args['title'];
    
        // Already exists
        if(isset($settings[$name])){
            return new WP_Error('acfe_dbt_import_already_exists', __("Block type \"{$title}\" already exists. Import aborted."));
        }
    
        // Import Post
        $post_id = false;
    
        $post = array(
            'post_title'    => $title,
            'post_name'     => $name,
            'post_type'     => $this->post_type,
            'post_status'   => 'publish'
        );
    
        $post = apply_filters("acfe/block_type/import_post",                 $post, $name);
        $post = apply_filters("acfe/block_type/import_post/name={$name}",    $post, $name);
    
        if($post !== false){
            $post_id = wp_insert_post($post);
        }
    
        if(!$post_id || is_wp_error($post_id)){
            return new WP_Error('acfe_dbt_import_error', __("Something went wrong with the block type \"{$title}\". Import aborted."));
        }
    
        // Import Args
        $args = apply_filters("acfe/block_type/import_args",                 $args, $name, $post_id);
        $args = apply_filters("acfe/block_type/import_args/name={$name}",    $args, $name, $post_id);
        $args = apply_filters("acfe/block_type/import_args/name={$post_id}", $args, $name, $post_id);
    
        if($args === false)
            return $post_id;
        
        // Import Fields
        acf_enable_filter('local');
        
        do_action("acfe/block_type/import_fields",                  $name, $args, $post_id);
        do_action("acfe/block_type/import_fields/name={$name}",     $name, $args, $post_id);
        do_action("acfe/block_type/import_fields/id={$post_id}",    $name, $args, $post_id);
    
        acf_disable_filter('local');
    
        // Save
        $this->save_post($post_id);
        
        return $post_id;
    
    }
    
    /*
     * Import: Fields
     */
    function import_fields($name, $args, $post_id){
        
        update_field('name',            $name, $post_id);
        update_field('description',     $args['description'], $post_id);
        update_field('category',        $args['category'], $post_id);
        update_field('keywords',        acf_encode_choices($args['keywords'], false), $post_id);
        update_field('post_types',      $args['post_types'], $post_id);
        update_field('mode',            $args['mode'], $post_id);
        update_field('align',           $args['align'], $post_id);
        update_field('render_callback', $args['render_callback'], $post_id);
        update_field('enqueue_assets',  $args['enqueue_assets'], $post_id);
        update_field('render_template', $args['render_template'], $post_id);
        update_field('enqueue_style',   $args['enqueue_style'], $post_id);
        update_field('enqueue_script',  $args['enqueue_script'], $post_id);
    
        // Align
        if(empty($args['align']))
            update_field('align', 'none', $post_id);
    
        // Icon
        if(!empty($args['icon'])){
        
            // Simple
            if(is_string($args['icon'])){
            
                update_field('icon_type', 'simple', $post_id);
                update_field('icon_text', $args['icon'], $post_id);
            
            }
        
            // Colors
            elseif(is_array($args['icon'])){
            
                update_field('icon_type', 'colors', $post_id);
            
                update_field('icon_background', $args['icon']['background'], $post_id);
                update_field('icon_foreground', $args['icon']['foreground'], $post_id);
                update_field('icon_src', $args['icon']['src'], $post_id);
            
            }
        
        }
    
        // Supports: Align
        update_field('supports_align', $args['supports']['align'], $post_id);
    
        if(is_array($args['supports']['align'])){
        
            update_field('supports_align_args', acf_encode_choices($args['supports']['align'], false), $post_id);
        
        }
    
        // Supports: Mode
        update_field('supports_mode', $args['supports']['mode'], $post_id);
    
        // Supports: Multiple
        update_field('supports_multiple', $args['supports']['multiple'], $post_id);
        
    }
    
    /*
     * Export: Choices
     */
    function export_choices(){
        
        $choices = array();
        $settings = acfe_get_settings($this->settings);
    
        if(!$settings)
            return $choices;
    
        foreach($settings as $name => $args){
        
            $choices[$name] = esc_html($args['title']);
        
        }
    
        return $choices;
    
    }
    
    /*
     * Export: Data
     */
    function export_data($name){
        
        // Settings
        $settings = acfe_get_settings($this->settings);
        
        // Doesn't exist
        if(!isset($settings[$name]))
            return false;
        
        $args = $settings[$name];
        
        // Filters
        $args = apply_filters("acfe/block_type/export_args",                 $args, $name);
        $args = apply_filters("acfe/block_type/export_args/name={$name}",    $args, $name);
        
        // Return
        return $args;
        
    }
    
    /*
     * Export: PHP
     */
    function export_php($data){
        
        // prevent default translation and fake __() within string
        acf_update_setting('l10n_var_export', true);
        
        $str_replace = array(
            "  "            => "\t",
            "'!!__(!!\'"    => "__('",
            "!!\', !!\'"    => "', '",
            "!!\')!!'"      => "')",
            "array ("       => "array("
        );
        
        $preg_replace = array(
            '/([\t\r\n]+?)array/'   => 'array',
            '/[0-9]+ => array/'     => 'array'
        );
        
        // Get settings.
        $l10n = acf_get_setting('l10n');
        $l10n_textdomain = acf_get_setting('l10n_textdomain');
        
        echo "if( function_exists('acf_register_block_type') ):" . "\r\n" . "\r\n";
        
        foreach($data as $args){
            
            // Translate settings if textdomain is set.
            if($l10n && $l10n_textdomain){
                
                $args['title'] = acf_translate($args['title']);
                $args['description'] = acf_translate($args['description']);
                
            }
            
            // code
            $code = var_export($args, true);
            
            // change double spaces to tabs
            $code = str_replace(array_keys($str_replace), array_values($str_replace), $code);
            
            // correctly formats "=> array("
            $code = preg_replace(array_keys($preg_replace), array_values($preg_replace), $code);
            
            // esc_textarea
            $code = esc_textarea($code);
            
            // echo
            echo "acf_register_block_type({$code});" . "\r\n" . "\r\n";
            
        }
        
        echo "endif;";
        
    }
    
    /*
     * Reset
     */
    function reset(){
        
        $args = apply_filters("acfe/block_type/reset_args", array(
            'post_type'         => $this->post_type,
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'post_status'       => array('publish', 'acf-disabled'),
        ));
        
        $posts = get_posts($args);
        
        if(empty($posts))
            return false;
        
        foreach($posts as $post_id){
            $this->save_post($post_id);
        }
    
        // Log
        acf_log('[ACF Extended] Reset: Block Types');
        
        return true;
        
    }
    
    /*
     * Multilang Save
     */
    function l10n_save($name, $args, $post_id){
        
        // Bail early
        if(!acfe_is_wpml())
            return;
        
        // Translate: Title
        if(isset($args['title'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Title', $args['title']);
        }
        
        // Translate: Description
        if(isset($args['description'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Description', $args['description']);
        }
        
    }
    
    /*
     * Multilang Register
     */
    function l10n_register($args, $name){
        
        // Translate: Title
        if(isset($args['title'])){
            $args['title'] = acfe_translate($args['title'], 'Title', $this->textdomain);
        }
        
        // Translate: Description
        if(isset($args['description'])){
            $args['description'] = acfe_translate($args['description'], 'Description', $this->textdomain);
        }
        
        return $args;
        
    }
    
    /*
     * Add Local Field Group
     */
    function add_local_field_group(){
    
        //$__experimental_jsx = array();
        $experimental_jsx = array();
        $align_content = array();
        $supports_align_content = array();
    
        if(acf_version_compare(acf_get_setting('version'),  '>=', '5.9')){
        
            $experimental_jsx = array(
                'key' => 'field_acfe_dbt_supports_experimental_jsx',
                'label' => 'Inner Block',
                'name' => 'supports_experimental_jsx',
                'type' => 'true_false',
                'instructions' => 'Enable inner block feature. Defaults to false.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'acfe_validate' => '',
                'acfe_update' => '',
                'acfe_permissions' => '',
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'True',
                'ui_off_text' => 'False',
            );
        
            $supports_align_content = array(
                'key' => 'field_acfe_dbt_supports_align_content',
                'label' => 'Align Content',
                'name' => 'supports_align_content',
                'type' => 'true_false',
                'instructions' => 'Set the "xy" position of content using a 3×3 matrix grid. Defaults to false.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'acfe_validate' => '',
                'acfe_update' => '',
                'acfe_permissions' => '',
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'True',
                'ui_off_text' => 'False',
            );
        
            $align_content = array(
                'key' => 'field_acfe_dbt_align_content',
                'label' => 'Align content',
                'name' => 'align_content',
                'type' => 'text',
                'instructions' => 'Specifies the default attribute value.',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_acfe_dbt_supports_align_content',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'acfe_validate' => '',
                'acfe_update' => '',
                'acfe_permissions' => '',
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            );
        
        }
    
        /**
         * Add Local Field Group
         */
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_block_type',
            'title' => __('Dynamic Block Type', 'acfe'),
        
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $this->post_type,
                    ),
                ),
            ),
        
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        
            'fields' => array(
                array(
                    'key' => 'field_acfe_dbt_tab_general',
                    'label' => 'General',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dbt_name',
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'acfe_slug',
                    'instructions' => '(String) A unique name that identifies the block (without namespace).<br />
Note: A block name can only contain lowercase alphanumeric characters and dashes, and must begin with a letter.',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => array(
                        array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_description',
                    'label' => 'Description',
                    'name' => 'description',
                    'type' => 'textarea',
                    'instructions' => '(String) (Optional) This is a short description for your block.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 3,
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_category',
                    'label' => 'Category',
                    'name' => 'category',
                    'type' => 'text',
                    'instructions' => '(String) Blocks are grouped into categories to help users browse and discover them. The core provided categories are [ common | formatting | layout | widgets | embed ]. Plugins and Themes can also register custom block categories.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => 'common',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_keywords',
                    'label' => 'Keywords',
                    'name' => 'keywords',
                    'type' => 'textarea',
                    'instructions' => '(Array) (Optional) An array of search terms to help user discover the block while searching.<br />
One line for each keyword. ie:<br /><br />
quote<br />
mention<br />
cite',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_post_types',
                    'label' => 'Post types',
                    'name' => 'post_types',
                    'type' => 'acfe_post_types',
                    'instructions' => '(Array) (Optional) An array of post types to restrict this block type to.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'field_type' => 'checkbox',
                    'return_format' => 'name',
                ),
                array(
                    'key' => 'field_acfe_dbt_mode',
                    'label' => 'Mode',
                    'name' => 'mode',
                    'type' => 'select',
                    'instructions' => '(String) (Optional) The display mode for your block. Available settings are “auto”, “preview” and “edit”. Defaults to “preview”.<br /><br />
auto: Preview is shown by default but changes to edit form when block is selected.<br />
preview: Preview is always shown. Edit form appears in sidebar when block is selected.<br />
edit: Edit form is always shown.<br /><br />

Note. When in “preview” or “edit” modes, an icon will appear in the block toolbar to toggle between modes.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'choices' => array(
                        'preview' => 'Preview',
                        'auto' => 'Auto',
                        'edit' => 'Edit',
                    ),
                    'default_value' => array(
                        0 => 'preview',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_align',
                    'label' => 'Align',
                    'name' => 'align',
                    'type' => 'select',
                    'instructions' => '(String) (Optional) The default block alignment. Available settings are “left”, “center”, “right”, “wide” and “full”. Defaults to an empty string.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'choices' => array(
                        'none' => 'None',
                        'left' => 'Left',
                        'center' => 'Center',
                        'right' => 'Right',
                        'wide' => 'Wide',
                        'full' => 'Full',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_tab_icon',
                    'label' => 'Icon',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dbt_icon_type',
                    'label' => 'Icon Type',
                    'name' => 'icon_type',
                    'type' => 'select',
                    'instructions' => 'Simple: Specify a Dashicons class or SVG path<br />
Colors: Specify colors & Dashicons class',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'choices' => array(
                        'simple' => 'Simple',
                        'colors' => 'Colors',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_icon_text',
                    'label' => 'Icon',
                    'name' => 'icon_text',
                    'type' => 'text',
                    'instructions' => '(String) (Optional) An icon property can be specified to make it easier to identify a block. These can be any of WordPress’ Dashicons, or a custom svg element.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dbt_icon_type',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_icon_background',
                    'label' => 'Icon background',
                    'name' => 'icon_background',
                    'type' => 'color_picker',
                    'instructions' => 'Specifying a background color to appear with the icon e.g.: in the inserter.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dbt_icon_type',
                                'operator' => '==',
                                'value' => 'colors',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_icon_foreground',
                    'label' => 'Icon foreground',
                    'name' => 'icon_foreground',
                    'type' => 'color_picker',
                    'instructions' => 'Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dbt_icon_type',
                                'operator' => '==',
                                'value' => 'colors',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_icon_src',
                    'label' => 'Icon src',
                    'name' => 'icon_src',
                    'type' => 'text',
                    'instructions' => 'Specifying a dashicon for the block',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dbt_icon_type',
                                'operator' => '==',
                                'value' => 'colors',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_tab_render',
                    'label' => 'Render',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dbt_render_template',
                    'label' => 'Render template',
                    'name' => 'render_template',
                    'type' => 'text',
                    'instructions' => '(String) The path to a template file used to render the block HTML. This can either be a relative path to a file within the active theme or a full path to any file.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_render_callback',
                    'label' => 'Render callback',
                    'name' => 'render_callback',
                    'type' => 'text',
                    'instructions' => '(Callable) (Optional) Instead of providing a render_template, a callback function name may be specified to output the block’s HTML.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_tab_enqueue',
                    'label' => 'Enqueue',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dbt_enqueue_style',
                    'label' => 'Enqueue style',
                    'name' => 'enqueue_style',
                    'type' => 'text',
                    'instructions' => '(String) (Optional) The url to a .css file to be enqueued whenever your block is displayed (front-end and back-end).',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_enqueue_script',
                    'label' => 'Enqueue script',
                    'name' => 'enqueue_script',
                    'type' => 'text',
                    'instructions' => '(String) (Optional) The url to a .js file to be enqueued whenever your block is displayed (front-end and back-end).',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_enqueue_assets',
                    'label' => 'Enqueue assets',
                    'name' => 'enqueue_assets',
                    'type' => 'text',
                    'instructions' => '(Callable) (Optional) A callback function that runs whenever your block is displayed (front-end and back-end) and enqueues scripts and/or styles.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dbt_tab_supports',
                    'label' => 'Supports',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dbt_supports_align',
                    'label' => 'Align',
                    'name' => 'supports_align',
                    'type' => 'true_false',
                    'instructions' => 'This property adds block controls which allow the user to change the block’s alignment. Defaults to true. Set to false to hide the alignment toolbar. Set to an array of specific alignment names to customize the toolbar.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
                array(
                    'key' => 'field_acfe_dbt_supports_align_args',
                    'label' => 'Align arguments',
                    'name' => 'supports_align_args',
                    'type' => 'textarea',
                    'instructions' => 'Set to an array of specific alignment names to customize the toolbar.<br />
One line for each name. ie:<br /><br />
left<br />
right<br />
full',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dbt_supports_align',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => '',
                    'new_lines' => '',
                ),
            
                array(
                    'key' => 'field_acfe_dbt_supports_anchor',
                    'label' => 'Anchor',
                    'name' => 'supports_anchor',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
            
                $experimental_jsx,
            
                $supports_align_content,
            
                $align_content,
            
                array(
                    'key' => 'field_acfe_dbt_supports_mode',
                    'label' => 'Mode',
                    'name' => 'supports_mode',
                    'type' => 'true_false',
                    'instructions' => 'This property allows the user to toggle between edit and preview modes via a button. Defaults to true.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
                array(
                    'key' => 'field_acfe_dbt_supports_multiple',
                    'label' => 'Multiple',
                    'name' => 'supports_multiple',
                    'type' => 'true_false',
                    'instructions' => 'This property allows the block to be added multiple times. Defaults to true.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
            ),
        ));
    
    }
    
}

acf_new_instance('acfe_dynamic_block_types');

endif;