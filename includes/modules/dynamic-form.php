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
        add_action('init',                                                          array($this, 'register_post_types'));
        add_action('acf/save_post',                                                 array($this, 'save_form'), 20);
        add_action('pre_get_posts',                                                 array($this, 'admin_list'));
        add_action('acf/submit_form',                                               array($this, 'submit'), 10, 2);
		
		// filters
		add_filter('acf/get_post_types',                                            array($this, 'filter_post_type'), 10, 2);
        add_filter('edit_posts_per_page',                                           array($this, 'admin_ppp'), 10, 2);
        add_filter('acf/prepare_field/name=acfe_form_actions',                      array($this, 'prepare_actions'));
        add_filter('acf/prepare_field/name=acfe_form_email_files',                  array($this, 'prepare_email_files'));
        add_filter('acf/prepare_field/name=acfe_form_field_groups',                 array($this, 'field_groups_choices'));
        
        add_filter('acf/prepare_field/name=acfe_form_submission_field_groups',      array($this, 'map_field_groups'));
        add_filter('acf/prepare_field/name=acfe_form_submission_post_title',        array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_submission_post_status',       array($this, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_submission_post_author',       array($this, 'map_fields_deep'));

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
        
        add_filter('acf/prepare_field/name=acfe_form_email_file',                   array($this, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_meta',                    array($this, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_submission_meta',              array($this, 'map_fields'));
		
        add_filter('acf/validate_post_id',                                          array($this, 'validate_post_id'), 10, 2);
        add_filter('gettext',                                                       array($this, 'error_translation'), 99, 3);
        add_filter('acf/load_field_groups',                                         array($this, 'get_field_groups'));
        add_filter('acf/location/rule_match/post_type',                             array($this, 'get_field_group_visibility'), 10, 4);
        
        add_filter('manage_edit-acfe-form_columns',                                 array($this, 'form_admin_columns'));
        add_action('manage_acfe-form_posts_custom_column',                          array($this, 'form_admin_columns_html'), 10, 2);
        
        add_filter('manage_edit-acfe-form-submission_columns',                      array($this, 'submission_admin_columns'));
        add_action('manage_acfe-form-submission_posts_custom_column',               array($this, 'submission_admin_columns_html'), 10, 2);
        
        // Shortcode
        add_shortcode('acfe_form',                                                  array($this, 'add_shortcode'));
        
	}
    
    function initialize(){
        
        // globals
		global $typenow;
        
        // Restrict
		if($typenow !== 'acfe-form')
			return;
        
        // vars
		$this->fields_groups = $this->get_fields_groups();
        
        add_action('add_meta_boxes',        array($this, 'add_meta_boxes'));
        
        add_filter('acf/pre_render_fields', array($this, 'render_integration'), 10, 2);
        
    }
    
    function register_post_types(){
        
        // ACFE Form
        register_post_type('acfe-form', array(
            'label'                 => 'Forms',
            'description'           => 'Forms',
            'labels'                => array(
                'name'          => 'Forms',
                'singular_name' => 'Form',
                'menu_name'     => 'Forms',
                'edit_item'     => 'Edit Form',
                'add_new_item'  => 'New Form',
            ),
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_icon'             => 'dashicons-layout',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'rewrite'               => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
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
        
        if(acf_get_setting('acfe/modules/dynamic_forms_submissions', true)){
            
            // ACFE Form Submission
            register_post_type('acfe-form-submission', array(
                'label'                 => 'Submissions',
                'description'           => 'Submissions',
                'labels'                => array(
                    'name'          => 'Submissions',
                    'singular_name' => 'Submission',
                    'menu_name'     => 'Submissions',
                    'edit_item'     => 'Edit Submission',
                    'add_new_item'  => 'New Submission',
                ),
                'supports'              => array('title', 'author'),
                'hierarchical'          => false,
                'public'                => false,
                'show_ui'               => true,
                'show_in_menu'          => 'edit.php?post_type=acfe-form',
                'menu_icon'             => false,
                'show_in_admin_bar'     => false,
                'show_in_nav_menus'     => false,
                'can_export'            => false,
                'has_archive'           => false,
                'rewrite'               => false,
                'exclude_from_search'   => true,
                'publicly_queryable'    => true,
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
        
    }
    
    function filter_post_type($post_types, $args){
        
        if(empty($post_types))
            return $post_types;
        
        foreach($post_types as $k => $post_type){
            
            if($post_type !== 'acfe-form' && $post_type !== 'acfe-form-submission')
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
        
        $fields_groups = $this->get_fields_groups($post_id);
        
        if(empty($fields_groups))
            return;
        
        foreach($fields_groups as $field_group){
            
            if(isset($field_group['acfe_form']) && !empty($field_group['acfe_form']))
                continue;
            
            // Update field group
            $field_group['acfe_form'] = true;
            
            acf_update_field_group($field_group);
            
        }
        
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
        
        if(!acf_get_setting('acfe/modules/dynamic_forms_submissions', true)){
            
            if(isset($field['layouts']) && !empty($field['layouts'])){
                
                foreach($field['layouts'] as $layout_key => $layout){
                    
                    if($layout['name'] !== 'submission')
                        continue;
                    
                    unset($field['layouts'][$layout_key]);
                    break;
                    
                }
                
            }
            
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
            
            $fields = acf_get_fields($field_group);
            if(empty($fields))
                continue;
            
            foreach($fields as $s_field){
                
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
        
        if(!empty($args['form'])){
            
            $form_attributes = get_field('acfe_form_attributes', $form_id);
            
            $args['form_attributes']['class'] = $form_attributes['acfe_form_attributes_class'];
            $args['form_attributes']['id'] = $form_attributes['acfe_form_attributes_id'];
            
        }
        
        $acfe_form_fields_attributes = get_field('acfe_form_fields_attributes', $form_id);
        
        $args['fields_wrapper_class'] = $acfe_form_fields_attributes['acfe_form_fields_wrapper_class'];
        $args['fields_class'] = $acfe_form_fields_attributes['acfe_form_fields_class'];
        
        $args['html_before_fields'] = get_field('acfe_form_html_before_fields', $form_id);
        $args['custom_html'] = get_field('acfe_form_custom_html', $form_id);
        $args['html_after_fields'] = get_field('acfe_form_html_after_fields', $form_id);
        $args['form_submit'] = get_field('acfe_form_form_submit', $form_id);
        $args['submit_value'] = get_field('acfe_form_submit_value', $form_id);
        $args['html_submit_button'] = get_field('acfe_form_html_submit_button', $form_id);
        $args['html_submit_spinner'] = get_field('acfe_form_html_submit_spinner', $form_id);
        
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
        
        // Actions
        if(have_rows('acfe_form_actions', $form_id)):
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
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
                        $_post_id = null;
                        
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
                        
                        break;
                        
                    }
                    
                }
                
            endwhile;
        endif;
        
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
                
                $fields = acf_get_fields($field_group);
                if(empty($fields))
                    continue;
                
                foreach($fields as $field){
                    
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
    
    // Form submission
    function submit($form, $post_id){
        
        if(!isset($form['acfe_form_id']) || !isset($form['acfe_form_name']))
            return;
        
        $form_id = $form['acfe_form_id'];
        $form_name = $form['acfe_form_name'];
        
        // Actions
        if(have_rows('acfe_form_actions', $form_id)):
        
            $acf = array();
            
            // ACF $_POST
            if(isset($_POST['acf']))
                $acf = $_POST['acf'];
            
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                // Submission
                if(get_row_layout() === 'submission'){
                    
                    $_post_status = get_sub_field('acfe_form_submission_post_status');
                    
                    $_post_title_group = get_sub_field('acfe_form_submission_post_title_group');
                    $_post_title = $_post_title_group['acfe_form_submission_post_title'];
                    $_post_title_custom = $_post_title_group['acfe_form_submission_post_title_custom'];
                    
                    $_post_author_group = get_sub_field('acfe_form_submission_post_author_group');
                    $_post_author = $_post_author_group['acfe_form_submission_post_author'];
                    $_post_author_custom = $_post_author_group['acfe_form_submission_post_author_custom'];
                    
                    // Insert Post
                    $_post_id = wp_insert_post(array(
                        'post_title'    => 'Submission',
                        'post_type'     => 'acfe-form-submission'
                    ));
                    
                    // ID
                    $args['ID'] = $_post_id;
                    
                    // Submission status
                    $args['post_status'] = $_post_status;
                    
                    if(acf_is_field_key($_post_status)){
                        
                        $args['post_status'] = $this->map_field_value($_post_status, $acf);
                        
                    }
                    
                    // Submission title
                    $args['post_title'] = $_post_id;
                    
                    if(acf_is_field_key($_post_title)){
                        
                        $args['post_title'] = $this->map_field_value($_post_title, $acf);
                        
                    }elseif($_post_title === 'custom'){
                        
                        $args['post_title'] = $this->map_text_value($_post_title_custom, $acf);
                        
                    }
                    
                    // Submission author
                    if($_post_author === 'current_user'){
                        
                        $args['post_author'] = get_current_user_id();
                        
                    }elseif($_post_author === 'custom_user_id'){
                        
                        $args['post_author'] = $_post_author_custom;
                        
                    }elseif(acf_is_field_key($_post_author)){
                        
                        $args['post_author'] = $this->map_field_value($_post_author, $acf);
                        
                    }
                    
                    // Update Post
                    $_post_id = wp_update_post($args);
                    
                    // Submission: Field groups
                    $_field_groups = get_sub_field('acfe_form_submission_field_groups');
                    
                    update_field('acfe_form_submission_field_groups', $_field_groups, $_post_id);
                    
                    // Submission: Form
                    update_field('acfe_form_submission_form', $form_id, $_post_id);
                    
                    // Submission: Meta
                    $_meta = get_sub_field('acfe_form_submission_meta');
                    
                    $data = $this->filter_meta($_meta, $acf);
                    
                    if(!empty($data)){
                        
                        // Save meta fields
                        acf_save_post($_post_id, $data);
                    
                    }
                    
                }
                
                // Post
                elseif(get_row_layout() === 'post'){
                    
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
                        
                        // Update Post
                        $_post_id = wp_update_post($args);
                        
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
                            
                            $_post_id = $this->map_text_value($_post_id_custom, $acf);
                        
                        // Field
                        }elseif(acf_is_field_key($_post_id_data)){
                            
                            $_post_id = $this->map_field_value($_post_id_data, $acf);
                            
                        }
                        
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
                        
                        // Update Post
                        $_post_id = wp_update_post($args);
                        
                        // Meta save
                        $_meta = get_sub_field('acfe_form_post_meta');
                        
                        $data = $this->filter_meta($_meta, $acf);
                        
                        if(!empty($data)){
                            
                            // Save meta fields
                            acf_save_post($_post_id, $data);
                        
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
                            
                            if(isset($file['ID']) && !empty($file['ID']))
                                $attachments[] = get_attached_file($file['ID']);
                                
                    
                        endwhile;
                    endif;
                    
                    if(empty($from) || empty($to))
                        continue;
                    
                    $headers[] = 'From: ' . $from;
                    $headers[] = 'Content-Type: text/html';
                    $headers[] = 'charset=UTF-8';
                     
                    wp_mail($to, $subject, $content, $headers, $attachments);
                    
                }
                
                // Custom
                elseif(get_row_layout() === 'custom'){
                    
                    $action_name = get_sub_field('acfe_form_custom_action');
                    
                    do_action($action_name, $form_name, $post_id, $form);
                    
                }
                
            endwhile;
        endif;
        
    }
    
    // Allow form post_id null
    function validate_post_id($post_id, $old_post_id){
        
        if($old_post_id === null)
            return false;

        return $post_id;
        
    }

    // Remove '1 field requires attention'
    function error_translation($translated_text, $text, $domain){
        
        if($domain !== 'acf')
            return $translated_text;

        if(in_array($text, array('1 field requires attention', '%d fields require attention')))
            return ' ';

        return $translated_text;
        
    }

    // Set active to true for submissions in order to set visibility
    function get_field_groups($field_groups){
        
        if(empty($field_groups))
            return $field_groups;
        
        $post_id = get_the_ID();
        if(!$post_id)
            return $field_groups;
        
        if(get_post_type($post_id) !== 'acfe-form-submission')
            return $field_groups;
        
        foreach($field_groups as &$field_group){
            
            $field_group['active'] = true;
            
        }
        
        return $field_groups;
        
    }
    
    function get_field_group_visibility($result, $rule, $screen, $field_group){
        
        if(!isset($screen['post_type']) || $screen['post_type'] !== 'acfe-form-submission')
            return $result;
        
        $post_id = $screen['post_id'];
        
        $_field_groups = get_field('acfe_form_submission_field_groups', $post_id);
        
        if(empty($_field_groups))
            return $result;
        
        foreach($_field_groups as $_field_group_key){
            
            if($field_group['key'] !== $_field_group_key)
                continue;
            
            return true;
            
        }
        
        return $result;
        
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
            'label'     => 'Content shortcode',
            'value'     => '',
            'message'   => '<code>[acfe_form name="' . $form_name . '"]</code> or <code>[acfe_form ID="' . $form_id . '"]</code>',
            'new_lines' => false,
        );
        
        ob_start();
        ?>
        <pre>&lt;?php acf_form_head(); ?&gt;
&lt;?php get_header(); ?&gt;
    
    &lt;!-- Using Form Name --&gt;
    &lt;?php acfe_form(&apos;<?php echo $form_name; ?>&apos;); ?&gt;
    
    &lt;!-- Using Form ID --&gt;
    &lt;?php acfe_form(<?php echo $form_id; ?>); ?&gt;
    
&lt;?php get_footer(); ?&gt;</pre>
        <?php $html = ob_get_clean();
        
        $fields[] = array(
            'type'      => 'message',
            'name'      => '',
            'label'     => 'PHP integration',
            'value'     => '',
            'message'   => $html,
            'new_lines' => false,
        );
        
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
            
            $submissions = $posts = $emails = $customs = array();
            $found = false;
            
            if(have_rows('acfe_form_actions', $post_id)):
                while(have_rows('acfe_form_actions', $post_id)): the_row();
                    
                    // Submission
                    if(get_row_layout() === 'submission' && acf_get_setting('acfe/modules/dynamic_forms_submissions', true)){
                        
                        $submissions[] = '<span class="acf-js-tooltip dashicons dashicons-media-text" title="Submission"></span>';
                        $found = true;
                    
                    }
                    
                    // Post
                    elseif(get_row_layout() === 'post'){
                        
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
                
                endwhile;
            endif;
            
            if(!empty($submissions))
                echo implode('', $submissions);
            
            if(!empty($posts))
                echo implode('', $posts);
            
            if(!empty($emails))
                echo implode('', $emails);
            
            if(!empty($customs))
                echo implode('', $customs);
            
            if(!$found)
                echo '—';
            
        }
        
        // Field groups
        elseif($column == 'shortcode'){
            
            echo '<code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">[acfe_form name="' . get_field('acfe_form_name', $post_id) . '"]</code>';
            
        }
        
    }
    
    function submission_admin_columns($columns){
        
        if(isset($columns['date']))
            unset($columns['date']);
        
        if(isset($columns['author']))
            unset($columns['author']);
        
        $columns['form'] = __('Form');
        $columns['date'] = __('Date');
        $columns['author'] = __('Author');
        
        return $columns;
        
    }
    
    
    function submission_admin_columns_html($column, $post_id){
        
        // Name
        if($column == 'form'){
            
            $form_id = get_field('acfe_form_submission_form', $post_id);
            $form = get_post($form_id);
            
            if(empty($form_id) || empty($form)){
                
                echo '—';
                return;
                
            }
            
            echo '<a href="' . admin_url('post.php?post=' . $form_id . '&action=edit') . '">' . get_the_title($form_id) . '</a>';
            
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
     * ACF Form: render_form
     *
     */
    function render_form($args = array()){
        
        // array
		if( is_array($args) ) {
			
			$args = $this->validate_form( $args );
			
		// id
		} else {
			
			$args = $this->get_form( $args );
			
		}
		
		
		// bail early if no args
		if( !$args ) return false;
		
		
		// load values from this post
		$post_id = $args['post_id'];
		
		
		// dont load values for 'new_post'
		if( $post_id === 'new_post' ) $post_id = false;
		
		
		// register local fields
		foreach( $this->fields as $k => $field ) {
			
			acf_add_local_field($field);
			
		}
		
		
		// vars
		$field_groups = array();
		$fields = array();
		
		
		// post_title
		if( $args['post_title'] ) {
			
			// load local field
			$_post_title = acf_get_field('_post_title');
			$_post_title['value'] = $post_id ? get_post_field('post_title', $post_id) : '';
			
			
			// append
			$fields[] = $_post_title;
			
		}
		
		
		// post_content
		if( $args['post_content'] ) {
			
			// load local field
			$_post_content = acf_get_field('_post_content');
			$_post_content['value'] = $post_id ? get_post_field('post_content', $post_id) : '';
			
			
			// append
			$fields[] = $_post_content;
					
		}
		
        // Custom HTML
		if( isset($args['custom_html']) && !empty($args['custom_html']) ) {
			
			$field_groups = false;
		
		}
        
		// specific fields
		elseif( $args['fields'] ) {
			
			foreach( $args['fields'] as $selector ) {
				
				// append field ($strict = false to allow for better compatibility with field names)
				$fields[] = acf_maybe_get_field( $selector, $post_id, false );
				
			}
			
		}
        
        // Field groups
        elseif( $args['field_groups'] ) {
			
			foreach( $args['field_groups'] as $selector ) {
			
				$field_groups[] = acf_get_field_group( $selector );
				
			}
			
		}
        
        // New post: field groups
        elseif( $args['post_id'] == 'new_post' ) {
			
			$field_groups = acf_get_field_groups( $args['new_post'] );
		
		}
        
        // Current post: field groups
        else {
			
			$field_groups = acf_get_field_groups(array(
				'post_id' => $args['post_id']
			));
			
		}
        
        
		
		
		//load fields based on field groups
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $field_group ) {
				
				$field_group_fields = acf_get_fields( $field_group );
				
				if( !empty($field_group_fields) ) {
					
					foreach( array_keys($field_group_fields) as $i ) {
						
						$fields[] = acf_extract_var($field_group_fields, $i);
					}
					
				}
			
			}
		
		}
		
		
		// honeypot
		if( $args['honeypot'] ) {
			
			$fields[] = acf_get_field('_validate_email');
			
		}
		
		
		// updated message
		if( !empty($_GET['updated']) ) {
            
            if($args['updated_message']){
                
                if(!empty($args['html_updated_message'])){
                    
                    printf( $args['html_updated_message'], $args['updated_message'] );
                    
                }else{
                    
                    echo $args['updated_message'];
                    
                }
                
            }
            
            if(isset($args['updated_hide_form']) && !empty($args['updated_hide_form']))
                return;
			
		}
        
        add_filter('acf/prepare_field', function($field) use($args){
            
            $field['wrapper']['class'] .= $args['fields_wrapper_class'];
            $field['class'] .= $args['fields_class'];
            
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
		if( $args['form'] ): ?>
		
		<form <?php acf_esc_attr_e( $args['form_attributes']); ?>>
			
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
			
            if(isset($args['custom_html']) && !empty($args['custom_html'])) {
                
                echo acfe_form_render_fields($args['custom_html'], $post_id, $args);
            
            } else {
                
                // render
                acf_render_fields( $fields, $post_id, $args['field_el'], $args['instruction_placement'] );
                
            }
			
			// html after fields
			echo $args['html_after_fields'];
			
			
			?>
		</div>
		
		<?php if((!isset($args['form_submit']) && $args['form']) || (isset($args['form_submit']) && !empty($args['form_submit']))): ?>
		
            <div class="acf-form-submit">
                
                <?php printf( $args['html_submit_button'], $args['submit_value'] ); ?>
                <?php echo $args['html_submit_spinner']; ?>
                
            </div>
        
        <?php endif; ?>
		
        <?php if( $args['form'] ): ?>
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
				'layout_submission' => array(
					'key' => 'layout_submission',
					'name' => 'submission',
					'label' => 'Submission action',
					'display' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_acfe_form_submission_post_type',
							'label' => 'Post type',
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
							'hide_admin' => 0,
							'acfe_permissions' => '',
							'message' => 'Submission',
							'new_lines' => '',
							'esc_html' => 0,
						),
						array(
							'key' => 'field_acfe_form_submission_post_status',
							'label' => 'Post status',
							'name' => 'acfe_form_submission_post_status',
							'type' => 'acfe_post_statuses',
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
							'field_type' => 'select',
							'default_value' => '',
							'return_format' => 'name',
							'allow_null' => 0,
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
							'placeholder' => '',
							'post_status' => array(
							),
						),
						array(
							'key' => 'field_acfe_form_submission_post_title_group',
							'label' => 'Post title',
							'name' => 'acfe_form_submission_post_title_group',
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
									'key' => 'field_acfe_form_submission_post_title',
									'label' => '',
									'name' => 'acfe_form_submission_post_title',
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
										0 => 'publish',
									),
									'allow_null' => 0,
									'multiple' => 0,
									'ui' => 0,
									'return_format' => 'value',
									'ajax' => 0,
									'placeholder' => '',
								),
								array(
									'key' => 'field_acfe_form_submission_post_title_custom',
									'label' => '',
									'name' => 'acfe_form_submission_post_title_custom',
									'type' => 'text',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_submission_post_title',
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
									'placeholder' => 'My {field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
							),
						),
						array(
							'key' => 'field_acfe_form_submission_post_author_group',
							'label' => 'Post author',
							'name' => 'acfe_form_submission_post_author_group',
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
									'key' => 'field_acfe_form_submission_post_author',
									'label' => '',
									'name' => 'acfe_form_submission_post_author',
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
									'key' => 'field_acfe_form_submission_post_author_custom',
									'label' => '',
									'name' => 'acfe_form_submission_post_author_custom',
									'type' => 'user',
									'instructions' => '',
									'required' => 1,
									'conditional_logic' => array(
										array(
											array(
												'field' => 'field_acfe_form_submission_post_author',
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
							'key' => 'field_acfe_form_submission_field_groups',
							'label' => 'Field groups',
							'name' => 'acfe_form_submission_field_groups',
							'type' => 'checkbox',
							'instructions' => 'Attach field groups to the submission',
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
						array(
							'key' => 'field_acfe_form_submission_meta',
							'label' => 'Meta fields',
							'name' => 'acfe_form_submission_meta',
							'type' => 'checkbox',
							'instructions' => 'Choose which fields should be saved',
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
									'placeholder' => 'My {field:name} *',
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
									'placeholder' => 'my-{field:name} *',
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
							'label' => 'Load data',
							'name' => 'acfe_form_post_update_load',
							'type' => 'true_false',
							'instructions' => 'Prefill the form inputs if datas are available',
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
									'placeholder' => '123{field:name} *',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
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
									'placeholder' => 'My {field:name} *',
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
									'placeholder' => 'my-{field:name} *',
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
							'instructions' => 'Choose which fields should be saved',
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
							'instructions' => '',
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