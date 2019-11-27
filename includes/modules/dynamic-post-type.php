<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_post_types'))
    return;

/**
 * Register Dynamic Post Type
 */
add_action('init', 'acfe_dpt_register');
function acfe_dpt_register(){
    
    register_post_type('acfe-dpt', array(
        'label'                 => 'Post Types',
        'description'           => 'Post Types',
        'labels'                => array(
            'name'          => 'Post Types',
            'singular_name' => 'Post Type',
            'menu_name'     => 'Post Types',
            'edit_item'     => 'Edit Post Type',
            'add_new_item'  => 'New Post Type',
        ),
        'supports'              => false,
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
            'publish_posts'         => acf_get_setting('capability'),
            'edit_posts'            => acf_get_setting('capability'),
            'edit_others_posts'     => acf_get_setting('capability'),
            'delete_posts'          => acf_get_setting('capability'),
            'delete_others_posts'   => acf_get_setting('capability'),
            'read_private_posts'    => acf_get_setting('capability'),
            'edit_post'             => acf_get_setting('capability'),
            'delete_post'           => acf_get_setting('capability'),
            'read_post'             => acf_get_setting('capability'),
        )
    ));

}

/**
 * WP Register Post Types
 */
add_action('init', 'acfe_dpt_registers');
function acfe_dpt_registers(){
    
    $dynamic_post_types = get_option('acfe_dynamic_post_types', array());
    if(empty($dynamic_post_types))
        return;
    
    foreach($dynamic_post_types as $name => $register_args){
        
        // Register: Execute
        register_post_type($name, $register_args);
        
    }

}

/**
 * ACF Exclude Dynamic Post Type from available post types
 */
add_filter('acf/get_post_types', 'acfe_dpt_exclude', 10, 2);
function acfe_dpt_exclude($post_types, $args){
    
    if(empty($post_types))
        return $post_types;
    
    foreach($post_types as $k => $post_type){
        
        if($post_type != 'acfe-dpt')
            continue;
        
        unset($post_types[$k]);
        
    }
    
    return $post_types;
    
}

add_action('post_submitbox_misc_actions', 'acfe_dpt_misc_actions');
function acfe_dpt_misc_actions($post){
    
    if($post->post_type !== 'acfe-dpt')
        return;
    
    $name = get_field('acfe_dpt_name', $post->ID);
    
    ?>
    <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
        <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dpt_export&action=php&keys=' . $name); ?>">PHP</a> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dpt_export&action=json&keys=' . $name); ?>">Json</a>
    </div>
    <?php
    
}

/**
 * Dynamic Post Type Save
 */
