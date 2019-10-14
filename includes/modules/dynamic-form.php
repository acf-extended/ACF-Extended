<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form')):

class acfe_form{
	
	public $fields_groups = array();
    
	function __construct(){
		
		// actions
        add_action('load-post.php',                                                 array($this, 'initialize'));
        add_action('init',                                                          array($this, 'register_post_type'));
        add_action('admin_menu',                                                    array($this, 'register_menu'));
        add_action('acf/save_post',                                                 array($this, 'save_form'), 20);
        add_action('pre_get_posts',                                                 array($this, 'admin_list'));
        
        // Enqueue / Redirect
        add_action('template_redirect',                                             array($this, 'init'));
        
        // Validation
        add_action('acf/validate_save_post',                                        array($this, 'validate'), 4);
        
        // Submit
        add_action('acf/submit_form',                                               array($this, 'submit'), 5, 2);
        
        // Submit: Actions
        add_action('acfe/form/submit',                                              array($this, 'submit_actions'), 0, 2);
		
		// filters
		add_filter('acf/get_post_types',                                            array($this, 'filter_post_type'), 10, 2);
        add_filter('edit_posts_per_page',                                           array($this, 'admin_ppp'), 10, 2);
        add_filter('acf/prepare_field/name=acfe_form_actions',                      array($this, 'prepare_actions'));
        add_filter('acf/prepare_field/name=acfe_form_email_files',                  array($this, 'prepare_email_files'));
        add_filter('acf/prepare_field/name=acfe_form_field_groups',                 array($this, 'field_groups_choices'));

        add_filter('acf/prepare_field/name=acfe_form_option_name',                  array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_create_post_type',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_create_post_status',      array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_create_post_title',       array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_create_post_name',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_create_post_content',     array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_create_post_author',      array($this, 'map_fields_deep'));

        add_filter('acf/prepare_field/name=acfe_form_post_update_post_id',          array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_update_post_type',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_update_post_status',      array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_update_post_title',       array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_update_post_name',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_update_post_content',     array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_update_post_author',      array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_create_email',            array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_username',         array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_password',         array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_first_name',       array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_last_name',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_nickname',         array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_display_name',     array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_create_role',             array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_create_name',             array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_create_slug',             array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_create_parent',           array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_create_description',      array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_create_taxonomy',         array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_update_term_id',          array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_update_name',             array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_update_slug',             array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_update_parent',           array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_update_description',      array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_update_taxonomy',         array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_update_user_id',          array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_email',            array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_email_load',       array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_username',         array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_password',         array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_first_name',       array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_last_name',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_nickname',         array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_display_name',     array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_update_role',             array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_email_file',                   array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_option_meta',                  array($this, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_post_meta',                    array($this, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_user_meta',                    array($this, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_term_meta',                    array($this, 'map_fields'));
		
        add_filter('acf/pre_load_post_id',                                          array($this, 'validate_post_id'), 10, 2);
        add_filter('gettext',                                                       array($this, 'error_translation'), 99, 3);
        
        add_filter('manage_edit-acfe-form_columns',                                 array($this, 'form_admin_columns'));
        add_action('manage_acfe-form_posts_custom_column',                          array($this, 'form_admin_columns_html'), 10, 2);
        
        // Shortcode
        add_shortcode('acfe_form',                                                  array($this, 'add_shortcode'));
        
	}
    
    function initialize(){
        
        // globals
		global $typenow;
        
        // ACFE Form
		if($typenow !== 'acfe-form')
            return;
    
        // vars
        $this->fields_groups = $this->get_fields_groups();
        
        add_action('add_meta_boxes',        array($this, 'add_meta_boxes'));
        
        add_filter('acf/pre_render_fields', array($this, 'render_integration'), 10, 2);
        
    }
    
    function register_post_type(){
        
        // ACFE Form
        register_post_type('acfe-form', array(
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
            )
        ));
        
    }
    
    function register_menu(){
        
        if(!acf_get_setting('show_admin'))
            return;
        
        add_submenu_page('edit.php?post_type=acf-field-group', __('Forms', 'acf'), __('Forms', 'acf'), acf_get_setting('capability'), 'edit.php?post_type=acfe-form');
        
        add_filter('parent_file', array($this, 'register_menu_highlight'));
        
    }
    
    
    function register_menu_highlight($parent_file){
        
        global $pagenow;
        
        if($pagenow !== 'post.php' && $pagenow !== 'post-new.php')
            return $parent_file;
        
        $post_type = get_post_type();
        
        if($post_type !== 'acfe-form')
            return $parent_file;
        
        return 'edit.php?post_type=acf-field-group';
        
    }
    
    function filter_post_type($post_types, $args){
        
        if(empty($post_types))
            return $post_types;
        
        foreach($post_types as $k => $post_type){
            
            if($post_type !== 'acfe-form')
                continue;
            
            unset($post_types[$k]);
            
        }
        
        return $post_types;
        
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
        
        if(get_post_type($post_id) !== 'acfe-form')
            return;
        
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
    
    /**
     * Admin: List
     */
    function admin_list($query){
        
        if(!is_admin() || !$query->is_main_query())
            return;
        
        global $pagenow;
        if($pagenow !== 'edit.php')
            return;
        
        $post_type = $query->get('post_type');
        if($post_type != 'acfe-form')
            return;
        
        if(!isset($_REQUEST['orderby']) || empty($_REQUEST['orderby']))
            $query->set('orderby', 'name');
        
        if(!isset($_REQUEST['order']) || empty($_REQUEST['order']))
            $query->set('order', 'ASC');
        
    }
    
    /**
     * Admin Posts Per Page
     */
    function admin_ppp($ppp, $post_type){
        
        if($post_type !== 'acfe-form')
            return $ppp;
        
        global $pagenow;
        if($pagenow != 'edit.php')
            return $ppp;
        
        return 999;
        
    }
    
    function add_meta_boxes(){
        
        $data = $this->fields_groups;
        
        if(empty($data))
            return;
        
        add_meta_box(
        
            // ID
            'acfe-form-details', 
            
            // Title
            __('Fields', 'acf'), 
            
            // Render
            array($this, 'render_meta_boxes'), 
            
            // Screen
            'acfe-form', 
            
            // Position
            'normal', 
            
            // Priority
            'default'
            
        );
        
    }

    function render_meta_boxes($array, $data){
        
        foreach($this->fields_groups as $field_group){ ?>
            
            <div class="acf-field">
        
                <div class="acf-label">
                    <label for="acf-_post_title"><a href="<?php echo admin_url('post.php?post=' . $field_group['ID'] . '&action=edit'); ?>"><?php echo $field_group['title']; ?></a></label>
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
    
    function prepare_actions($field){
        
        $field['instructions'] = 'Add actions on form submission';
        
        $data = $this->fields_groups;
        
        if(empty($data)){
            
            $field['instructions'] .= '<br /><u>No field groups are currently mapped</u>';
            
        }
        
        return $field;
        
    }
    
    function prepare_email_files($field){
        
        $data = $this->fields_groups;
        
        if(empty($data))
            return false;
        
        return $field;
        
    }
    
    
    function map_fields_deep($field){
        
        $choices = array();
        
        if(!empty($field['choices']))
            $choices['Generic'] = $field['choices'];
        
        $fields_choices = $this->get_fields_choices(true);
        
        if(!empty($fields_choices)){
            
            $field['choices'] = array_merge($choices, $fields_choices);
            
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

    function get_fields_choices($deep = false){
        
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
                        
                        $this->get_fields_choices_recursive($choices[$field_group_title], $field);
                        
                    }
                    
                }
                
            }
            
        }
        
        return $choices;
        
    }

    function get_fields_choices_recursive(&$choices, $field){
        
        $label = '';
        
        $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
        $label = str_repeat('- ', $ancestors) . $label;
        
        $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
        $label .= $field['required'] ? ' *' : '';
        
        $choices[$field['key']] = $label. ' (' . $field['key'] . ')';
        
        if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
            
            foreach($field['sub_fields'] as $s_field){
                
                $this->get_fields_choices_recursive($choices, $s_field);
                
            }
            
        }
        
    }
    
