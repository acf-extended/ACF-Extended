<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_options_pages'))
    return;

/**
 * Register Dynamic Options Page
 */
add_action('init', 'acfe_dop_register');
function acfe_dop_register(){
    
    register_post_type('acfe-dop', array(
        'label'                 => 'Options Page',
        'description'           => 'Options Page',
        'labels'                => array(
            'name'          => 'Options Pages',
            'singular_name' => 'Options Page',
            'menu_name'     => 'Options Pages',
            'edit_item'     => 'Edit Options Page',
            'add_new_item'  => 'New Options Page',
        ),
        'supports'              => array('title'),
        'hierarchical'          => true,
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
            'publish_posts'         => acf_get_setting('capability'),
            'edit_posts'            => acf_get_setting('capability'),
            'edit_others_posts'     => acf_get_setting('capability'),
            'delete_posts'          => acf_get_setting('capability'),
            'delete_others_posts'   => acf_get_setting('capability'),
            'read_private_posts'    => acf_get_setting('capability'),
            'edit_post'             => acf_get_setting('capability'),
            'delete_post'           => acf_get_setting('capability'),
            'read_post'             => acf_get_setting('capability'),
        ),
		'acfe_admin_orderby'    => 'title',
		'acfe_admin_order'      => 'ASC',
		'acfe_admin_ppp'        => 999,
    ));

}

/**
 * Dynamic Options Page Menu: Submenu Highlight
 */
add_filter('submenu_file', 'acfe_dop_menu_sub_highlight');
function acfe_dop_menu_sub_highlight($submenu_file){
    
    global $pagenow;
    
    if($pagenow !== 'post-new.php')
        return $submenu_file;
    
    $post_type = get_post_type();
    if($post_type !== 'acfe-dop')
        return $submenu_file;
    
    return 'edit.php?post_type=acfe-dop';
    
}

/**
 * ACF Register Options Pages
 */
add_action('init', 'acfe_dop_registers');
function acfe_dop_registers(){
	
	$dynamic_options_pages = acfe_settings('modules.dynamic_option.data');
    
    if(empty($dynamic_options_pages))
        return;
    
    $options_top_pages = array();
    $options_sub_pages = array();
    
    foreach($dynamic_options_pages as $name => $args){
        
        // Sub pages
        if(acf_maybe_get($args, 'parent_slug')){
            
            $options_sub_pages[$name] = $args;
            continue;
            
        }
        
        // Top pages
        $options_top_pages[$name] = $args;
        
    }
    
    if(!empty($options_sub_pages)){
        
        usort($options_sub_pages, function($a, $b){
            return (int) $a['position'] - (int) $b['position'];
        });
        
    }
    
    // Merge
    $options_pages = array_merge($options_top_pages, $options_sub_pages);
    
    // Bail early if empty
    if(empty($options_pages))
        return;
        
    foreach($options_pages as $name => $args){
    
        // Textdomain
        $textdomain = 'ACF Extended: Options Pages';
    
        // Page Title
        if(isset($args['page_title'])){
        
            acfe__($args['page_title'], 'Menu_title', $textdomain);
        
        }
    
        // Menu Title
        if(isset($args['menu_title'])){
        
            acfe__($args['menu_title'], 'Menu_title', $textdomain);
        
        }
    
        // Update button
        if(isset($args['update_button'])){
        
            acfe__($args['update_button'], 'Update_button', $textdomain);
        
        }
    
        // Updated message
        if(isset($args['updated_message'])){
        
            acfe__($args['updated_message'], 'Updated_message', $textdomain);
        
        }
        
        // Register: Execute
        acf_add_options_page($args);
        
    }

}

/**
 * ACF Exclude Dynamic Options Page from available post types
 */
add_filter('acf/get_post_types', 'acfe_dop_exclude', 10, 2);
function acfe_dop_exclude($post_types, $args){
    
    if(empty($post_types))
        return $post_types;
    
    foreach($post_types as $k => $post_type){
        
        if($post_type !== 'acfe-dop')
            continue;
        
        unset($post_types[$k]);
        
    }
    
    return $post_types;
    
}

add_action('post_submitbox_misc_actions', 'acfe_dop_misc_actions');
function acfe_dop_misc_actions($post){
    
    if($post->post_type !== 'acfe-dop')
        return;
    
    $name = get_field('menu_slug', $post->ID);
    
    ?>
    <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
        <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dop_export&action=php&keys=' . $name); ?>">PHP</a> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dop_export&action=json&keys=' . $name); ?>">Json</a>
    </div>
    <?php
    
}

/**
 * Dynamic Options Page Save
 */
