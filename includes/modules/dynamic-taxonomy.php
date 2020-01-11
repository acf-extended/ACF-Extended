<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_taxonomies'))
    return;

/**
 * Register Dynamic Taxonomy
 */
add_action('init', 'acfe_dt_register');
function acfe_dt_register(){
    
    register_post_type('acfe-dt', array(
        'label'                 => __('Taxonomies', 'acfe'),
        'description'           => __('Taxonomies', 'acfe'),
        'labels'                => array(
            'name'          => __('Taxonomies', 'acfe'),
            'singular_name' => __('Taxonomy', 'acfe'),
            'menu_name'     => __('Taxonomies', 'acfe'),
            'edit_item'     => __('Edit Taxonomy', 'acfe'),
            'add_new_item'  => __('New Taxonomy', 'acfe'),
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
 * WP Register Taxonomies
 */
add_action('init', 'acfe_dt_registers');
function acfe_dt_registers(){
    
    $dynamic_taxonomies = get_option('acfe_dynamic_taxonomies', array());
    if(empty($dynamic_taxonomies))
        return;
    
    foreach($dynamic_taxonomies as $name => $register_args){
        
        // Extract 'post_types' from 'register_args'
        $post_types = acf_extract_var($register_args, 'post_types', array());
        
        // Register: Execute
        register_taxonomy($name, $post_types, $register_args);
        
        // Filter Admin: Posts Per Page
        add_filter('edit_' . $name . '_per_page', 'acfe_dt_filter_admin_ppp');
        
    }

}

/**
 * ACF Exclude Dynamic Taxonomy from available post types
 */
add_filter('acf/get_post_types', 'acfe_dt_exclude', 10, 2);
function acfe_dt_exclude($post_types, $args){
    
    if(empty($post_types))
        return $post_types;
    
    foreach($post_types as $k => $post_type){
        
        if($post_type != 'acfe-dt')
            continue;
        
        unset($post_types[$k]);
        
    }
    
    return $post_types;
    
}

add_action('post_submitbox_misc_actions', 'acfe_dt_misc_actions');
function acfe_dt_misc_actions($post){
    
    if($post->post_type !== 'acfe-dt')
        return;
    
    $name = get_field('acfe_dt_name', $post->ID);
    
    ?>
    <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
        <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> <?php echo __('Export:', 'acfe') ?> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dt_export&action=php&keys=' . $name); ?>">PHP</a> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dt_export&action=json&keys=' . $name); ?>">Json</a>
    </div>
    <?php
    
}

/**
 * Dynamic Taxonomy Save
 */
add_action('acf/save_post', 'acfe_dt_filter_save', 20);
function acfe_dt_filter_save($post_id){
    
    if(get_post_type($post_id) != 'acfe-dt')
        return;
    
    $title = get_field('label', $post_id);
    $name = get_field('acfe_dt_name', $post_id);
    
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
    $post_types = get_field('post_types', $post_id);
    $public = get_field('public', $post_id);
    $publicly_queryable = get_field('publicly_queryable', $post_id);
    $update_count_callback = get_field('update_count_callback', $post_id);
    $sort = get_field('sort', $post_id);
    
    // Labels
    $labels = get_field('labels', $post_id);
    $labels_args = array();
    foreach($labels as $k => $l){
        if(empty($l))
            continue;
        
        $labels_args[$k] = $l;
    }
    
    // Menu
    $show_ui = get_field('show_ui', $post_id);
    $show_in_menu = get_field('show_in_menu', $post_id);
    $show_in_nav_menus = get_field('show_in_nav_menus', $post_id);
    $show_tagcloud = get_field('show_tagcloud', $post_id);
    $meta_box_cb = get_field('meta_box_cb', $post_id);
    $meta_box_cb_custom = get_field('meta_box_cb_custom', $post_id);
    $show_in_quick_edit = get_field('show_in_quick_edit', $post_id);
    $show_admin_column = get_field('show_admin_column', $post_id);
    
    // Capability
    $capabilities = acf_decode_choices(get_field('capabilities', $post_id), true);
    
    // Single
    $single_template = get_field('acfe_dt_single_template', $post_id);
    $single_posts_per_page = (int) get_field('acfe_dt_single_posts_per_page', $post_id);
    $single_orderby = get_field('acfe_dt_single_orderby', $post_id);
    $single_order = get_field('acfe_dt_single_order', $post_id);
    $rewrite = get_field('rewrite', $post_id);
    $rewrite_args_select = get_field('rewrite_args_select', $post_id);
    $rewrite_args = get_field('rewrite_args', $post_id);
    
    // Admin
    $admin_posts_per_page = (int) get_field('acfe_dt_admin_terms_per_page', $post_id);
    $admin_orderby = get_field('acfe_dt_admin_orderby', $post_id);
    $admin_order = get_field('acfe_dt_admin_order', $post_id);
    
    // REST
    $show_in_rest = get_field('show_in_rest', $post_id);
    $rest_base = get_field('rest_base', $post_id);
    $rest_controller_class = get_field('rest_controller_class', $post_id);
    
    // Register: Args
    $register_args = array(
        'label'                 => $label,
        'description'           => $description,
        'hierarchical'          => $hierarchical,
        'post_types'            => $post_types,
        'public'                => $public,
        'publicly_queryable'    => $publicly_queryable,
        'update_count_callback' => $update_count_callback,
        'sort'                  => $sort,
        
        // Labels
        'labels'                => $labels_args,
        
        // Menu
        'show_ui'               => $show_ui,
        'show_in_menu'          => $show_in_menu,
        'show_in_nav_menus'     => $show_in_nav_menus,
        'show_tagcloud'         => $show_tagcloud,
        'show_in_quick_edit'    => $show_in_quick_edit,
        'show_admin_column'     => $show_admin_column,
        
        // Single
        'rewrite'               => $rewrite,
        
        // REST
        'show_in_rest'          => $show_in_rest,
        'rest_base'             => $rest_base,
        'rest_controller_class' => $rest_controller_class,
        
        // ACFE: Single
        'acfe_single_template'  => $single_template,
        'acfe_single_ppp'       => $single_posts_per_page,
        'acfe_single_orderby'   => $single_orderby,
        'acfe_single_order'     => $single_order,
        
        // ACFE: Admin
        'acfe_admin_ppp'        => $admin_posts_per_page,
        'acfe_admin_orderby'    => $admin_orderby,
        'acfe_admin_order'      => $admin_order,
    );
    
    // Rewrite: override
    if($rewrite && $rewrite_args_select){
        
        $register_args['rewrite'] = array(
            'slug'          => $rewrite_args['acfe_dt_rewrite_slug'],
            'with_front'    => $rewrite_args['acfe_dt_rewrite_with_front'],
            'hierarchical'  => $rewrite_args['hierarchical']
        );
        
    }
    
    // Capabilities
    $register_args['capabilities'] = $capabilities;
    
    // Metabox CB
    $register_args['meta_box_cb'] = null;
    
    if($meta_box_cb === 'false')
        $register_args['meta_box_cb'] = false;
    
    elseif($meta_box_cb === 'custom')
        $register_args['meta_box_cb'] = $meta_box_cb_custom;
    
    // Get ACFE option
    $option = get_option('acfe_dynamic_taxonomies', array());
    
    // Create ACFE option
    $option[$name] = $register_args;
    
    // Sort keys ASC
    ksort($option);
    
    // Update ACFE option
    update_option('acfe_dynamic_taxonomies', $option);
    
    // Flush permalinks
    flush_rewrite_rules();
    
}

/**
 * Dynamic Taxonomy Status Publish > Trash
 */
add_action('publish_to_trash', 'acfe_dt_filter_status_trash');
function acfe_dt_filter_status_trash($post){
    
    if(get_post_type($post->ID) != 'acfe-dt')
        return;
    
    $post_id = $post->ID;
    $name = get_field('acfe_dt_name', $post_id);
    
    // Get ACFE option
    $option = get_option('acfe_dynamic_taxonomies', array());
    
    // Check ACFE option
    if(isset($option[$name]))
        unset($option[$name]);
    
    // Update ACFE option
    update_option('acfe_dynamic_taxonomies', $option);
    
    // Flush permalinks
    flush_rewrite_rules();
    
}

/**
 * Dynamic Taxonomy Status Trash > Publish
 */
add_action('trash_to_publish', 'acfe_dt_filter_status_publish');
function acfe_dt_filter_status_publish($post){
    
    if(get_post_type($post->ID) != 'acfe-dt')
        return;
    
    acfe_dt_filter_save($post->ID);
    
}

/**
 * Dynamic Taxonomy Admin: List
 */
add_action('pre_get_posts', 'acfe_dt_admin_pre_get_posts');
function acfe_dt_admin_pre_get_posts($query){
    
    if(!is_admin() || !$query->is_main_query())
        return;
    
    global $pagenow;
    if($pagenow != 'edit.php')
        return;
    
    $post_type = $query->get('post_type');
    if($post_type != 'acfe-dt')
        return;
    
    $query->set('orderby', 'name');
    $query->set('order', 'ASC');
    
}

/**
 * Dynamic Taxonomy Admin: Posts Per Page
 */
add_filter('edit_posts_per_page', 'acfe_dt_admin_ppp', 10, 2);
function acfe_dt_admin_ppp($ppp, $post_type){
    
    if($post_type != 'acfe-dt')
        return $ppp;
    
    global $pagenow;
    if($pagenow != 'edit.php')
        return $ppp;
    
    return 999;
    
}

/**
 * Filter Admin: List
 */
add_filter('get_terms_args', 'acfe_dt_filter_admin_list', 10, 2);
function acfe_dt_filter_admin_list($args, $taxonomies){
    
    if(!is_admin())
        return $args;
    
    global $pagenow;
    if($pagenow != 'edit-tags.php')
        return $args;
    
    if(empty($taxonomies))
        return $args;
    
    $taxonomy = array_shift($taxonomies);
    $taxonomy_obj = get_taxonomy($taxonomy);
    
    $acfe_admin_orderby = (isset($taxonomy_obj->acfe_admin_orderby) && !empty($taxonomy_obj->acfe_admin_orderby));
    $acfe_admin_order = (isset($taxonomy_obj->acfe_admin_order) && !empty($taxonomy_obj->acfe_admin_order));
    
    if($acfe_admin_orderby && (!isset($_REQUEST['orderby']) || empty($_REQUEST['orderby'])))
        $args['orderby'] = $taxonomy_obj->acfe_admin_orderby;
    
    if($acfe_admin_order && (!isset($_REQUEST['order']) || empty($_REQUEST['order'])))
        $args['order'] = $taxonomy_obj->acfe_admin_order;
    
    return $args;
    
}

/**
 * Filter Admin: Posts Per Page
 */
function acfe_dt_filter_admin_ppp($ppp){
    
    global $pagenow;
    if($pagenow != 'edit-tags.php')
        return $ppp;
    
    $taxonomy = $_GET['taxonomy'];
    if(empty($taxonomy))
        return $ppp;
    
    $taxonomy_obj = get_taxonomy($taxonomy);
    if(!isset($taxonomy_obj->acfe_admin_ppp) || empty($taxonomy_obj->acfe_admin_ppp))
        return $ppp;
    
    // Check if user has a screen option
    if(!empty(get_user_option('edit_' . $taxonomy . '_per_page')))
        return $ppp;
    
    return $taxonomy_obj->acfe_admin_ppp;
    
}

/**
 * Filter Front: List + Posts Per Page
 */
add_action('pre_get_posts', 'acfe_dt_filter_front_list');
function acfe_dt_filter_front_list($query){
    
    if(is_admin() || !$query->is_main_query() || !is_tax())
        return;

    $taxonomy = $query->get('taxonomy');
    $taxonomy_obj = get_taxonomy($taxonomy);
    
    $acfe_single_ppp = (isset($taxonomy_obj->acfe_single_ppp) && !empty($taxonomy_obj->acfe_single_ppp));
    $acfe_single_orderby = (isset($taxonomy_obj->acfe_single_orderby) && !empty($taxonomy_obj->acfe_single_orderby));
    $acfe_single_order = (isset($taxonomy_obj->acfe_single_order) && !empty($taxonomy_obj->acfe_single_order));
    
    if($acfe_single_ppp)
        $query->set('posts_per_page', $taxonomy_obj->acfe_single_ppp);
    
    if($acfe_single_orderby)
        $query->set('orderby', $taxonomy_obj->acfe_single_orderby);
    
    if($acfe_single_order)
        $query->set('order', $taxonomy_obj->acfe_single_order);
    
}

/**
 * Filter Front: Template
 */
add_filter('template_include', 'acfe_dt_filter_template', 999);
function acfe_dt_filter_template($template){
    
    if(!is_tax() && !is_category() && !is_tag())
        return $template;
    
    if(!isset(get_queried_object()->taxonomy))
        return $template;
    
    $taxonomy_obj = get_queried_object()->taxonomy;
    
    foreach(get_taxonomies(array('public' => true), 'objects') as $taxonomy){
        if($taxonomy_obj != $taxonomy->name || !isset($taxonomy->acfe_single_template))
            continue;
        
        if($locate = locate_template(array($taxonomy->acfe_single_template)))
            return $locate;
    }
    
    return $template;
    
}

/**
 * Admin List Columns
 */
add_filter('manage_edit-acfe-dt_columns', 'acfe_dt_admin_columns');
function acfe_dt_admin_columns($columns){
    
    if(isset($columns['date']))
        unset($columns['date']);
    
    $columns['acfe-name'] = __('Name', 'acfe');
    $columns['acfe-post-types'] = __('Post Types', 'acfe');
    $columns['acfe-terms'] = __('Terms', 'acfe');
    
    return $columns;
    
}

/**
 * Admin List Columns HTML
 */
add_action('manage_acfe-dt_posts_custom_column', 'acfe_dt_admin_columns_html', 10, 2);
function acfe_dt_admin_columns_html($column, $post_id){
    
    // Name
    if($column === 'acfe-name'){
        
        echo '<code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">' . get_field('acfe_dt_name', $post_id) . '</code>';
        
    }
    
    // Post Types
    elseif($column === 'acfe-post-types'){
        
        $post_types = get_field('post_types', $post_id);
        
        if(empty($post_types)){
            
            echo '—';
            return;
            
        }
        
        $post_types_names = array();
        foreach($post_types as $post_type_slug){
            
            $post_type_obj = get_post_type_object($post_type_slug);
            if(empty($post_type_obj))
                continue;
            
            $post_types_names[] = $post_type_obj->label;
            
        }
        
        if(empty($post_types_names)){
            
            echo '—';
            return;
            
        }
        
        echo implode(', ', $post_types_names);
        
    }
    
    // Terms
    elseif($column === 'acfe-terms'){
        
        // Name
        $name = get_field('acfe_dt_name', $post_id);
        
        // Count
        $count = wp_count_terms($name, array(
            'hide_empty' => false
        ));
        
        echo '<a href="' . admin_url('edit-tags.php?taxonomy=' . $name) . '">' . $count . '</a>';
        
    }
    
}

/**
 * Admin List Row Actions
 */
add_filter('post_row_actions','acfe_dt_admin_row', 10, 2);
function acfe_dt_admin_row($actions, $post){

    if($post->post_type !== 'acfe-dt' || $post->post_status !== 'publish')
        return $actions;
    
    $post_id = $post->ID;
    $name = get_field('acfe_dt_name', $post_id);
    
    $actions['acfe_dt_export_php'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dt_export&action=php&keys=' . $name) . '">' . __('PHP') . '</a>';
    $actions['acfe_dt_export_json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dt_export&action=json&keys=' . $name) . '">' . __('Json') . '</a>';
    
    return $actions;
    
}

/**
 * Admin Disable Name
 */
add_filter('acf/prepare_field/name=acfe_dt_name', 'acfe_dt_admin_disable_name');
function acfe_dt_admin_disable_name($field){
    
    global $pagenow;
    if($pagenow != 'post.php')
        return $field;
    
    $field['disabled'] = true;
    
    return $field;
    
}

/**
 * Admin Validate Name
 */
add_filter('acf/validate_value/name=acfe_dt_name', 'acfe_dt_admin_validate_name', 10, 4);
function acfe_dt_admin_validate_name($valid, $value, $field, $input){
    
	if(!$valid)
        return $valid;
    
    // Reserved taxonomies
    $excludes = array(
        
        // Reserved WP Taxonomies: https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
        'attachment',
        'attachment_id',
        'author',
        'author_name',
        'calendar',
        'cat',
        'category',
        'category__and',
        'category__in',
        'category__not_in',
        'category_name',
        'comments_per_page',
        'comments_popup',
        'customize_messenger_channel',
        'customized',
        'cpage',
        'day',
        'debug',
        'error',
        'exact',
        'feed',
        'fields',
        'hour',
        'link_category',
        'm',
        'minute',
        'monthnum',
        'more',
        'name',
        'nav_menu',
        'nonce',
        'nopaging',
        'offset',
        'order',
        'orderby',
        'p',
        'page',
        'page_id',
        'paged',
        'pagename',
        'pb',
        'perm',
        'post',
        'post__in',
        'post__not_in',
        'post_format',
        'post_mime_type',
        'post_status',
        'post_tag',
        'post_type',
        'posts',
        'posts_per_archive_page',
        'posts_per_page',
        'preview',
        'robots',
        's',
        'search',
        'second',
        'sentence',
        'showposts',
        'static',
        'subpost',
        'subpost_id',
        'tag',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_id',
        'tag_slug__and',
        'tag_slug__in',
        'taxonomy',
        'tb',
        'term',
        'theme',
        'type',
        'w',
        'withcomments',
        'withoutcomments',
        'year',
        
        // ACF Extended
        'acf-field-group-category',
        
    );
    
    if(in_array($value, $excludes))
        return __('This taxonomy name is reserved', 'acfe');
    
    // Editing Current Dynamic Taxonomy
    $current_post_id = $_POST['_acf_post_id'];
    $current_post_type = false;
    
    if(!empty($current_post_id))
        $current_post_type = get_field('acfe_dt_name', $current_post_id);
    
    if($value === $current_post_type)
        return $valid;
    
    // Listing WP Taxonomies
    global $wp_taxonomies;
    if(!empty($wp_taxonomies)){
        foreach($wp_taxonomies as $taxonomy){
            if($value != $taxonomy->name)
                continue;
            
            $valid = __('This taxonomy name already exists', 'acfe');
        }
    }
	
	return $valid;
    
}

/**
 * Admin Add Config Button
 */
add_action('admin_footer-edit-tags.php', 'acfe_dt_admin_footer', 99);
function acfe_dt_admin_footer(){
    
    if(!current_user_can(acf_get_setting('capability')))
        return;
    
    // Get taxonomy
    global $taxnow;
    
    // Check taxonomy
    $taxonomy = $taxnow;
    if(empty($taxonomy))
        return;
    
    // Taxonomy object
    $taxonomy_obj = get_taxonomy($taxonomy);
    if(!isset($taxonomy_obj->acfe_admin_ppp))
        return;
    
    // Get Dynamic Post Type Post
    $acfe_dt_post_type = get_page_by_path($taxonomy, 'OBJECT', 'acfe-dt');
    
    if(empty($acfe_dt_post_type))
        return;
    
    ?>
    <script type="text/html" id="tmpl-acfe-dt-title-config">
        &nbsp;<a href="<?php echo admin_url('post.php?post=' . $acfe_dt_post_type->ID . '&action=edit'); ?>" class="page-title-action acfe-dt-admin-config"><span class="dashicons dashicons-admin-generic"></span></a>
    </script>
    
    <script type="text/javascript">
    (function($){
        
        // Add button
        $('.wrap .wp-heading-inline').after($('#tmpl-acfe-dt-title-config').html());
        
    })(jQuery);
    </script>
    <?php
    
}

/**
 * Add Local Field Group
 */
acf_add_local_field_group(array(
    'key' => 'group_acfe_dynamic_taxonomy',
    'title' => __('Dynamic Taxonomy', 'acfe'),
    
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'acfe-dt',
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
            'key' => 'field_acfe_dt_tab_general',
            'label' => __('General', 'acfe'),
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
            'key' => 'field_acfe_dt_label',
            'label' => __('Label', 'acfe'),
            'name' => 'label',
            'type' => 'text',
            'instructions' => __('A plural descriptive name for the taxonomy marked for translation', 'acfe'),
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
            'maxlength' => '',
        ),
        array(
            'key' => 'field_acfe_dt_name',
            'label' => __('Name', 'acfe'),
            'name' => 'acfe_dt_name',
            'type' => 'acfe_slug',
            'instructions' => __('The name of the taxonomy. Name should only contain lowercase letters and the underscore character, and not be more than 32 characters long (database structure restriction)', 'acfe'),
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
            'maxlength' => 32,
        ),
        array(
            'key' => 'field_acfe_dt_description',
            'label' => __('Description', 'acfe'),
            'name' => 'description',
            'type' => 'text',
            'instructions' => __('Include a description of the taxonomy', 'acfe'),
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
            'key' => 'field_acfe_dt_hierarchical',
            'label' => __('Hierarchical', 'acfe'),
            'name' => 'hierarchical',
            'type' => 'true_false',
            'instructions' => __('Is this taxonomy hierarchical (have descendants) like categories or not hierarchical like tags', 'acfe'),
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
            'key' => 'field_acfe_dt_post_types',
            'label' => __('Post types', 'acfe'),
            'name' => 'post_types',
            'type' => 'acfe_post_types',
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
            'field_type' => 'checkbox',
            'return_format' => 'name',
        ),
        array(
            'key' => 'field_acfe_dt_public',
            'label' => __('Public', 'acfe'),
            'name' => 'public',
            'type' => 'true_false',
            'instructions' => __('Whether a taxonomy is intended for use publicly either via the admin interface or by front-end users', 'acfe'),
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
            'key' => 'field_acfe_dt_publicly_queryable',
            'label' => __('Publicly queryable', 'acfe'),
            'name' => 'publicly_queryable',
            'type' => 'true_false',
            'instructions' => __('Whether the taxonomy is publicly queryable', 'acfe'),
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
            'key' => 'field_acfe_dt_update_count_callback',
            'label' => __('Update count callback', 'acfe'),
            'name' => 'update_count_callback',
            'type' => 'text',
            'instructions' => __('A function name that will be called when the count of an associated $object_type, such as post, is updated', 'acfe'),
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
            'key' => 'field_acfe_dt_meta_box_cb',
            'label' => __('Meta box callback', 'acfe'),
            'name' => 'meta_box_cb',
            'type' => 'select',
            'instructions' => __('Provide a callback function name for the meta box display.<br /><br/>Defaults to the categories meta box for hierarchical taxonomies and the tags meta box for non-hierarchical taxonomies. No meta box is shown if set to false.', 'acfe'),
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
                'custom' => 'Custom',
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
            'key' => 'field_acfe_dt_meta_box_cb_custom',
            'label' => __('Meta box callback', 'acfe'),
            'name' => 'meta_box_cb_custom',
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
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_acfe_dt_meta_box_cb',
                        'operator' => '==',
                        'value' => 'custom',
                    )
                )
            )
        ),
        array(
            'key' => 'field_acfe_dt_sort',
            'label' => __('Sort', 'acfe'),
            'name' => 'sort',
            'type' => 'true_false',
            'instructions' => __('Whether this taxonomy should remember the order in which terms are added to objects', 'acfe'),
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
            'key' => 'field_acfe_dt_tab_labels',
            'label' => __('Labels', 'acfe'),
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
            'key' => 'field_acfe_dt_labels',
            'label' => __('Labels', 'acfe'),
            'name' => 'labels',
            'type' => 'group',
            'instructions' => __('An array of labels for this taxonomy. By default tag labels are used for non-hierarchical types and category labels for hierarchical ones.<br /><br />
Default: if empty, name is set to label value, and singular_name is set to name value.', 'acfe'),
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
                    'key' => 'field_acfe_dt_singular_name',
                    'label' => __('Singular name', 'acfe'),
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
                    'key' => 'field_acfe_dt_menu_name',
                    'label' => __('Menu name', 'acfe'),
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
                    'key' => 'field_acfe_dt_all_items',
                    'label' => __('All items', 'acfe'),
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
                    'key' => 'field_acfe_dt_edit_item',
                    'label' => __('Edit item', 'acfe'),
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
                    'key' => 'field_acfe_dt_view_item',
                    'label' => __('View item', 'acfe'),
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
                    'key' => 'field_acfe_dt_update_item',
                    'label' => __('Update item', 'acfe'),
                    'name' => 'update_item',
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
                    'key' => 'field_acfe_dt_add_new_item',
                    'label' => __('Add new item', 'acfe'),
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
                    'key' => 'field_acfe_dt_new_item_name',
                    'label' => __('New item name', 'acfe'),
                    'name' => 'new_item_name',
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
                    'key' => 'field_acfe_dt_parent_item',
                    'label' => __('Parent item', 'acfe'),
                    'name' => 'parent_item',
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
                    'key' => 'field_acfe_dt_parent_item_colon',
                    'label' => __('Parent item colon', 'acfe'),
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
                    'key' => 'field_acfe_dt_search_items',
                    'label' => __('Search items', 'acfe'),
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
                    'key' => 'field_acfe_dt_popular_items',
                    'label' => __('Popular items', 'acfe'),
                    'name' => 'popular_items',
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
                    'key' => 'field_acfe_dt_separate_items_with_commas',
                    'label' => __('Separate items with commas', 'acfe'),
                    'name' => 'separate_items_with_commas',
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
                    'key' => 'field_acfe_dt_add_or_remove_items',
                    'label' => __('Add or remove items', 'acfe'),
                    'name' => 'add_or_remove_items',
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
                    'key' => 'field_acfe_dt_choose_from_most_used',
                    'label' => __('Choose from most used', 'acfe'),
                    'name' => 'choose_from_most_used',
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
                    'key' => 'field_acfe_dt_not_found',
                    'label' => __('Not found', 'acfe'),
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
                    'key' => 'field_acfe_dt_back_to_items',
                    'label' => __('Back to items', 'acfe'),
                    'name' => 'back_to_items',
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
            'key' => 'field_acfe_dt_tab_menu',
            'label' => __('Menu', 'acfe'),
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
            'key' => 'field_acfe_dt_show_ui',
            'label' => __('Show UI', 'acfe'),
            'name' => 'show_ui',
            'type' => 'true_false',
            'instructions' => __('Whether to generate a default UI for managing this post type in the admin', 'acfe'),
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
            'key' => 'field_acfe_dt_show_in_menu',
            'label' => __('Show in menu', 'acfe'),
            'name' => 'show_in_menu',
            'type' => 'true_false',
            'instructions' => __('Where to show the taxonomy in the admin menu. show_ui must be true', 'acfe'),
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
            'key' => 'field_acfe_dt_show_in_nav_menus',
            'label' => __('Show in nav menus', 'acfe'),
            'name' => 'show_in_nav_menus',
            'type' => 'true_false',
            'instructions' => __('true makes this taxonomy available for selection in navigation menus', 'acfe'),
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
            'key' => 'field_acfe_dt_show_tagcloud',
            'label' => __('Show tagcloud', 'acfe'),
            'name' => 'show_tagcloud',
            'type' => 'true_false',
            'instructions' => __('Whether to allow the Tag Cloud widget to use this taxonomy', 'acfe'),
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
            'key' => 'field_acfe_dt_show_in_quick_edit',
            'label' => __('Show in quick edit', 'acfe'),
            'name' => 'show_in_quick_edit',
            'type' => 'true_false',
            'instructions' => __('Whether to show the taxonomy in the quick/bulk edit panel', 'acfe'),
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
            'key' => 'field_acfe_dt_show_admin_column',
            'label' => __('Show admin column', 'acfe'),
            'name' => 'show_admin_column',
            'type' => 'true_false',
            'instructions' => __('Whether to allow automatic creation of taxonomy columns on associated post-types table', 'acfe'),
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
            'key' => 'field_acfe_dt_tab_capability',
            'label' => __('Capability', 'acfe'),
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
            'key' => 'field_acfe_dt_capabilities',
            'label' => __('Capabilities', 'acfe'),
            'name' => 'capabilities',
            'type' => 'textarea',
            'instructions' => __('An array of the capabilities for this taxonomy:<br /><br />
manage_terms<br />
edit_terms<br />
delete_terms<br />
assign_terms', 'acfe'),
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
            'key' => 'field_acfe_dt_tab_single',
            'label' => __('Single', 'acfe'),
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
            'key' => 'field_acfe_dt_single_template',
            'label' => __('Template', 'acfe'),
            'name' => 'acfe_dt_single_template',
            'type' => 'text',
            'instructions' => __('ACF Extended: Which template file to load for the term query. More informations on <a href="https://developer.wordpress.org/themes/basics/template-hierarchy/">Template hierarchy</a>', 'acfe'),
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
            'prepend' =>  str_replace(home_url(), '', ACFE_THEME_URL) . '/',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_acfe_dt_single_posts_per_page',
            'label' => __('Posts per page', 'acfe'),
            'name' => 'acfe_dt_single_posts_per_page',
            'type' => 'number',
            'instructions' => __('ACF Extended: Number of posts to display on the admin list screen', 'acfe'),
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
            'key' => 'field_acfe_dt_single_orderby',
            'label' => __('Order by', 'acfe'),
            'name' => 'acfe_dt_single_orderby',
            'type' => 'text',
            'instructions' => __('ACF Extended: Sort retrieved posts by parameter in the admin list screen. Defaults to \'date (post_date)\'.', 'acfe'),
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
            'key' => 'field_acfe_dt_single_order',
            'label' => __('Order', 'acfe'),
            'name' => 'acfe_dt_single_order',
            'type' => 'select',
            'instructions' => __('ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the admin list screen. Defaults to \'DESC\'.', 'acfe'),
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
            'key' => 'field_acfe_dt_rewrite',
            'label' => __('Rewrite', 'acfe'),
            'name' => 'rewrite',
            'type' => 'true_false',
            'instructions' => __('Set to false to prevent automatic URL rewriting a.k.a. "pretty permalinks". Pass an argument array to override default URL settings for permalinks', 'acfe'),
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
            'key' => 'field_acfe_dt_rewrite_args_select',
            'label' => __('Rewrite Arguments', 'acfe'),
            'name' => 'rewrite_args_select',
            'type' => 'true_false',
            'instructions' => __('Use additional rewrite arguments', 'acfe'),
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_acfe_dt_rewrite',
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
            'key' => 'field_acfe_dt_rewrite_args',
            'label' => __('Rewrite Arguments', 'acfe'),
            'name' => 'rewrite_args',
            'type' => 'group',
            'instructions' => __('Additional arguments', 'acfe'),
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_acfe_dt_rewrite',
                        'operator' => '==',
                        'value' => '1',
                    ),
                    array(
                        'field' => 'field_acfe_dt_rewrite_args_select',
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
            'acfe_permissions' => '',
            'layout' => 'row',
            'sub_fields' => array(
                array(
                    'key' => 'field_acfe_dt_rewrite_slug',
                    'label' => __('Slug', 'acfe'),
                    'name' => 'acfe_dt_rewrite_slug',
                    'type' => 'text',
                    'instructions' => __('Used as pretty permalink text (i.e. /tag/) - defaults to $taxonomy (taxonomy\'s name slug)', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dt_rewrite_args_select',
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
                    'key' => 'field_acfe_dt_rewrite_with_front',
                    'label' => __('With front', 'acfe'),
                    'name' => 'acfe_dt_rewrite_with_front',
                    'type' => 'true_false',
                    'instructions' => __('Allowing permalinks to be prepended with front base', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dt_rewrite_args_select',
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
                    'key' => 'field_acfe_dt_rewrite_hierarchical',
                    'label' => __('Hierarchical', 'acfe'),
                    'name' => 'hierarchical',
                    'type' => 'true_false',
                    'instructions' => __('True or false allow hierarchical urls', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dt_rewrite_args_select',
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
            ),
        ),
        array(
            'key' => 'field_acfe_dt_tab_admin',
            'label' => __('Admin', 'acfe'),
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
            'key' => 'field_acfe_dt_admin_terms_per_page',
            'label' => __('Terms per page', 'acfe'),
            'name' => 'acfe_dt_admin_terms_per_page',
            'type' => 'number',
            'instructions' => __('ACF Extended: Number of terms to display on the admin list screen', 'acfe'),
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
            'key' => 'field_acfe_dt_admin_orderby',
            'label' => __('Order by', 'acfe'),
            'name' => 'acfe_dt_admin_orderby',
            'type' => 'text',
            'instructions' => __('ACF Extended: Sort retrieved terms by parameter in the admin list screen. Accepts term fields \'name\', \'slug\', \'term_group\', \'term_id\', \'id\', \'description\', \'parent\', \'count\' (for term taxonomy count), or \'none\' to omit the ORDER BY clause', 'acfe'),
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
            'default_value' => 'name',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_acfe_dt_admin_order',
            'label' => __('Order', 'acfe'),
            'name' => 'acfe_dt_admin_order',
            'type' => 'select',
            'instructions' => __('ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the admin list screen. Defaults to \'ASC\'.', 'acfe'),
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
                0 => 'ASC',
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'return_format' => 'value',
            'ajax' => 0,
            'placeholder' => '',
        ),
        array(
            'key' => 'field_acfe_dt_tab_rest',
            'label' => __('REST', 'acfe'),
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
            'key' => 'field_acfe_dt_show_in_rest',
            'label' => __('Show in rest', 'acfe'),
            'name' => 'show_in_rest',
            'type' => 'true_false',
            'instructions' => __('Whether to include the taxonomy in the REST API', 'acfe'),
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
            'key' => 'field_acfe_dt_rest_base',
            'label' => __('Rest base', 'acfe'),
            'name' => 'rest_base',
            'type' => 'text',
            'instructions' => __('To change the base url of REST API route', 'acfe'),
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
            'key' => 'field_acfe_dt_rest_controller_class',
            'label' => __('Rest controller class', 'acfe'),
            'name' => 'rest_controller_class',
            'type' => 'text',
            'instructions' => __('REST API Controller class name', 'acfe'),
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
            'default_value' => 'WP_REST_Terms_Controller',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
    ),
));
