<?php

if(!defined('ABSPATH'))
    exit;

// Register notices store.
acf_register_store('acfe/form')->prop('multisite', true);

if(!class_exists('acfe_form')):

class acfe_form{
    
    public $post_type = '';
    public $fields_groups = array();
    
    public $posts = array();
    public $users = array();
    
    public $query_vars = array();
    
    function __construct(){
        
        // Post Type
        $this->post_type = 'acfe-form';
        
        // Admin
        add_action('init',                                                          array($this, 'init'));
        add_action('admin_menu',                                                    array($this, 'admin_menu'));
        add_action('current_screen',                                                array($this, 'current_screen'));
        add_filter('post_row_actions',                                              array($this, 'row_actions'), 10, 2);
        
        // ACF
        add_filter('acf/get_post_types',                                            array($this, 'filter_post_type'), 10, 2);
        add_filter('acf/pre_load_post_id',                                          array($this, 'validate_post_id'), 10, 2);
        
        // Fields
        add_filter('acf/load_value/name=acfe_form_custom_html_enable',              array($this, 'prepare_custom_html'), 10, 3);
        add_filter('acf/prepare_field/name=acfe_form_actions',                      array($this, 'prepare_actions'));
        add_filter('acf/prepare_field/name=acfe_form_field_groups',                 array($this, 'field_groups_choices'));
        
        // Format values
        add_filter('acfe/form/format_value/type=post_object',                       array($this, 'format_value_post_object'), 5, 4);
        add_filter('acfe/form/format_value/type=relationship',                      array($this, 'format_value_post_object'), 5, 4);
        add_filter('acfe/form/format_value/type=user',                              array($this, 'format_value_user'), 5, 4);
        add_filter('acfe/form/format_value/type=taxonomy',                          array($this, 'format_value_taxonomy'), 5, 4);
        add_filter('acfe/form/format_value/type=image',                             array($this, 'format_value_file'), 5, 4);
        add_filter('acfe/form/format_value/type=file',                              array($this, 'format_value_file'), 5, 4);
        add_filter('acfe/form/format_value/type=select',                            array($this, 'format_value_select'), 5, 4);
        add_filter('acfe/form/format_value/type=checkbox',                          array($this, 'format_value_select'), 5, 4);
        add_filter('acfe/form/format_value/type=radio',                             array($this, 'format_value_select'), 5, 4);
        add_filter('acfe/form/format_value/type=google_map',                        array($this, 'format_value_google_map'), 5, 4);
        
        add_action('acf/render_field/name=acfe_form_validation_advanced_field_validation',  array($this, 'doc_validation_field'));
        add_action('acf/render_field/name=acfe_form_validation_advanced_form_validation',   array($this, 'doc_validation_form'));
        add_action('acf/render_field/name=acfe_form_submission_advanced_submission',        array($this, 'doc_submission'));
        
        add_action('acf/render_field/name=acfe_form_cheatsheet_field',              array($this, 'doc_field'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_fields',             array($this, 'doc_fields'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_get_field',          array($this, 'doc_get_field'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_query_var',          array($this, 'doc_query_var'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_post',       array($this, 'doc_current_post'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_term',       array($this, 'doc_current_term'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_user',       array($this, 'doc_current_user'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_author',     array($this, 'doc_current_author'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_form',       array($this, 'doc_current_form'));
        
        // Ajax
        /*
        add_action('wp_ajax_acf/fields/select/query',                               array($this, 'ajax_query_post'), 5);
        add_action('wp_ajax_nopriv_acf/fields/select/query',                        array($this, 'ajax_query_post'), 5);
        
        add_action('wp_ajax_acf/fields/select/query',                               array($this, 'ajax_query_user'), 5);
        add_action('wp_ajax_nopriv_acf/fields/select/query',                        array($this, 'ajax_query_user'), 5);
        
        add_action('wp_ajax_acf/fields/select/query',                               array($this, 'ajax_query_term'), 5);
        add_action('wp_ajax_nopriv_acf/fields/select/query',                        array($this, 'ajax_query_term'), 5);
        */
    }
    
    function init(){
        
        // Post Type
        register_post_type($this->post_type, array(
            'label'                 => __('Forms', 'acf'),
            'description'           => __('Forms', 'acf'),
            'labels'                => array(
                'name'          => __('Forms', 'acf'),
                'singular_name' => __('Form', 'acf'),
                'menu_name'     => __('Forms', 'acf'),
                'edit_item'     => 'Edit Form',
                'add_new_item'  => 'New Form',
            ),
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_icon'             => 'dashicons-feedback',
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
            'acfe_admin_ppp'        => 999,
            'acfe_admin_orderby'    => 'title',
            'acfe_admin_order'      => 'ASC',
        ));
        
    }
    
    function admin_menu(){
        
        if(!acf_get_setting('show_admin'))
            return;
        
        add_submenu_page('edit.php?post_type=acf-field-group', __('Forms', 'acf'), __('Forms', 'acf'), acf_get_setting('capability'), 'edit.php?post_type=' . $this->post_type);
        
    }
    
    function current_screen(){
        
        global $typenow;
        
        if($typenow !== $this->post_type)
            return;
        
        // customize post_status
		global $wp_post_statuses;
		
		// modify publish post status
		$wp_post_statuses['publish']->label_count = _n_noop('Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'acf');
        
        add_action('load-edit.php',     array($this, 'load_list'));
        add_action('load-post.php',     array($this, 'load_post'));
        add_action('load-post-new.php', array($this, 'load_post_new'));
        
        add_action('load-post.php',     array($this, 'load'));
        add_action('load-post-new.php', array($this, 'load'));
        
    }
    
    function load_list(){
        
        // Columns
        add_filter('manage_edit-' . $this->post_type . '_columns',         array($this, 'admin_columns'));
        add_action('manage_' . $this->post_type . '_posts_custom_column',  array($this, 'admin_columns_html'), 10, 2);
        
    }
    
    function load_post(){
    
        // vars
        $this->fields_groups = $this->get_fields_groups();
        
        add_action('add_meta_boxes',                array($this, 'add_meta_boxes'));
        
        // Misc actions
        add_action('post_submitbox_misc_actions',   array($this, 'misc_actions'));
        
        // Actions
        $form_id = $_REQUEST['post'];
        
        if(have_rows('acfe_form_actions', $form_id)):
            
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                $action = get_row_layout();
                
                // Custom Action
                if($action === 'custom')
                    continue;
            
                $alias = get_sub_field('acfe_form_custom_alias');
                $query_var = get_sub_field('acfe_form_custom_query_var');
                
                $action_label = '';
                $action_type = '';
                
                if($action === 'post'){
                    
                    $action_label = 'Post';
                    
                    $post_action = get_sub_field('acfe_form_post_action');
                    
                    if($post_action === 'insert_post'){
                        
                        $action_type = 'Create Post';
                        
                    }elseif($post_action === 'update_post'){
                        
                        $action_type = 'Update Post';
                        
                    }
                    
                    
                }elseif($action === 'term'){
                    
                    $action_label = 'Term';
                    
                    $term_action = get_sub_field('acfe_form_term_action');
                    
                    if($term_action === 'insert_term'){
                        
                        $action_type = 'Create Term';
                        
                    }elseif($term_action === 'update_term'){
                        
                        $action_type = 'Update Term';
                        
                    }
                    
                }elseif($action === 'user'){
                    
                    $action_label = 'User';
                    
                    $term_action = get_sub_field('acfe_form_user_action');
                    
                    if($term_action === 'insert_user'){
                        
                        $action_type = 'Create User';
                        
                    }elseif($term_action === 'update_user'){
                        
                        $action_type = 'Update User';
                        
                    }elseif($term_action === 'log_user'){
                        
                        $action_type = 'Log User';
                        
                    }
                    
                }elseif($action === 'email'){
                    
                    $action_label = 'E-mail';
                    $action_type = 'Send';
                    
                }
                
                if(empty($alias) || empty($query_var))
                    continue;
                
                $this->query_vars[] = array(
                    'action'        => $action,
                    'action_label'  => $action_label,
                    'action_type'   => $action_type,
                    'alias'         => $alias
                );
            
            endwhile;
        endif;
        
    }
    
    function load_post_new(){
        
        // ...
        
    }
    
    function load(){
        
        // Save Post
        add_action('acf/save_post', array($this, 'save_form'), 20);
        
        // Metaboxes
        remove_meta_box('slugdiv', $this->post_type, 'normal');
        
        // Menu
        add_filter('parent_file', function(){
            return 'edit.php?post_type=acf-field-group';
        });
        
        // Submenu
        add_filter('submenu_file', function(){
            return 'edit.php?post_type=' . $this->post_type;
        });
        
        // Footer
        add_action('admin_footer', array($this, 'load_footer'));
        
    }
    
    function misc_actions($post){
        
        $name = get_field('acfe_form_name', $post->ID);
        
        ?>
        <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
            <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_form_export&action=json&keys=' . $name); ?>">Json</a>
        </div>
        <?php
        
    }
    
    function load_footer(){
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
                    alert('Form title is required.');
                    
                    // focus
                    $title.focus();
                    
                }
                
            });
            
        })(jQuery);
        </script>
        <?php
    }
    