    function form($param){
        
        // String
        if(is_string($param)){
            
            $form = get_page_by_path($param, OBJECT, 'acfe-form');
            if(!$form)
                return false;
            
            // Form
            $form_id = $form->ID;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // Int
        elseif(is_int($param)){
            
            if(get_post_type($param) !== 'acfe-form')
                return false;
            
            // Form
            $form_id = $param;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // Array
        elseif(is_array($param)){
            
            $param = wp_parse_args($param, array(
                'acfe_form_id'      => false,
                'acfe_form_name'    => false
            ));
            
            if(!$param['acfe_form_id'] && !$param['acfe_form_name'])
                return false;
            
            $valid = false;
            
            if($param['acfe_form_id']){
                
                if(get_post_type((int)$param['acfe_form_id']) === 'acfe-form'){
                    
                    // Form
                    $form_id = $param['acfe_form_id'];
                    $form_name = get_field('acfe_form_name', $form_id);
                    
                    $param['acfe_form_name'] = $form_name;
                    
                    $valid = true;
                    
                }
                
            }
            
            if(!$valid && $param['acfe_form_name']){
                
                $get_form = get_posts(array(
                    'post_type'         => 'acfe-form',
                    'posts_per_page'    => 1,
                    'fields'            => 'ids',
                    'post_name__in'     => array($param['acfe_form_name'])
                ));
                
                if(!empty($get_form)){
                    
                    // Form
                    $form_id = $get_form[0];
                    $form_name = $param['acfe_form_name'];
                    
                    $param['acfe_form_id'] = $form_id;
                    
                    $valid = true;
                    
                }
                
            }
            
            if(!$valid)
                return false;
            
        }
        
        // ACF Args
        $args = array();
        
        // ACFE Form
        $args['acfe_form_id'] = $form_id;
        $args['acfe_form_name'] = $form_name;
        
        // Field Groups
        $args['field_groups'] = get_field('acfe_form_field_groups', $form_id);
        
        // General
        $args['form'] = get_field('acfe_form_form_element', $form_id);
        $args['form_attributes']['class'] = 'acfe-form';
        $args['form_attributes']['id'] = '';
        
        if(!empty($args['form'])){
            
            $form_attributes = get_field('acfe_form_attributes', $form_id);
            
            $args['form_attributes']['class'] .= ' ' . $form_attributes['acfe_form_attributes_class'];
            $args['form_attributes']['id'] = $form_attributes['acfe_form_attributes_id'];
            
        }
        
        $acfe_form_fields_attributes = get_field('acfe_form_fields_attributes', $form_id);
        
        $args['fields_wrapper_class'] = $acfe_form_fields_attributes['acfe_form_fields_wrapper_class'];
        $args['fields_class'] = $acfe_form_fields_attributes['acfe_form_fields_class'];
        
        if(!empty($args['fields_class']))
            $args['form_attributes']['data-acfe-form-fields-class'] = $args['fields_class'];
        
        $args['html_before_fields'] = get_field('acfe_form_html_before_fields', $form_id);
        $args['custom_html'] = get_field('acfe_form_custom_html', $form_id);
        $args['html_after_fields'] = get_field('acfe_form_html_after_fields', $form_id);
        $args['form_submit'] = get_field('acfe_form_form_submit', $form_id);
        $args['submit_value'] = get_field('acfe_form_submit_value', $form_id);
        $args['html_submit_button'] = get_field('acfe_form_html_submit_button', $form_id);
        $args['html_submit_spinner'] = get_field('acfe_form_html_submit_spinner', $form_id);
        
        // Validation
        $args['errors_position'] = get_field('acfe_form_errors_position', $form_id);
        
        if(!empty($args['errors_position']))
            $args['form_attributes']['data-acfe-form-errors-position'] = $args['errors_position'];
        
        $args['errors_class'] = get_field('acfe_form_errors_class', $form_id);
        
        if(!empty($args['errors_class']))
            $args['form_attributes']['data-acfe-form-errors-class'] = $args['errors_class'];
        
        // Submission
        $args['updated_message'] = get_field('acfe_form_updated_message', $form_id);
        $args['html_updated_message'] = get_field('acfe_form_html_updated_message', $form_id);
        $args['updated_hide_form'] = get_field('acfe_form_updated_hide_form', $form_id);
        $args['return'] = get_field('acfe_form_return', $form_id);
        
        if(empty($args['return']))
            $args['return'] = add_query_arg('updated', 'true', acf_get_current_url());
        
        // Advanced
        $args['honeypot'] = get_field('acfe_form_honeypot', $form_id);
        $args['kses'] = get_field('acfe_form_kses', $form_id);
        $args['uploader'] = get_field('acfe_form_uploader', $form_id);
        $args['form_field_el'] = get_field('acfe_form_form_field_el', $form_id);
        $args['label_placement'] = get_field('acfe_form_label_placement', $form_id);
        $args['instruction_placement'] = get_field('acfe_form_instruction_placement', $form_id);
        
        // Default behavior: No save, no update.
        $args['post_id'] = null;
        
        // Fields mapping
        $args['map'] = array();
        
        // Actions
        if(have_rows('acfe_form_actions', $form_id)):
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                // Option
                if(get_row_layout() === 'option'){

                    $acfe_form_option_load = get_sub_field('acfe_form_option_load');
                    
                    if(empty($acfe_form_option_load))
                        continue;
                    
                    $_option_name_group = get_sub_field('acfe_form_option_name_group');
                    $_option_name = $_option_name_group['acfe_form_option_name'];
                    $_option_name_custom = $_option_name_group['acfe_form_option_name_custom'];
                    
                    // var
                    $_post_id = $args['post_id'];
                    
                    // Custom
                    if($_option_name === 'custom'){
                        
                        $_post_id = $this->map_text_value_get_field($_option_name_custom);
                        
                    // Field
                    }elseif(acf_is_field_key($_option_name)){
                        
                        $_post_id = get_field($_option_name);
                    
                    }
                    
                    $_post_id = apply_filters('acfe/form/load/option_name',                      $_post_id, $args);
                    $_post_id = apply_filters('acfe/form/load/option_name/name=' . $form_name,   $_post_id, $args);
                    $_post_id = apply_filters('acfe/form/load/option_name/id=' . $form_id,       $_post_id, $args);
                    
                    // ID
                    $args['post_id'] = $_post_id;
                    
                }
            
                // Post
                if(get_row_layout() === 'post'){
                    
                    // Behavior
                    $post_behavior = get_sub_field('acfe_form_post_behavior');
                    
                    // Update Post
                    if($post_behavior === 'update_post'){

                        $acfe_form_post_update_load = get_sub_field('acfe_form_post_update_load');
                        
                        if(empty($acfe_form_post_update_load))
                            continue;
                        
                        $_post_id_group = get_sub_field('acfe_form_post_update_post_id_group');
                        $_post_id_data = $_post_id_group['acfe_form_post_update_post_id'];
                        $_post_id_custom = $_post_id_group['acfe_form_post_update_post_id_custom'];
                        
                        $_post_type = get_sub_field('acfe_form_post_update_post_type');
                        $_post_status = get_sub_field('acfe_form_post_update_post_status');
                        
                        $_post_title_group = get_sub_field('acfe_form_post_update_post_title_group');
                        $_post_title = $_post_title_group['acfe_form_post_update_post_title'];
                        
                        $_post_name_group = get_sub_field('acfe_form_post_update_post_name_group');
                        $_post_name = $_post_name_group['acfe_form_post_update_post_name'];
                        
                        $_post_content_group = get_sub_field('acfe_form_post_update_post_content_group');
                        $_post_content = $_post_content_group['acfe_form_post_update_post_content'];
                        
                        $_post_author_group = get_sub_field('acfe_form_post_update_post_author_group');
                        $_post_author = $_post_author_group['acfe_form_post_update_post_author'];
                        
                        // var
                        $_post_id = $args['post_id'];
                        
                        // Current post
                        if($_post_id_data === 'current_post'){
                            
                            $_post_id = acf_get_valid_post_id();
                        
                        // Custom Post ID
                        }elseif($_post_id_data === 'custom_post_id'){
                            
                            $_post_id = $this->map_text_value_get_field($_post_id_custom);
                        
                        // Field
                        }elseif(acf_is_field_key($_post_id_data)){
                            
                            $_post_id = get_field($_post_id_data);
                        
                        }
                        
                        $_post_id = apply_filters('acfe/form/load/post_id',                      $_post_id, $args);
                        $_post_id = apply_filters('acfe/form/load/post_id/name=' . $form_name,   $_post_id, $args);
                        $_post_id = apply_filters('acfe/form/load/post_id/id=' . $form_id,       $_post_id, $args);
                        
                        // ID
                        $args['post_id'] = $_post_id;
                        
                        // Post type
                        if(acf_is_field_key($_post_type)){
                            
                            $args['map'][$_post_type]['value'] = get_post_field('post_type', $_post_id);
                            
                        }
                        
                        // Post status
                        if(acf_is_field_key($_post_status)){
                            
                            $args['map'][$_post_status]['value'] = get_post_field('post_status', $_post_id);
                            
                        }
                        
                        // Post title
                        if(acf_is_field_key($_post_title)){
                            
                            $args['map'][$_post_title]['value'] = get_post_field('post_title', $_post_id);
                            
                        }
                        
                        // Post name
                        if(acf_is_field_key($_post_name)){
                            
                            $args['map'][$_post_name]['value'] = get_post_field('post_name', $_post_id);
                            
                        }
                        
                        // Post content
                        if(acf_is_field_key($_post_content)){
                            
                            $args['map'][$_post_content]['value'] = get_post_field('post_content', $_post_id);
                            
                        }
                        
                        // Post author
                        if(acf_is_field_key($_post_author)){
                            
                            $args['map'][$_post_author]['value'] = get_post_field('post_author', $_post_id);
                            
                        }
                        
                    }
                    
                }
                
                // Term
                if(get_row_layout() === 'term'){
                    
                    // Behavior
                    $term_behavior = get_sub_field('acfe_form_term_behavior');
                    
                    // Update Post
                    if($term_behavior === 'update_term'){

                        $acfe_form_term_update_load = get_sub_field('acfe_form_term_update_load');
                        
                        if(empty($acfe_form_term_update_load))
                            continue;
                        
                        $_term_id_group = get_sub_field('acfe_form_term_update_term_id_group');
                        $_term_id_data = $_term_id_group['acfe_form_term_update_term_id'];
                        $_term_id_custom = $_term_id_group['acfe_form_term_update_term_id_custom'];
                        
                        $_term_name_group = get_sub_field('acfe_form_term_update_name_group');
                        $_term_name = $_term_name_group['acfe_form_term_update_name'];
                        
                        $_term_slug_group = get_sub_field('acfe_form_term_update_slug_group');
                        $_term_slug = $_term_slug_group['acfe_form_term_update_slug'];
                        
                        $_term_taxonomy = get_sub_field('acfe_form_term_update_taxonomy');
                        
                        $_term_parent_group = get_sub_field('acfe_form_term_update_parent_group');
                        $_term_parent = $_term_parent_group['acfe_form_term_update_parent'];
                        
                        $_term_description_group = get_sub_field('acfe_form_term_update_description_group');
                        $_term_description = $_term_description_group['acfe_form_term_update_description'];
                        
                        // var
                        $_post_id = $args['post_id'];
                        
                        // Current post
                        if($_term_id_data === 'current_term'){
                            
                            $_post_id = get_current_object_id();
                        
                        // Custom Post ID
                        }elseif($_term_id_data === 'custom_term_id'){
                            
                            $_post_id = $this->map_text_value_get_field($_term_id_custom);
                        
                        // Field
                        }elseif(acf_is_field_key($_term_id_data)){
                            
                            $_post_id = get_field($_term_id_data);
                        
                        }
                        
                        $_post_id = apply_filters('acfe/form/load/term_id',                      $_post_id, $args);
                        $_post_id = apply_filters('acfe/form/load/term_id/name=' . $form_name,   $_post_id, $args);
                        $_post_id = apply_filters('acfe/form/load/term_id/id=' . $form_id,       $_post_id, $args);
                        
                        // ID
                        $args['post_id'] = $_post_id;
                        
                        // Name
                        if(acf_is_field_key($_term_name)){
                            
                            $args['map'][$_term_name]['value'] = get_term_field('name', $_post_id);
                            
                        }
                        
                        // Slug
                        if(acf_is_field_key($_term_slug)){
                            
                            $args['map'][$_term_slug]['value'] = get_term_field('slug', $_post_id);
                            
                        }
                        
                        // Taxonomy
                        if(acf_is_field_key($_term_taxonomy)){
                            
                            $args['map'][$_term_taxonomy]['value'] = get_term_field('taxonomy', $_post_id);
                            
                        }
                        
                        // Parent
                        if(acf_is_field_key($_term_parent)){
                            
                            $args['map'][$_term_parent]['value'] = get_term_field('parent', $_post_id);
                            
                        }
                        
                        // Description
                        if(acf_is_field_key($_term_description)){
                            
                            $args['map'][$_term_description]['value'] = get_term_field('description', $_post_id);
                            
                        }
                        
                    }
                    
                }
                
                // User
                if(get_row_layout() === 'user'){
                    
                    // Behavior
                    $user_behavior = get_sub_field('acfe_form_user_behavior');
                    
                    // Update User
                    if($user_behavior === 'update_user'){

                        $acfe_form_user_update_load = get_sub_field('acfe_form_user_update_load');
                        
                        if(empty($acfe_form_user_update_load))
                            continue;
                        
                        $_user_id_data_group = get_sub_field('acfe_form_user_update_user_id_group');
                        $_user_id_data = $_user_id_data_group['acfe_form_user_update_user_id'];
                        $_user_id_data_custom = $_user_id_data_group['acfe_form_user_update_user_id_custom'];
                        
                        $_user_email = get_sub_field('acfe_form_user_update_email');
                        $_user_username = get_sub_field('acfe_form_user_update_username');
                        $_user_password = get_sub_field('acfe_form_user_update_password');
                        
                        $_user_first_name_group = get_sub_field('acfe_form_user_update_first_name_group');
                        $_user_first_name = $_user_first_name_group['acfe_form_user_update_first_name'];
                        
                        $_user_last_name_group = get_sub_field('acfe_form_user_update_last_name_group');
                        $_user_last_name = $_user_last_name_group['acfe_form_user_update_last_name'];
                        
                        $_user_nickname_group = get_sub_field('acfe_form_user_update_nickname_group');
                        $_user_nickname = $_user_nickname_group['acfe_form_user_update_nickname'];
                        
                        $_user_display_name_group = get_sub_field('acfe_form_user_update_display_name_group');
                        $_user_display_name = $_user_display_name_group['acfe_form_user_update_display_name'];
                        
                        $_user_role = get_sub_field('acfe_form_user_update_role');
                        
                        // var
                        $_user_id = $args['post_id'];
                        
                        // Current post
                        if($_user_id_data === 'current_user'){
                            
                            $_user_id = get_current_user_id();
                        
                        // Custom Post ID
                        }elseif($_user_id_data === 'custom_user_id'){
                            
                            $_user_id = $_user_id_data_custom;
                        
                        }elseif(acf_is_field_key($_user_id_data)){
                            
                            $_user_id = get_field($_user_id_data);
                        
                        }
                        
                        $_user_id = apply_filters('acfe/form/load/user_id',                      $_user_id, $args);
                        $_user_id = apply_filters('acfe/form/load/user_id/name=' . $form_name,   $_user_id, $args);
                        $_user_id = apply_filters('acfe/form/load/user_id/id=' . $form_id,       $_user_id, $args);
                        
                        $user_data = get_userdata($_user_id);
                        
                        if(!empty($user_data)){
                            
                            // ID
                            $args['post_id'] = 'user_' . $_user_id;
                            
                            // Email
                            if(acf_is_field_key($_user_email)){
                                
                                $args['map'][$_user_email]['value'] = $user_data->user_email;
                                
                            }
                            
                            // Username
                            if(acf_is_field_key($_user_username)){
                                
                                $args['map'][$_user_username]['value'] = $user_data->user_login;
                                $args['map'][$_user_username]['maxlength'] = 60;
                                
                            }
                            
                            // Password
                            if(acf_is_field_key($_user_password)){
                                
                                //$args['map'][$_user_password]['value'] = $user_data->user_pass;
                                
                            }
                            
                            // First name
                            if(acf_is_field_key($_user_first_name)){
                                
                                $args['map'][$_user_first_name]['value'] = $user_data->first_name;
                                
                            }
                            
                            // Last name
                            if(acf_is_field_key($_user_last_name)){
                                
                                $args['map'][$_user_last_name]['value'] = $user_data->last_name;
                                
                            }
                            
                            // Nickname
                            if(acf_is_field_key($_user_nickname)){
                                
                                $args['map'][$_user_nickname]['value'] = $user_data->nickname;
                                
                            }
                            
                            // Display name
                            if(acf_is_field_key($_user_display_name)){
                                
                                $args['map'][$_user_display_name]['value'] = $user_data->display_name;
                                
                            }
                            
                            // Role
                            if(acf_is_field_key($_user_role)){
                                
                                $args['map'][$_user_role]['value'] = $user_data->role;
                                
                            }
                        
                        }
                        
                    }
                    
                }
                
            endwhile;
        endif;
        
        $args['map'] = apply_filters('acfe/form/load/fields',                       $args['map']);
        $args['map'] = apply_filters('acfe/form/load/fields/name=' . $form_name,    $args['map']);
        $args['map'] = apply_filters('acfe/form/load/fields/id=' . $form_id,        $args['map']);
        
        // Let user bypass default form settings
        if(is_array($param)){
            
            $args = array_replace_recursive($args, $param);
            
        }
        
        // ACF Form
        acf_form($args);
        
    }
    
    function render_fields($content, $post_id, $args){
        
        // Mapping
        $form_id = $args['acfe_form_id'];
        $form_name = $args['acfe_form_name'];
        
        $mapped_field_groups = $this->get_fields_groups($form_id);
        $mapped_fields = array();
        
        if(!empty($mapped_field_groups)){
            
            foreach($mapped_field_groups as $field_group){
                
                if(empty($field_group['fields']))
                    continue;
                
                foreach($field_group['fields'] as $field){
                    
                    $mapped_fields[] = $field;
                    
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
                
                acf_render_fields($fields, $post_id, $args['field_el'], $args['instruction_placement']);
                
                $render_field = ob_get_clean();
                
                $content = str_replace('{field:' . $field_key . '}', $render_field, $content);
                
            }
            
        }
        
        // Match {field_group:key}
        if(preg_match_all('/{field_group:(.*?)}/', $content, $matches)){
            
            $field_groups = acf_get_field_groups();
            
            foreach($matches[1] as $i => $field_group_key){
                
                $fields = false;
                
                // Field group key
                if(strpos($field_group_key, 'group_') === 0){
                    
                    $fields = acf_get_fields($field_group_key);
                
                // Field group title
                }else{
                    
                    if(!empty($field_groups)){
                        
                        foreach($field_groups as $field_group){
                            
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
                
                acf_render_fields($fields, $post_id, $args['field_el'], $args['instruction_placement']);
                
                $render_fields = ob_get_clean();
                
                $content = str_replace('{field_group:' . $field_group_key . '}', $render_fields, $content);
                
            }
            
        }
        
        return $content;
        
    }
    
    function format_value($value, $field){
        
        $return = acf_format_value($value, false, $field);
        
        // Post Object & Relationship
        if($field['type'] == 'post_object' || $field['type'] == 'relationship'){
            
            $value = acf_get_array($value);
            $array = array();
            
            foreach($value as $post_id){
                
                $array[] = get_the_title($post_id);
                
            }
            
            $return = implode(', ', $array);
            
        }
        
        // User
        elseif($field['type'] == 'user'){
            
            $value = acf_get_array($value);
            $array = array();
            
            foreach($value as $user_id){
                
                $user_data = get_userdata($user_id);
                $array[] = $user_data->user_nicename;
                
            }
            
            $return = implode(', ', $array);
            
        }
        
        // Taxonomy
        elseif($field['type'] == 'taxonomy'){
            
            $value = acf_get_array($value);
            $array = array();
            
            foreach($value as $term_id){
                
                $term = get_term($term_id);
                $array[] = $term->name;
                
            }
            
            $return = implode(', ', $array);
            
        }
        
        return $return;
        
    }
    
    function map_fields_values($array, &$data = array()){
        
        if(empty($array))
            return false;
            
        foreach($array as $field_key => $value){
            
            $field = acf_get_field($field_key);
            
            // bypass _validate_email (honeypot)
            if(!$field || !isset($field['name']) || $field['name'] === '_validate_email')
                continue;
            
            $data[] = array(
                'name'  => $field['name'],
                'key'   => $field['key'],
                'value' => $this->format_value($value, $field),
            );
            
            if(is_array($value)){
                
                $this->map_fields_values($value, $data);
                
            }
            
        }
        
        return $data;
        
    }
    
    function map_text_value($content, $acf = false){
        
        if(!$acf)
            $acf = $_POST['acf'];

        if(!$acf)
            return false;
        
        $data = $this->map_fields_values($acf);
        
        // Match {field:key}
        if(preg_match_all('/{field:(.*?)}/', $content, $matches)){
            
            foreach($matches[1] as $i => $field_key){

                if(!empty($data)){
                    
                    foreach($data as $field){
                        
                        if($field['name'] !== $field_key && $field['key'] !== $field_key)
                            continue;
                            
                        $content = str_replace('{field:' . $field_key . '}', $field['value'], $content);
                        break;
                        
                    }
                    
                }
                
                $content = str_replace('{field:' . $field_key . '}', '', $content);
                
            }
            
        }
        
        // Match {fields}
        if(preg_match('/{fields}/', $content, $matches)){
            
            $content_html = '';
            
            if(!empty($data)){
                
                foreach($data as $field){
                    
                    $content_html .= $field['name'] . ': ' . $field['value'] . "<br/>\n";
                    
                }
                
            }
            
            $content = str_replace('{fields}', $content_html, $content);
            
        }
        
        return $content;
        
    }
    
    function map_field_value($field_key, $acf = false){
        
        if(!$acf)
            $acf = $_POST['acf'];

        if(!$acf)
            return false;
        
        $data = $this->map_fields_values($acf);
        
        if(empty($data))
            return false;
        
        foreach($data as $field){
            
            if($field['key'] !== $field_key)
                continue;
            
            return $field['value'];
            
        }
        
    }
    
    function map_text_value_get_field($content){
        
        // Match {field:key}
        if(preg_match_all('/{field:(.*?)}/', $content, $matches)){
            
            foreach($matches[1] as $i => $field_key){
                
                $value = get_field($field_key);
                $content = str_replace('{field:' . $field_key . '}', $value, $content);
                
            }
            
        }
        
        return $content;
        
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
    
    function init(){
        
        if(!acf_maybe_get_POST('_acf_form') || !acf_maybe_get_POST('_acf_nonce'))
			return;
        
        acf()->form_front->check_submit_form();
        
    }
    
    function validate(){
        
        if(!acfe_form_is_front())
            return;
        
		if(!acf_maybe_get_POST('_acf_form'))
            return;
        
    	$form = json_decode(acf_decrypt($_POST['_acf_form']), true);
        
        if(empty($form))
            return;
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        $post_id = acf_maybe_get($form, '_acf_post_id');
        
        if(!$form_name || !$form_id)
            return;
        
        acf_setup_meta($_POST['acf'], 'acfe_form_validation', true);
        
            do_action('acfe/form/validation',                       $form, $post_id);
            do_action('acfe/form/validation/name=' . $form_name,    $form, $post_id);
            do_action('acfe/form/validation/id=' . $form_id,        $form, $post_id);
        
        acf_reset_meta('acfe_form_validation');
        
    }
    
    // Form submission
    function submit($form, $post_id){
        
        if(!acfe_form_is_front())
            return;
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        if(!$form_name || !$form_id)
            return;
        
        acf_setup_meta($_POST['acf'], 'acfe_form_submit', true);
        
            do_action('acfe/form/submit',                       $form, $post_id);
            do_action('acfe/form/submit/name=' . $form_name,    $form, $post_id);
            do_action('acfe/form/submit/id=' . $form_id,        $form, $post_id);
        
        acf_reset_meta('acfe_form_submit');
        
    }
    
    function submit_actions($form, $post_id){
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        // Actions
        if(have_rows('acfe_form_actions', $form_id)):
        
            $acf = array();
            
            // ACF $_POST
            if(isset($_POST['acf']))
                $acf = $_POST['acf'];
            
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                // Option
                if(get_row_layout() === 'option'){
                    
                    $_option_name_group = get_sub_field('acfe_form_option_name_group');
                    $_option_name = $_option_name_group['acfe_form_option_name'];
                    $_option_name_custom = $_option_name_group['acfe_form_option_name_custom'];
                    
                    // var
                    $_post_id = false;
                    
                    // Current post
                    if($_option_name === 'custom'){
                        
                        $_post_id = $this->map_text_value($_option_name_custom, $acf);
                        
                    // Field
                    }elseif(acf_is_field_key($_option_name)){
                        
                        $_post_id = $this->map_field_value($_option_name, $acf);
                        
                    }
                    
                    do_action('acfe/form/submit/option',                       $form, $_post_id);
                    do_action('acfe/form/submit/option/name=' . $form_name,    $form, $_post_id);
                    do_action('acfe/form/submit/option/id=' . $form_id,        $form, $_post_id);
                    
                    // Meta save
                    $_meta = get_sub_field('acfe_form_option_meta');
                    
                    $data = $this->filter_meta($_meta, $acf);
                    
                    if(!empty($data)){
                        
                        // Save meta fields
                        acf_save_post($_post_id, $data);
                    
                    }
                    
                }
                
                // Post
                if(get_row_layout() === 'post'){
                    
                    // Behavior
                    $post_behavior = get_sub_field('acfe_form_post_behavior');
                    
                    // Create Post
                    if($post_behavior === 'create_post'){
                        
                        $_post_type = get_sub_field('acfe_form_post_create_post_type');
                        $_post_status = get_sub_field('acfe_form_post_create_post_status');
                        
                        $_post_title_group = get_sub_field('acfe_form_post_create_post_title_group');
                        $_post_title = $_post_title_group['acfe_form_post_create_post_title'];
                        $_post_title_custom = $_post_title_group['acfe_form_post_create_post_title_custom'];
                        
                        $_post_name_group = get_sub_field('acfe_form_post_create_post_name_group');
                        $_post_name = $_post_name_group['acfe_form_post_create_post_name'];
                        $_post_name_custom = $_post_name_group['acfe_form_post_create_post_name_custom'];
                        
                        $_post_content_group = get_sub_field('acfe_form_post_create_post_content_group');
                        $_post_content = $_post_content_group['acfe_form_post_create_post_content'];
                        $_post_content_custom = $_post_content_group['acfe_form_post_create_post_content_custom'];
                        
                        $_post_author_group = get_sub_field('acfe_form_post_create_post_author_group');
                        $_post_author = $_post_author_group['acfe_form_post_create_post_author'];
                        $_post_author_custom = $_post_author_group['acfe_form_post_create_post_author_custom'];
                        
                        // Insert Post
                        $_post_id = wp_insert_post(array(
                            'post_title' => 'post'
                        ));
                        
                        $args = array();
                        
                        // ID
                        $args['ID'] = $_post_id;
                        
                        // Post type
                        $args['post_type'] = $_post_type;
                        
                        if(acf_is_field_key($_post_type)){
                            
                            $args['post_type'] = $this->map_field_value($_post_type, $acf);
                            
                        }
                        
                        // Post status
                        $args['post_status'] = $_post_status;
                        
                        if(acf_is_field_key($_post_status)){
                            
                            $args['post_status'] = $this->map_field_value($_post_status, $acf);
                            
                        }
                        
                        // Post title
                        $args['post_title'] = $_post_id;
                        
                        if(acf_is_field_key($_post_title)){
                            
                            $args['post_title'] = $this->map_field_value($_post_title, $acf);
                            
                        }elseif($_post_title === 'custom'){
                            
                            $args['post_title'] = $this->map_text_value($_post_title_custom, $acf);
                            
                        }
                        
                        // Post name
                        $args['post_name'] = $args['post_title'];
                        
                        if(acf_is_field_key($_post_name)){
                            
                            $args['post_name'] = $this->map_field_value($_post_name, $acf);
                            
                        }elseif($_post_name === 'generated_id'){
                            
                            $args['post_name'] = $_post_id;
                            
                        }elseif($_post_name === 'custom'){
                            
                            $args['post_name'] = $this->map_text_value($_post_name_custom, $acf);
                            
                        }
                        
                        // Post content
                        if(acf_is_field_key($_post_content)){
                            
                            $args['post_content'] = $this->map_field_value($_post_content, $acf);
                            
                        }elseif($_post_content === 'custom'){
                            
                            $args['post_content'] = $this->map_text_value($_post_content_custom, $acf);
                            
                        }
                        
                        // Post author
                        if($_post_author === 'current_user'){
                            
                            $args['post_author'] = get_current_user_id();
                            
                        }elseif($_post_author === 'custom_user_id'){
                            
                            $args['post_author'] = $_post_author_custom;
                            
                        }elseif(acf_is_field_key($_post_author)){
                            
                            $args['post_author'] = $this->map_field_value($_post_author, $acf);
                            
                        }
                        
                        $args = apply_filters('acfe/form/submit/insert_post_args',                      $args, $form, $_post_id);
                        $args = apply_filters('acfe/form/submit/insert_post_args/name=' . $form_name,   $args, $form, $_post_id);
                        $args = apply_filters('acfe/form/submit/insert_post_args/id=' . $form_id,       $args, $form, $_post_id);
                        
                        if($args === false)
                            continue;
                        
                        // Update Post
                        $_post_id = wp_update_post($args);
                        
                        do_action('acfe/form/submit/insert_post',                       $form, $_post_id, $args);
                        do_action('acfe/form/submit/insert_post/name=' . $form_name,    $form, $_post_id, $args);
                        do_action('acfe/form/submit/insert_post/id=' . $form_id,        $form, $_post_id, $args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_post_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post($_post_id, $data);
                        
                        }
                        
                    }
                    
                    // Update Post
                    elseif($post_behavior === 'update_post'){
                        
                        $_post_id_group = get_sub_field('acfe_form_post_update_post_id_group');
                        $_post_id_data = $_post_id_group['acfe_form_post_update_post_id'];
                        $_post_id_custom = $_post_id_group['acfe_form_post_update_post_id_custom'];
                        
                        $_post_type = get_sub_field('acfe_form_post_update_post_type');
                        $_post_status = get_sub_field('acfe_form_post_update_post_status');
                        
                        $_post_title_group = get_sub_field('acfe_form_post_update_post_title_group');
                        $_post_title = $_post_title_group['acfe_form_post_update_post_title'];
                        $_post_title_custom = $_post_title_group['acfe_form_post_update_post_title_custom'];
                        
                        $_post_name_group = get_sub_field('acfe_form_post_update_post_name_group');
                        $_post_name = $_post_name_group['acfe_form_post_update_post_name'];
                        $_post_name_custom = $_post_name_group['acfe_form_post_update_post_name_custom'];
                        
                        $_post_content_group = get_sub_field('acfe_form_post_update_post_content_group');
                        $_post_content = $_post_content_group['acfe_form_post_update_post_content'];
                        $_post_content_custom = $_post_content_group['acfe_form_post_update_post_content_custom'];
                        
                        $_post_author_group = get_sub_field('acfe_form_post_update_post_author_group');
                        $_post_author = $_post_author_group['acfe_form_post_update_post_author'];
                        $_post_author_custom = $_post_author_group['acfe_form_post_update_post_author_custom'];
                        
                        // var
                        $_post_id = false;
                        
                        // Current post
                        if($_post_id_data === 'current_post'){
                            
                            $_post_id = acf_get_valid_post_id();
                        
                        // Custom Post ID
                        }elseif($_post_id_data === 'custom_post_id'){
                            
                            $_post_id = $_post_id_custom;
                        
                        // Field
                        }elseif(acf_is_field_key($_post_id_data)){
                            
                            $_post_id = $this->map_field_value($_post_id_data, $acf);
                            
                        }
                        
                        $args = array();
                        
                        // ID
                        $args['ID'] = $_post_id;
                        
                        // Post type
                        if(!empty($_post_type)){
                            
                            if(acf_is_field_key($_post_type)){
                                
                                $args['post_type'] = $this->map_field_value($_post_type, $acf);
                                
                            }else{
                                
                                $args['post_type'] = $_post_type;
                                
                            }
                            
                        }
                        
                        // Post status
                        if(!empty($_post_status)){
                            
                            if(acf_is_field_key($_post_status)){
                                
                                $args['post_status'] = $this->map_field_value($_post_status, $acf);
                                
                            }else{
                                
                                $args['post_status'] = $_post_status;
                                
                            }
                            
                        }
                        
                        // Post title
                        if(!empty($_post_title)){
                            
                            if(acf_is_field_key($_post_title)){
                                
                                $args['post_title'] = $this->map_field_value($_post_title, $acf);
                                
                            }elseif($_post_title === 'generated_id'){
                                
                                $args['post_title'] = $_post_id;
                                
                            }elseif($_post_title === 'custom'){
                                
                                $args['post_title'] = $this->map_text_value($_post_title_custom, $acf);
                                
                            }
                            
                        }
                        
                        // Post name
                        if(!empty($_post_name)){
                            
                            if(acf_is_field_key($_post_name)){
                                
                                $args['post_name'] = $this->map_field_value($_post_name, $acf);
                                
                            }elseif($_post_name === 'generated_id'){
                                
                                $args['post_name'] = $_post_id;
                                
                            }elseif($_post_name === 'custom'){
                                
                                $args['post_name'] = $this->map_text_value($_post_name_custom, $acf);
                                
                            }
                            
                        }
                        
                        // Post content
                        if(!empty($_post_content)){
                            
                            if(acf_is_field_key($_post_content)){
                                
                                $args['post_content'] = $this->map_field_value($_post_content, $acf);
                                
                            }elseif($_post_content === 'custom'){
                                
                                $args['post_content'] = $this->map_text_value($_post_content_custom, $acf);
                                
                            }
                            
                        }
                        
                        // Post author
                        if(!empty($_post_author)){
                            
                            if($_post_author === 'current_user'){
                                
                                $args['post_author'] = get_current_user_id();
                                
                            }elseif($_post_author === 'custom_user_id'){
                                
                                $args['post_author'] = $_post_author_custom;
                                
                            }elseif(acf_is_field_key($_post_author)){
                                
                                $args['post_author'] = $this->map_field_value($_post_author, $acf);
                                
                            }
                            
                        }
                        
                        $args = apply_filters('acfe/form/submit/update_post_args',                      $args, $form, $_post_id);
                        $args = apply_filters('acfe/form/submit/update_post_args/name=' . $form_name,   $args, $form, $_post_id);
                        $args = apply_filters('acfe/form/submit/update_post_args/id=' . $form_id,       $args, $form, $_post_id);
                        
                        if($args === false)
                            continue;
                        
                        // Update Post
                        $_post_id = wp_update_post($args);
                        
                        do_action('acfe/form/submit/update_post',                       $form, $_post_id, $args);
                        do_action('acfe/form/submit/update_post/name=' . $form_name,    $form, $_post_id, $args);
                        do_action('acfe/form/submit/update_post/id=' . $form_id,        $form, $_post_id, $args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_post_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post($_post_id, $data);
                        
                        }
                        
                    }
                    
                }
                
                // Term
                if(get_row_layout() === 'term'){
                    
                    // Behavior
                    $term_behavior = get_sub_field('acfe_form_term_behavior');
                    
                    // Create Term
                    if($term_behavior === 'create_term'){
                        
                        $_term_name_group = get_sub_field('acfe_form_term_create_name_group');
                        $_term_name = $_term_name_group['acfe_form_term_create_name'];
                        $_term_name_custom = $_term_name_group['acfe_form_term_create_name_custom'];
                        
                        $_term_slug_group = get_sub_field('acfe_form_term_create_slug_group');
                        $_term_slug = $_term_slug_group['acfe_form_term_create_slug'];
                        $_term_slug_custom = $_term_slug_group['acfe_form_term_create_slug_custom'];
                        
                        $_term_taxonomy = get_sub_field('acfe_form_term_create_taxonomy');
                        
                        $_term_parent_group = get_sub_field('acfe_form_term_create_parent_group');
                        $_term_parent = $_term_parent_group['acfe_form_term_create_parent'];
                        $_term_parent_custom = $_term_parent_group['acfe_form_term_create_parent_custom'];
                        
                        $_term_description_group = get_sub_field('acfe_form_term_create_description_group');
                        $_term_description = $_term_description_group['acfe_form_term_create_description'];
                        $_term_description_custom = $_term_description_group['acfe_form_term_create_description_custom'];
                        
                        $args = array();
                        
                        // Name
                        $args['name'] = '';
                        
                        if(acf_is_field_key($_term_name)){
                            
                            $args['name'] = $this->map_field_value($_term_name, $acf);
                            
                        }elseif($_term_name === 'custom'){
                            
                            $args['name'] = $this->map_text_value($_term_name_custom, $acf);
                            
                        }
                        
                        // Taxonomy
                        $args['taxonomy'] = $_term_taxonomy;
                        
                        if(acf_is_field_key($_term_taxonomy)){
                            
                            $args['taxonomy'] = $this->map_field_value($_term_taxonomy, $acf);
                            
                        }
                        
                        // Args
                        
                        // Slug
                        if(acf_is_field_key($_term_slug)){
                            
                            $args['slug'] = $this->map_field_value($_term_slug, $acf);
                            
                        }elseif($_term_slug === 'custom'){
                            
                            $args['slug'] = $this->map_text_value($_term_slug_custom, $acf);
                            
                        }
                        
                        // Parent
                        if(acf_is_field_key($_term_parent)){
                            
                            $args['parent'] = $this->map_field_value($_term_parent, $acf);
                            
                        }elseif($_term_parent === 'custom'){
                            
                            $args['parent'] = $this->map_text_value($_term_parent_custom, $acf);
                            
                        }
                        
                        // Description
                        if(acf_is_field_key($_term_description)){
                            
                            $args['description'] = $this->map_field_value($_term_description, $acf);
                            
                        }elseif($_term_description === 'custom'){
                            
                            $args['description'] = $this->map_text_value($_term_description_custom, $acf);
                            
                        }
                        
                        $args = apply_filters('acfe/form/submit/insert_term_args',                      $args, $form);
                        $args = apply_filters('acfe/form/submit/insert_term_args/name=' . $form_name,   $args, $form);
                        $args = apply_filters('acfe/form/submit/insert_term_args/id=' . $form_id,       $args, $form);
                        
                        if($args === false)
                            continue;
                        
                        // Insert Term
                        $_term_return = wp_insert_term($args['name'], $args['taxonomy'], $args);
                        
                        if(is_wp_error($_term_return))
                            continue;
                        
                        $_term_id = $_term_return['term_id'];
                        
                        do_action('acfe/form/submit/insert_term',                       $form, $_term_id, $args);
                        do_action('acfe/form/submit/insert_term/name=' . $form_name,    $form, $_term_id, $args);
                        do_action('acfe/form/submit/insert_term/id=' . $form_id,        $form, $_term_id, $args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_term_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post('term_' . $_term_id, $data);
                        
                        }
                        
                    }
                    
                    // Update Term
                    elseif($term_behavior === 'update_term'){
                        
                        $_term_id_data_group = get_sub_field('acfe_form_term_update_term_id_group');
                        $_term_id_data = $_term_id_data_group['acfe_form_term_update_term_id'];
                        $_term_id_data_custom = $_term_id_data_group['acfe_form_term_update_term_id_custom'];
                        
                        $_term_name_group = get_sub_field('acfe_form_term_update_name_group');
                        $_term_name = $_term_name_group['acfe_form_term_update_name'];
                        $_term_name_custom = $_term_name_group['acfe_form_term_update_name_custom'];
                        
                        $_term_slug_group = get_sub_field('acfe_form_term_update_slug_group');
                        $_term_slug = $_term_slug_group['acfe_form_term_update_slug'];
                        $_term_slug_custom = $_term_slug_group['acfe_form_term_update_slug_custom'];
                        
                        $_term_taxonomy = get_sub_field('acfe_form_term_update_taxonomy');
                        
                        $_term_parent_group = get_sub_field('acfe_form_term_update_parent_group');
                        $_term_parent = $_term_parent_group['acfe_form_term_update_parent'];
                        $_term_parent_custom = $_term_parent_group['acfe_form_term_update_parent_custom'];
                        
                        $_term_description_group = get_sub_field('acfe_form_term_update_description_group');
                        $_term_description = $_term_description_group['acfe_form_term_update_description'];
                        $_term_description_custom = $_term_description_group['acfe_form_term_update_description_custom'];
                        
                        $_term_id = false;
                        
                        // Current Term
                        if($_term_id_data === 'current_term'){
                            
                            $_term_id = get_current_object_id();
                        
                        // Custom Term ID
                        }elseif($_term_id_data === 'custom_term_id'){
                            
                            $_term_id = $this->map_text_value($_term_id_data_custom, $acf);
                        
                        // Field
                        }elseif(acf_is_field_key($_term_id_data)){
                            
                            $_term_id = $this->map_field_value($_term_id_data, $acf);
                            
                        }
                        
                        $args = array();
                        
                        $args['ID'] = $_term_id;
                        
                        // Taxonomy
                        if(!empty($_term_taxonomy)){
                            
                            $args['taxonomy'] = $_term_taxonomy;
                            
                            if(acf_is_field_key($_term_taxonomy)){
                                
                                $args['taxonomy'] = $this->map_field_value($_term_taxonomy, $acf);
                                
                            }
                            
                        }else{
                            
                            $get_term = get_term($_term_id);
                            
                            $args['taxonomy'] = $get_term->taxonomy;
                            
                        }
                        
                        // Args
                        
                        // Name
                        if(!empty($_term_name)){
                            
                            if(acf_is_field_key($_term_name)){
                                
                                $args['name'] = $this->map_field_value($_term_name, $acf);
                                
                            }elseif($_term_name === 'custom'){
                                
                                $args['name'] = $this->map_text_value($_term_name_custom, $acf);
                                
                            }
                            
                        }
                        
                        // Slug
                        if(!empty($_term_slug)){
                            
                            if(acf_is_field_key($_term_slug)){
                                
                                $args['slug'] = $this->map_field_value($_term_slug, $acf);
                                
                            }elseif($_term_slug === 'custom'){
                                
                                $args['slug'] = $this->map_text_value($_term_slug_custom, $acf);
                                
                            }
                            
                        }
                        
                        // Parent
                        if(!empty($_term_parent)){
                            
                            if(acf_is_field_key($_term_parent)){
                                
                                $args['parent'] = $this->map_field_value($_term_parent, $acf);
                                
                            }elseif($_term_parent === 'custom'){
                                
                                $args['parent'] = $this->map_text_value($_term_parent_custom, $acf);
                                
                            }
                        
                        }
                        
                        // Description
                        if(!empty($_term_description)){
                            
                            if(acf_is_field_key($_term_description)){
                                
                                $args['description'] = $this->map_field_value($_term_description, $acf);
                                
                            }elseif($_term_description === 'custom'){
                                
                                $args['description'] = $this->map_text_value($_term_description_custom, $acf);
                                
                            }
                            
                        }
                        
                        
                        $args = apply_filters('acfe/form/submit/update_term_args',                      $args, $form, $_term_id);
                        $args = apply_filters('acfe/form/submit/update_term_args/name=' . $form_name,   $args, $form, $_term_id);
                        $args = apply_filters('acfe/form/submit/update_term_args/id=' . $form_id,       $args, $form, $_term_id);
                        
                        if($args === false)
                            continue;
                        
                        // Update Post
                        $_term_return = wp_update_term($args['ID'], $args['taxonomy'], $args);
                        
                        if(is_wp_error($_term_return))
                            continue;
                        
                        $_term_id = $_term_return['term_id'];
                        
                        do_action('acfe/form/submit/update_term',                       $form, $_term_id, $args);
                        do_action('acfe/form/submit/update_term/name=' . $form_name,    $form, $_term_id, $args);
                        do_action('acfe/form/submit/update_term/id=' . $form_id,        $form, $_term_id, $args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_term_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post('term_' . $_term_id, $data);
                        
                        }
                        
                    }
                    
                }
                
                // User
                elseif(get_row_layout() === 'user'){
                    
                    // Behavior
                    $user_behavior = get_sub_field('acfe_form_user_behavior');
                    
                    // Create User
                    if($user_behavior === 'create_user'){
                        
                        $_user_email = get_sub_field('acfe_form_user_create_email');
                        $_user_username = get_sub_field('acfe_form_user_create_username');
                        $_user_password = get_sub_field('acfe_form_user_create_password');
                        
                        $_user_first_name_group = get_sub_field('acfe_form_user_create_first_name_group');
                        $_user_first_name = $_user_first_name_group['acfe_form_user_create_first_name'];
                        $_user_first_name_custom = $_user_first_name_group['acfe_form_user_create_first_name_custom'];
                        
                        $_user_last_name_group = get_sub_field('acfe_form_user_create_last_name_group');
                        $_user_last_name = $_user_last_name_group['acfe_form_user_create_last_name'];
                        $_user_last_name_custom = $_user_last_name_group['acfe_form_user_create_last_name_custom'];
                        
                        $_user_nickname_group = get_sub_field('acfe_form_user_create_nickname_group');
                        $_user_nickname = $_user_nickname_group['acfe_form_user_create_nickname'];
                        $_user_nickname_custom = $_user_nickname_group['acfe_form_user_create_nickname_custom'];
                        
                        $_user_display_name_group = get_sub_field('acfe_form_user_create_display_name_group');
                        $_user_display_name = $_user_display_name_group['acfe_form_user_create_display_name'];
                        $_user_display_name_custom = $_user_display_name_group['acfe_form_user_create_display_name_custom'];
                        
                        $_user_role = get_sub_field('acfe_form_user_create_role');
                        
                        $args = array();
                        
                        // Email
                        $args['user_email'] = '';
                        
                        if(acf_is_field_key($_user_email)){
                            
                            $args['user_email'] = $this->map_field_value($_user_email, $acf);
                            
                        }
                        
                        // Username
                        $args['user_login'] = '';
                        
                        if(acf_is_field_key($_user_username)){
                            
                            $args['user_login'] = $this->map_field_value($_user_username, $acf);
                            
                        }
                        
                        // Password
                        $args['user_pass'] = '';
                        
                        if(acf_is_field_key($_user_password)){
                            
                            $args['user_pass'] = $this->map_field_value($_user_password, $acf);
                            
                        }elseif($_user_password === 'generate_password'){
                                
                                $args['user_pass'] = wp_generate_password(8, false);
                                
                        }
                        
                        // First name
                        if(acf_is_field_key($_user_first_name)){
                            
                            $args['first_name'] = $this->map_field_value($_user_first_name, $acf);
                            
                        }elseif($_user_first_name === 'custom'){
                            
                            $args['first_name'] = $this->map_text_value($_user_first_name_custom, $acf);
                            
                        }
                        
                        // Last name
                        if(acf_is_field_key($_user_last_name)){
                            
                            $args['last_name'] = $this->map_field_value($_user_last_name, $acf);
                            
                        }elseif($_user_last_name === 'custom'){
                            
                            $args['last_name'] = $this->map_text_value($_user_last_name_custom, $acf);
                            
                        }
                        
                        // Nickname
                        if(acf_is_field_key($_user_nickname)){
                            
                            $args['nickname'] = $this->map_field_value($_user_nickname, $acf);
                            
                        }elseif($_user_nickname === 'custom'){
                            
                            $args['nickname'] = $this->map_text_value($_user_nickname_custom, $acf);
                            
                        }
                        
                        // Display name
                        if(acf_is_field_key($_user_display_name)){
                            
                            $args['display_name'] = $this->map_field_value($_user_display_name, $acf);
                            
                        }elseif($_user_display_name === 'custom'){
                            
                            $args['display_name'] = $this->map_text_value($_user_display_name_custom, $acf);
                            
                        }
                        
                        // Role
                        if(!empty($_user_role)){
                            
                            if(acf_is_field_key($_user_role)){
                                
                                $args['role'] = $this->map_field_value($_user_role, $acf);
                                
                            }else{
                                
                                $args['role'] = $_user_role;
                                
                            }
                            
                        }
                        
                        $args = apply_filters('acfe/form/submit/insert_user_args',                      $args, $form);
                        $args = apply_filters('acfe/form/submit/insert_user_args/name=' . $form_name,   $args, $form);
                        $args = apply_filters('acfe/form/submit/insert_user_args/id=' . $form_id,       $args, $form);
                        
                        if($args === false)
                            continue;
                        
                        // User
                        $_user_id = wp_insert_user($args);
                        
                        do_action('acfe/form/submit/insert_user',                       $form, $_user_id, $args);
                        do_action('acfe/form/submit/insert_user/name=' . $form_name,    $form, $_user_id, $args);
                        do_action('acfe/form/submit/insert_user/id=' . $form_id,        $form, $_user_id, $args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_user_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post('user_' . $_user_id, $data);
                        
                        }
                        
                    }
                    
                    // Update User
                    elseif($user_behavior === 'update_user'){
                        
                        $_user_id_data_group = get_sub_field('acfe_form_user_update_user_id_group');
                        $_user_id_data = $_user_id_data_group['acfe_form_user_update_user_id'];
                        $_user_id_data_custom = $_user_id_data_group['acfe_form_user_update_user_id_custom'];
                        
                        $_user_email = get_sub_field('acfe_form_user_update_email');
                        $_user_username = get_sub_field('acfe_form_user_update_username');
                        $_user_password = get_sub_field('acfe_form_user_update_password');
                        
                        $_user_first_name_group = get_sub_field('acfe_form_user_update_first_name_group');
                        $_user_first_name = $_user_first_name_group['acfe_form_user_update_first_name'];
                        $_user_first_name_custom = $_user_first_name_group['acfe_form_user_update_first_name_custom'];
                        
                        $_user_last_name_group = get_sub_field('acfe_form_user_update_last_name_group');
                        $_user_last_name = $_user_last_name_group['acfe_form_user_update_last_name'];
                        $_user_last_name_custom = $_user_last_name_group['acfe_form_user_update_last_name_custom'];
                        
                        $_user_nickname_group = get_sub_field('acfe_form_user_update_nickname_group');
                        $_user_nickname = $_user_nickname_group['acfe_form_user_update_nickname'];
                        $_user_nickname_custom = $_user_nickname_group['acfe_form_user_update_nickname_custom'];
                        
                        $_user_display_name_group = get_sub_field('acfe_form_user_update_display_name_group');
                        $_user_display_name = $_user_display_name_group['acfe_form_user_update_display_name'];
                        $_user_display_name_custom = $_user_display_name_group['acfe_form_user_update_display_name_custom'];
                        
                        $_user_role = get_sub_field('acfe_form_user_update_role');
                        
                        // var
                        $_user_id = false;
                        
                        // Current user
                        if($_user_id_data === 'current_user'){
                            
                            $_user_id = get_current_user_id();
                        
                        // Custom User ID
                        }elseif($_user_id_data === 'custom_user_id'){
                            
                            $_user_id = $_user_id_data_custom;
                        
                        }
                        
                        $args = array();
                        
                        // ID
                        $args['ID'] = $_user_id;
                        
                        // Email
                        if(acf_is_field_key($_user_email)){
                            
                            $args['user_email'] = $this->map_field_value($_user_email, $acf);
                            
                        }
                        
                        // Username
                        if(acf_is_field_key($_user_username)){
                            
                            $args['user_login'] = $this->map_field_value($_user_username, $acf);
                            
                        }
                        
                        // Password
                        if(acf_is_field_key($_user_password)){
                            
                            $args['user_pass'] = $this->map_field_value($_user_password, $acf);
                            
                        }elseif($_user_password === 'generate_password'){
                            
                            $args['user_pass'] = wp_generate_password(8, false);
                            
                        }
                        
                        // First name
                        if(acf_is_field_key($_user_first_name)){
                            
                            $args['first_name'] = $this->map_field_value($_user_first_name, $acf);
                            
                        }elseif($_user_first_name === 'custom'){
                            
                            $args['first_name'] = $this->map_text_value($_user_first_name_custom, $acf);
                            
                        }
                        
                        // Last name
                        if(acf_is_field_key($_user_last_name)){
                            
                            $args['last_name'] = $this->map_field_value($_user_last_name, $acf);
                            
                        }elseif($_user_last_name === 'custom'){
                            
                            $args['last_name'] = $this->map_text_value($_user_last_name_custom, $acf);
                            
                        }
                        
                        // Nickname
                        if(acf_is_field_key($_user_nickname)){
                            
                            $args['nickname'] = $this->map_field_value($_user_nickname, $acf);
                            
                        }elseif($_user_nickname === 'custom'){
                            
                            $args['nickname'] = $this->map_text_value($_user_nickname_custom, $acf);
                            
                        }
                        
                        // Display name
                        if(acf_is_field_key($_user_display_name)){
                            
                            $args['display_name'] = $this->map_field_value($_user_display_name, $acf);
                            
                        }elseif($_user_display_name === 'custom'){
                            
                            $args['display_name'] = $this->map_text_value($_user_display_name_custom, $acf);
                            
                        }
                        
                        // Role
                        if(!empty($_user_role)){
                            
                            if(acf_is_field_key($_user_role)){
                                
                                $args['role'] = $this->map_field_value($_user_role, $acf);
                                
                            }else{
                                
                                $args['role'] = $_user_role;
                                
                            }
                            
                        }
                        
                        $args = apply_filters('acfe/form/submit/update_user_args',                      $args, $form, $_user_id);
                        $args = apply_filters('acfe/form/submit/update_user_args/name=' . $form_name,   $args, $form, $_user_id);
                        $args = apply_filters('acfe/form/submit/update_user_args/id=' . $form_id,       $args, $form, $_user_id);
                        
                        if($args === false)
                            continue;
                        
                        // User
                        $_user_id = wp_update_user($args);
                        
                        do_action('acfe/form/submit/update_user',                       $form, $_user_id, $args);
                        do_action('acfe/form/submit/update_user/name=' . $form_name,    $form, $_user_id, $args);
                        do_action('acfe/form/submit/update_user/id=' . $form_id,        $form, $_user_id, $args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_user_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post('user_' . $_user_id, $data);
                        
                        }
                        
                    }
                    
                }
                
                // E-mail
                elseif(get_row_layout() === 'email'){
                    
                    $from = get_sub_field('acfe_form_email_from');
                    $from = $this->map_text_value($from, $acf);
                    
                    $to = get_sub_field('acfe_form_email_to');
                    $to = $this->map_text_value($to, $acf);
                    
                    $subject = get_sub_field('acfe_form_email_subject');
                    $subject = $this->map_text_value($subject, $acf);
                    
                    $content = get_sub_field('acfe_form_email_content');
                    $content = $this->map_text_value($content, $acf);
                    
                    $attachments = array();
                    
                    if(have_rows('acfe_form_email_files')):
                        while(have_rows('acfe_form_email_files')): the_row();
                        
                            $file = get_sub_field('acfe_form_email_file');
                            $file = $this->map_field_value($file, $acf);
                            
                            if(!acf_maybe_get($file, 'ID'))
                                continue;
                            
                            $attachments[] = get_attached_file($file['ID']);
                    
                        endwhile;
                    endif;
                    
                    $headers[] = 'From: ' . $from;
                    $headers[] = 'Content-Type: text/html';
                    $headers[] = 'charset=UTF-8';
                    
                    $args = array(
                        'from'          => $from,
                        'to'            => $to,
                        'subject'       => $subject,
                        'content'       => $content,
                        'headers'       => $headers,
                        'attachments'   => $attachments,
                    );
                    
                    $args = apply_filters('acfe/form/submit/mail_args',                      $args, $form);
                    $args = apply_filters('acfe/form/submit/mail_args/name=' . $form_name,   $args, $form);
                    $args = apply_filters('acfe/form/submit/mail_args/id=' . $form_id,       $args, $form);
                    
                    if($args === false)
                        continue;
                     
                    wp_mail($args['to'], $args['subject'], $args['content'], $args['headers'], $args['attachments']);
                    
                    do_action('acfe/form/submit/mail',                       $form, $args);
                    do_action('acfe/form/submit/mail/name=' . $form_name,    $form, $args);
                    do_action('acfe/form/submit/mail/id=' . $form_id,        $form, $args);
                    
                }
                
                // Custom
                elseif(get_row_layout() === 'custom'){
                    
                    $action_name = get_sub_field('acfe_form_custom_action');
                    
                    do_action($action_name, $form, $post_id);
                    
                }
                
            endwhile;
        endif;
        
    }
    
    // Allow form post_id null
    function validate_post_id($null, $post_id){
        
        if($post_id === null)
            return false;

        return $null;
        
    }

    // Remove '1 field requires attention'
    function error_translation($translated_text, $text, $domain){
        
        if($domain !== 'acf')
            return $translated_text;

        if(in_array($text, array('1 field requires attention', '%d fields require attention')))
            return ' ';

        return $translated_text;
        
    }
    
    function add_shortcode($atts){

        $atts = shortcode_atts(array(
            'name'  => false,
            'ID'    => false
        ), $atts, 'acfe_form');
        
        if(!empty($atts['name'])){
            
            ob_start();
            
                acfe_form($atts['name']);
            
            return ob_get_clean();
            
        }
        
        if(!empty($atts['ID'])){
            
            ob_start();
            
                acfe_form($atts['ID']);
            
            return ob_get_clean();
            
        }
        
        return;
    
    }
    
    function render_integration($fields, $post_id){
        
        $_fields = $fields;
        $last_field = end($_fields);
        
        if(!$last_field || $last_field['name'] !== 'acfe_form_instruction_placement')
            return $fields;
        
        $form_id = $post_id;
        $form_name = get_field('acfe_form_name', $form_id);
        
        $fields[] = array(
            'type'  => 'tab',
            'name'  => '',
            'label' => 'Integration',
            'value' => '',
        );
        
        $fields[] = array(
            'type'      => 'message',
            'name'      => '',
            'label'     => 'Shortcode',
            'value'     => '',
            'message'   => '<code>[acfe_form name="' . $form_name . '"]</code> or <code>[acfe_form ID="' . $form_id . '"]</code>',
            'new_lines' => false,
        );
        
        ob_start();
        ?>
        <pre>&lt;?php get_header(); ?&gt;
    
    &lt;!-- <?php echo get_the_title($form_id); ?> --&gt;
    &lt;?php acfe_form(&apos;<?php echo $form_name; ?>&apos;); ?&gt;
    
&lt;?php get_footer(); ?&gt;</pre>
        <?php $html = ob_get_clean();
        
        $fields[] = array(
            'type'      => 'message',
            'name'      => '',
            'label'     => 'PHP Form Integration',
            'value'     => '',
            'message'   => $html,
            'new_lines' => false,
        );
        
        if(!empty($this->fields_groups)){
            
            foreach($this->fields_groups as $field_group){
                
                if(empty($field_group['fields']))
                    continue;
                
                $_fields = $field_group['fields'];
                break;
                
            }
            
            if(!empty($_fields)){
                
                $field = $_fields[0];
                
                $_form_name = str_replace('-', '_', $form_name);
                $_field_name = str_replace('-', '_', sanitize_title($field['name']));
                
                // Field Validation
                
                ob_start();
                ?>
                <pre>&lt;?php

add_filter(&apos;acf/validate_value/name=<?php echo $field['name']; ?>&apos;, &apos;my_<?php echo $_field_name; ?>_validation&apos;, 10, 4);
function my_<?php echo $_field_name; ?>_validation($valid, $value, $field, $input){
    
    if(!$valid)
        return $valid;
    
    if($value === &apos;Hello&apos;)
        $valid = &apos;Hello is not allowed&apos;;
    
    return $valid;
    
}</pre>
                <?php $html = ob_get_clean();
                
                $fields[] = array(
                    'type'      => 'message',
                    'name'      => '',
                    'label'     => 'PHP Field Validation',
                    'value'     => '',
                    'message'   => $html,
                    'new_lines' => false,
                );
                
                
                // Form Validation
                
                ob_start();
                ?>
                <pre>&lt;?php 

add_action(&apos;acfe/form/validation/name=<?php echo $form_name; ?>&apos;, &apos;my_<?php echo $_form_name; ?>_validation&apos;, 10, 2);
function my_<?php echo $_form_name; ?>_validation($form, $target_post_id){
    
    /**
     * @array       $form Form arguments
     * @bool/string $target_post_id Targeted post id
     */
    
    $<?php echo $_field_name; ?> = get_field(&apos;<?php echo $field['name']; ?>&apos;);
    $<?php echo $_field_name; ?>_unformatted = get_field(&apos;<?php echo $field['name']; ?>&apos;, false, false);
    
    if($<?php echo $_field_name; ?> === &apos;Hello&apos;){
        
        acfe_form_add_field_error(&apos;<?php echo $field['name']; ?>&apos;, &apos;Hello is not allowed&apos;);
        
    }
    
}</pre>
                <?php $html = ob_get_clean();
                
                $fields[] = array(
                    'type'      => 'message',
                    'name'      => '',
                    'label'     => 'PHP Form Validation',
                    'value'     => '',
                    'message'   => $html,
                    'new_lines' => false,
                );
                
                
                // Form Submission
                
                ob_start();
                ?>
                <pre>&lt;?php

add_action(&apos;acfe/form/submit/name=<?php echo $form_name; ?>&apos;, &apos;my_<?php echo $_form_name; ?>_submit&apos;, 10, 2);
function my_<?php echo $_form_name; ?>_submit($form, $target_post_id){
    
    /**
     * @array       $form Form arguments
     * @bool/string $target_post_id Targeted post id
     */
    
    $<?php echo $_field_name; ?> = get_field(&apos;<?php echo $field['name']; ?>&apos;);
    $<?php echo $_field_name; ?>_unformatted = get_field(&apos;<?php echo $field['name']; ?>&apos;, false, false);
    
    if($<?php echo $_field_name; ?> === &apos;do_something&apos;){
        
        // Do something
        
    }
    
}</pre>
                <?php $html = ob_get_clean();
                
                $fields[] = array(
                    'type'      => 'message',
                    'name'      => '',
                    'label'     => 'PHP Form Submit: Custom Action',
                    'value'     => '',
                    'message'   => $html,
                    'new_lines' => false,
                );
                
            }
            
        }
        
        return $fields;
        
    }
    
    function form_admin_columns($columns){
        
        if(isset($columns['date']))
            unset($columns['date']);
        
        $columns['name'] = __('Name');
        $columns['field_groups'] = __('Field groups', 'acf');
        $columns['actions'] = __('Actions');
        $columns['shortcode'] = __('Shortcode');
        
        return $columns;
        
    }
    
    function form_admin_columns_html($column, $post_id){
        
        // Name
        if($column == 'name'){
            
            echo '<code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">' . get_field('acfe_form_name', $post_id) . '</code>';
            
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
                
                $field_group = acf_get_field_group($field_group_key);
                
                $fg[] = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
                
            }
            
            echo implode(', ', $fg);
            
        }
        
        // Actions
        elseif($column == 'actions'){
            
            $posts = $emails = $customs = $users = array();
            $found = false;
            
            if(have_rows('acfe_form_actions', $post_id)):
                while(have_rows('acfe_form_actions', $post_id)): the_row();
                    
                    // Post
                    if(get_row_layout() === 'post'){
                        
                        $behavior = get_sub_field('acfe_form_post_behavior');
                        
                        // Create
                        if($behavior === 'create_post'){
                            
                            $posts[] = '<span class="acf-js-tooltip dashicons dashicons-edit" title="Create post"></span>';
                            $found = true;
                            
                        }
                        
                        // Update
                        elseif($behavior === 'update_post'){
                            
                            $posts[] = '<span class="acf-js-tooltip dashicons dashicons-update" title="Update post"></span>';
                            $found = true;
                            
                        }
                        
                    }
                    
                    // E-mail
                    elseif(get_row_layout() === 'email'){
                        
                        $emails[] = '<span class="acf-js-tooltip dashicons dashicons-email" title="E-mail"></span>';
                        $found = true;
                        
                    }
                    
                    // Custom
                    elseif(get_row_layout() === 'custom'){
                        
                        $customs[] = '<span class="acf-js-tooltip dashicons dashicons-editor-code" title="Custom action"></span>';
                        $found = true;
                        
                    }
                    
                    // User
                    elseif(get_row_layout() === 'user'){
                        
                        $users[] = '<span class="acf-js-tooltip dashicons dashicons-admin-users" title="User"></span>';
                        $found = true;
                        
                    }
                
                endwhile;
            endif;
            
            if(!empty($posts))
                echo implode('', $posts);
            
            if(!empty($emails))
                echo implode('', $emails);
            
            if(!empty($customs))
                echo implode('', $customs);
            
            if(!empty($users))
                echo implode('', $users);
            
            if(!$found)
                echo '—';
            
        }
        
        // Field groups
        elseif($column == 'shortcode'){
            
            echo '<code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">[acfe_form name="' . get_field('acfe_form_name', $post_id) . '"]</code>';
            
        }
        
    }
    
}

// initialize
acf()->acfe_form = new acfe_form();

endif;

function acfe_form($args = array()){
	
	acf()->acfe_form->form($args);
	
}

function acfe_form_render_fields($content, $post_id, $args){
    
	return acf()->acfe_form->render_fields($content, $post_id, $args);
	
}

if(!class_exists('acfe_form_front')):

class acfe_form_front extends acf_form_front{
    
    /*
     * ACF Form: render_form()
     *
     */
    function render_form($args = array()){
        
        // array
		if(is_array($args)){
			
			$args = $this->validate_form($args);
			
		}
        
        // id
        else{
			
			$args = $this->get_form($args);
			
		}
        
        
		// bail early if no args
		if(!$args)
            return false;
		
        // load acf scripts
		acf_enqueue_scripts();
        
		// load values from this post
		$post_id = $args['post_id'];
		
        
		// dont load values for 'new_post'
		if($post_id === 'new_post')
            $post_id = false;
		
        
		// register local fields
		foreach($this->fields as $k => $field){
			
			acf_add_local_field($field);
			
		}
		
		// vars
		$field_groups = array();
		$fields = array();
		
		
		// post_title
		if($args['post_title']){
			
			// load local field
			$_post_title = acf_get_field('_post_title');
			$_post_title['value'] = $post_id ? get_post_field('post_title', $post_id) : '';
            
			// append
			$fields[] = $_post_title;
			
		}
		
		
		// post_content
		if($args['post_content']){
			
			// load local field
			$_post_content = acf_get_field('_post_content');
			$_post_content['value'] = $post_id ? get_post_field('post_content', $post_id) : '';
			
			// append
			$fields[] = $_post_content;
            
		}
		
        
        // Custom HTML
		if(acf_maybe_get($args, 'custom_html')){
			
			$field_groups = false;
            
		}
        
		// specific fields
		elseif($args['fields']){
			
			foreach($args['fields'] as $selector){
				
				// append field ($strict = false to allow for better compatibility with field names)
				$fields[] = acf_maybe_get_field($selector, $post_id, false);
				
			}
			
		}
        
        // Field groups
        elseif($args['field_groups']){
			
			foreach($args['field_groups'] as $selector){
			
				$field_groups[] = acf_get_field_group($selector);
				
			}
			
		}
        
        // New post: field groups
        elseif($args['post_id'] == 'new_post'){
			
			$field_groups = acf_get_field_groups($args['new_post']);
            
		}
        
        // Current post: field groups
        else{
			
			$field_groups = acf_get_field_groups(array(
				'post_id' => $args['post_id']
			));
			
		}
        
        
		//load fields based on field groups
		if(!empty($field_groups)){
			
			foreach($field_groups as $field_group){
				
				$field_group_fields = acf_get_fields($field_group);
				
				if(!empty($field_group_fields)){
					
					foreach(array_keys($field_group_fields) as $i){
						
						$fields[] = acf_extract_var($field_group_fields, $i);
                        
					}
					
				}
			
			}
		
		}
        
        
		// honeypot
		if($args['honeypot']){
			
			$fields[] = acf_get_field('_validate_email');
			
		}
		
		
		// updated message
		if(!empty($_GET['updated'])){
            
            if($args['updated_message']){
                
                if(!empty($args['html_updated_message'])){
                    
                    printf($args['html_updated_message'], $args['updated_message']);
                    
                }
                
                else{
                    
                    echo $args['updated_message'];
                    
                }
                
            }
            
            if(acf_maybe_get($args, 'updated_hide_form'))
                return;
			
		}
        
        add_filter('acf/prepare_field', function($field) use($args){
            
            $field['wrapper']['class'] .= ' ' . $args['fields_wrapper_class'];
            $field['class'] .= ' ' . $args['fields_class'];
            
            return $field;
            
        });
        
        
        if(acf_maybe_get($args, 'map')){
            
            foreach($args['map'] as $field_key => $array){
                
                add_filter('acf/prepare_field/key=' . $field_key, function($field) use($array){
                    
                    $field = array_merge($field, $array);
                    
                    return $field;
                    
                });
                
            }
            
        }
        
		// uploader (always set incase of multiple forms on the page)
		acf_update_setting('uploader', $args['uploader']);
		
		
		// display form
		if($args['form']): ?>
		
		<form <?php acf_esc_attr_e($args['form_attributes']); ?>>
			
            <?php endif; 
                
            // render post data
            acf_form_data(array( 
                'screen'	=> 'acf_form',
                'post_id'	=> $args['post_id'],
                'form'		=> acf_encrypt(json_encode($args))
            ));
            
            ?>
            
            <div class="acf-fields acf-form-fields -<?php echo $args['label_placement']; ?>">
            
                <?php
                
                // html before fields
                echo $args['html_before_fields'];
                
                // Custom HTML
                if(isset($args['custom_html']) && !empty($args['custom_html'])){
                    
                    echo acfe_form_render_fields($args['custom_html'], $post_id, $args);
                
                }
                
                // Normal Render
                else{
                    
                    acf_render_fields($fields, $post_id, $args['field_el'], $args['instruction_placement']);
                    
                }
                
                // html after fields
                echo $args['html_after_fields'];
                
                ?>
                
            </div>
            
            <?php if((!isset($args['form_submit']) && $args['form']) || (isset($args['form_submit']) && !empty($args['form_submit']))): ?>
            
                <div class="acf-form-submit">
                    
                    <?php printf($args['html_submit_button'], $args['submit_value']); ?>
                    <?php echo $args['html_submit_spinner']; ?>
                    
                </div>
            
            <?php endif; ?>
		
        <?php if($args['form']): ?>
		</form>
		<?php endif;
        
    }
    
}

acf()->form_front = new acfe_form_front();

endif;

acf_add_local_field_group(array(
	'key' => 'group_acfe_dynamic_form',
	'title' => 'Dynamic Form',
	'fields' => array(
		array(
			'key' => 'field_acfe_form_tab_general',
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
			'key' => 'field_acfe_form_name',
			'label' => 'Form name',
			'name' => 'acfe_form_name',
			'type' => 'acfe_slug',
			'instructions' => 'The unique form slug',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_acfe_form_field_groups',
			'label' => 'Field groups',
			'name' => 'acfe_form_field_groups',
			'type' => 'select',
			'instructions' => 'Select field groups to map fields',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'choices' => array(
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 1,
			'ui' => 1,
			'ajax' => 0,
			'return_format' => 'value',
			'placeholder' => '',
		),
		array(
			'key' => 'field_acfe_form_form_element',
			'label' => 'Form element',
			'name' => 'acfe_form_form_element',
			'type' => 'true_false',
			'instructions' => 'Whether or not to create a <code>&lt;form&gt;</code> element',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
        array(
            'key' => 'field_acfe_form_attributes',
            'label' => 'Form attributes',
            'name' => 'acfe_form_attributes',
            'type' => 'group',
            'instructions' => 'Form class and id',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'acfe_permissions' => '',
            'layout' => 'block',
            'acfe_group_modal' => 0,
            'conditional_logic' => array(
				array(
					array(
						'field' => 'field_acfe_form_form_element',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
            'sub_fields' => array(
                array(
                    'key' => 'field_acfe_form_attributes_class',
                    'label' => '',
                    'name' => 'acfe_form_attributes_class',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'acf-form',
                    'placeholder' => '',
                    'prepend' => 'class',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_attributes_id',
                    'label' => '',
                    'name' => 'acfe_form_attributes_id',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => 'id',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                array(
                    'key' => 'field_acfe_form_attributes_action',
                    'label' => '',
                    'name' => 'acfe_form_attributes_action',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => 'action',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_attributes_method',
                    'label' => '',
                    'name' => 'acfe_form_attributes_method',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'post',
                    'placeholder' => '',
                    'prepend' => 'method',
                    'append' => '',
                    'maxlength' => '',
                ),
                */
                
            ),
        ),
        array(
            'key' => 'field_acfe_form_fields_attributes',
            'label' => 'Fields class',
            'name' => 'acfe_form_fields_attributes',
            'type' => 'group',
            'instructions' => 'Add class to all fields',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'acfe_permissions' => '',
            'layout' => 'block',
            'acfe_group_modal' => 0,
            'conditional_logic' => array(),
            'sub_fields' => array(
                array(
                    'key' => 'field_acfe_form_fields_wrapper_class',
                    'label' => '',
                    'name' => 'acfe_form_fields_wrapper_class',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => 'wrapper class',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_fields_class',
                    'label' => '',
                    'name' => 'acfe_form_fields_class',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => 'field class',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ),
		array(
			'key' => 'field_acfe_form_html_before_fields',
			'label' => 'HTML Before render',
			'name' => 'acfe_form_html_before_fields',
			'type' => 'textarea',
			'instructions' => 'Extra HTML to add before the fields',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
			'acfe_textarea_code' => 1,
		),
		array(
			'key' => 'field_acfe_form_custom_html',
			'label' => 'HTML Form render',
			'name' => 'acfe_form_custom_html',
			'type' => 'textarea',
			'instructions' => 'Render your own customized HTML. This will bypass the field groups render.<br /><br />
Field groups may be included using <code>{field_group:group_key}</code><br/><code>{field_group:Group title}</code><br/><br/>
Fields may be included using <code>{field:field_key}</code><br/><code>{field:field_name}</code>',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 12,
			'new_lines' => '',
			'acfe_textarea_code' => 1,
		),
		array(
			'key' => 'field_acfe_form_html_after_fields',
			'label' => 'HTML After render',
			'name' => 'acfe_form_html_after_fields',
			'type' => 'textarea',
			'instructions' => 'Extra HTML to add after the fields',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
			'acfe_textarea_code' => 1,
		),
		array(
			'key' => 'field_acfe_form_form_submit',
			'label' => 'Submit button',
			'name' => 'acfe_form_form_submit',
			'type' => 'true_false',
			'instructions' => 'Whether or not to create a form submit button. Defaults to true',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
		array(
			'key' => 'field_acfe_form_submit_value',
			'label' => 'Submit value',
			'name' => 'acfe_form_submit_value',
			'type' => 'text',
			'instructions' => 'The text displayed on the submit button',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_acfe_form_form_submit',
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
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => 'Submit',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_acfe_form_html_submit_button',
			'label' => 'Submit button',
			'name' => 'acfe_form_html_submit_button',
			'type' => 'textarea',
			'instructions' => 'HTML used to render the submit button.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_acfe_form_form_submit',
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
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
			'acfe_textarea_code' => 1,
		),
		array(
			'key' => 'field_acfe_form_html_submit_spinner',
			'label' => 'Submit spinner',
			'name' => 'acfe_form_html_submit_spinner',
			'type' => 'textarea',
			'instructions' => 'HTML used to render the submit button loading spinner.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_acfe_form_form_submit',
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
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '<span class="acf-spinner"></span>',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
			'acfe_textarea_code' => 1,
		),
		array(
			'key' => 'field_acfe_form_tab_submission',
			'label' => 'Submission',
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
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'placement' => 'top',
			'endpoint' => 0,
		),
        array(
			'key' => 'field_acfe_form_errors_position',
			'label' => 'Errors position',
			'name' => 'acfe_form_errors_position',
			'type' => 'radio',
			'instructions' => 'Choose where to display errors',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'choices' => array(
				'above' => 'Above fields',
				'below' => 'Below fields',
				'group' => 'Group errors',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'default_value' => 'above',
			'layout' => 'vertical',
			'return_format' => 'value',
			'save_other_choice' => 0,
		),
        array(
			'key' => 'field_acfe_form_errors_class',
			'label' => 'Errors class',
			'name' => 'acfe_form_errors_class',
			'type' => 'text',
			'instructions' => 'Add class to error message',
			'required' => 0,
			'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_acfe_form_errors_position',
                        'operator' => '!=',
                        'value' => 'group',
                    )
                )
            ),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
        
		array(
			'key' => 'field_acfe_form_updated_message',
			'label' => 'Success message',
			'name' => 'acfe_form_updated_message',
			'type' => 'wysiwyg',
			'instructions' => 'A message displayed above the form after being redirected. Can also be set to false for no message',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => 'Post updated',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
			'delay' => 0,
		),
		array(
			'key' => 'field_acfe_form_html_updated_message',
			'label' => 'Success wrapper HTML',
			'name' => 'acfe_form_html_updated_message',
			'type' => 'textarea',
			'instructions' => 'HTML used to render the updated message.<br /><br />
If used, you have to include the following code <code>%s</code> to print the actual \'Success message\' above.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
			'acfe_textarea_code' => 1,
		),
		array(
			'key' => 'field_acfe_form_updated_hide_form',
			'label' => 'Hide form',
			'name' => 'acfe_form_updated_hide_form',
			'type' => 'true_false',
			'instructions' => 'Hide form on successful submission',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_acfe_form_return',
						'operator' => '==empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
		array(
			'key' => 'field_acfe_form_return',
			'label' => 'Redirection',
			'name' => 'acfe_form_return',
			'type' => 'text',
			'instructions' => 'The URL to be redirected to after the form is submit. Defaults to the current URL with a GET parameter <code>?updated=true</code>.<br /><br />
A special placeholder <code>%post_url%</code> will be converted to post\'s permalink (handy if creating a new post)<br /><br />
A special placeholder <code>%post_id%</code> will be converted to post\'s ID (handy if creating a new post)<br />',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_acfe_form_tab_actions',
			'label' => 'Actions',
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
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_acfe_form_actions',
			'label' => 'Actions',
			'name' => 'acfe_form_actions',
			'type' => 'flexible_content',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'acfe_flexible_stylised_button' => 1,
			'acfe_flexible_layouts_thumbnails' => 0,
			'acfe_flexible_layouts_templates' => 0,
			'acfe_flexible_layouts_placeholder' => 0,
			'acfe_flexible_close_button' => 0,
			'acfe_flexible_title_edition' => 0,
			'acfe_flexible_copy_paste' => 0,
			'acfe_flexible_modal_edition' => 0,
			'acfe_flexible_modal' => array(
				'acfe_flexible_modal_enabled' => '0',
			),
			'acfe_flexible_layouts_state' => 'open',
			'layouts' => array(
                'layout_custom' => array(
					'key' => 'layout_custom',
					'name' => 'custom',
					'label' => 'Custom action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_custom_action',
							'label' => 'Action name',
							'name' => 'acfe_form_custom_action',
							'type' => 'text',
							'instructions' => 'Trigger: <code>do_action(\'action_name\', $form, $target_post_id)</code>',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
					),
					'min' => '',
					'max' => '',
				),
				'layout_email' => array(
					'key' => 'layout_email',
					'name' => 'email',
					'label' => 'Email action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_email_instructions',
							'label' => 'Instructions',
							'name' => '',
							'type' => 'message',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'message' => 'Fields may be included using <code>{field:field_key}</code> or <code>{field:title}</code>.<br />
All fields may be included using <code>{fields}</code>.',
							'new_lines' => '',
							'esc_html' => 0,
						),
						array(
							'key' => 'field_acfe_form_email_from',
							'label' => 'From',
							'name' => 'acfe_form_email_from',
							'type' => 'text',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'default_value' => '',
							'placeholder' => 'Name <email@domain.com>',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_acfe_form_email_to',
							'label' => 'To',
							'name' => 'acfe_form_email_to',
							'type' => 'email',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'default_value' => '',
							'placeholder' => 'email@domain.com',
							'prepend' => '',
							'append' => '',
						),
						array(
							'key' => 'field_acfe_form_email_subject',
							'label' => 'Subject',
							'name' => 'acfe_form_email_subject',
							'type' => 'text',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_acfe_form_email_content',
							'label' => 'Content',
							'name' => 'acfe_form_email_content',
							'type' => 'wysiwyg',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'default_value' => '',
							'tabs' => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
							'delay' => 0,
						),
						array(
							'key' => 'field_acfe_form_email_files',
							'label' => 'Attachments',
							'name' => 'acfe_form_email_files',
							'type' => 'repeater',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'acfe_repeater_stylised_button' => 0,
							'collapsed' => '',
							'min' => 0,
							'max' => 0,
							'layout' => 'table',
							'button_label' => 'Add file',
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_email_file',
									'label' => 'File',
									'name' => 'acfe_form_email_file',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
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
							),
						),
					),
					'min' => '',
					'max' => '',
				),
                
                // Option
                /*
                'layout_option' => array(
					'key' => 'layout_option',
					'name' => 'option',
					'label' => 'Option action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_option_load',
							'label' => 'Load values',
							'name' => 'acfe_form_option_load',
							'type' => 'true_false',
							'instructions' => 'Fill inputs with available values',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'message' => '',
							'default_value' => 1,
							'ui' => 1,
							'ui_on_text' => '',
							'ui_off_text' => '',
						),
						array(
							'key' => 'field_acfe_form_option_name_group',
							'label' => 'Targeted option',
							'name' => 'acfe_form_option_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_option_name',
									'label' => '',
									'name' => 'acfe_form_option_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom option name',
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
									'key' => 'field_acfe_form_option_name_custom',
									'label' => '',
									'name' => 'acfe_form_option_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_option_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Option name or {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_option_meta',
							'label' => 'Meta fields',
							'name' => 'acfe_form_option_meta',
							'type' => 'checkbox',
							'instructions' => 'Choose which ACF fields should be saved to this option',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'choices' => array(
							),
							'allow_custom' => 0,
							'default_value' => array(
							),
							'layout' => 'vertical',
							'toggle' => 1,
							'return_format' => 'value',
							'save_custom' => 0,
						),
					),
					'min' => '',
					'max' => '',
				),
                */
                
				'layout_post' => array(
					'key' => 'layout_post',
					'name' => 'post',
					'label' => 'Post action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_post_behavior',
							'label' => 'Type',
							'name' => 'acfe_form_post_behavior',
							'type' => 'radio',
							'instructions' => 'Choose the action type',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'choices' => array(
								'create_post' => 'Create post',
								'update_post' => 'Update post',
							),
							'allow_null' => 0,
							'other_choice' => 0,
							'default_value' => 'create_post',
							'layout' => 'vertical',
							'return_format' => 'value',
							'save_other_choice' => 0,
						),
						array(
							'key' => 'field_acfe_form_post_create_post_type',
							'label' => 'Post type',
							'name' => 'acfe_form_post_create_post_type',
							'type' => 'acfe_post_types',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'create_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(
							),
							'ajax' => 0,
							'placeholder' => '',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
							'post_type' => array(
							),
						),
						array(
							'key' => 'field_acfe_form_post_create_post_status',
							'label' => 'Post status',
							'name' => 'acfe_form_post_create_post_status',
							'type' => 'acfe_post_statuses',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'create_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(
							),
							'ajax' => 0,
							'placeholder' => '',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
							'post_status' => array(
							),
						),
						array(
							'key' => 'field_acfe_form_post_create_post_title_group',
							'label' => 'Post title',
							'name' => 'acfe_form_post_create_post_title_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'create_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_create_post_title',
									'label' => '',
									'name' => 'acfe_form_post_create_post_title',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'generated_id' => 'Generated ID',
										'custom' => 'Custom title',
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
									'key' => 'field_acfe_form_post_create_post_title_custom',
									'label' => '',
									'name' => 'acfe_form_post_create_post_title_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_create_post_title',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_create_post_name_group',
							'label' => 'Post slug',
							'name' => 'acfe_form_post_create_post_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'create_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_create_post_name',
									'label' => '',
									'name' => 'acfe_form_post_create_post_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'generated_id' => 'Generated ID',
										'custom' => 'Custom slug',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_create_post_name_custom',
									'label' => '',
									'name' => 'acfe_form_post_create_post_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_create_post_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_create_post_content_group',
							'label' => 'Post content',
							'name' => 'acfe_form_post_create_post_content_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'create_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_create_post_content',
									'label' => '',
									'name' => 'acfe_form_post_create_post_content',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom content',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'None',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_create_post_content_custom',
									'label' => '',
									'name' => 'acfe_form_post_create_post_content_custom',
									'type' => 'wysiwyg',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_create_post_content',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'tabs' => 'all',
									'toolbar' => 'full',
									'media_upload' => 1,
									'delay' => 0,
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_create_post_author_group',
							'label' => 'Post author',
							'name' => 'acfe_form_post_create_post_author_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'create_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_create_post_author',
									'label' => '',
									'name' => 'acfe_form_post_create_post_author',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'current_user' => 'Current user',
										'custom_user_id' => 'Custom user',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_create_post_author_custom',
									'label' => '',
									'name' => 'acfe_form_post_create_post_author_custom',
									'type' => 'user',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_create_post_author',
												'operator' => '==',
												'value' => 'custom_user_id',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'role' => '',
									'allow_null' => 0,
									'multiple' => 0,
									'return_format' => 'id',
									'acfe_bidirectional' => array(
										'acfe_bidirectional_enabled' => '0',
									),
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_load',
							'label' => 'Load values',
							'name' => 'acfe_form_post_update_load',
							'type' => 'true_false',
							'instructions' => 'Fill inputs with available values',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'message' => '',
							'default_value' => 1,
							'ui' => 1,
							'ui_on_text' => '',
							'ui_off_text' => '',
						),
						array(
							'key' => 'field_acfe_form_post_update_post_id_group',
							'label' => 'Targeted post',
							'name' => 'acfe_form_post_update_post_id_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_update_post_id',
									'label' => '',
									'name' => 'acfe_form_post_update_post_id',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'current_post' => 'Current post',
										'custom_post_id' => 'Custom post ID',
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
                                    'key' => 'field_acfe_form_post_update_post_id_custom',
                                    'label' => '',
                                    'name' => 'acfe_form_post_update_post_id_custom',
                                    'type' => 'post_object',
                                    'instructions' => '',
                                    'required' => 1,
                                    'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_update_post_id',
												'operator' => '==',
												'value' => 'custom_post_id',
											),
										),
									),
                                    'wrapper' => array(
                                        'width' => '',
                                        'class' => '',
                                        'id' => '',
                                    ),
                                    'acfe_permissions' => '',
                                    'post_type' => '',
                                    'taxonomy' => '',
                                    'allow_null' => 0,
                                    'multiple' => 0,
                                    'return_format' => 'id',
                                    'ui' => 1,
                                ),
                                /*
								array(
									'key' => 'field_acfe_form_post_update_post_id_custom',
									'label' => '',
									'name' => 'acfe_form_post_update_post_id_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_update_post_id',
												'operator' => '==',
												'value' => 'custom_post_id',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'ID or {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
                                */
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_post_type',
							'label' => 'Post type',
							'name' => 'acfe_form_post_update_post_type',
							'type' => 'acfe_post_types',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'post_type' => '',
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 1,
							'placeholder' => 'Default',
							'multiple' => 0,
							'ui' => 0,
							'ajax' => 0,
							'other_choice' => 0,
							'save_other_choice' => 0,
							'layout' => 'vertical',
							'toggle' => 0,
							'allow_custom' => 0,
							'save_custom' => 0,
							'choices' => array(
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_post_status',
							'label' => 'Post status',
							'name' => 'acfe_form_post_update_post_status',
							'type' => 'acfe_post_statuses',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 1,
							'placeholder' => 'Default',
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(
							),
							'ajax' => 0,
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
							'post_status' => array(
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_post_title_group',
							'label' => 'Post title',
							'name' => 'acfe_form_post_update_post_title_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_update_post_title',
									'label' => '',
									'name' => 'acfe_form_post_update_post_title',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'generated_id' => 'Generated ID',
										'custom' => 'Custom title',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_update_post_title_custom',
									'label' => '',
									'name' => 'acfe_form_post_update_post_title_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_update_post_title',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_post_name_group',
							'label' => 'Post slug',
							'name' => 'acfe_form_post_update_post_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_update_post_name',
									'label' => '',
									'name' => 'acfe_form_post_update_post_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'generated_id' => 'Generated ID',
										'custom' => 'Custom slug',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_update_post_name_custom',
									'label' => '',
									'name' => 'acfe_form_post_update_post_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_update_post_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_post_content_group',
							'label' => 'Post content',
							'name' => 'acfe_form_post_update_post_content_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_update_post_content',
									'label' => '',
									'name' => 'acfe_form_post_update_post_content',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom content',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_update_post_content_custom',
									'label' => '',
									'name' => 'acfe_form_post_update_post_content_custom',
									'type' => 'wysiwyg',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_update_post_content',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'tabs' => 'all',
									'toolbar' => 'full',
									'media_upload' => 1,
									'delay' => 0,
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_update_post_author_group',
							'label' => 'Post author',
							'name' => 'acfe_form_post_update_post_author_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_post_behavior',
										'operator' => '==',
										'value' => 'update_post',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_post_update_post_author',
									'label' => '',
									'name' => 'acfe_form_post_update_post_author',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'current_user' => 'Current user',
										'custom_user_id' => 'Custom user',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_post_update_post_author_custom',
									'label' => '',
									'name' => 'acfe_form_post_update_post_author_custom',
									'type' => 'user',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_post_update_post_author',
												'operator' => '==',
												'value' => 'custom_user_id',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'role' => '',
									'allow_null' => 0,
									'multiple' => 0,
									'return_format' => 'id',
									'acfe_bidirectional' => array(
										'acfe_bidirectional_enabled' => '0',
									),
								),
							),
						),
						array(
							'key' => 'field_acfe_form_post_meta',
							'label' => 'Meta fields',
							'name' => 'acfe_form_post_meta',
							'type' => 'checkbox',
							'instructions' => 'Choose which ACF fields should be saved to this post',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'choices' => array(
							),
							'allow_custom' => 0,
							'default_value' => array(
							),
							'layout' => 'vertical',
							'toggle' => 1,
							'return_format' => 'value',
							'save_custom' => 0,
						),
					),
					'min' => '',
					'max' => '',
				),
                
                'layout_term' => array(
					'key' => 'layout_term',
					'name' => 'term',
					'label' => 'Term action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_term_behavior',
							'label' => 'Type',
							'name' => 'acfe_form_term_behavior',
							'type' => 'radio',
							'instructions' => 'Choose the action type',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'choices' => array(
								'create_term' => 'Create term',
								'update_term' => 'Update term',
							),
							'allow_null' => 0,
							'other_choice' => 0,
							'default_value' => 'create_term',
							'layout' => 'vertical',
							'return_format' => 'value',
							'save_other_choice' => 0,
						),
						array(
							'key' => 'field_acfe_form_term_create_name_group',
							'label' => 'Name',
							'name' => 'acfe_form_term_create_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'create_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_create_name',
									'label' => '',
									'name' => 'acfe_form_term_create_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom name',
									),
									'default_value' => array(
									),
									'allow_null' => 0,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => '',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_term_create_name_custom',
									'label' => '',
									'name' => 'acfe_form_term_create_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_create_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_create_slug_group',
							'label' => 'Slug',
							'name' => 'acfe_form_term_create_slug_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'create_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_create_slug',
									'label' => '',
									'name' => 'acfe_form_term_create_slug',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom slug',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_term_create_slug_custom',
									'label' => '',
									'name' => 'acfe_form_term_create_slug_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_create_slug',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_create_taxonomy',
							'label' => 'Taxonomy',
							'name' => 'acfe_form_term_create_taxonomy',
							'type' => 'acfe_taxonomies',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'create_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'taxonomy' => '',
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(
							),
							'ajax' => 0,
							'placeholder' => '',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
						),
                        array(
							'key' => 'field_acfe_form_term_create_parent_group',
							'label' => 'Parent',
							'name' => 'acfe_form_term_create_parent_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'create_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_create_parent',
									'label' => '',
									'name' => 'acfe_form_term_create_parent',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom parent',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_term_create_parent_custom',
									'label' => '',
									'name' => 'acfe_form_term_create_parent_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_create_parent',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Term ID or {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_create_description_group',
							'label' => 'Description',
							'name' => 'acfe_form_term_create_description_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'create_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_create_description',
									'label' => '',
									'name' => 'acfe_form_term_create_description',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom description',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'ajax' => 0,
									'placeholder' => 'Default',
								),
								array(
									'key' => 'field_acfe_form_term_create_description_custom',
									'label' => '',
									'name' => 'acfe_form_term_create_description_custom',
									'type' => 'wysiwyg',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_create_description',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'tabs' => 'all',
									'toolbar' => 'full',
									'media_upload' => 1,
									'delay' => 0,
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_update_load',
							'label' => 'Load values',
							'name' => 'acfe_form_term_update_load',
							'type' => 'true_false',
							'instructions' => 'Fill inputs with available values',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'message' => '',
							'default_value' => 1,
							'ui' => 1,
							'ui_on_text' => '',
							'ui_off_text' => '',
						),
						array(
							'key' => 'field_acfe_form_term_update_term_id_group',
							'label' => 'Targeted term',
							'name' => 'acfe_form_term_update_term_id_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_update_term_id',
									'label' => '',
									'name' => 'acfe_form_term_update_term_id',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'current_term' => 'Current term',
										'custom_term_id' => 'Custom term',
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
									'key' => 'field_acfe_form_term_update_term_id_custom',
									'label' => '',
									'name' => 'acfe_form_term_update_term_id_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_update_term_id',
												'operator' => '==',
												'value' => 'custom_term_id',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Term ID or {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_update_name_group',
							'label' => 'Name',
							'name' => 'acfe_form_term_update_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_update_name',
									'label' => '',
									'name' => 'acfe_form_term_update_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_term_update_name_custom',
									'label' => '',
									'name' => 'acfe_form_term_update_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_update_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_update_slug_group',
							'label' => 'Slug',
							'name' => 'acfe_form_term_update_slug_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_update_slug',
									'label' => '',
									'name' => 'acfe_form_term_update_slug',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom slug',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_term_update_slug_custom',
									'label' => '',
									'name' => 'acfe_form_term_update_slug_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_update_slug',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_update_taxonomy',
							'label' => 'Taxonomy',
							'name' => 'acfe_form_term_update_taxonomy',
							'type' => 'acfe_taxonomies',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'taxonomy' => '',
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 1,
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(
							),
							'ajax' => 0,
							'placeholder' => 'Default',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
						),
                        array(
							'key' => 'field_acfe_form_term_update_parent_group',
							'label' => 'Parent',
							'name' => 'acfe_form_term_update_parent_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_update_parent',
									'label' => '',
									'name' => 'acfe_form_term_update_parent',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom parent',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_term_update_parent_custom',
									'label' => '',
									'name' => 'acfe_form_term_update_parent_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_update_parent',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Term ID or {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_update_description_group',
							'label' => 'Description',
							'name' => 'acfe_form_term_update_description_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_term_behavior',
										'operator' => '==',
										'value' => 'update_term',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_term_update_description',
									'label' => '',
									'name' => 'acfe_form_term_update_description',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom description',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'ajax' => 0,
									'placeholder' => 'Default',
								),
								array(
									'key' => 'field_acfe_form_term_update_description_custom',
									'label' => '',
									'name' => 'acfe_form_term_update_description_custom',
									'type' => 'wysiwyg',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_term_update_description',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'tabs' => 'all',
									'toolbar' => 'full',
									'media_upload' => 1,
									'delay' => 0,
								),
							),
						),
						array(
							'key' => 'field_acfe_form_term_meta',
							'label' => 'Meta fields',
							'name' => 'acfe_form_term_meta',
							'type' => 'checkbox',
							'instructions' => 'Choose which ACF fields should be saved to this term',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'choices' => array(
							),
							'allow_custom' => 0,
							'default_value' => array(
							),
							'layout' => 'vertical',
							'toggle' => 1,
							'return_format' => 'value',
							'save_custom' => 0,
						),
					),
					'min' => '',
					'max' => '',
				),
                
                'layout_user' => array(
					'key' => 'layout_user',
					'name' => 'user',
					'label' => 'User action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_user_behavior',
							'label' => 'Type',
							'name' => 'acfe_form_user_behavior',
							'type' => 'radio',
							'instructions' => 'Choose the action type',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'choices' => array(
								'create_user' => 'Create user',
								'update_user' => 'Update user',
							),
							'allow_null' => 0,
							'other_choice' => 0,
							'default_value' => 'create_user',
							'layout' => 'vertical',
							'return_format' => 'value',
							'save_other_choice' => 0,
						),
                        array(
                            'key' => 'field_acfe_form_user_create_email',
                            'label' => 'E-mail',
                            'name' => 'acfe_form_user_create_email',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(),
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
                            'key' => 'field_acfe_form_user_create_username',
                            'label' => 'Username',
                            'name' => 'acfe_form_user_create_username',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(),
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
                            'key' => 'field_acfe_form_user_create_password',
                            'label' => 'Password',
                            'name' => 'acfe_form_user_create_password',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'generate_password' => 'Generate password',
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
							'key' => 'field_acfe_form_user_create_first_name_group',
							'label' => 'First name',
							'name' => 'acfe_form_user_create_first_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_create_first_name',
									'label' => '',
									'name' => 'acfe_form_user_create_first_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom first name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_create_first_name_custom',
									'label' => '',
									'name' => 'acfe_form_user_create_first_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_create_first_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_user_create_last_name_group',
							'label' => 'Last name',
							'name' => 'acfe_form_user_create_last_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_create_last_name',
									'label' => '',
									'name' => 'acfe_form_user_create_last_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom last name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_create_last_name_custom',
									'label' => '',
									'name' => 'acfe_form_user_create_last_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_create_last_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_user_create_nickname_group',
							'label' => 'Nickname',
							'name' => 'acfe_form_user_create_nickname_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_create_nickname',
									'label' => '',
									'name' => 'acfe_form_user_create_nickname',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom nickname',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_create_nickname_custom',
									'label' => '',
									'name' => 'acfe_form_user_create_nickname_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_create_nickname',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_user_create_display_name_group',
							'label' => 'Display name',
							'name' => 'acfe_form_user_create_display_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_create_display_name',
									'label' => '',
									'name' => 'acfe_form_user_create_display_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom display name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_create_display_name_custom',
									'label' => '',
									'name' => 'acfe_form_user_create_display_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_create_display_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
                        array(
							'key' => 'field_acfe_form_user_create_role',
							'label' => 'Role',
							'name' => 'acfe_form_user_create_role',
							'type' => 'acfe_user_roles',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'create_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'user_role' => '',
							'field_type' => 'select',
							'default_value' => '',
							'allow_null' => 1,
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(),
							'ajax' => 0,
							'placeholder' => 'Default',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
						),
						array(
							'key' => 'field_acfe_form_user_update_load',
							'label' => 'Load values',
							'name' => 'acfe_form_user_update_load',
							'type' => 'true_false',
							'instructions' => 'Fill inputs with available values',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'message' => '',
							'default_value' => 1,
							'ui' => 1,
							'ui_on_text' => '',
							'ui_off_text' => '',
						),
						array(
							'key' => 'field_acfe_form_user_update_user_id_group',
							'label' => 'Targeted user',
							'name' => 'acfe_form_user_update_user_id_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_update_user_id',
									'label' => '',
									'name' => 'acfe_form_user_update_user_id',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'current_user' => 'Current user',
										'custom_user_id' => 'Custom user',
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
									'key' => 'field_acfe_form_user_update_user_id_custom',
									'label' => '',
									'name' => 'acfe_form_user_update_user_id_custom',
									'type' => 'user',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_update_user_id',
												'operator' => '==',
												'value' => 'custom_user_id',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'role' => '',
									'allow_null' => 0,
									'multiple' => 0,
									'return_format' => 'id',
									'acfe_bidirectional' => array(
										'acfe_bidirectional_enabled' => '0',
									),
								),
							),
						),
                        array(
                            'key' => 'field_acfe_form_user_update_email',
                            'label' => 'E-mail',
                            'name' => 'acfe_form_user_update_email',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 0,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                        ),
						array(
                            'key' => 'field_acfe_form_user_update_username',
                            'label' => 'Username',
                            'name' => 'acfe_form_user_update_username',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 0,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                        ),
						array(
                            'key' => 'field_acfe_form_user_update_password',
                            'label' => 'Password',
                            'name' => 'acfe_form_user_update_password',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'generate_password' => 'Generate password',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 0,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                        ),
						array(
							'key' => 'field_acfe_form_user_update_first_name_group',
							'label' => 'First name',
							'name' => 'acfe_form_user_update_first_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_update_first_name',
									'label' => '',
									'name' => 'acfe_form_user_update_first_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom first name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_update_first_name_custom',
									'label' => '',
									'name' => 'acfe_form_user_update_first_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_update_first_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_user_update_last_name_group',
							'label' => 'Last name',
							'name' => 'acfe_form_user_update_last_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_update_last_name',
									'label' => '',
									'name' => 'acfe_form_user_update_last_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom last name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_update_last_name_custom',
									'label' => '',
									'name' => 'acfe_form_user_update_last_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_update_last_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_user_update_nickname_group',
							'label' => 'Nickname',
							'name' => 'acfe_form_user_update_nickname_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_update_nickname',
									'label' => '',
									'name' => 'acfe_form_user_update_nickname',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom nickname',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_update_nickname_custom',
									'label' => '',
									'name' => 'acfe_form_user_update_nickname_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_update_nickname',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_user_update_display_name_group',
							'label' => 'Display name',
							'name' => 'acfe_form_user_update_display_name_group',
							'type' => 'group',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'layout' => 'block',
							'acfe_group_modal' => 0,
							'sub_fields' => array(
								array(
									'key' => 'field_acfe_form_user_update_display_name',
									'label' => '',
									'name' => 'acfe_form_user_update_display_name',
									'type' => 'select',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'choices' => array(
										'custom' => 'Custom display name',
									),
									'default_value' => array(
									),
									'allow_null' => 1,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'placeholder' => 'Default',
									'ajax' => 0,
								),
								array(
									'key' => 'field_acfe_form_user_update_display_name_custom',
									'label' => '',
									'name' => 'acfe_form_user_update_display_name_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_user_update_display_name',
												'operator' => '==',
												'value' => 'custom',
											),
										),
									),
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'acfe_permissions' => '',
									'default_value' => '',
									'placeholder' => 'Available tag: {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
                        array(
							'key' => 'field_acfe_form_user_update_role',
							'label' => 'Role',
							'name' => 'acfe_form_user_update_role',
							'type' => 'acfe_user_roles',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_acfe_form_user_behavior',
										'operator' => '==',
										'value' => 'update_user',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'user_role' => '',
							'field_type' => 'select',
							'default_value' => '',
							'allow_null' => 1,
							'multiple' => 0,
							'ui' => 0,
							'choices' => array(),
							'ajax' => 0,
							'placeholder' => 'Default',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
						),
						array(
							'key' => 'field_acfe_form_user_meta',
							'label' => 'Meta fields',
							'name' => 'acfe_form_user_meta',
							'type' => 'checkbox',
							'instructions' => 'Choose which ACF fields should be saved to this user',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'acfe_permissions' => '',
							'choices' => array(
							),
							'allow_custom' => 0,
							'default_value' => array(
							),
							'layout' => 'vertical',
							'toggle' => 1,
							'return_format' => 'value',
							'save_custom' => 0,
						),
					),
					'min' => '',
					'max' => '',
				),
			),
			'button_label' => 'Add action',
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_acfe_form_tab_advanced',
			'label' => 'Advanced',
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
			'key' => 'field_acfe_form_honeypot',
			'label' => 'Honeypot',
			'name' => 'acfe_form_honeypot',
			'type' => 'true_false',
			'instructions' => 'Whether to include a hidden input field to capture non human form submission. Defaults to true.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
		array(
			'key' => 'field_acfe_form_kses',
			'label' => 'Kses',
			'name' => 'acfe_form_kses',
			'type' => 'true_false',
			'instructions' => 'Whether or not to sanitize all $_POST data with the wp_kses_post() function. Defaults to true.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
		array(
			'key' => 'field_acfe_form_uploader',
			'label' => 'Uploader',
			'name' => 'acfe_form_uploader',
			'type' => 'radio',
			'instructions' => 'Whether to use the WP uploader or a basic input for image and file fields. Defaults to \'wp\' 
Choices of \'wp\' or \'basic\'.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'choices' => array(
				'wp' => 'Media modal',
				'basic' => 'Basic',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'default_value' => 'wp',
			'layout' => 'vertical',
			'return_format' => 'value',
			'save_other_choice' => 0,
		),
		array(
			'key' => 'field_acfe_form_form_field_el',
			'label' => 'Field element',
			'name' => 'acfe_form_form_field_el',
			'type' => 'radio',
			'instructions' => 'Determines element used to wrap a field. Defaults to \'div\'',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'choices' => array(
				'div' => '&lt;div&gt;',
				'tr' => '&lt;tr&gt;',
				'td' => '&lt;td&gt;',
				'ul' => '&lt;ul&gt;',
				'ol' => '&lt;ol&gt;',
				'dl' => '&lt;dl&gt;',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'default_value' => 'div',
			'layout' => 'vertical',
			'return_format' => 'value',
			'save_other_choice' => 0,
		),
		array(
			'key' => 'field_acfe_form_label_placement',
			'label' => 'Label placement',
			'name' => 'acfe_form_label_placement',
			'type' => 'radio',
			'instructions' => 'Determines where field labels are places in relation to fields. Defaults to \'top\'. <br />
Choices of \'top\' (Above fields) or \'left\' (Beside fields)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'choices' => array(
				'top' => 'Top',
				'left' => 'Left',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'default_value' => 'top',
			'layout' => 'vertical',
			'return_format' => 'value',
			'save_other_choice' => 0,
		),
		array(
			'key' => 'field_acfe_form_instruction_placement',
			'label' => 'Instruction placement',
			'name' => 'acfe_form_instruction_placement',
			'type' => 'radio',
			'instructions' => 'Determines where field instructions are places in relation to fields. Defaults to \'label\'. <br />
Choices of \'label\' (Below labels) or \'field\' (Below fields)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'acfe_permissions' => '',
			'choices' => array(
				'label' => 'Label',
				'field' => 'Field',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'default_value' => 'label',
			'layout' => 'vertical',
			'return_format' => 'value',
			'save_other_choice' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'acfe-form',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'acfe_display_title' => '',
	'acfe_permissions' => '',
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
));