add_action('acf/save_post', 'acfe_dop_filter_save', 20);
function acfe_dop_filter_save($post_id){
    
    if(get_post_type($post_id) !== 'acfe-dop')
        return;
    
    // Page Title
	$page_title = get_post_field('post_title', $post_id);
    
    // Menu Title
	$menu_title = get_field('menu_title', $post_id);
	
	if(empty($menu_title)){
		
		$menu_title = $page_title;
		
	}
	
	// Menu Slug
	$menu_slug = get_field('menu_slug', $post_id);
	
	if(empty($menu_slug)){
		
		$menu_slug = sanitize_title($menu_title);
		update_field('menu_slug', $menu_slug, $post_id);
		
	}
    
    // Parent
    $parent = 0;
	
	$parent_slug = get_field('parent_slug', $post_id);
    
    if(!empty($parent_slug)){
        
        $get_dop_parent = get_posts(array(
            'post_type'         => 'acfe-dop',
            'posts_per_page'    => 1,
            'fields'            => 'ids',
            'meta_query'        => array(
                array(
                    'key'   => 'menu_slug',
                    'value' => $parent_slug
                )
            )
        ));
        
        if(!empty($get_dop_parent)){
	
			$parent = $get_dop_parent[0];
        
        }
        
    }
    
    // Update post
    wp_update_post(array(
        'ID'            => $post_id,
        'post_name'     => $menu_slug,
        'post_parent'   => $parent,
    ));
    
    // Register Args
    $capability = get_field('capability', $post_id);
    $position = get_field('position', $post_id);
    $icon_url = get_field('icon_url', $post_id);
    $redirect = get_field('redirect', $post_id);
    $p_id = get_field('post_id', $post_id);
    $autoload = get_field('autoload', $post_id);
    $update_button = get_field('update_button', $post_id);
    $updated_message = get_field('updated_message', $post_id);
    
    // Register: Args
    $register_args = array(
        'page_title'        => $page_title,
        'menu_title'        => $menu_title,
        'menu_slug'         => $menu_slug,
        'capability'        => $capability,
        'position'          => $position,
        'parent_slug'       => $parent_slug,
        'icon_url'          => $icon_url,
        'redirect'          => $redirect,
        'post_id'           => $p_id,
        'autoload'          => $autoload,
        'update_button'     => $update_button,
        'updated_message'   => $updated_message,
    );
    
    // Redirect
    $register_args['redirect'] = true;
    
    if(empty($redirect))
        $register_args['redirect'] = false;
    
    // Post ID
    if(empty($p_id))
        $register_args['post_id'] = 'options';
    
    // Autoload
    $register_args['autoload'] = true;
    
    if(empty($autoload))
        $register_args['autoload'] = false;
        
    // Get ACFE option
	$option = acfe_settings('modules.dynamic_option.data');
    
    // Create ACFE option
    $option[$menu_slug] = $register_args;
    
    // Sort keys ASC
    ksort($option);
    
    // Update ACFE option
	acfe_settings('modules.dynamic_option.data', $option, true);
    
}

/**
 * Dynamic Options Page Status Publish > Trash
 */
add_action('publish_to_trash', 'acfe_dop_filter_status_trash');
function acfe_dop_filter_status_trash($post){
    
    if(get_post_type($post->ID) !== 'acfe-dop')
        return;
    
    $post_id = $post->ID;
    $name = get_field('menu_slug', $post_id);
    
    // Get ACFE option
	$option = acfe_settings('modules.dynamic_option.data');
    
    // Check ACFE option
    acfe_unset($option, $name);
    
    // Update ACFE option
	acfe_settings('modules.dynamic_option.data', $option, true);
    
}

/**
 * Dynamic Options Page Status Trash > Publish
 */
add_action('trash_to_publish', 'acfe_dop_filter_status_publish');
function acfe_dop_filter_status_publish($post){
    
    if(get_post_type($post->ID) !== 'acfe-dop')
        return;
    
    acfe_dop_filter_save($post->ID);
    
}

add_action('pre_get_posts', 'acfe_dop_filter_admin_list', 15);
function acfe_dop_filter_admin_list($query){
    
    global $pagenow;
    
    if (!is_admin() || !$query->is_main_query() || $pagenow !== 'edit.php' || $query->get('post_type') !== 'acfe-dop')
        return;
    
    $query->set('meta_key', 'position');
    $query->set('orderby', 'meta_value_num title');
    $query->set('order', 'ASC');
    
}

/**
 * Admin List Columns
 */