    function filter_post_type($post_types, $args){
        
        if(empty($post_types))
            return $post_types;
        
        foreach($post_types as $k => $post_type){
            
            if($post_type !== $this->post_type)
                continue;
            
            unset($post_types[$k]);
            
        }
        
        return $post_types;
        
    }
    
    function row_actions($actions, $post){

        if($post->post_type !== $this->post_type || $post->post_status !== 'publish')
            return $actions;
        
        $post_id = $post->ID;
        $name = get_field('acfe_form_name', $post_id);
        
        $actions['acfe_form_export_json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe_tool_form_export&action=json&keys=' . $name) . '">' . __('Json') . '</a>';
        
        return $actions;
        
    }
    
    function get_fields_groups($form_id = false){
        
        if(!$form_id)
            $form_id = get_the_ID();
        
        if(!$form_id && isset($_REQUEST['post']))
            $form_id = $_REQUEST['post'];
        
        if(!$form_id)
            return false;
        
        $return = array();
        
        // Field Groups
        $field_groups = get_field('acfe_form_field_groups', $form_id);
        
        if(!empty($field_groups)){
            
            foreach($field_groups as $field_group_key){
                
                $field_group = acf_get_field_group($field_group_key);
                if(!$field_group)
                    continue;
                
                $field_group['fields'] = acf_get_fields($field_group);
                
                $return[] = $field_group;
                
            }
            
        }
        
        // return
        return $return;
        
    }
    
    function save_form($post_id){
        
        // Get Post
        $name = get_field('acfe_form_name', $post_id);
        
        // Update post
        wp_update_post(array(
            'ID'            => $post_id,
            'post_name'     => $name,
        ));
        
        $new_name = get_post_field('post_name', $post_id);
        
        if($new_name !== $name)
            update_field('acfe_form_name', $new_name, $post_id);
        
    }
    
    function add_meta_boxes(){
        
        // Add Instructions
        add_meta_box(
            
            // ID
            'acfe-form-integration',
            
            // Title
            'Integration', 
            
            // Render
            array($this, 'render_form_integration_meta_box'), 
            
            // Screen
            $this->post_type, 
            
            // Position
            'side', 
            
            // Priority
            'core'
            
        );
        
        $data = $this->fields_groups;
        
        if(!empty($data)){
            
            add_meta_box(
            
                // ID
                'acfe-form-details', 
                
                // Title
                __('Fields', 'acf'), 
                
                // Render
                array($this, 'render_form_details_meta_box'), 
                
                // Screen
                $this->post_type, 
                
                // Position
                'normal', 
                
                // Priority
                'default'
                
            );
        
        }
        
    }
    
    function render_form_integration_meta_box($post){
        
        $form_id = $post->ID;
        $form_name = get_field('acfe_form_name', $form_id);
        
        ?>
        <div class="acf-field">
        
            <div class="acf-label">
                <label>Shortcodes:</label>
            </div>
            
            <div class="acf-input">
                
                <code>[acfe_form ID="<?php echo $form_id; ?>"]</code><br /><br />
                <code>[acfe_form name="<?php echo $form_name; ?>"]</code>
                
            </div>
            
        </div>
        
        <div class="acf-field">
        
            <div class="acf-label">
                <label>PHP code:</label>
            </div>
            
            <div class="acf-input">
                
                <pre>&lt;?php get_header(); ?&gt;

&lt;!-- <?php echo get_the_title($form_id); ?> --&gt;
&lt;?php acfe_form(&apos;<?php echo $form_name; ?>&apos;); ?&gt;
    
&lt;?php get_footer(); ?&gt;</pre>
                
            </div>
            
        </div>
        
        <script type="text/javascript">
        if(typeof acf !== 'undefined'){
            
            acf.newPostbox(<?php echo wp_json_encode(array(
                'id'		=> 'acfe-form-integration',
                'key'		=> '',
                'style'		=> 'default',
                'label'		=> 'top',
                'edit'		=> false
            )); ?>);
            
        }	
        </script>
        <?php
    }

