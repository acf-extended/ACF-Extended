<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_block_types'))
    return;

/**
 * Require ACF Pro 5.8
 */
if(version_compare(ACF_VERSION, '5.8', '<'))
    return;

/**
 * Register Dynamic Block Type
 */
add_action('init', 'acfe_dbt_register');
function acfe_dbt_register(){
    
    register_post_type('acfe-dbt', array(
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
 * Dynamic Block Type Menu: Submenu Highlight
 */
add_filter('submenu_file', 'acfe_dbt_menu_sub_highlight');
function acfe_dbt_menu_sub_highlight($submenu_file){
    
    global $pagenow;
    
    if($pagenow !== 'post-new.php')
        return $submenu_file;
    
    $post_type = get_post_type();
    if($post_type !== 'acfe-dbt')
        return $submenu_file;
    
    return 'edit.php?post_type=acfe-dbt';
    
}

/**
 * ACF Register Block Types
 * Note: We're already in acf/init, 99. No need to re-hook
 */
acfe_dbt_registers();

function acfe_dbt_registers(){
	
	$dynamic_block_types = acfe_settings('modules.dynamic_block_type.data');
	
    if(empty($dynamic_block_types))
        return;
    
    foreach($dynamic_block_types as $name => $args){
    
        if(acf_has_block_type('acf/' . $name))
            continue;
    
        // Textdomain
        $textdomain = 'ACF Extended: Block Types';
    
        // Title
        if(isset($args['title'])){
        
            acfe__($args['title'], 'Title', $textdomain);
        
        }
    
        // Description
        if(isset($args['description'])){
        
            acfe__($args['description'], 'Description', $textdomain);
        
        }
    
        // Template
        $render_template = $args['render_template'];
        
        if(!empty($render_template)){
    
            $template = acfe_locate_file_path($render_template);
    
            if(!empty($template)){
        
                $args['render_template'] = $template;
        
            }
            
        }
        
    
        // Style
        $enqueue_style = $args['enqueue_style'];
        
        if(!empty($enqueue_style)){
    
            $style = acfe_locate_file_url($enqueue_style);
    
            if(!empty($style)){
        
                $args['enqueue_style'] = $style;
        
            }
            
        }
        
    
        // Script
        $enqueue_script = $args['enqueue_script'];
        
        if(!empty($enqueue_script)){
    
            $script = acfe_locate_file_url($enqueue_script);
    
            if(!empty($script)){
        
                $args['enqueue_script'] = $script;
        
            }
            
        }
    
        // Register Block Type
        acf_register_block_type($args);
        
    }

}

/**
 * ACF Exclude Dynamic Options Page from available post types
 */
add_filter('acf/get_post_types', 'acfe_dbt_exclude', 10, 2);
function acfe_dbt_exclude($post_types, $args){
    
    if(empty($post_types))
        return $post_types;
    
    foreach($post_types as $k => $post_type){
        
        if($post_type !== 'acfe-dbt')
            continue;
        
        unset($post_types[$k]);
        
    }
    
    return $post_types;
    
}

add_action('post_submitbox_misc_actions', 'acfe_dbt_misc_actions');
function acfe_dbt_misc_actions($post){
    
    if($post->post_type !== 'acfe-dbt')
        return;
    
    $name = get_field('name', $post->ID);
    
    ?>
    <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
        <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dbt_export&action=php&keys=' . $name); ?>">PHP</a> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dbt_export&action=json&keys=' . $name); ?>">Json</a>
    </div>
    <?php
    
}

/**
 * Dynamic Block Types Save
 */
add_action('acf/save_post', 'acfe_dbt_filter_save', 20);
function acfe_dbt_filter_save($post_id){
    
    if(get_post_type($post_id) !== 'acfe-dbt')
        return;
    
    // Register Args
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
    $register_args = array(
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
        'enqueue_assets'    => $enqueue_assets
    );
    
    // Align
    if($align === 'none')
        $register_args['align'] = '';
    
    // Icon
    $icon_type = get_field('icon_type', $post_id);
    
    // Icon: Simple
    if($icon_type === 'simple'){
        
        $icon_text = get_field('icon_text', $post_id);
        
        $register_args['icon'] = $icon_text;
        
    }
    
    // Icon: Colors
    elseif($icon_type == 'colors'){
        
        $icon_background = get_field('icon_background', $post_id);
        $icon_foreground = get_field('icon_foreground', $post_id);
        $icon_src = get_field('icon_src', $post_id);
        
        $register_args['icon'] = array(
            'background'    => $icon_background,
            'foreground'    => $icon_foreground,
            'src'           => $icon_src,
        );
        
        
    }
    
    // Supports: Align
    $supports_align = get_field('supports_align', $post_id);
    $supports_align_args = acf_decode_choices(get_field('supports_align_args', $post_id), true);
    
    $register_args['supports']['align'] = false;
    if(!empty($supports_align)){
        
        $register_args['supports']['align'] = true;
        
        if(!empty($supports_align_args))
            $register_args['supports']['align'] = $supports_align_args;
        
    }
    
    // Supports: Mode
    $supports_mode = get_field('supports_mode', $post_id);
    
    $register_args['supports']['mode'] = false;
    if(!empty($supports_mode))
        $register_args['supports']['mode'] = true;
    
    // Supports: Multiple
    $supports_multiple = get_field('supports_multiple', $post_id);
    
    $register_args['supports']['multiple'] = false;
    if(!empty($supports_multiple))
        $register_args['supports']['multiple'] = true;
    
    // Supports: Experimental JSX
    $experimental_jsx = get_field('supports_experimental_jsx', $post_id);
    
    $register_args['supports']['__experimental_jsx'] = false;
    if(!empty($experimental_jsx))
        $register_args['supports']['__experimental_jsx'] = true;
    
    // Supports: Align Content
    $supports_align_content = get_field('supports_align_content', $post_id);
    
    $register_args['supports']['align_content'] = false;
    if(!empty($supports_align_content))
        $register_args['supports']['align_content'] = true;
    
        
    // Get ACFE option
	$option = acfe_settings('modules.dynamic_block_type.data');
    
    // Create ACFE option
    $option[$name] = $register_args;
    
    // Sort keys ASC
    ksort($option);
    
    // Update ACFE option
	acfe_settings('modules.dynamic_block_type.data', $option, true);
	
	// Update post
	wp_update_post(array(
		'ID'            => $post_id,
		'post_name'     => $name,
	));
    
}

/**
 * Dynamic Block Type Status Publish > Trash
 */
add_action('publish_to_trash', 'acfe_dbt_filter_status_trash');
function acfe_dbt_filter_status_trash($post){
    
    if(get_post_type($post->ID) !== 'acfe-dbt')
        return;
    
    $post_id = $post->ID;
    $name = get_field('name', $post_id);
    
    // Get ACFE option
	$option = acfe_settings('modules.dynamic_block_type.data');
    
    // Check ACFE option
    if(isset($option[$name]))
        unset($option[$name]);
    
    // Update ACFE option
	acfe_settings('modules.dynamic_block_type.data', $option, true);
    
}

/**
 * Dynamic Block Type Status Trash > Publish
 */
add_action('trash_to_publish', 'acfe_dbt_filter_status_publish');
function acfe_dbt_filter_status_publish($post){
    
    if(get_post_type($post->ID) !== 'acfe-dbt')
        return;
    
    acfe_dbt_filter_save($post->ID);
    
}

/**
 * Admin List Columns
 */
add_filter('manage_edit-acfe-dbt_columns', 'acfe_dbt_admin_columns');
function acfe_dbt_admin_columns($columns){
    
    if(isset($columns['date']))
        unset($columns['date']);
    
    $columns['name'] = __('Name');
    $columns['category'] = __('Category');
    $columns['post_types'] = __('Post Types');
    $columns['render'] = __('Render');
    
    return $columns;
    
}

/**
 * Admin List Columns HTML
 */
add_action('manage_acfe-dbt_posts_custom_column', 'acfe_dbt_admin_columns_html', 10, 2);
function acfe_dbt_admin_columns_html($column, $post_id){
    
    // Name
    if($column == 'name'){
        
        echo '<code style="font-size: 12px;">' . get_field('name', $post_id) . '</code>';
        
    }
    
    // Category
    elseif($column == 'category'){
        
        echo ucfirst(get_field('category', $post_id));
        
    }
    
    // Post Types
    elseif($column == 'post_types'){
        
        $post_types = get_field('post_types', $post_id);
        
        if(empty($post_types)){
            echo '—';
            return;
        }
        
        $post_types_names = array();
        foreach($post_types as $post_type_slug){
            $post_type_obj = get_post_type_object($post_type_slug);
            $post_types_names[] = $post_type_obj->label;
        }
        
        if(empty($post_types_names)){
            echo '—';
            return;
        }
        
        echo implode(', ', $post_types_names);
        
    }
    
    // Render
    elseif($column == 'render'){
        
        $render_template = get_field('render_template', $post_id);
        $render_callback = get_field('render_callback', $post_id);
        
        if(!empty($render_template)){
            
            echo '<code style="font-size: 12px;">' . $render_template . '</code>';
            
        }
        
        elseif(!empty($render_callback)){
            
            echo '<code style="font-size: 12px;">' . $render_callback . '</code>';
            
        }
        
        else{
            
            echo '—';
            
        }
        
    }
    
}

/**
 * Admin List Row Actions
 */
add_filter('post_row_actions','acfe_dbt_admin_row', 10, 2);
function acfe_dbt_admin_row($actions, $post){

    if($post->post_type !== 'acfe-dbt' || $post->post_status !== 'publish')
        return $actions;
    
    $post_id = $post->ID;
    $name = get_field('name', $post_id);
    
    $actions['acfe_dpt_export_php'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dbt_export&action=php&keys=' . $name) . '">' . __('PHP') . '</a>';
    $actions['acfe_dpt_export_json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_dbt_export&action=json&keys=' . $name) . '">' . __('Json') . '</a>';
    
    return $actions;
    
}

add_filter('enter_title_here', 'acfe_dbt_admin_placeholder_title', 10, 2);
function acfe_dbt_admin_placeholder_title($placeholder, $post){
	
	// Get post type
	global $typenow;
	
	// Check post type
	$post_type = $typenow;
	if($post_type !== 'acfe-dbt')
		return $placeholder;
	
	return 'Block Type Title';
	
}

add_action('admin_footer-post.php', 'acfe_dbt_admin_validate_title');
function acfe_dbt_admin_validate_title(){
	
	// Get post type
	global $typenow;
	
	// Check post type
	$post_type = $typenow;
	if($post_type !== 'acfe-dbt')
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
                    alert('Block Type Title is required.');

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
add_filter('acf/validate_value/key=field_acfe_dbt_name', 'acfe_dbt_admin_validate_name', 10, 4);
function acfe_dbt_admin_validate_name($valid, $value, $field, $input){
	
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

add_filter('acf/update_value/key=field_acfe_dbt_name', 'acfe_dbt_admin_update_name', 10, 3);
function acfe_dbt_admin_update_name($value, $post_id, $field){
	
	// Previous value
	$_value = get_field($field['name'], $post_id);
	
	// Value Changed. Delete option
	if($_value !== $value){
		
		acfe_settings()->delete('modules.dynamic_block_type.data.' . $_value);
		
	}
	
	return $value;
	
}

add_action('load-post.php', 'acfe_dbt_load');
function acfe_dbt_load(){
        
    // globals
    global $typenow;
    
    // Restrict
    if($typenow !== 'acfe-dbt')
        return;
    
    add_action('add_meta_boxes', 'acfe_dbt_load_meta_boxes');
    
    if(!isset($_REQUEST['post']))
        return;
    
    $post_id = $_REQUEST['post'];
    $name = get_field('name', $post_id);
    
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

add_action('load-post-new.php', 'acfe_dbt_load_new');
function acfe_dbt_load_new(){
    
    // globals
    global $typenow;
    
    // Restrict
    if($typenow !== 'acfe-dbt')
        return;
    
    $prepend = acfe_get_setting('theme_folder') ? trailingslashit(acfe_get_setting('theme_folder')) : '';
    
    add_filter('acf/prepare_field/name=render_template', function($field) use($prepend){
        
        $prepend = apply_filters('acfe/block_type/prepend/template', $prepend, '');
        
        $field['prepend'] = $prepend;
        
        return $field;
        
    });
    
    add_filter('acf/prepare_field/name=enqueue_style', function($field) use($prepend){
        
        $prepend = apply_filters('acfe/block_type/prepend/style', $prepend, '');
        
        $field['prepend'] = $prepend;
        
        return $field;
        
    });
    
    add_filter('acf/prepare_field/name=enqueue_script', function($field) use($prepend){
        
        $prepend = apply_filters('acfe/block_type/prepend/script', $prepend, '');
        
        $field['prepend'] = $prepend;
        
        return $field;
        
    });
    
}

function acfe_dbt_load_meta_boxes(){
    
    $name = get_field('name', get_the_ID());
    
    $data = acf_get_field_groups(array(
        'block' => 'acf/' . $name
    ));
    
    if(empty($data))
        return;
    
    add_meta_box(
    
        // ID
        'acfe-dbt-field-groups', 
        
        // Title
        __('Field groups', 'acf'), 
        
        // Render
        'acfe_dbt_load_meta_boxes_render', 
        
        // Screen
        'acfe-dbt', 
        
        // Position
        'normal', 
        
        // Priority
        'default',
        
        // Data
        $data
        
    );
    
}

function acfe_dbt_load_meta_boxes_render($array, $data){

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
                                    
                                    acfe_dbt_get_fields_labels_recursive($array, $field);
                                    
                                }
                                
                                foreach($array as $field_key => $field_label){
                                    
                                    $field = acf_get_field($field_key);
                                    $type = acf_get_field_type($field['type']);
                                    $type_label = '-';
                                    if(isset($type->label))
                                        $type_label = $type->label;
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
    if(typeof acf !== 'undefined'){
        
        acf.newPostbox(<?php echo wp_json_encode(array(
            'id'		=> 'acfe-dbt-field-groups',
            'key'		=> '',
            'style'		=> 'default',
            'label'		=> 'left',
            'edit'		=> false
        )); ?>);
        
    }	
    </script>
    <?php
    
}

function acfe_dbt_get_fields_labels_recursive(&$array, $field){
    
    $label = '';
    
    $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
    $label = str_repeat('- ', $ancestors) . $label;
    
    $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
    $label .= $field['required'] ? ' <span class="acf-required">*</span>' : '';
    
    $array[$field['key']] = $label;
    
    if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
        
        foreach($field['sub_fields'] as $s_field){
            
            acfe_dbt_get_fields_labels_recursive($array, $s_field);
            
        }
        
    }
    
}

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
                'value' => 'acfe-dbt',
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
                '5cd2ca4caa18b' => array(
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
            'instructions' => '(String) (Optional) The display mode for your block. Available settings are “auto”, “preview” and “edit”. Defaults to “auto”.<br /><br />
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
                'auto' => 'Auto',
                'preview' => 'Preview',
                'edit' => 'Edit',
            ),
            'default_value' => array(
                0 => 'auto',
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