add_filter('manage_edit-acfe-dop_columns', 'acfe_dop_admin_columns');
function acfe_dop_admin_columns($columns){
    
    acfe_unset($columns, 'date');
    
    $columns['name'] = __('Menu slug');
    $columns['post_id'] = __('Post ID');
    $columns['autoload'] = __('Autoload');
    
    return $columns;
    
}

/**
 * Admin List Columns HTML
 */
add_action('manage_acfe-dop_posts_custom_column', 'acfe_dop_admin_columns_html', 10, 2);
function acfe_dop_admin_columns_html($column, $post_id){
    
    // Name
    if($column === 'name'){
        
        $name = get_field('menu_slug', $post_id);
        
        echo '<code style="font-size: 12px;">' . $name . '</code>';
        
    }
    
    // Post ID
    elseif($column === 'post_id'){
        
        $p_id = get_field('post_id', $post_id);
        if(empty($p_id))
            $p_id = 'options';
        
        echo '<code style="font-size: 12px;">' . $p_id. '</code>';
        
    }
    
    // Autoload
    elseif($column === 'autoload'){
        
        $autoload = get_field('autoload', $post_id);
        
        if(empty($autoload))
            echo 'No';
        else
            echo 'Yes';
        
    }
    
}

/**
 * Admin List Row Actions
 */