    function render_form_details_meta_box($array, $data){
        
        foreach($this->fields_groups as $field_group){ ?>
        
            <?php 
            acf_disable_filters();
            
                $field_group_db = acf_get_field_group($field_group['key']);
            
            acf_enable_filters();
            ?>
            
            <div class="acf-field">
        
                <div class="acf-label">
                    <label><a href="<?php echo admin_url('post.php?post=' . $field_group_db['ID'] . '&action=edit'); ?>"><?php echo $field_group['title']; ?></a></label>
                    <p class="description"><?php echo $field_group['key']; ?></p>
                </div>
                
                <div class="acf-input">
                    
                    <?php if(!empty($field_group['fields'])){ ?>
                        
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
                                    foreach($field_group['fields'] as $field){
                                        
                                        $this->get_fields_labels_recursive($array, $field);
                                        
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
                                        <td width="25%"><code style="font-size:12px;"><?php echo $field['name']; ?></code></td>
                                        <td width="25%"><code style="font-size:12px;"><?php echo $field_key; ?></code></td>
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
                'id'		=> 'acfe-form-details',
                'key'		=> '',
                'style'		=> 'default',
                'label'		=> 'left',
                'edit'		=> false
            )); ?>);
            
        }	
        </script>
        <?php
        
    }
    
    function get_fields_labels_recursive(&$array, $field){
        
        $label = '';
        
        $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
        $label = str_repeat('- ', $ancestors) . $label;
        
        $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
        $label .= $field['required'] ? ' <span class="acf-required">*</span>' : '';
        
        $array[$field['key']] = $label;
        
        if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
            
            foreach($field['sub_fields'] as $s_field){
                
                $this->get_fields_labels_recursive($array, $s_field);
                
            }
            
        }
        
    }
    
    // Field groups choices
    function field_groups_choices($field){
        
        acf_disable_filters();
        
        $field_groups = acf_get_field_groups();
        if(empty($field_groups))
            return $field;
        
        foreach($field_groups as $field_group){
            
            $field['choices'][$field_group['key']] = $field_group['title'];
            
        }
        
        acf_enable_filters();
        
        return $field;
        
    }
    
    function map_field_groups($field){
        
        $post_id = get_the_ID();
        
        $field_groups = get_field('acfe_form_field_groups', $post_id);
        
        if(empty($field_groups))
            return false;
        
        foreach($field_groups as $field_group_key){
            
            $field_group = acf_get_field_group($field_group_key);
            if(!$field_group)
                continue;
            
            $field['choices'][$field_group_key] = $field_group['title'];
            
        }
        
        return $field;
        
    }
    
    function prepare_custom_html($value, $post_id, $field){
        
        $custom_html = trim(get_field('acfe_form_custom_html', $post_id));
        
        if($value === false && !empty($custom_html))
            $value = true;
        
        return $value;
        
    }
    
    function prepare_actions($field){
        
        $field['instructions'] = 'Add actions on form submission';
        
        $data = $this->fields_groups;
        
        if(empty($data)){
            
            $field['instructions'] .= '<br /><u>No field groups are currently mapped</u>';
            
        }
        
        return $field;
        
    }
	
	function map_fields_deep_no_custom($field){
		
		$choices = array();
		
		if(!empty($field['choices'])){
			
			$generic = true;
			
			if(is_array($field['choices']) && count($field['choices']) === 1){
				
				reset($field['choices']);
				$key = key($field['choices']);
				
				if(acf_is_field_key($key))
					$generic = false;
				
			}
			
			if($generic)
				$choices['Generic'] = $field['choices'];
			
		}
		
		$fields_choices = $this->get_fields_choices(true, $field);
		
		if(!empty($fields_choices)){
			
			$field['choices'] = array_merge($choices, $fields_choices);
			
		}
		
		return $field;
		
	}
    
    function map_fields_deep($field){
        
        // Map Fields
	    $fields_choices = $this->get_fields_choices(true, $field);
	
	    if(!empty($fields_choices)){
		
		    $field['choices'] = array_merge($field['choices'], $fields_choices);
		
	    }
	    
        if($field['type'] === 'select'){
            
            // Query Vars
            if(!empty($this->query_vars)){
                
                parse_str($field['prefix'], $output);
                $keys = acfe_array_keys_r($output);
                
                if(acf_maybe_get($keys, 1) !== 'field_acfe_form_actions')
                    return $field;
                
                foreach($this->query_vars as $row => $query_var){
                    
                    $field_row = acf_maybe_get($keys, 2);
                    $field_row = str_replace('row-', '', $field_row);
                    
                    if((is_numeric($field_row) && $field_row > $row) || $field_row === 'acfcloneindex'){
                    
                        $action = $query_var['action'];
                        $action_label = $query_var['action_label'];
                        $action_type = $query_var['action_type'];
                        $alias = $query_var['alias'];
                        
                        $tags = array();
                        
                        if($action === 'post'){
                            
                            $tags = array(
                                "{query_var:$alias:id}" => 'Post ID',
                                "{query_var:$alias:post_title}" => 'Title',
                                "{query_var:$alias:permalink}" => 'Permalink',
                                "{query_var:$alias:admin_url}" => 'Admin URL',
                            );
                            
                        }
                        
                        elseif($action === 'term'){
                            
                            $tags = array(
                                "{query_var:$alias:id}" => 'Term ID',
                                "{query_var:$alias:name}" => 'Name',
                                "{query_var:$alias:permalink}" => 'Permalink',
                                "{query_var:$alias:admin_url}" => 'Admin URL',
                            );
                            
                        }
                        
                        elseif($action === 'user'){
                            
                            $tags = array(
                                "{query_var:$alias:id}" => 'User ID',
                                "{query_var:$alias:user_email}" => 'E-mail',
                                "{query_var:$alias:permalink}" => 'Permalink',
                            );
                            
                        }
                        
                        elseif($action === 'email'){
                            
                            $tags = array(
                                "{query_var:$alias:from}" => 'From',
                                "{query_var:$alias:to}" => 'To',
                                "{query_var:$alias:content}" => 'Content',
                            );
                            
                        }
                        
                        foreach($tags as $tag_key => $tag_value){
                            
                            $field['choices']["Action: $action_type ($alias)"][$tag_key] = $tag_value . ' ' . $tag_key;
                            
                        }
                    
                    }
                    
                }
            }
            
            // Templates Tags Examples
            $field['choices']["Current: Post"]['{current:post:id}'] = 'Post ID {current:post:id}';
            $field['choices']["Current: Post"]['{current:post:post_title}'] = 'Title {current:post:post_title}';
            $field['choices']["Current: Post"]['{current:post:permalink}'] = 'Permalink {current:post:permalink}';
            $field['choices']["Current: Post"]['{current:post:post_author}'] = 'Author {current:post:post_author}';
            
            $field['choices']["Current: Term"]['{current:term:id}'] = 'Term ID {current:term:id}';
            $field['choices']["Current: Term"]['{current:term:name}'] = 'Name {current:term:name}';
            $field['choices']["Current: Term"]['{current:term:permalink}'] = 'Permalink {current:term:permalink}';
            
            $field['choices']["Current: User"]['{current:user:id}'] = 'User ID {current:user:id}';
            $field['choices']["Current: User"]['{current:user:user_email}'] = 'E-mail {current:user:user_email}';
            $field['choices']["Current: User"]['{current:user:permalink}'] = 'Permalink {current:user:permalink}';
            
            $field['choices']["Current: Author"]['{current:author:id}'] = 'Author ID {current:author:id}';
            $field['choices']["Current: Author"]['{current:author:user_email}'] = 'E-mail {current:author:user_email}';
            $field['choices']["Current: Author"]['{current:author:permalink}'] = 'Permalink {current:author:permalink}';
            
            $field['choices']["Current: Form"]['{current:form:id}'] = 'Form ID {current:form:id}';
            $field['choices']["Current: Form"]['{current:form:title}'] = 'Title {current:form:title}';
            $field['choices']["Current: Form"]['{current:form:name}'] = 'Name {current:form:name}';
            
        }
	    
        // Clean Choices
	    if(!empty($field['choices'])){
		
		    $sub_values = array();
		
		    foreach($field['choices'] as $category => $values){
			
		        // Generate available values
			    if(is_array($values)){
				
				    $sub_values = array_merge($sub_values, $values);
				
				// Generate 'Generic'
			    }else{
				
				    unset($field['choices'][$category]);
				
				    $field['choices']['Generic'][$category] = $values;
				
			    }
			
		    }
		    
		    // Compare available vs Generic
		    if(isset($field['choices']['Generic'])){
			
			    foreach($field['choices']['Generic'] as $k => $generic){
				
				    if(!isset($sub_values[$k]))
					    continue;
				    
				    // Cleanup
				    unset($field['choices']['Generic'][$k]);
				
			    }
			    
			    if(empty($field['choices']['Generic']))
			        unset($field['choices']['Generic']);
			
		    }
		    
		    // Move Generic to Top
		    if(isset($field['choices']['Generic'])){
		        
		        $new_generic = array(
		            'Generic' => $field['choices']['Generic']
                );
		        
			    unset($field['choices']['Generic']);
			
			    $field['choices'] = array_merge($new_generic, $field['choices']);
		    
		    }
		
	    }
        
        return $field;
        
    }
    
    function map_fields($field){
        
        $fields_choices = $this->get_fields_choices();
        
        if(empty($fields_choices))
            return false;
        
        $field['choices'] = $fields_choices;
        
        return $field;
        
    }

    function get_fields_choices($deep = false, $original_field = array()){
        
        $data = $this->fields_groups;
        $choices = array();
        
        if(empty($data))
            return false;
        
        $field_groups = array();
        
        foreach($data as $field_group){
            
            if(empty($field_group['fields']))
                continue;
            
            foreach($field_group['fields'] as $s_field){
                
                $field_groups[$field_group['title']][] = $s_field;
                
            }
            
        }
        
        if(!empty($field_groups)){
            
            foreach($field_groups as $field_group_title => $fields){
                
                foreach($fields as $field){
                    
                    if(isset($choices[$field_group_title][$field['key']]))
                        continue;
                    
                    // First level
                    if(!$deep){
                        
                        $label = !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
                        $label .= $field['required'] ? ' *' : '';
                        
                        $choices[$field_group_title][$field['key']] = $label. ' (' . $field['key'] . ')';
                        
                    // Deep
                    }else{
                        
                        $this->get_fields_choices_recursive($choices[$field_group_title], $field, $original_field);
                        
                    }
                    
                }
                
            }
            
        }
        
        return $choices;
        
    }

    function get_fields_choices_recursive(&$choices, $field, $original_field){
        
        $label = '';
        
        $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
        $label = str_repeat('- ', $ancestors) . $label;
        
        $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
        $label .= $field['required'] ? ' *' : '';
        
        /*
        if(acf_maybe_get($original_field, 'type') === 'select'){
            
            $label = $label . ' <code style="font-size:12px;">{field:' . $field['name'] . '}</code>';
            
        }else{
            
            $label = $label . ' (' . $field['key'] . ')';
            
        }
        */
        
        $label = $label . ' (' . $field['key'] . ')';
        
        $choices[$field['key']] = $label;
        
        if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
            
            foreach($field['sub_fields'] as $s_field){
                
                $this->get_fields_choices_recursive($choices, $s_field, $original_field);
                
            }
            
        }
        
    }
    
    function render_fields($content, $post_id, $args){
        
        // Mapping
        $form_id = $args['form_id'];
        $form_name = $args['form_name'];
        
        $mapped_field_groups = $this->get_fields_groups($form_id);
	    $mapped_field_groups_keys = wp_list_pluck($mapped_field_groups, 'key');
        $mapped_fields = array();
        
        if(!empty($mapped_field_groups)){
            
            $post = acf_get_post_id_info($post_id);
	
	        // Apply Field Groups Rules
	        if($post['type'] === 'post' && $args['field_groups_rules']){
		
		        $filter = array(
			        'post_id'   => $post_id,
			        'post_type' => get_post_type($post_id),
		        );
		
		        $filtered = array();
		
		        foreach($mapped_field_groups as $field_group){
			
			        // Deleted field group
			        if(!isset($field_group['location']))
				        continue;
			
			        // Force active
			        $field_group['active'] = true;
			
			        if(acf_get_field_group_visibility($field_group, $filter)){
				
				        $filtered[] = $field_group;
				
			        }
			
		        }
		
		        $mapped_field_groups = $filtered;
		
	        }
	        
	        if(!empty($mapped_field_groups)){
		
		        $mapped_field_groups_keys = wp_list_pluck($mapped_field_groups, 'key');
		
		        foreach($mapped_field_groups as $field_group){
			
			        if(empty($field_group['fields']))
				        continue;
			
			        foreach($field_group['fields'] as $field){
				
				        $mapped_fields[] = $field;
				
			        }
			
		        }
	           
            }
            
        }
        
        // Match {field:key}
        if(preg_match_all('/{field:(.*?)}/', $content, $matches)){
            
            foreach($matches[1] as $i => $field_key){
                
                $field = false;
                
                // Field key
                if(strpos($field_key, 'field_') === 0){
                    
                    $field = acf_get_field($field_key);
                
                // Field name
                }else{
                    
                    if(!empty($mapped_fields)){
                        
                        foreach($mapped_fields as $mapped_field){
                            
                            if($mapped_field['name'] !== $field_key)
                                continue;
                            
                            $field = $mapped_field;
                            break;
                            
                        }
                        
                    }
                    
                }
                
                if(!$field){
                    
                    $content = str_replace('{field:' . $field_key . '}', '', $content);
                    continue;
                    
                }
                
                $fields = array();
                $fields[] = $field;
                
                ob_start();
                
                acf_render_fields($fields, acf_uniqid('acfe_form'), $args['field_el'], $args['instruction_placement']);
                
                $render_field = ob_get_clean();
                
                $content = str_replace('{field:' . $field_key . '}', $render_field, $content);
                
            }
            
        }
        
        // Match {field_group:key}
        if(preg_match_all('/{field_group:(.*?)}/', $content, $matches)){
            
            //$field_groups = acf_get_field_groups();
            
            foreach($matches[1] as $i => $field_group_key){
                
                $fields = false;
	
	            if(!empty($mapped_field_groups)){
                    
                    // Field group key
                    if(strpos($field_group_key, 'group_') === 0){
                        
                        if(in_array($field_group_key, $mapped_field_groups_keys))
                            $fields = acf_get_fields($field_group_key);
                    
                    // Field group title
                    }else{
                        
                        foreach($mapped_field_groups as $field_group){
                            
                            if($field_group['title'] !== $field_group_key)
                                continue;
                            
                            $fields = acf_get_fields($field_group['key']);
                            break;
                            
                        }
                        
                    }
		
	            }
                
                if(!$fields){
                    
                    $content = str_replace('{field_group:' . $field_group_key . '}', '', $content);
                    continue;
                    
                }
                
                ob_start();
                
                acf_render_fields($fields, acf_uniqid('acfe_form'), $args['field_el'], $args['instruction_placement']);
                
                $render_fields = ob_get_clean();
                
                $content = str_replace('{field_group:' . $field_group_key . '}', $render_fields, $content);
                
            }
            
        }
        
        return $content;
        
    }
    
    // Post Object & Relationship
    function format_value_post_object($value, $_value, $post_id, $field){
        
        $value = acf_get_array($_value);
        $array = array();
        
        foreach($value as $p_id){
            
            $array[] = get_the_title($p_id);
            
        }
        
        return implode(', ', $array);
        
    }
    
    // Google Map
    function format_value_google_map($value, $_value, $post_id, $field){
        
        if(is_string($value)){
            
            $value = json_decode(wp_unslash($value), true);
            
        }
        
        $value = acf_get_array($value);
        
        $address = acf_maybe_get($value, 'address');
        
        return $address;
        
    }
    
    // User
    function format_value_user($value, $_value, $post_id, $field){
        
        $value = acf_get_array($_value);
        $array = array();
        
        foreach($value as $user_id){
            
            $user_data = get_userdata($user_id);
            $array[] = $user_data->user_nicename;
            
        }
        
        return implode(', ', $array);
        
    }
    
    // Taxonomy
    function format_value_taxonomy($value, $_value, $post_id, $field){
        
        $value = acf_get_array($_value);
        $array = array();
        
        foreach($value as $term_id){
            
            $term = get_term($term_id);
            $array[] = $term->name;
            
        }
        
        return implode(', ', $array);
        
    }
    
    // Image / File
    function format_value_file($value, $_value, $post_id, $field){
        
        if(isset($_value['title']))
            return $_value['title'];
        
        return $value;
        
    }
    
    // Select / Checkbox / Radio
    function format_value_select($value, $_value, $post_id, $field){
        
        $value = acf_get_array($_value);
        $array = array();
        
        foreach($value as $v){
            
            $array[] = acf_maybe_get($field['choices'], $v, $v);
            
        }
        
        return implode(', ', $array);
        
    }
    
    function format_value_array($value){
        
        if(!is_array($value))
            return $value;
        
        $return = array();
        
        foreach($value as $i => $v){
            
            $key = '';
            if(!is_numeric($i))
                $key = $i . ': ';
            
            $return[] = $key . $this->format_value_array($v);
            
        }
        
        return implode(', ', $return);
        
    }
    
    function format_value($value, $post_id = 0, $field){
        
        $_value = $value;
        
        $value = acf_format_value($value, $post_id, $field);
        
        $value = apply_filters('acfe/form/format_value',                        $value, $_value, $post_id, $field);
        $value = apply_filters('acfe/form/format_value/type=' . $field['type'], $value, $_value, $post_id, $field);
        $value = apply_filters('acfe/form/format_value/key=' . $field['key'],   $value, $_value, $post_id, $field);
        $value = apply_filters('acfe/form/format_value/name=' . $field['name'], $value, $_value, $post_id, $field);
        
        // Is Array? Fallback
        if(is_array($value)){
            
            $value = $this->format_value_array($value);
            
        }
        
        return $value;
        
    }
    
    function map_fields_values(&$data = array(), $array = array()){
        
        if(empty($array)){
            
            if(!acf_maybe_get_POST('acf'))
                return array();
            
            $array = $_POST['acf'];
            
        }
            
        foreach($array as $field_key => $value){
            
            if(!acf_is_field_key($field_key))
                continue;
            
            $field = acf_get_field($field_key);
            
            // bypass _validate_email (honeypot)
            if(!$field || !isset($field['name']) || $field['name'] === '_validate_email')
                continue;
            
            $data[] = array(
                'label' => $field['label'],
                'name'  => $field['name'],
                'key'   => $field['key'],
                'field' => $field,
                'value' => $value,
            );
            
            if(is_array($value) && !empty($value)){
                
                $this->map_fields_values($data, $value);
                
            }
            
        }
        
        return $data;
        
    }
    
    function map_field_value($content, $post_id = 0, $form = array()){
        
        // Get store
        $store = acf_get_store('acfe/form');
        
        // Store found
        if(!$store->has('data')){
	
	        $data = $this->map_fields_values();
	
	        // Set Store: ACF meta
	        $store->set('data', $data);
	        
        }
	    
	    $is_array = false;
	    
	    if(is_array($content)){
	        
	        $is_array = true;
	        
        }
	    
	    $content = acf_array($content);
	    
	    foreach($content as &$c){
		
		    // Match field_abcdef123456
		    $c = acfe_form_map_field_key($c);
		
		    // Match {field:name} {field:key}
		    $c = acfe_form_map_field($c);
		
		    // Match {fields}
		    $c = acfe_form_map_fields($c);
		
		    // Match current_post {current:post:id}
		    $c = acfe_form_map_current($c, $post_id, $form);
		
		    // Match {get_field:name} {get_field:name:123}
		    $c = acfe_form_map_get_field($c, $post_id);
		
		    // Match {query_var:name} {query_var:name:key}
		    $c = acfe_form_map_query_var($c);
	       
        }
	    
	    if($is_array)
	        return $content;
	    
	    if(isset($content[0]))
	        return $content[0];
	    
	    return false;
        
    }
	
	function map_field_value_load($content, $post_id = 0, $form = array()){
		
		$is_array = false;
		
		if(is_array($content)){
			
			$is_array = true;
			
		}
		
		$content = acf_array($content);
		
		foreach($content as &$c) {
			
			// Match current_post {current:post:id}
			$c = acfe_form_map_current($c, $post_id, $form);
			
			// Match {get_field:name} {get_field:name:123}
			$c = acfe_form_map_get_field($c, $post_id);
			
			// Match {query_var:name} {query_var:name:key}
			$c = acfe_form_map_query_var($c);
			
		}
		
		if($is_array)
			return $content;
		
		if(isset($content[0]))
			return $content[0];
		
		return false;
		
	}
    
    function filter_meta($meta, $acf){
        
        if(empty($meta) || empty($acf))
            return false;
        
        foreach($acf as $field_key => $value){
            
            if(in_array($field_key, $meta))
                continue;
            
            unset($acf[$field_key]);
            
        }
        
        return $acf;
        
    }
    
    // Allow form post_id null
    function validate_post_id($null, $post_id){
        
        if($post_id === null)
            return false;

        return $null;
        
    }
    
    /**
     *  List: Columns
     */
    function admin_columns($columns){
        
        if(isset($columns['date']))
            unset($columns['date']);
        
        $columns['name'] = __('Name');
        $columns['field_groups'] = __('Field groups', 'acf');
        $columns['actions'] = __('Actions');
        $columns['shortcode'] = __('Shortcode');
        
        return $columns;
        
    }
    
    /**
     *  List: Columns HTML
     */
    function admin_columns_html($column, $post_id){
        
        // Name
        if($column == 'name'){
            
            echo '<code style="font-size: 12px;">' . get_field('acfe_form_name', $post_id) . '</code>';
            
        }
        
        // Field groups
        elseif($column == 'field_groups'){
            
            $field_groups = get_field('acfe_form_field_groups', $post_id);
            
            if(empty($field_groups)){
                
                echo '—';
                return;
                
            }
            
            $fg = array();
            
            foreach($field_groups as $field_group_key){
                
                acf_disable_filters();
                
                    $field_group = acf_get_field_group($field_group_key);
                
                acf_enable_filters();
                
                $fg[] = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
                
            }
            
            echo implode(', ', $fg);
            
        }
        
        // Actions
        elseif($column == 'actions'){
            
            $customs = $emails = $posts = $terms = $users = array();
            $found = false;
            
            if(have_rows('acfe_form_actions', $post_id)):
                while(have_rows('acfe_form_actions', $post_id)): the_row();
                
                    // Custom
                    if(get_row_layout() === 'custom'){
                        
                        $action_name = get_sub_field('acfe_form_custom_action');
                        
                        $customs[] = '<span class="acf-js-tooltip dashicons dashicons-editor-code" title="Custom action: ' . $action_name . '"></span>';
                        $found = true;
                        
                    }
                
                    // E-mail
                    elseif(get_row_layout() === 'email'){
                        
                        $emails[] = '<span class="acf-js-tooltip dashicons dashicons-email" title="E-mail"></span>';
                        $found = true;
                        
                    }
                    
                    // Post
                    elseif(get_row_layout() === 'post'){
                        
                        $action = get_sub_field('acfe_form_post_action');
                        
                        // Insert
                        if($action === 'insert_post'){
                            
                            $posts[] = '<span class="acf-js-tooltip dashicons dashicons-edit" title="Create post"></span>';
                            $found = true;
                            
                        }
                        
                        // Update
                        elseif($action === 'update_post'){
                            
                            $posts[] = '<span class="acf-js-tooltip dashicons dashicons-update" title="Update post"></span>';
                            $found = true;
                            
                        }
                        
                    }
                    
                    // Term
                    elseif(get_row_layout() === 'term'){
                        
                        $action = get_sub_field('acfe_form_term_action');
                        
                        // Insert
                        if($action === 'insert_term'){
                            
                            $terms[] = '<span class="acf-js-tooltip dashicons dashicons-category" title="Create term"></span>';
                            $found = true;
                            
                        }
                        
                        // Update
                        elseif($action === 'update_term'){
                            
                            $terms[] = '<span class="acf-js-tooltip dashicons dashicons-category" title="Update term"></span>';
                            $found = true;
                            
                        }
                        
                    }
                    
                    // User
                    elseif(get_row_layout() === 'user'){
                        
                        $action = get_sub_field('acfe_form_user_action');
                        
                        // Insert
                        if($action === 'insert_user'){
                            
                            $users[] = '<span class="acf-js-tooltip dashicons dashicons-admin-users" title="Create user"></span>';
                            $found = true;
                            
                        }
                        
                        // Update
                        elseif($action === 'update_user'){
                            
                            $users[] = '<span class="acf-js-tooltip dashicons dashicons-admin-users" title="Update user"></span>';
                            $found = true;
                            
                        }
                        
                    }
                
                endwhile;
            endif;
            
            if(!empty($customs))
                echo implode('', $customs);
            
            if(!empty($emails))
                echo implode('', $emails);
            
            if(!empty($posts))
                echo implode('', $posts);
            
            if(!empty($terms))
                echo implode('', $terms);
            
            if(!empty($users))
                echo implode('', $users);
            
            if(!$found)
                echo '—';
            
        }
        
        // Field groups
        elseif($column == 'shortcode'){
            
            echo '<code style="font-size: 12px;">[acfe_form name="' . get_field('acfe_form_name', $post_id) . '"]</code>';
            
        }
        
    }
    
    function doc_validation_field($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
        
        <pre>/**
 * Perform custom validation on a field's value
 * Reference: <a href="https://www.advancedcustomfields.com/resources/acf-validate_value/" target="_blank">https://www.advancedcustomfields.com/resources/acf-validate_value/</a>
 */
add_filter(&apos;acf/validate_value/name=my_field&apos;, &apos;my_field_validation&apos;, 10, 4);
function my_field_validation($valid, $value, $field, $input){
    
    if(!$valid)
        return $valid;
    
    if($value === &apos;Hello&apos;)
        $valid = &apos;Hello is not allowed&apos;;
    
    return $valid;
    
}</pre>
        <?php
        
    }
    
    function doc_validation_form($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
        
        <pre>/**
 * @array       $form       Form arguments
 * @int/string  $post_id    Current post id
 */
add_action(&apos;acfe/form/validation/form=<?php echo $form_name; ?>&apos;, &apos;my_form_validation&apos;, 10, 2);
function my_form_validation($form, $post_id){
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field(&apos;my_field&apos;);
    $my_field_unformatted = get_field(&apos;my_field&apos;, false, false);
    
    if($my_field === &apos;Hello&apos;){
        
        // Add validation error
        acfe_add_validation_error(&apos;my_field&apos;, &apos;Hello is not allowed&apos;);
        
        // Add general validation error
        acfe_add_validation_error(&apos;&apos;, &apos;There is an error&apos;);
        
    }
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $post_my_field = get_field(&apos;my_field&apos;, 145);
    $post_my_field_unformatted = get_field(&apos;my_field&apos;, 145, false);
    
}</pre>
        <?php
        
    }
    
    function doc_submission($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
        
        <pre>
add_action(&apos;acfe/form/submit&apos;, &apos;my_form_submit&apos;, 10, 2);
add_action(&apos;acfe/form/submit/form=<?php echo $form_name; ?>&apos;, &apos;my_form_submit&apos;, 10, 2);</pre>

<br />
        
        <pre>/**
 * @array       $form       Form arguments
 * @int/string  $post_id    Current post id
 */
add_action(&apos;acfe/form/submit/form=<?php echo $form_name; ?>&apos;, &apos;my_form_submit&apos;, 10, 2);
function my_form_submit($form, $post_id){
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field(&apos;my_field&apos;);
    $my_field_unformatted = get_field(&apos;my_field&apos;, false, false);
    
    if($my_field === &apos;Hello&apos;){
        
        // Do something
        
    }
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $post_my_field = get_field(&apos;my_field&apos;, 145);
    $post_my_field_unformatted = get_field(&apos;my_field&apos;, 145, false);
    
}</pre>
        <?php
        
    }
    
    function doc_field($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{field:field_5e5c07b6dfae9}</code></td>
                    <td>User input</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{field:my_field}</code></td>
                    <td>User input</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{field:my_field:false}</code></td>
                    <td>User input (unformatted)</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_fields($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{fields}</code></td>
                    <td>My text: User input<br /><br />My textarea: User input<br /><br />My date: 2020-03-01</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_get_field($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{get_field:my_field}</code></td>
                    <td>DB value (current post)</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{get_field:my_field:current}</code></td>
                    <td>DB value (current post)</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{get_field:my_field:128}</code></td>
                    <td>DB value (post:128)</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{get_field:my_field:128:false}</code></td>
                    <td>DB value (post:128 - unformatted)</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_query_var($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:name}</code></td>
                    <td>value</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:name:key}</code></td>
                    <td>Array value</td>
                </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-post-action:ID}</code></td>
                    <td>128</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-post-action:post_title}</code></td>
                    <td>Title</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-post-action:permalink}</code></td>
                    <td><?php echo home_url('my-post'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-post-action:...}</code></td>
                    <td>(See <code>{current:post}</code> tags)</td>
                </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-term-action:ID}</code></td>
                    <td>23</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-term-action:name}</code></td>
                    <td>Term</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-term-action:permalink}</code></td>
                    <td><?php echo home_url('taxonomy/term'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-term-action:...}</code></td>
                    <td>(See <code>{current:term}</code> tags)</td>
                </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-user-action:ID}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-user-action:user_email}</code></td>
                    <td>user@domain.com</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-user-action:display_name}</code></td>
                    <td>John Doe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-user-action:...}</code></td>
                    <td>(See <code>{current:user}</code> tags)</td>
                </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-email-action:from}</code></td>
                    <td>Website <email@domain.com></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-email-action:to}</code></td>
                    <td>email@domain.com</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-email-action:subject}</code></td>
                    <td>Subject</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{query_var:my-user-action:content}</code></td>
                    <td>Content</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_current_post($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post}</code></td>
                    <td>128</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:ID}</code></td>
                    <td>128</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_author}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_date}</code></td>
                    <td>2020-03-01 20:07:48</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_date_gmt}</code></td>
                    <td>2020-03-01 19:07:48</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_content}</code></td>
                    <td>Content</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_title}</code></td>
                    <td>Title</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_excerpt}</code></td>
                    <td>Excerpt</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:permalink}</code></td>
                    <td><?php echo home_url('my-post'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:admin_url}</code></td>
                    <td><?php echo admin_url('post.php?post=128&action=edit'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_status}</code></td>
                    <td>publish</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:comment_status}</code></td>
                    <td>closed</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:ping_status}</code></td>
                    <td>closed</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_password}</code></td>
                    <td>password</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_name}</code></td>
                    <td>name</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:to_ping}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:pinged}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_modified}</code></td>
                    <td>2020-03-01 20:07:48</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_modified_gmt}</code></td>
                    <td>2020-03-01 19:07:48</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_content_filtered}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_parent}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:guid}</code></td>
                    <td><?php echo home_url('?page_id=128'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:menu_order}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_type}</code></td>
                    <td>page</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:post_mime_type}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:comment_count}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:post:filter}</code></td>
                    <td>raw</td>
                </tr>
                
            </tbody>
        </table>
        <?php
    }
    
    function doc_current_term($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term}</code></td>
                    <td>23</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:ID}</code></td>
                    <td>23</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:term_id}</code></td>
                    <td>23</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:name}</code></td>
                    <td>Term</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:slug}</code></td>
                    <td>term</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:permalink}</code></td>
                    <td><?php echo home_url('taxonomy/term'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:admin_url}</code></td>
                    <td><?php echo admin_url('term.php?tag_ID=23'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:term_group}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:term_taxonomy_id}</code></td>
                    <td>23</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:taxonomy}</code></td>
                    <td>taxonomy</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:description}</code></td>
                    <td>Content</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:parent}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:count}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:term:filter}</code></td>
                    <td>raw</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_current_user($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:ID}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_login}</code></td>
                    <td>login</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_pass}</code></td>
                    <td>password_hash</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_nicename}</code></td>
                    <td>nicename</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_email}</code></td>
                    <td>user@domain.com</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_url}</code></td>
                    <td>https://www.website.com</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:permalink}</code></td>
                    <td><?php echo home_url('author/johndoe'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:admin_url}</code></td>
                    <td><?php echo admin_url('user-edit.php?user_id=1'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_registered}</code></td>
                    <td>2020-02-22 22:10:02</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_activation_key}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:user_status}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:display_name}</code></td>
                    <td>John Doe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:nickname}</code></td>
                    <td>JohnDoe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:first_name}</code></td>
                    <td>John</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:last_name}</code></td>
                    <td>Doe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:description}</code></td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:rich_editing}</code></td>
                    <td>true</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:syntax_highlighting}</code></td>
                    <td>true</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:comment_shortcuts}</code></td>
                    <td>false</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:admin_color}</code></td>
                    <td>fresh</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:use_ssl}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:show_admin_bar_front}</code></td>
                    <td>true</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:locale}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:wp_capabilities}</code></td>
                    <td>a:1:{s:13:"administrator";b:1;}</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:wp_user_level}</code></td>
                    <td>10</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:dismissed_wp_pointers}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:show_welcome_panel}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:user:show_welcome_panel}</code></td>
                    <td>1</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_current_author($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:ID}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_login}</code></td>
                    <td>login</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_pass}</code></td>
                    <td>password_hash</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_nicename}</code></td>
                    <td>nicename</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_email}</code></td>
                    <td>user@domain.com</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_url}</code></td>
                    <td>https://www.website.com</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:permalink}</code></td>
                    <td><?php echo home_url('author/johndoe'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:admin_url}</code></td>
                    <td><?php echo admin_url('user-edit.php?user_id=1'); ?></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_registered}</code></td>
                    <td>2020-02-22 22:10:02</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_activation_key}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:user_status}</code></td>
                    <td>0</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:display_name}</code></td>
                    <td>John Doe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:nickname}</code></td>
                    <td>JohnDoe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:first_name}</code></td>
                    <td>John</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:last_name}</code></td>
                    <td>Doe</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:description}</code></td>
                    <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:rich_editing}</code></td>
                    <td>true</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:syntax_highlighting}</code></td>
                    <td>true</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:comment_shortcuts}</code></td>
                    <td>false</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:admin_color}</code></td>
                    <td>fresh</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:use_ssl}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:show_admin_bar_front}</code></td>
                    <td>true</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:locale}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:wp_capabilities}</code></td>
                    <td>a:1:{s:13:"administrator";b:1;}</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:wp_user_level}</code></td>
                    <td>10</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:dismissed_wp_pointers}</code></td>
                    <td></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:show_welcome_panel}</code></td>
                    <td>1</td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:author:show_welcome_panel}</code></td>
                    <td>1</td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function doc_current_form($field){
        ?>
        <table class="acf-table">
            <tbody>
                <tr class="acf-row">
                    <td width="35%"><code>{current:form}</code></td>
                    <td>11<br/></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:form:ID}</code></td>
                    <td>11<br/></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:form:title}</code></td>
                    <td>Form<br/></td>
                </tr>
                <tr class="acf-row">
                    <td width="35%"><code>{current:form:name}</code></td>
                    <td>form<br/></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
}

