<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dynamic_post_types')):

class acfe_dynamic_post_types extends acfe_dynamic_module{
    
    /*
     * Initialize
     */
    function initialize(){
        
        $this->name = 'post_type';
        $this->active = acf_get_setting('acfe/modules/post_types');
        $this->settings = 'modules.post_types';
        $this->post_type = 'acfe-dpt';
        $this->label = 'Post Type Label';
        $this->textdomain = 'ACF Extended: Post Types';
        
        $this->tool = 'acfe_dynamic_post_types_export';
        $this->tools = array('php', 'json');
        $this->columns = array(
            'acfe-name'         => __('Name', 'acf'),
            'acfe-taxonomies'   => __('Taxonomies', 'acf'),
            'acfe-posts'        => __('Posts', 'acf'),
        );
        
    }
    
    /*
     * Actions
     */
    function actions(){
        
        // Features
        add_action('admin_footer-edit.php',                 array($this, 'admin_config'));
        add_action('pre_get_posts',                         array($this, 'admin_archive_posts'));
        add_filter('edit_posts_per_page',                   array($this, 'admin_archive_ppp'), 10, 2);
        add_action('pre_get_posts',                         array($this, 'front_archive_posts'));
        add_filter('template_include',                      array($this, 'front_template'), 999);
        
        // Validate
        add_filter('acf/validate_value/name=acfe_dpt_name', array($this, 'validate_name'), 10, 4);
        add_filter('acf/update_value/name=acfe_dpt_name',   array($this, 'update_name'), 10, 3);
        
        // Save
        add_filter('acfe/post_type/save_args',              array($this, 'save_args'), 10, 3);
        add_action('acfe/post_type/save',                   array($this, 'save'), 10, 3);
        
        // Import
        add_action('acfe/post_type/import_fields',          array($this, 'import_fields'), 10, 3);
        add_action('acfe/post_type/import',                 array($this, 'after_import'), 10, 2);
        
        // Multilang
        add_action('acfe/post_type/save',                   array($this, 'l10n_save'), 10, 3);
        add_filter('acfe/post_type/register',               array($this, 'l10n_register'), 10, 2);
        
    }
    
    /*
     * Get Name
     */
    function get_name($post_id){
        
        return get_field('acfe_dpt_name', $post_id);
        
    }
    
    /*
     * Init
     */
    function init(){
        
        $this->register_post_type();
        $this->register_user_post_types();
        
    }
    