add_filter('page_row_actions','acfe_dop_admin_row', 10, 2);
function acfe_dop_admin_row($actions, $post){

    if($post->post_type !== 'acfe-dop' || $post->post_status !== 'publish')
        return $actions;
    
    $name = get_field('menu_slug', $post->ID);
    
    $actions['acfe_dop_export_php'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dop_export&action=php&keys=' . $name) . '">' . __('PHP') . '</a>';
    $actions['acfe_dop_export_json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dop_export&action=json&keys=' . $name) . '">' . __('Json') . '</a>';
    
    return $actions;
    
}

/**
 * Admin Add Config Button
 */
add_action('admin_footer', 'acfe_dop_admin_footer');
function acfe_dop_admin_footer(){
	
	if(!current_user_can(acf_get_setting('capability')))
		return;
	
	global $plugin_page;
	
	if(!$plugin_page)
	    return;
	
	$page = acf_get_options_page($plugin_page);
	
	if(!acf_maybe_get($page, 'menu_slug'))
	    return;
	
	// Get Dynamic Options Page
	$acfe_dop_options_page = get_posts(array(
	    'post_type'         => 'acfe-dop',
        'posts_per_page'    => 1,
        'name'              => $page['menu_slug']
    ));
	
	if(empty($acfe_dop_options_page))
		return;
	
	$acfe_dop_options_page = $acfe_dop_options_page[0];
	
	?>
    <script type="text/html" id="tmpl-acfe-dop-title-config">
        <a href="<?php echo admin_url('post.php?post=' . $acfe_dop_options_page->ID . '&action=edit'); ?>" class="page-title-action acfe-dop-admin-config"><span class="dashicons dashicons-admin-generic"></span></a>
    </script>

    <script type="text/javascript">
        (function($){

            // Add button
            $('.wrap h1').append($('#tmpl-acfe-dop-title-config').html());

        })(jQuery);
    </script>
	<?php
	
}

add_filter('enter_title_here', 'acfe_dop_admin_placeholder_title', 10, 2);
function acfe_dop_admin_placeholder_title($placeholder, $post){
	
	// Get post type
	global $typenow;
	
	// Check post type
	$post_type = $typenow;
	if($post_type !== 'acfe-dop')
		return $placeholder;
	
	return 'Options Page Title';
	
}

add_action('admin_footer-post.php', 'acfe_dop_admin_validate_title');
function acfe_dop_admin_validate_title(){
	
	// Get post type
	global $typenow;
	
	// Check post type
	$post_type = $typenow;
	if($post_type !== 'acfe-dop')
		return;
	
	?>
    <script type="text/javascript">
        (function($){

            if(typeof acf === 'undefined')
                return;

            $('#post').submit(function(e){

                // vars
                var $title = $('#titlewrap #title');

                // empty
                if(!$title.val()){

                    // prevent default
                    e.preventDefault();

                    // alert
                    alert('Options Page Title is required.');

                    // focus
                    $title.focus();

                }

            });

        })(jQuery);
    </script>
	<?php
}

/**
 * Admin Validate Name
 */
add_filter('acf/validate_value/key=field_acfe_dop_menu_slug', 'acfe_dop_admin_validate_name', 10, 4);
function acfe_dop_admin_validate_name($valid, $value, $field, $input){
	
	if(!$valid)
		return $valid;
	
	// Editing Current Block Type
	$current_post_id = acf_maybe_get_POST('post_ID');
	
	if(!empty($current_post_id)){
		
		$current_name = get_field($field['name'], $current_post_id);
		
		if($value === $current_name)
			return $valid;
		
	}
	
	// Check existing ACF Options Pages
	$pages = acf_get_options_pages();
	
	if(!empty($pages)){
		
		foreach($pages as $slug => $page){
			
			if($slug !== $value)
				continue;
			
			$valid = __('This options page slug already exists');
			
		}
		
	}
	
	return $valid;
	
}

add_filter('acf/update_value/key=field_acfe_dop_menu_slug', 'acfe_dop_admin_update_name', 10, 3);
function acfe_dop_admin_update_name($value, $post_id, $field){
	
	// Previous value
	$_value = get_field($field['name'], $post_id);
	
	// Value Changed. Delete option
	if($_value !== $value){
		
		acfe_settings()->delete('modules.dynamic_option.data.' . $_value);
		
	}
	
	return $value;
	
}

/**
 * Add Local Field Group
 */
acf_add_local_field_group(array(
    'key' => 'group_acfe_dynamic_options_page',
    'title' => __('Dynamic Options Page', 'acfe'),
    
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'acfe-dop',
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
			'key' => 'field_acfe_dop_menu_slug',
			'label' => 'Menu slug',
			'name' => 'menu_slug',
			'type' => 'acfe_slug',
			'instructions' => '(string) The URL slug used to uniquely identify this options page. Defaults to a url friendly version of Menu Title',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_validate' => '',
			'acfe_update' => array(
				'5cd2a4d60fbf2' => array(
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
            'key' => 'field_acfe_dop_menu_title',
            'label' => 'Menu title',
            'name' => 'menu_title',
            'type' => 'text',
            'instructions' => '(string) The title displayed in the wp-admin sidebar. Defaults to Page Title',
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
            'key' => 'field_acfe_dop_capability',
            'label' => 'Capability',
            'name' => 'capability',
            'type' => 'text',
            'instructions' => '(string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.<br /><br />

Read more about capability here: <a href="https://wordpress.org/support/article/roles-and-capabilities/">https://wordpress.org/support/article/roles-and-capabilities/</a>',
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
            'default_value' => 'edit_posts',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_acfe_dop_position',
            'label' => 'Position',
            'name' => 'position',
            'type' => 'text',
            'instructions' => '(int|string) The position in the menu order this menu should appear. Defaults to bottom of utility menu items.<br /><br />

WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!<br />
Risk of conflict can be reduced by using decimal instead of integer values, e.g. \'63.3\' instead of 63 (must use quotes).',
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
            'key' => 'field_acfe_dop_parent_slug',
            'label' => 'Parent slug',
            'name' => 'parent_slug',
            'type' => 'text',
            'instructions' => '(string) The slug of another WP admin page. if set, this will become a child page.',
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
            'key' => 'field_acfe_dop_icon_url',
            'label' => 'Icon url',
            'name' => 'icon_url',
            'type' => 'text',
            'instructions' => '(string) The icon class for this menu. Defaults to default WordPress gear.<br /><br />
Read more about dashicons here: <a href="https://developer.wordpress.org/resource/dashicons/">https://developer.wordpress.org/resource/dashicons/</a>',
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
            'key' => 'field_acfe_dop_redirect',
            'label' => 'Redirect',
            'name' => 'redirect',
            'type' => 'true_false',
            'instructions' => '(boolean) If set to true, this options page will redirect to the first child page (if a child page exists). 
If set to false, this parent page will appear alongside any child pages. Defaults to true',
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
            'key' => 'field_acfe_dop_post_id',
            'label' => 'Post ID',
            'name' => 'post_id',
            'type' => 'text',
            'instructions' => '(int|string) The \'$post_id\' to save/load data to/from. Can be set to a numeric post ID (123), or a string (\'user_2\'). 
Defaults to \'options\'.',
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
            'default_value' => 'options',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_acfe_dop_autoload',
            'label' => 'Autoload',
            'name' => 'autoload',
            'type' => 'true_false',
            'instructions' => '(boolean)	Whether to load the option (values saved from this options page) when WordPress starts up.
Defaults to false.',
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
        array(
            'key' => 'field_acfe_dop_update_button',
            'label' => 'Update button',
            'name' => 'update_button',
            'type' => 'text',
            'instructions' => '(string) The update button text.',
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
            'default_value' => 'Update',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_acfe_dop_updated_message',
            'label' => 'Updated Message',
            'name' => 'updated_message',
            'type' => 'text',
            'instructions' => '(string) The message shown above the form on submit.',
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
            'default_value' => 'Options Updated',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
    ),
));