// initialize
acfe()->acfe_form = new acfe_form();

endif;

function acfe_form_render_fields($content, $post_id, $args){
    
    return acfe()->acfe_form->render_fields($content, $post_id, $args);
    
}

function acfe_form_map_field_value($field, $post_id = 0, $form = array()){
    
    return acfe()->acfe_form->map_field_value($field, $post_id, $form);
    
}

function acfe_form_map_field_value_load($field, $post_id = 0, $form = array()){
    
    return acfe()->acfe_form->map_field_value_load($field, $post_id, $form);
    
}

function acfe_form_filter_meta($meta, $acf){
    
    return acfe()->acfe_form->filter_meta($meta, $acf);
    
}

function acfe_form_format_value($value, $post_id = 0, $field){
    
    return acfe()->acfe_form->format_value($value, $post_id, $field);
    
}

// Match field_abcdef123456
function acfe_form_map_field_key($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(!acf_is_field_key($content))
        return $content;
    
    // Setup data
    $data = array();
    
    $store = acf_get_store('acfe/form');
    
    if($store->has('data')){
        
        $data = $store->get('data');
        
    }
    
    if(!empty($data)){
        
        foreach($data as $field){
            
            if($field['key'] !== $content)
                continue;
            
            return $field['value'];
            
        }
        
    }
    
    return false;
    
}