    /*
     * Register Post Type
     */
    function register_post_type(){
    
        $capability = acf_get_setting('capability');
        
        if(!acf_get_setting('show_admin'))
            $capability = false;
        
        register_post_type($this->post_type, array(
            'label'                 => 'Post Types',
            'description'           => 'Post Types',
            'labels'                => array(
                'name'          => 'Post Types',
                'singular_name' => 'Post Type',
                'menu_name'     => 'Post Types',
                'edit_item'     => 'Edit Post Type',
                'add_new_item'  => 'New Post Type',
            ),
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'tools.php',
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
     * Register User Post Types
     */
    function register_user_post_types(){
        
        $settings = apply_filters('acfe/post_type/prepare_register', acfe_get_settings($this->settings));
    
        if(empty($settings))
            return;
        
        foreach($settings as $name => $args){
    
            // Bail early
            if(empty($name) || post_type_exists($name))
                continue;
            
            // Filters
            $args = apply_filters("acfe/post_type/register",                 $args, $name);
            $args = apply_filters("acfe/post_type/register/name={$name}",    $args, $name);
    
            if($args === false)
                continue;
    
            // Register
            register_post_type($name, $args);
        
        }
        
    }
    
    /*
     * Post Screen
     */
    function post_screen(){
        
        flush_rewrite_rules();
        
    }
    
    /*
     * Edit Row Actions View
     */
    function edit_row_actions_view($post, $name){
    
        return '<a href="' . admin_url("edit.php?post_type={$name}") . '">' . __('View') . '</a>';
    
    }
    
    /*
     * Edit Columns HTML
     */
    function edit_columns_html($column, $post_id){
    
        switch($column){
            
            // Name
            case 'acfe-name':
                
                echo '<code style="font-size: 12px;">' . $this->get_name($post_id) . '</code>';
                break;
                
            // Taxonomies
            case 'acfe-taxonomies':
                
                $tax = '—';
    
                $get_taxonomies = acf_get_array(get_field('taxonomies', $post_id));
                
                if(!empty($get_taxonomies)){
                    
                    $taxonomies = array();
                    
                    foreach($get_taxonomies as $taxonomy){
                        
                        if(!taxonomy_exists($taxonomy))
                            continue;
                        
                        $taxonomies[] = $taxonomy;
                        
                    }
                    
                    if(!empty($taxonomies)){
    
                        $taxonomy_labels = acf_get_taxonomy_labels($taxonomies);
    
                        if(!empty($taxonomy_labels)){
                            $tax = implode(', ', $taxonomy_labels);
                        }
                        
                    }
                    
                }
                
                echo $tax;
                break;
                
            // Posts
            case 'acfe-posts':
                
                // vars
                $c = '—';
                $name = $this->get_name($post_id);
                $count = wp_count_posts($name);
    
                if(!empty($count) && isset($count->publish)){
    
                    $count_publish = $count->publish;
                    $c = '<a href="' . admin_url('edit.php?post_type=' . $name) . '">' . $count_publish . '</a>';
        
                }
    
                echo $c;
                break;
                
        }
        
    }
    
    /*
     * Admin Config Button
     */
    function admin_config(){
        
        if(!acf_current_user_can_admin())
            return;
        
        global $typenow;
        
        if(empty($typenow))
            return;
        
        $post_type_obj = get_post_type_object($typenow);
        
        if(!isset($post_type_obj->acfe_admin_ppp))
            return;
        
        $post = get_page_by_path($typenow, 'OBJECT', $this->post_type);
        
        if(empty($post))
            return;
        
        ?>
        <script type="text/html" id="tmpl-acfe-dpt-title-config">
            <a href="<?php echo admin_url("post.php?post={$post->ID}&action=edit"); ?>" class="page-title-action acfe-edit-module-button"><span class="dashicons dashicons-admin-generic"></span></a>
        </script>

        <script type="text/javascript">
        (function($){
            $('.wrap .page-title-action').before($('#tmpl-acfe-dpt-title-config').html());
        })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Admin: Archive
     */
    function admin_archive_posts($query){
        
        global $pagenow;
        
        if(!is_admin() || !$query->is_main_query() || $pagenow !== 'edit.php')
            return;
        
        $post_type = $query->get('post_type');
        $object = get_post_type_object($post_type);
        
        $admin_order_by = acfe_maybe_get($object, 'acfe_admin_orderby');
        $admin_order = acfe_maybe_get($object, 'acfe_admin_order');
        
        if($admin_order_by && !acfe_maybe_get_REQUEST('orderby'))
            $query->set('orderby', $admin_order_by);
        
        if($admin_order && !acfe_maybe_get_REQUEST('order'))
            $query->set('order', $admin_order);
        
    }
    
    /*
     * Admin: Posts Per Page
     */
    function admin_archive_ppp($ppp, $post_type){
        
        global $pagenow;
        
        if($pagenow !== 'edit.php')
            return $ppp;
        
        $object = get_post_type_object($post_type);
        $admin_ppp = acfe_maybe_get($object, 'acfe_admin_ppp');
        $user_ppp = get_user_option("edit_{$post_type}_per_page");
        
        if(!$admin_ppp || !empty($user_ppp))
            return $ppp;
        
        return $admin_ppp;
        
    }
    
    /*
     * Front: Archive
     */
    function front_archive_posts($query){
        
        if(is_admin() || !$query->is_main_query() || !is_post_type_archive())
            return;
        
        $post_type = $query->get('post_type');
        $object = get_post_type_object($post_type);
        
        $archive_ppp = acfe_maybe_get($object, 'acfe_archive_ppp');
        $archive_orderby = acfe_maybe_get($object, 'acfe_archive_orderby');
        $archive_order = acfe_maybe_get($object, 'acfe_archive_order');
        
        if($archive_ppp)
            $query->set('posts_per_page', $archive_ppp);
        
        if($archive_orderby)
            $query->set('orderby', $archive_orderby);
        
        if($archive_order)
            $query->set('order', $archive_order);
        
    }
    
    /*
     * Front: Template
     */
    function front_template($template){
        
        if(!is_single() && !is_post_type_archive() && !is_home())
            return $template;
        
        // Get_query_var
        $query_var = get_query_var('post_type', false);
        
        if(is_array($query_var) && !empty($query_var))
            $query_var = $query_var[0];
        
        foreach(get_post_types(array(), 'objects') as $post_type){
            
            // Get_query_var check
            $is_query_var = ($query_var && $query_var === $post_type->name);
            
            // Get_post_type check
            $get_post_type = (get_post_type() === $post_type->name);
            
            // Acfe_archive_template
            $acfe_archive_template = (isset($post_type->acfe_archive_template) && !empty($post_type->acfe_archive_template));
            
            // Acfe_archive_template
            $acfe_single_template = (isset($post_type->acfe_single_template) && !empty($post_type->acfe_single_template));
            
            // Global check
            if(!$get_post_type || !$is_query_var || (!$acfe_archive_template && !$acfe_single_template))
                continue;
            
            $rule = array();
            $rule['is_archive'] = is_post_type_archive($post_type->name);
            $rule['has_archive'] = $post_type->has_archive;
            $rule['is_single'] = is_singular($post_type->name);
            
            // Post Exception
            if($post_type->name === 'post'){
                $rule['is_archive'] = is_home();
                $rule['has_archive'] = true;
            }
            
            // Archive
            if($rule['has_archive'] && $rule['is_archive'] && $acfe_archive_template && ($locate = locate_template(array($post_type->acfe_archive_template))))
                return $locate;
            
            // Single
            elseif($rule['is_single'] && $acfe_single_template && ($locate = locate_template(array($post_type->acfe_single_template))))
                return $locate;
            
        }
        
        return $template;
        
    }
    
    /*
     * Validate Name
     */
    function validate_name($valid, $value, $field, $input){
        
        if(!$valid)
            return $valid;
    
        // Reserved WP Post types
        // See: https://codex.wordpress.org/Function_Reference/register_post_type#Reserved_Post_Types
        $exclude = array(
            'post',
            'posts',
            'page',
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'action',
            'author',
            'order',
            'theme',
        );
        
        $exclude = array_merge($exclude, acfe_get_setting('reserved_post_types', array()));
        
        // Reserved Names
        if(in_array($value, $exclude))
            return __('This post type name is reserved');
        
        // Editing Current Dynamic Post Type
        $current_post_id = acf_maybe_get_POST('post_ID');
        
        if(!empty($current_post_id)){
            
            $current_name = get_field($field['name'], $current_post_id);
            
            if($value === $current_name)
                return $valid;
            
        }
        
        // Check existing WP Post Types
        global $wp_post_types;
        
        if(!empty($wp_post_types)){
            
            foreach($wp_post_types as $post_type){
                
                if($value !== $post_type->name)
                    continue;
                
                $valid = __('This post type name already exists');
                
            }
            
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
     * ACF Save post
     */
    function save_post($post_id){
        
        // vars
        $args = array();
        $name = $this->get_name($post_id);
        
        // Filters
        $args = apply_filters("acfe/post_type/save_args",               $args, $name, $post_id);
        $args = apply_filters("acfe/post_type/save_args/name={$name}",  $args, $name, $post_id);
        $args = apply_filters("acfe/post_type/save_args/id={$post_id}", $args, $name, $post_id);
        
        if($args === false)
            return;
        
        // Actions
        do_action("acfe/post_type/save",               $name, $args, $post_id);
        do_action("acfe/post_type/save/name={$name}",  $name, $args, $post_id);
        do_action("acfe/post_type/save/id={$post_id}", $name, $args, $post_id);
        
    }
    
    /*
     * Save Args
     */
    function save_args($args, $name, $post_id){
        
        $label = get_post_field('post_title', $post_id);
        $name = get_field('acfe_dpt_name', $post_id);
        $description = get_field('description', $post_id);
        $hierarchical = get_field('hierarchical', $post_id);
        $supports = get_field('supports', $post_id);
        $taxonomies = acf_get_array(get_field('taxonomies', $post_id));
        $public = get_field('public', $post_id);
        $exclude_from_search = get_field('exclude_from_search', $post_id);
        $publicly_queryable = get_field('publicly_queryable', $post_id);
        $can_export = get_field('can_export', $post_id);
        $delete_with_user = get_field('delete_with_user', $post_id);
        
        // Labels
        $labels = acf_get_array(get_field('labels', $post_id));
        $labels_args = array();
        foreach($labels as $k => $l){
            if(empty($l))
                continue;
            
            $labels_args[$k] = $l;
        }
        
        // Menu
        $menu_position = get_field('menu_position', $post_id);
        $menu_icon = get_field('menu_icon', $post_id);
        $show_ui = get_field('show_ui', $post_id);
        $show_in_menu = get_field('show_in_menu', $post_id);
        $show_in_menu_text = get_field('show_in_menu_text', $post_id);
        $show_in_nav_menus = get_field('show_in_nav_menus', $post_id);
        $show_in_admin_bar = get_field('show_in_admin_bar', $post_id);
        
        // Capability
        $capability_type = acf_decode_choices(get_field('capability_type', $post_id), true);
        $capabilities = acf_decode_choices(get_field('capabilities', $post_id));
        $map_meta_cap = get_field('map_meta_cap', $post_id);
        
        // Archive
        $archive_template = get_field('acfe_dpt_archive_template', $post_id);
        $archive_posts_per_page = (int) get_field('acfe_dpt_archive_posts_per_page', $post_id);
        $archive_orderby = get_field('acfe_dpt_archive_orderby', $post_id);
        $archive_order = get_field('acfe_dpt_archive_order', $post_id);
        $has_archive = get_field('has_archive', $post_id);
        $has_archive_slug = get_field('has_archive_slug', $post_id);
        
        // Single
        $single_template = get_field('acfe_dpt_single_template', $post_id);
        $rewrite = get_field('rewrite', $post_id);
        $rewrite_args_select = get_field('rewrite_args_select', $post_id);
        $rewrite_args = get_field('rewrite_args', $post_id);
        
        // Admin
        $admin_archive = get_field('acfe_dpt_admin_archive', $post_id);
        $admin_posts_per_page = (int) get_field('acfe_dpt_admin_posts_per_page', $post_id);
        $admin_orderby = get_field('acfe_dpt_admin_orderby', $post_id);
        $admin_order = get_field('acfe_dpt_admin_order', $post_id);
        
        // REST
        $show_in_rest = get_field('show_in_rest', $post_id);
        $rest_base = get_field('rest_base', $post_id);
        $rest_controller_class = get_field('rest_controller_class', $post_id);
        
        // Register: Args
        $args = array(
            'label'                 => $label,
            'description'           => $description,
            'hierarchical'          => $hierarchical,
            'supports'              => $supports,
            'taxonomies'            => $taxonomies,
            'public'                => $public,
            'exclude_from_search'   => $exclude_from_search,
            'publicly_queryable'    => $publicly_queryable,
            'can_export'            => $can_export,
            'delete_with_user'      => $delete_with_user,
            
            // Labels
            'labels'                => $labels_args,
            
            // Menu
            'menu_icon'             => $menu_icon,
            'show_ui'               => $show_ui,
            'show_in_menu'          => $show_in_menu,
            'show_in_nav_menus'     => $show_in_nav_menus,
            'show_in_admin_bar'     => $show_in_admin_bar,
            
            // Single
            'rewrite'               => $rewrite,
            
            // Archive
            'has_archive'           => $has_archive,
            
            // REST
            'show_in_rest'          => $show_in_rest,
            'rest_base'             => $rest_base,
            'rest_controller_class' => $rest_controller_class,
            
            // ACFE: Archive
            'acfe_archive_template' => $archive_template,
            'acfe_archive_ppp'      => $archive_posts_per_page,
            'acfe_archive_orderby'  => $archive_orderby,
            'acfe_archive_order'    => $archive_order,
            
            // ACFE: Single
            'acfe_single_template'  => $single_template,
            
            // ACFE: Admin
            'acfe_admin_archive'    => $admin_archive,
            'acfe_admin_ppp'        => $admin_posts_per_page,
            'acfe_admin_orderby'    => $admin_orderby,
            'acfe_admin_order'      => $admin_order,
        );
        
        // Menu Position
        if(!acf_is_empty($menu_position))
            $args['menu_position'] = (int) $menu_position;
        
        // Has archive: override
        if($has_archive && $has_archive_slug)
            $args['has_archive'] = $has_archive_slug;
        
        // Rewrite: override
        if($rewrite && $rewrite_args_select){
            
            $args['rewrite'] = array(
                'slug'          => $rewrite_args['acfe_dpt_rewrite_slug'],
                'with_front'    => $rewrite_args['acfe_dpt_rewrite_with_front'],
                'feeds'         => $rewrite_args['feeds'],
                'pages'         => $rewrite_args['pages'],
            );
            
        }
        
        // Show in menu (text)
        if($show_in_menu && !empty($show_in_menu_text))
            $args['show_in_menu'] = $show_in_menu_text;
        
        // Capability type
        $args['capability_type'] = $capability_type;
        if(is_array($capability_type) && count($capability_type) == 1)
            $args['capability_type'] = $capability_type[0];
        
        // Capabilities
        $args['capabilities'] = $capabilities;
        
        // Map meta cap
        $args['map_meta_cap'] = null;
        
        if($map_meta_cap === 'false')
            $args['map_meta_cap'] = false;

        elseif($map_meta_cap === 'true')
            $args['map_meta_cap'] = true;
        
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
        
        // Flush permalinks
        flush_rewrite_rules();
        
    }
    
    /*
     * Import
     */
    function import($name, $args){
        
        // Vars
        $settings = acfe_get_settings($this->settings);
        $title = $args['label'];
        
        // Already exists
        if(isset($settings[$name])){
            return new WP_Error('acfe_dpt_import_already_exists', __("Post type \"{$title}\" already exists. Import aborted."));
        }
        
        // Import Post
        $post_id = false;
        
        $post = array(
            'post_title'    => $title,
            'post_name'     => $name,
            'post_type'     => $this->post_type,
            'post_status'   => 'publish'
        );
    
        $post = apply_filters("acfe/post_type/import_post",                 $post, $name);
        $post = apply_filters("acfe/post_type/import_post/name={$name}",    $post, $name);
    
        if($post !== false){
            $post_id = wp_insert_post($post);
        }
        
        if(!$post_id || is_wp_error($post_id)){
            return new WP_Error('acfe_dpt_import_error', __("Something went wrong with the post type \"{$title}\". Import aborted."));
        }
        
        // Import Args
        $args = apply_filters("acfe/post_type/import_args",                 $args, $name, $post_id);
        $args = apply_filters("acfe/post_type/import_args/name={$name}",    $args, $name, $post_id);
        $args = apply_filters("acfe/post_type/import_args/name={$post_id}", $args, $name, $post_id);
        
        if($args === false)
            return $post_id;
    
        // Import Fields
        acf_enable_filter('local');
        
        do_action("acfe/post_type/import_fields",                  $name, $args, $post_id);
        do_action("acfe/post_type/import_fields/name={$name}",     $name, $args, $post_id);
        do_action("acfe/post_type/import_fields/id={$post_id}",    $name, $args, $post_id);
        
        acf_disable_filter('local');
    
        // Save
        $this->save_post($post_id);
        
        return $post_id;
        
    }
    
    /*
     * Import Fields
     */
    function import_fields($name, $args, $post_id){
    
        // Register Args
        update_field('acfe_dpt_name', $name, $post_id);
        update_field('description', $args['description'], $post_id);
        update_field('hierarchical', $args['hierarchical'], $post_id);
        update_field('supports', $args['supports'], $post_id);
        update_field('taxonomies', $args['taxonomies'], $post_id);
        update_field('public', $args['public'], $post_id);
        update_field('exclude_from_search', $args['exclude_from_search'], $post_id);
        update_field('publicly_queryable', $args['publicly_queryable'], $post_id);
        update_field('can_export', $args['can_export'], $post_id);
        update_field('delete_with_user', $args['delete_with_user'], $post_id);
    
        // Labels
        if(!empty($args['labels'])){
        
            foreach($args['labels'] as $label_key => $label_value){
                update_field('labels_' . $label_key, $label_value, $post_id);
            }
        
        }
    
        // Menu
        update_field('menu_position', acf_maybe_get($args, 'menu_position'), $post_id);
        update_field('menu_icon', $args['menu_icon'], $post_id);
        update_field('show_ui', $args['show_ui'], $post_id);
        update_field('show_in_menu', $args['show_in_menu'], $post_id);
        update_field('show_in_nav_menus', $args['show_in_nav_menus'], $post_id);
        update_field('show_in_admin_bar', $args['show_in_admin_bar'], $post_id);
    
        // Capability
        update_field('capability_type', acf_encode_choices($args['capability_type'], false), $post_id);
        update_field('map_meta_cap', $args['map_meta_cap'], $post_id);
    
        if(isset($args['capabilities']))
            update_field('capabilities', acf_encode_choices($args['capabilities'], false), $post_id);
    
        // Archive
        update_field('acfe_dpt_archive_template', $args['acfe_archive_template'], $post_id);
        update_field('acfe_dpt_archive_posts_per_page', $args['acfe_archive_ppp'], $post_id);
        update_field('acfe_dpt_archive_orderby', $args['acfe_archive_orderby'], $post_id);
        update_field('acfe_dpt_archive_order', $args['acfe_archive_order'], $post_id);
        update_field('has_archive', $args['has_archive'], $post_id);
    
        // Single
        update_field('acfe_dpt_single_template', $args['acfe_single_template'], $post_id);
        update_field('rewrite', $args['rewrite'], $post_id);
    
        // Admin
        update_field('acfe_dpt_admin_posts_per_page', $args['acfe_admin_ppp'], $post_id);
        update_field('acfe_dpt_admin_orderby', $args['acfe_admin_orderby'], $post_id);
        update_field('acfe_dpt_admin_order', $args['acfe_admin_order'], $post_id);
    
        // REST
        update_field('show_in_rest', $args['show_in_rest'], $post_id);
        update_field('rest_base', $args['rest_base'], $post_id);
        update_field('rest_controller_class', $args['rest_controller_class'], $post_id);
    
        // Has archive: override
        if($args['has_archive'] && is_string($args['has_archive']))
            update_field('has_archive_slug', $args['has_archive'], $post_id);
    
        // Rewrite: override
        if($args['rewrite'] && is_array($args['rewrite'])){
        
            update_field('rewrite', true, $post_id);
        
            update_field('rewrite_args_select', true, $post_id);
        
            update_field('rewrite_args_acfe_dpt_rewrite_slug', $args['rewrite']['slug'], $post_id);
            update_field('rewrite_args_acfe_dpt_rewrite_with_front', $args['rewrite']['with_front'], $post_id);
            update_field('rewrite_args_feeds', $args['rewrite']['feeds'], $post_id);
            update_field('rewrite_args_pages', $args['rewrite']['pages'], $post_id);
        
        }
    
        // Show in menu (text)
        if($args['show_in_menu'] && is_string($args['show_in_menu']))
            update_field('show_in_menu_text', $args['show_in_menu'], $post_id);
    
        // Map meta cap
        if($args['map_meta_cap'] === false)
            update_field('map_meta_cap', 'false', $post_id);

        elseif($args['map_meta_cap'] === true)
            update_field('map_meta_cap', 'true', $post_id);
        
    }
    
    /*
     * After Import
     */
    function after_import($ids, $data){
        
        flush_rewrite_rules();
        
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
            
            $choices[$name] = esc_html($args['label']);
            
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
        $args = apply_filters("acfe/post_type/export_args",                 $args, $name);
        $args = apply_filters("acfe/post_type/export_args/name={$name}",    $args, $name);
        
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
        
        foreach($data as $post_type => $args){
            
            // Translate settings if textdomain is set.
            if($l10n && $l10n_textdomain){
                
                $args['label'] = acf_translate($args['label']);
                $args['description'] = acf_translate($args['description']);
                
                if(!empty($args['labels'])){
                    
                    foreach($args['labels'] as $key => &$label){
                        $args['labels'][$key] = acf_translate($label);
                    }
                    
                }
                
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
            echo "register_post_type('{$post_type}', {$code});" . "\r\n" . "\r\n";
            
        }
        
    }
    
    /*
     * Reset
     */
    function reset(){
        
        $args = apply_filters('acfe/post_type/reset_args', array(
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
        acf_log('[ACF Extended] Reset: Post Types');
        
        return true;
        
    }
    
    /*
     * Multilang Save
     */
    function l10n_save($name, $args, $post_id){
        
        // Bail early
        if(!acfe_is_wpml())
            return;
        
        // Translate: Label
        if(isset($args['label'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Label', $args['label']);
        }
        
        // Translate: Description
        if(isset($args['description'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Description', $args['description']);
        }
        
        // Translate: Labels
        if(isset($args['labels'])){
            
            foreach($args['labels'] as $label_name => &$label_text){
                do_action('wpml_register_single_string', $this->textdomain, ucfirst($label_name), $label_text);
            }
            
        }
        
    }
    
    /*
     * Multilang Register
     */
    function l10n_register($args, $name){
        
        // Translate: Label
        if(isset($args['label'])){
            $args['label'] = acfe_translate($args['label'], 'Label', $this->textdomain);
        }
        
        // Translate: Description
        if(isset($args['description'])){
            $args['description'] = acfe_translate($args['description'], 'Description', $this->textdomain);
        }
        
        // Translate: Labels
        if(isset($args['labels'])){
            
            foreach($args['labels'] as $label_name => &$label_text){
                $label_text = acfe_translate($label_text, ucfirst($label_name), $this->textdomain);
            }
            
        }
        
        return $args;
        
    }
    
    /*
     * Add Local Field Group
     */
    function add_local_field_group(){
    
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_post_type',
            'title' => __('Dynamic Post Type', 'acfe'),
        
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
                    'key' => 'field_acfe_dpt_tab_general',
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
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_name',
                    'label' => 'Name',
                    'name' => 'acfe_dpt_name',
                    'type' => 'acfe_slug',
                    'instructions' => 'Post type name. Max. 20 characters, cannot contain capital letters or spaces',
                    'required' => 1,
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
                    'maxlength' => 20,
                ),
                array(
                    'key' => 'field_acfe_dpt_description',
                    'label' => 'Description',
                    'name' => 'description',
                    'type' => 'text',
                    'instructions' => 'A short descriptive summary of the post type',
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
                    'key' => 'field_acfe_dpt_hierarchical',
                    'label' => 'Hierarchical',
                    'name' => 'hierarchical',
                    'type' => 'true_false',
                    'instructions' => 'Whether the post type is hierarchical (e.g. page). Allows Parent to be specified. The \'supports\' parameter should contain \'page-attributes\' to show the parent select box on the editor page.',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_supports',
                    'label' => 'Supports',
                    'name' => 'supports',
                    'type' => 'checkbox',
                    'instructions' => 'An alias for calling add_post_type_support() directly. As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behavior.',
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
                        'title' => 'Title',
                        'editor' => 'Editor',
                        'author' => 'Author',
                        'thumbnail' => 'Thumbnail',
                        'excerpt' => 'Excerpt',
                        'trackbacks' => 'Trackbacks',
                        'custom-fields' => 'Custom fields',
                        'comments' => 'Comments',
                        'revisions' => 'Revisions',
                        'page-attributes' => 'Page attributes',
                        'post-formats' => 'Post formats',
                    ),
                    'allow_custom' => 1,
                    'save_custom' => 1,
                    'default_value' => array(
                        0 => 'title',
                        1 => 'thumbnail',
                        2 => 'custom-fields',
                    ),
                    'layout' => 'vertical',
                    'toggle' => 0,
                    'return_format' => 'value',
                ),
                array(
                    'key' => 'field_acfe_dpt_taxonomies',
                    'label' => 'Taxonomies',
                    'name' => 'taxonomies',
                    'type' => 'acfe_taxonomies',
                    'instructions' => 'An array of registered taxonomies like category or post_tag that will be used with this post type. This can be used in lieu of calling register_taxonomy_for_object_type() directly. Custom taxonomies still need to be registered with register_taxonomy()',
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
                    'multiple' => 0,
                    'allow_null' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_public',
                    'label' => 'Public',
                    'name' => 'public',
                    'type' => 'true_false',
                    'instructions' => 'Controls how the type is visible to authors (show_in_nav_menus, show_ui) and readers (exclude_from_search, publicly_queryable)',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_exclude_from_search',
                    'label' => 'Exclude from search',
                    'name' => 'exclude_from_search',
                    'type' => 'true_false',
                    'instructions' => 'Whether to exclude posts with this post type from front end search results',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_publicly_queryable',
                    'label' => 'Publicly queryable',
                    'name' => 'publicly_queryable',
                    'type' => 'true_false',
                    'instructions' => 'Whether queries can be performed on the front end as part of parse_request()',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_can_export',
                    'label' => 'Can export',
                    'name' => 'can_export',
                    'type' => 'true_false',
                    'instructions' => 'Can this post type be exported',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_delete_with_user',
                    'label' => 'Delete with user',
                    'name' => 'delete_with_user',
                    'type' => 'select',
                    'instructions' => 'Whether to delete posts of this type when deleting a user. If true, posts of this type belonging to the user will be moved to trash when then user is deleted.<br /><br />If false, posts of this type belonging to the user will not be trashed or deleted. If not set (the default), posts are trashed if the post type supports author. Otherwise posts are not trashed or deleted',
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
                        'null' => 'Null (default)',
                        'false' => 'False',
                        'true' => 'True',
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
                    'key' => 'field_acfe_dpt_tab_menu',
                    'label' => 'Menu',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_menu_position',
                    'label' => 'Menu position',
                    'name' => 'menu_position',
                    'type' => 'number',
                    'instructions' => 'The position in the menu order the post type should appear. show_in_menu must be true',
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
                    'min' => 0,
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_menu_icon',
                    'label' => 'Menu icon',
                    'name' => 'menu_icon',
                    'type' => 'text',
                    'instructions' => 'The url to the icon to be used for this menu or the name of the icon from the iconfont (<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a>)',
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
                    'default_value' => 'dashicons-admin-post',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_show_ui',
                    'label' => 'Show UI',
                    'name' => 'show_ui',
                    'type' => 'true_false',
                    'instructions' => 'Whether to generate a default UI for managing this post type in the admin',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_show_in_menu',
                    'label' => 'Show in menu',
                    'name' => 'show_in_menu',
                    'type' => 'true_false',
                    'instructions' => 'Where to show the post type in the admin menu. show_ui must be true',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_show_in_menu_text',
                    'label' => 'Show in menu (text)',
                    'name' => 'show_in_menu_text',
                    'type' => 'text',
                    'instructions' => 'If an existing top level page such as \'tools.php\' or \'edit.php?post_type=page\', the post type will be placed as a sub menu of that page',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'field_acfe_dpt_show_in_menu',
                                'operator'  => '==',
                                'value'     => '1',
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
                    'key' => 'field_acfe_dpt_show_in_nav_menus',
                    'label' => 'Show in nav menus',
                    'name' => 'show_in_nav_menus',
                    'type' => 'true_false',
                    'instructions' => 'Whether post_type is available for selection in navigation menus',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_show_in_admin_bar',
                    'label' => 'Show in admin bar',
                    'name' => 'show_in_admin_bar',
                    'type' => 'true_false',
                    'instructions' => 'Where to show the post type in the admin menu. show_ui must be true',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_tab_archive',
                    'label' => 'Archive',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_archive_template',
                    'label' => 'Template',
                    'name' => 'acfe_dpt_archive_template',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Which template file to load for the archive query. More informations on <a href="https://developer.wordpress.org/themes/basics/template-hierarchy/">Template hierarchy</a>',
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
                    'placeholder' => 'my-template.php',
                    'prepend' => trailingslashit(acfe_get_setting('theme_folder')),
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_has_archive',
                    'label' => 'Has archive',
                    'name' => 'has_archive',
                    'type' => 'true_false',
                    'instructions' => 'Enables post type archives.',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_has_archive_slug',
                    'label' => 'Slug',
                    'name' => 'has_archive_slug',
                    'type' => 'text',
                    'instructions' => 'Will use post type name as archive slug by default.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dpt_has_archive',
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
                    'placeholder' => 'Default',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_archive_posts_per_page',
                    'label' => 'Posts per page',
                    'name' => 'acfe_dpt_archive_posts_per_page',
                    'type' => 'number',
                    'instructions' => 'ACF Extended: Number of posts to display in the archive page',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => 10,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => -1,
                    'max' => '',
                    'step' => '',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dpt_has_archive',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_dpt_archive_orderby',
                    'label' => 'Order by',
                    'name' => 'acfe_dpt_archive_orderby',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Sort retrieved posts by parameter in the archive page. Defaults to \'date (post_date)\'.',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => array(
                        '5c9479dec93c4' => array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'date',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dpt_has_archive',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_dpt_archive_order',
                    'label' => 'Order',
                    'name' => 'acfe_dpt_archive_order',
                    'type' => 'select',
                    'instructions' => 'ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the archive page. Defaults to \'DESC\'.',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'choices' => array(
                        'ASC' => 'ASC',
                        'DESC' => 'DESC',
                    ),
                    'default_value' => array(
                        0 => 'DESC',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dpt_has_archive',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_dpt_tab_single',
                    'label' => 'Single',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_single_template',
                    'label' => 'Template',
                    'name' => 'acfe_dpt_single_template',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Which template file to load for the single query. More informations on <a href="https://developer.wordpress.org/themes/basics/template-hierarchy/">Template hierarchy</a>',
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
                    'placeholder' => 'my-template.php',
                    'prepend' => trailingslashit(acfe_get_setting('theme_folder')),
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_rewrite',
                    'label' => 'Rewrite',
                    'name' => 'rewrite',
                    'type' => 'true_false',
                    'instructions' => 'Triggers the handling of rewrites for this post type. To prevent rewrites, set to false.',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_rewrite_args_select',
                    'label' => 'Rewrite Arguments',
                    'name' => 'rewrite_args_select',
                    'type' => 'true_false',
                    'instructions' => 'Use additional rewrite arguments',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dpt_rewrite',
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
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_rewrite_args',
                    'label' => 'Rewrite Arguments',
                    'name' => 'rewrite_args',
                    'type' => 'group',
                    'instructions' => 'Additional arguments',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dpt_rewrite',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_dpt_rewrite_args_select',
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
                    'layout' => 'row',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_dpt_rewrite_slug',
                            'label' => 'Slug',
                            'name' => 'acfe_dpt_rewrite_slug',
                            'type' => 'text',
                            'instructions' => 'Customize the permalink structure slug. Defaults to the post type name value. Should be translatable.',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dpt_rewrite_args_select',
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
                            'placeholder' => 'Default',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_rewrite_with_front',
                            'label' => 'With front',
                            'name' => 'acfe_dpt_rewrite_with_front',
                            'type' => 'true_false',
                            'instructions' => 'Should the permalink structure be prepended with the front base. (example: if your permalink structure is /blog/, then your links will be: false->/news/, true->/blog/news/). Defaults to true.',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dpt_rewrite_args_select',
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
                            'message' => '',
                            'default_value' => 1,
                            'ui' => 1,
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_rewrite_feeds',
                            'label' => 'Feeds',
                            'name' => 'feeds',
                            'type' => 'true_false',
                            'instructions' => 'Should a feed permalink structure be built for this post type. Defaults to has_archive value.',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dpt_rewrite_args_select',
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
                            'message' => '',
                            'default_value' => 1,
                            'ui' => 1,
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_rewrite_pages',
                            'label' => 'Pages',
                            'name' => 'pages',
                            'type' => 'true_false',
                            'instructions' => 'Should the permalink structure provide for pagination. Defaults to true.',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dpt_rewrite_args_select',
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
                            'message' => '',
                            'default_value' => 1,
                            'ui' => 1,
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_dpt_tab_admin',
                    'label' => 'Admin',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_admin_archive',
                    'label' => 'Archive Page',
                    'name' => 'acfe_dpt_admin_archive',
                    'type' => 'true_false',
                    'instructions' => 'ACF Extended: Add an "Archive" Options Page as submenu of the post type.',
                    'required' => 0,
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_admin_posts_per_page',
                    'label' => 'Posts per page',
                    'name' => 'acfe_dpt_admin_posts_per_page',
                    'type' => 'number',
                    'instructions' => 'ACF Extended: Number of posts to display on the admin list screen.',
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
                    'default_value' => 10,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => -1,
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_admin_orderby',
                    'label' => 'Order by',
                    'name' => 'acfe_dpt_admin_orderby',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Sort retrieved posts by parameter in the admin list screen. Defaults to \'date (post_date)\'.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => array(
                        '5c9479dec93c4' => array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'date',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_admin_order',
                    'label' => 'Order',
                    'name' => 'acfe_dpt_admin_order',
                    'type' => 'select',
                    'instructions' => 'ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the admin list screen. Defaults to \'DESC\'.',
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
                        'ASC' => 'ASC',
                        'DESC' => 'DESC',
                    ),
                    'default_value' => array(
                        0 => 'DESC',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_tab_labels',
                    'label' => 'Labels',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_labels',
                    'label' => 'Labels',
                    'name' => 'labels',
                    'type' => 'group',
                    'instructions' => 'An array of labels for this post type. By default, post labels are used for non-hierarchical post types and page labels for hierarchical ones.<br /><br />
Default: if empty, \'name\' is set to value of \'label\', and \'singular_name\' is set to value of \'name\'.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'row',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_dpt_singular_name',
                            'label' => 'Singular name',
                            'name' => 'singular_name',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_add_new',
                            'label' => 'Add new',
                            'name' => 'add_new',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_add_new_item',
                            'label' => 'Add new item',
                            'name' => 'add_new_item',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_edit_item',
                            'label' => 'Edit item',
                            'name' => 'edit_item',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_new_item',
                            'label' => 'New item',
                            'name' => 'new_item',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_view_item',
                            'label' => 'View item',
                            'name' => 'view_item',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_view_items',
                            'label' => 'View items',
                            'name' => 'view_items',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_search_items',
                            'label' => 'Search items',
                            'name' => 'search_items',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_not_found',
                            'label' => 'Not found',
                            'name' => 'not_found',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_not_found_in_trash',
                            'label' => 'Not found in trash',
                            'name' => 'not_found_in_trash',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_parent_item_colon',
                            'label' => 'Parent item colon',
                            'name' => 'parent_item_colon',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_all_items',
                            'label' => 'All items',
                            'name' => 'all_items',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_archives',
                            'label' => 'Archives',
                            'name' => 'archives',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_attributes',
                            'label' => 'Attributes',
                            'name' => 'attributes',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_insert_into_item',
                            'label' => 'Insert into item',
                            'name' => 'insert_into_item',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_uploaded_to_this_item',
                            'label' => 'Uploaded to this item',
                            'name' => 'uploaded_to_this_item',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_featured_image',
                            'label' => 'Featured image',
                            'name' => 'featured_image',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_set_featured_image',
                            'label' => 'Set featured image',
                            'name' => 'set_featured_image',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_remove_featured_image',
                            'label' => 'Remove featured image',
                            'name' => 'remove_featured_image',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_use_featured_image',
                            'label' => 'Use featured image',
                            'name' => 'use_featured_image',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_menu_name',
                            'label' => 'Menu name',
                            'name' => 'menu_name',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_filter_items_list',
                            'label' => 'Filter items list',
                            'name' => 'filter_items_list',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_items_list_navigation',
                            'label' => 'Items list navigation',
                            'name' => 'items_list_navigation',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_items_list',
                            'label' => 'Items list',
                            'name' => 'items_list',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_name_admin_bar',
                            'label' => 'Name admin bar',
                            'name' => 'name_admin_bar',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_item_published',
                            'label' => 'Item published',
                            'name' => 'item_published',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_item_published_privately',
                            'label' => 'Item published privately',
                            'name' => 'item_published_privately',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_item_reverted_to_draft',
                            'label' => 'Item reverted to draft',
                            'name' => 'item_reverted_to_draft',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_item_scheduled',
                            'label' => 'Item scheduled',
                            'name' => 'item_scheduled',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dpt_item_updated',
                            'label' => 'Item updated',
                            'name' => 'item_updated',
                            'type' => 'text',
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
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_dpt_tab_capability',
                    'label' => 'Capability',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_capability_type',
                    'label' => 'Capability type',
                    'name' => 'capability_type',
                    'type' => 'textarea',
                    'instructions' => 'The string to use to build the read, edit, and delete capabilities.<br />
May be passed as an array to allow for alternative plurals when using this argument as a base to construct the capabilities, like this:<br /><br />

story<br />
stories',
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
                    'default_value' => 'post',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_capabilities',
                    'label' => 'Capabilities',
                    'name' => 'capabilities',
                    'type' => 'textarea',
                    'instructions' => 'An array of the capabilities for this post type. Specify capabilities like this:<br /><br />
publish_posts : publish_posts<br />
edit_post  : edit_post<br />
edit_posts  : edit_posts<br />
read_post : read_post<br />
delete_post : delete_post<br />
etc...',
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
                    'key' => 'field_acfe_dpt_map_meta_cap',
                    'label' => 'Map meta cap',
                    'name' => 'map_meta_cap',
                    'type' => 'select',
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
                    'choices' => array(
                        'null' => 'Null (default)',
                        'false' => 'False',
                        'true' => 'True',
                    ),
                    'default_value' => array(
                        0 => 'null',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_tab_rest',
                    'label' => 'REST',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dpt_show_in_rest',
                    'label' => 'Show in rest',
                    'name' => 'show_in_rest',
                    'type' => 'true_false',
                    'instructions' => 'Whether to expose this post type in the REST API. Set this to true for the post type to be available in the block editor',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dpt_rest_base',
                    'label' => 'Rest base',
                    'name' => 'rest_base',
                    'type' => 'text',
                    'instructions' => 'The base slug that this post type will use when accessed using the REST API',
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
                    'key' => 'field_acfe_dpt_rest_controller_class',
                    'label' => 'Rest controller class',
                    'name' => 'rest_controller_class',
                    'type' => 'text',
                    'instructions' => 'An optional custom controller to use instead of WP_REST_Posts_Controller. Must be a subclass of WP_REST_Controller',
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
                    'default_value' => 'WP_REST_Posts_Controller',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ));
        
    }
    
}

acf_new_instance('acfe_dynamic_post_types');

endif;