add_action('acf/save_post', 'acfe_dpt_filter_save', 20);
function acfe_dpt_filter_save($post_id){
    
    if(get_post_type($post_id) != 'acfe-dpt')
        return;
    
    $title = get_field('label', $post_id);
    $name = get_field('acfe_dpt_name', $post_id);
    
    // Update post
    wp_update_post(array(
        'ID'            => $post_id,
        'post_title'    => $title,
        'post_name'     => $name,
    ));
    
    // Register Args
    $label = get_field('label', $post_id);
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
    $labels = get_field('labels', $post_id);
    $labels_args = array();
    foreach($labels as $k => $l){
        if(empty($l))
            continue;
        
        $labels_args[$k] = $l;
    }
    
    // Menu
    $menu_position = (int) get_field('menu_position', $post_id);
    $menu_icon = get_field('menu_icon', $post_id);
    $show_ui = get_field('show_ui', $post_id);
    $show_in_menu = get_field('show_in_menu', $post_id);
    $show_in_menu_text = get_field('show_in_menu_text', $post_id);
    $show_in_nav_menus = get_field('show_in_nav_menus', $post_id);
    $show_in_admin_bar = get_field('show_in_admin_bar', $post_id);
    
    // Capability
    $capability_type = acf_decode_choices(get_field('capability_type', $post_id), true);
    $capabilities = acf_decode_choices(get_field('capabilities', $post_id), true);
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
    $register_args = array(
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
        'menu_position'         => $menu_position,
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
    
    // Has archive: override
    if($has_archive && $has_archive_slug)
        $register_args['has_archive'] = $has_archive_slug;
    
    // Rewrite: override
    if($rewrite && $rewrite_args_select){
        
        $register_args['rewrite'] = array(
            'slug'          => $rewrite_args['acfe_dpt_rewrite_slug'],
            'with_front'    => $rewrite_args['acfe_dpt_rewrite_with_front'],
            'feeds'         => $rewrite_args['feeds'],
            'pages'         => $rewrite_args['pages'],
        );
        
    }
    
    // Show in menu (text)
    if($show_in_menu && !empty($show_in_menu_text))
        $register_args['show_in_menu'] = $show_in_menu_text;
    
    // Capability type
    $register_args['capability_type'] = $capability_type;
    if(is_array($capability_type) && count($capability_type) == 1)
        $register_args['capability_type'] = $capability_type[0];
    
    // Capabilities
    $register_args['capabilities'] = $capabilities;
    
    // Map meta cap
    $register_args['map_meta_cap'] = null;
    
    if($map_meta_cap === 'false')
        $register_args['map_meta_cap'] = false;
    
    elseif($map_meta_cap === 'true')
        $register_args['map_meta_cap'] = true;
        
    // Get ACFE option
    $option = get_option('acfe_dynamic_post_types', array());
    
    // Create ACFE option
    $option[$name] = $register_args;
    
    // Sort keys ASC
    ksort($option);
    
    // Update ACFE option
    update_option('acfe_dynamic_post_types', $option);
    
    // Flush permalinks
    flush_rewrite_rules();
    
}

/**
 * Dynamic Post Type Status Publish > Trash
 */
add_action('publish_to_trash', 'acfe_dpt_filter_status_trash');
function acfe_dpt_filter_status_trash($post){
    
    if(get_post_type($post->ID) != 'acfe-dpt')
        return;
    
    $post_id = $post->ID;
    $name = get_field('acfe_dpt_name', $post_id);
    
    // Get ACFE option
    $option = get_option('acfe_dynamic_post_types', array());
    
    // Check ACFE option
    if(isset($option[$name]))
        unset($option[$name]);
    
    // Update ACFE option
    update_option('acfe_dynamic_post_types', $option);
    
    // Flush permalinks
    flush_rewrite_rules();
    
}

/**
 * Dynamic Post Type Status Trash > Publish
 */
add_action('trash_to_publish', 'acfe_dpt_filter_status_publish');
function acfe_dpt_filter_status_publish($post){
    
    if(get_post_type($post->ID) != 'acfe-dpt')
        return;
    
    acfe_dpt_filter_save($post->ID);
    
}

/**
 * Dynamic Post Type Admin: List
 */
add_action('pre_get_posts', 'acfe_dpt_admin_pre_get_posts');
function acfe_dpt_admin_pre_get_posts($query){
    
    if(!is_admin() || !$query->is_main_query())
        return;
    
    global $pagenow;
    if($pagenow != 'edit.php')
        return;
    
    $post_type = $query->get('post_type');
    if($post_type != 'acfe-dpt')
        return;
    
    $query->set('orderby', 'name');
    $query->set('order', 'ASC');
    
}

/**
 * Dynamic Post Type Admin: Posts Per Page
 */
add_filter('edit_posts_per_page', 'acfe_dpt_admin_ppp', 10, 2);
function acfe_dpt_admin_ppp($ppp, $post_type){
    
    if($post_type != 'acfe-dpt')
        return $ppp;
    
    global $pagenow;
    if($pagenow != 'edit.php')
        return $ppp;
    
    return 999;
    
}

/**
 * Filter Admin: List
 */
add_action('pre_get_posts', 'acfe_dpt_filter_admin_list');
function acfe_dpt_filter_admin_list($query){
    
    if(!is_admin() || !$query->is_main_query() || !is_post_type_archive())
        return;

    $post_type = $query->get('post_type');
    $post_type_obj = get_post_type_object($post_type);
    
    $acfe_admin_orderby = (isset($post_type_obj->acfe_admin_orderby) && !empty($post_type_obj->acfe_admin_orderby));
    $acfe_admin_order = (isset($post_type_obj->acfe_admin_order) && !empty($post_type_obj->acfe_admin_order));
    
    if($acfe_admin_orderby && (!isset($_REQUEST['orderby']) || empty($_REQUEST['orderby'])))
        $query->set('orderby', $post_type_obj->acfe_admin_orderby);
    
    if($acfe_admin_order && (!isset($_REQUEST['order']) || empty($_REQUEST['order'])))
        $query->set('order', $post_type_obj->acfe_admin_order);
    
    
}

/**
 * Filter Admin: Posts Per Page
 */
add_filter('edit_posts_per_page', 'acfe_dpt_filter_admin_ppp', 10, 2);
function acfe_dpt_filter_admin_ppp($ppp, $post_type){
    
    global $pagenow;
    if($pagenow != 'edit.php')
        return $ppp;
    
    $post_type_obj = get_post_type_object($post_type);
    if(!isset($post_type_obj->acfe_admin_ppp) || empty($post_type_obj->acfe_admin_ppp))
        return $ppp;
    
    // Check if user has a screen option
    if(!empty(get_user_option('edit_' . $post_type . '_per_page')))
        return $ppp;
    
    return $post_type_obj->acfe_admin_ppp;
    
}

/**
 * Filter Front: List + Posts Per Page
 */
add_action('pre_get_posts', 'acfe_dpt_filter_front_list');
function acfe_dpt_filter_front_list($query){
    
    if(is_admin() || !$query->is_main_query() || !is_post_type_archive())
        return;

    $post_type = $query->get('post_type');
    $post_type_obj = get_post_type_object($post_type);
    
    $acfe_archive_ppp = (isset($post_type_obj->acfe_archive_ppp) && !empty($post_type_obj->acfe_archive_ppp));
    $acfe_archive_orderby = (isset($post_type_obj->acfe_archive_orderby) && !empty($post_type_obj->acfe_archive_orderby));
    $acfe_archive_order = (isset($post_type_obj->acfe_archive_order) && !empty($post_type_obj->acfe_archive_order));
    
    if($acfe_archive_ppp)
        $query->set('posts_per_page', $post_type_obj->acfe_archive_ppp);
    
    if($acfe_archive_orderby)
        $query->set('orderby', $post_type_obj->acfe_archive_orderby);
    
    if($acfe_archive_order)
        $query->set('order', $post_type_obj->acfe_archive_order);
    
}

/**
 * Filter Front: Template
 */
add_filter('template_include', 'acfe_dpt_filter_template', 999);
function acfe_dpt_filter_template($template){
    
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

/**
 * Admin List Columns
 */
add_filter('manage_edit-acfe-dpt_columns', 'acfe_dpt_admin_columns');
function acfe_dpt_admin_columns($columns){
    
    if(isset($columns['date']))
        unset($columns['date']);
    
    $columns['acfe-name'] = __('Name');
    $columns['acfe-taxonomies'] = __('Taxonomies');
    $columns['acfe-posts'] = __('Posts');
    
    return $columns;
    
}

/**
 * Admin List Columns HTML
 */
add_action('manage_acfe-dpt_posts_custom_column', 'acfe_dpt_admin_columns_html', 10, 2);
function acfe_dpt_admin_columns_html($column, $post_id){
    
    // Name
    if($column === 'acfe-name'){
        
        echo '<code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">' . get_field('acfe_dpt_name', $post_id) . '</code>';
        
    }
    
    // Taxonomies
    elseif($column === 'acfe-taxonomies'){
        
        $taxonomies = acf_get_array(get_field('taxonomies', $post_id));
        
        if(empty($taxonomies)){
            
            echo '—';
            return;
            
        }
        
        $taxonomies_names = array();
        foreach($taxonomies as $taxonomy_slug){
            
            $taxonomy_obj = get_taxonomy($taxonomy_slug);
            if(empty($taxonomy_obj))
                continue;
            
            $taxonomies_names[] = $taxonomy_obj->label;
            
        }
        
        if(empty($taxonomies_names)){
            
            echo '—';
            return;
            
        }
        
        echo implode(', ', $taxonomies_names);
        
    }
    
    // Posts
    elseif($column === 'acfe-posts'){
        
        // Name
        $name = get_field('acfe_dpt_name', $post_id);
        
        // Count
        $count = wp_count_posts($name);
        if(empty($count)){
            
            echo '—';
            return;
            
        }
        
        $count_publish = $count->publish;
        
        echo '<a href="' . admin_url('edit.php?post_type=' . $name) . '">' . $count_publish . '</a>';
        
    }
    
}

/**
 * Admin List Row Actions
 */
add_filter('post_row_actions','acfe_dpt_admin_row', 10, 2);
function acfe_dpt_admin_row($actions, $post){

    if($post->post_type !== 'acfe-dpt' || $post->post_status !== 'publish')
        return $actions;
    
    $post_id = $post->ID;
    $name = get_field('acfe_dpt_name', $post_id);
    
    $actions['acfe_dpt_export_php'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dpt_export&action=php&keys=' . $name) . '">' . __('PHP') . '</a>';
    $actions['acfe_dpt_export_json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dpt_export&action=json&keys=' . $name) . '">' . __('Json') . '</a>';
    
    return $actions;
    
}

/**
 * Admin Add Config Button
 */
add_action('admin_footer-edit.php', 'acfe_dpt_admin_footer');
function acfe_dpt_admin_footer(){
    
    if(!current_user_can(acf_get_setting('capability')))
        return;
    
    // Get post type
    global $typenow;
    
    // Check post type
    $post_type = $typenow;
    if(empty($post_type))
        return;
    
    // Post type object
    $post_type_obj = get_post_type_object($post_type);
    if(!isset($post_type_obj->acfe_admin_ppp))
        return;
    
    // Get Dynamic Post Type Post
    $acfe_dpt_post_type = get_page_by_path($post_type, 'OBJECT', 'acfe-dpt');
    
    if(empty($acfe_dpt_post_type))
        return;
    
    ?>
    <script type="text/html" id="tmpl-acfe-dpt-title-config">
        <a href="<?php echo admin_url('post.php?post=' . $acfe_dpt_post_type->ID . '&action=edit'); ?>" class="page-title-action acfe-dpt-admin-config"><span class="dashicons dashicons-admin-generic"></span></a>
    </script>
    
    <script type="text/javascript">
    (function($){
        
        // Add button
        $('.wrap .page-title-action').before($('#tmpl-acfe-dpt-title-config').html());
        
    })(jQuery);
    </script>
    <?php
    
}

/**
 * Admin Disable Name
 */
add_filter('acf/prepare_field/name=acfe_dpt_name', 'acfe_dpt_admin_disable_name');
function acfe_dpt_admin_disable_name($field){
    
    global $pagenow;
    if($pagenow != 'post.php')
        return $field;
    
    $field['disabled'] = true;
    
    return $field;
    
}

/**
 * Admin Validate Name
 */
add_filter('acf/validate_value/name=acfe_dpt_name', 'acfe_dpt_admin_validate_name', 10, 4);
function acfe_dpt_admin_validate_name($valid, $value, $field, $input){
    
	if(!$valid)
        return $valid;
    
    $excludes = array(
        
        // Reserved WP Post types: https://codex.wordpress.org/Function_Reference/register_post_type#Reserved_Post_Types
        'post', 
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
        
        // ACF
        'acf-field-group',
        'acf-field',
        
        // ACF Extended
        'acfe-dbt',
        'acfe-dt',
        'acfe-dop',
        'acfe-dpt',
        'acfe-form',
        
    );
    
    if(in_array($value, $excludes))
        return __('This post type name is reserved');
    
    // Editing Current Dynamic Post Type
    $current_post_id = $_POST['_acf_post_id'];
    $current_post_type = false;
    
    if(!empty($current_post_id))
        $current_post_type = get_field('acfe_dpt_name', $current_post_id);
    
    if($value === $current_post_type)
        return $valid;
    
    // Listing WP Post Types
    global $wp_post_types;
    if(!empty($wp_post_types)){
        foreach($wp_post_types as $post_type){
            if($value != $post_type->name)
                continue;
            
            $valid = __('This post type name already exists');
        }
    }
	
	return $valid;
    
}

/**
 * Add Local Field Group
 */
acf_add_local_field_group(array(
    'key' => 'group_acfe_dynamic_post_type',
    'title' => __('Dynamic Post Type', 'acfe'),
    
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'acfe-dpt',
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
            ),
            'acfe_permissions' => '',
            'placement' => 'top',
            'endpoint' => 0,
        ),
        array(
            'key' => 'field_acfe_dpt_label',
            'label' => 'Label',
            'name' => 'label',
            'type' => 'text',
            'instructions' => 'General name for the post type, usually plural. Default is Posts/Pages',
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
            'user_roles' => array(
                0 => 'all',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
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
            'default_value' => 20,
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
            'instructions' => 'If an existing top level page such as \'tools.php\' or \'edit.php?post_type=page\', the post type will be placed as a sub menu of that',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5c9f5dd58d5ee',
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

edit_post<br />
read_post<br />
delete_post<br />
edit_posts<br />
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
            'prepend' => str_replace(home_url(), '', ACFE_THEME_URL) . '/',
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
            'key' => 'field_acfe_dpt_archive_orderby',
            'label' => 'Order by',
            'name' => 'acfe_dpt_archive_orderby',
            'type' => 'text',
            'instructions' => 'ACF Extended: Sort retrieved posts by parameter in the archive page. Defaults to \'date (post_date)\'.',
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
            'key' => 'field_acfe_dpt_archive_order',
            'label' => 'Order',
            'name' => 'acfe_dpt_archive_order',
            'type' => 'select',
            'instructions' => 'ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the archive page. Defaults to \'DESC\'.',
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
            'prepend' => str_replace(home_url(), '', ACFE_THEME_URL) . '/',
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
            'instructions' => 'Add archive page to the post type administration.',
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
            'instructions' => 'ACF Extended: Number of posts to display on the admin list screen',
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
            'instructions' => 'Whether to expose this post type in the REST API',
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