function acfe_form_map_current($content, $post_id = 0, $form = array()){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    // init
    $value = 0;
    
    // Post
    $post = acf_get_post_id_info($post_id);
    
    // Match current_post
    if($content === 'current_post'){
        
        if($post['type'] === 'post')
            $value = $post['id'];
        
        return $value;
        
    }
    
    // Match current_post_parent
    elseif($content === 'current_post_parent'){
        
        if($post['type'] === 'post')
            $value = get_post_field('post_parent', $post['id']);
        
        return $value;
        
    }
    
    // Match current_post_author
    elseif($content === 'current_post_author'){
        
        if($post['type'] === 'post')
            $value = get_post_field('post_author', $post['id']);
        
        return $value;
        
    }
    
    // Match current_term
    if($content === 'current_term'){
        
        if($post['type'] === 'term')
            $value = $post['id'];
        
        return $value;
        
    }
    
    // Match current_term_parent
    elseif($content === 'current_term_parent'){
        
        if($post['type'] === 'term')
            $value = get_term_field('parent', $post['id']);
        
        return $value;
        
    }
    
    // Match current_user
    elseif($content === 'current_user'){
        
        return get_current_user_id();
        
    }
    
    // Match generate_password
    elseif($content === 'generate_password'){
        
        return wp_generate_password(8, false);
        
    }
    
    // Match {current:post:id}
    elseif(strpos($content, '{current:') !== false){
        
        // Match {query_var:name}
        if(preg_match_all('/{current:(.*?)}/', $content, $matches)){
            
            foreach($matches[1] as $i => $name){
                
                $value = false;
                
                if(strpos($name, ':') !== false){
                
                    $explode = explode(':', $name);
                    
                    $type = $explode[0]; // post, term, user
                    $field = $explode[1]; // id, post_parent, post_title
                    
                    // {current:post:id}
                    if($type === 'post' && $post['type'] === 'post'){
                        
                        // id
                        if(strtolower($field) === 'id' || strtolower($field) === 'post_id'){
                            
                            $value = $post['id'];
                            
                        }
                        
                        // permalink
                        elseif(strtolower($field) === 'permalink'){
                            
                            $value = get_permalink($post['id']);
                            
                        }
                        
                        // admin url
                        elseif(strtolower($field) === 'admin_url'){
                            
                            $value = admin_url('post.php?post=' . $post['id'] . '&action=edit');
                            
                        }
                        
                        // other
                        else{
                            
                            $value = get_post_field($field, $post['id']);
                            
                        }
                        
                    }
                    
                    // {current:term:id}
                    elseif($type === 'term' && $post['type'] === 'term'){
                        
                        // id
                        if(strtolower($field) === 'id' || strtolower($field) === 'term_id'){
                            
                            $value = $post['id'];
                            
                        }
                        
                        // permalink
                        elseif(strtolower($field) === 'permalink'){
                            
                            $value = get_term_link($post['id']);
                            
                        }
                        
                        // admin url
                        elseif(strtolower($field) === 'admin_url'){
                            
                            $value = admin_url('term.php?tag_ID=' . $post['id']);
                            
                        }
                        
                        // other
                        else{
                            
                            $value = get_term_field($field, $post['id']);
                            
                        }
                        
                    }
                    
                    // {current:user:id}
                    elseif($type === 'user'){
                        
                        if(is_user_logged_in()){
                            
                            $user_id = get_current_user_id();
                            
                            // id
                            if(strtolower($field) === 'id' || strtolower($field) === 'user_id'){
                                
                                $value = $user_id;
                                
                            }
                            
                            // permalink
                            elseif(strtolower($field) === 'permalink'){
                                
                                $value = get_author_posts_url($user_id);
                                
                            }
                            
                            // admin url
                            elseif(strtolower($field) === 'admin_url'){
                                
                                $value = admin_url('user-edit.php?user_id=' . $user_id);
                                
                            }
                            
                            // other
                            else{
                            
                                $value = false;
                                
                                $user_object = get_user_by('ID', $user_id);
                                
                                if(isset($user_object->data)){
                                    
                                    // return array
                                    $user = json_decode(json_encode($user_object->data), true);
                                    
                                    $user_object_meta = get_user_meta($user_id);
                                    
                                    $user_meta = array();
                                    
                                    foreach($user_object_meta as $k => $v){
                                        
                                        if(!isset($v[0]))
                                            continue;
                                        
                                        $user_meta[$k] = $v[0];
                                        
                                    }
                                    
                                    $user = array_merge($user, $user_meta);
                                    
                                    $value = acf_maybe_get($user, $field);
                                
                                }
                            
                            }
                        
                        }
                        
                    }
                    
                    // {current:author:id}
                    elseif($type === 'author' && $post['type'] === 'post'){
                        
                        $user_id = get_post_field('post_author', $post['id']);
                        
                        if($user_id){
                            
                            // id
                            if(strtolower($field) === 'id' || strtolower($field) === 'user_id'){
                                
                                $value = $user_id;
                                
                            }
                            
                            // permalink
                            elseif(strtolower($field) === 'permalink'){
                                
                                $value = get_author_posts_url($user_id);
                                
                            }
                            
                            // admin url
                            elseif(strtolower($field) === 'admin_url'){
                                
                                $value = admin_url('user-edit.php?user_id=' . $user_id);
                                
                            }
                            
                            // other
                            else{
                            
                                $value = false;
                                
                                $user_object = get_user_by('ID', $user_id);
                                
                                if(isset($user_object->data)){
                                    
                                    // return array
                                    $user = json_decode(json_encode($user_object->data), true);
                                    
                                    $user_object_meta = get_user_meta($user_id);
                                    
                                    $user_meta = array();
                                    
                                    foreach($user_object_meta as $k => $v){
                                        
                                        if(!isset($v[0]))
                                            continue;
                                        
                                        $user_meta[$k] = $v[0];
                                        
                                    }
                                    
                                    $user = array_merge($user, $user_meta);
                                    
                                    $value = acf_maybe_get($user, $field);
                                
                                }
                            
                            }
                        
                        }
                        
                        
                    }
                    
                    // {current:form:id}
                    elseif($type === 'form'){
                        
                        if(strtolower($field) === 'id' || strtolower($field) === 'form_id'){
                            
                            $value = acf_maybe_get($form, 'form_id');
                            
                        }
                        
                        elseif(strtolower($field) === 'name' || strtolower($field) === 'form_name'){
                            
                            $value = acf_maybe_get($form, 'form_name');
                            
                        }
                        
                        elseif(strtolower($field) === 'title' || strtolower($field) === 'form_title'){
                            
                            $form_id = acf_maybe_get($form, 'form_id');
                            
                            if($form_id)
                                $value = get_the_title($form_id);
                            
                        }else{
                            
                            $value = acf_maybe_get($form, $field);
                            
                        }
                        
                    }
                
                }
                
                // {current:post}
                elseif($name === 'post' && $post['type'] === 'post'){
                    
                    $value = $post['id'];
                    
                }
                
                // {current:term}
                elseif($name === 'term' && $post['type'] === 'term'){
                    
                    $value = $post['id'];
                    
                }
                
                // {current:user}
                elseif($name === 'user'){
                    
                    $value = get_current_user_id();
                    
                }
                
                // {current:author}
                elseif($name === 'author' && $post['type'] === 'post'){
                    
                    $value = get_post_field('post_author', $post['id']);
                    
                }
                
                // {current:form}
                elseif($name === 'form'){
                    
                    $value = acf_maybe_get($form, 'form_id');
                    
                }
                
                $content = str_replace('{current:' . $name . '}', $value, $content);
                
            }
            
        }
        
    }
    
    return $content;
    
}

// Match {query_var:name} {query_var:name:key}
function acfe_form_map_query_var($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{query_var:') === false)
        return $content;
    
    // Match {query_var:name}
    if(preg_match_all('/{query_var:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            $query_var = get_query_var($name);
            
            if(strpos($name, ':') !== false){
                
                $explode = explode(':', $name);
                
                $query_var = get_query_var($explode[0]);
                
                if(is_array($query_var) && isset($query_var[$explode[1]])){
                    
                    $query_var = $query_var[$explode[1]];
                    
                }
                
            }
            
            $content = str_replace('{query_var:' . $name . '}', $query_var, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {get_field:name} {get_field:name:123}
function acfe_form_map_get_field($content, $post_id = 0){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{get_field:') === false)
        return $content;
    
    // Match {get_field:name}
    if(preg_match_all('/{get_field:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            if(strpos($name, ':') === false){
                
                $get_field = get_field($name, $post_id);
                
            }else{
                
                $explode = explode(':', $name);
                
                // Field
                $field = $explode[0];
                
                // ID
                $id = $explode[1];
                
                if($id === 'current')
                    $id = $post_id;
                
                // Format
                $format = true;
                
                if(acf_maybe_get($explode, 2) === 'false')
                    $format = false;
                
                $get_field = get_field($field, $id, $format);
                
            }
            
            $content = str_replace('{get_field:' . $name . '}', $get_field, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {field:name} {field:key}
function acfe_form_map_field($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{field:') === false)
        return $content;
    
    if(preg_match_all('/{field:(.*?)}/', $content, $matches)){
        
        // Setup data
        $data = array();
        
        $store = acf_get_store('acfe/form');
        
        if($store->has('data')){
            
            $data = $store->get('data');
            
        }
        
        foreach($matches[1] as $i => $field_key){
            
            $format = true;
            
            if(strpos($field_key, ':') !== false){
            
                $explode = explode(':', $field_key);
                
                $field_key = $explode[0]; // field_123abc
                $format = $explode[1]; // true / false
                
                if($format === 'false')
                    $format = false;
                
            }

            if(!empty($data)){
                
                foreach($data as $field){
                    
                    if($field['name'] !== $field_key && $field['key'] !== $field_key)
                        continue;
                    
                    // Value
                    $value = $field['value'];
                    
                    if($format)
                        $value = acfe_form_format_value($field['value'], 0, $field['field']);
                    
                    // Replace
                    $content = str_replace('{field:' . $field_key . '}', $value, $content);
                    
                    break;
                    
                }
                
            }
            
            // Fallback (clean)
            $content = str_replace('{field:' . $field_key . '}', '', $content);
            
        }
        
    }
    
    // Return
    return $content;
    
}

// Match {fields}
function acfe_form_map_fields($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{fields}') === false)
        return $content;
    
    // Match {fields}
    if(preg_match('/{fields}/', $content, $matches)){
        
        // Setup data
        $data = array();
        
        $store = acf_get_store('acfe/form');
        
        if($store->has('data')){
            
            $data = $store->get('data');
            
        }
        
        $content_html = '';
        
        if(!empty($data)){
            
            foreach($data as $field){
                
                // Label
                $label = !empty($field['label']) ? $field['label'] : $field['name'];
                
                // Value
                $value = acfe_form_format_value($field['value'], 0, $field['field']);
                
                // Add
                $content_html .= $label . ': ' . $value . "<br/>\n";
                
            }
            
        }
        
        // Replace
        $content = str_replace('{fields}', $content_html, $content);
        
    }
    
    // Return
    return $content;
    
}

function acfe_form_map_vs_fields($map, $fields, $post_id = 0, $form = array()){
    
    $return = array();
    
    foreach($map as $mkey => $mval){
        
        if(empty($mval))
            continue;
        
        $return[$mkey] = acfe_form_map_field_value($mval, $post_id, $form);
        
    }
    
    foreach($fields as $fkey => $fvalue){
        
        if(isset($return[$fkey]))
            continue;
        
        $return[$fkey] = acfe_form_map_field_value($fvalue, $post_id, $form);
        
    }
    
    return $return;
    
}