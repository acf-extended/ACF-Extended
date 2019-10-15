<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form')):

class acfe_form{
	
	public $post_type = '';
	public $fields_groups = array();
    
	function __construct(){
        
        // Post Type
        $this->post_type = 'acfe-form';
		
		// Admin
        add_action('init',                                                          array($this, 'init'));
        add_action('admin_menu',                                                    array($this, 'admin_menu'));
        add_action('current_screen',                                                array($this, 'current_screen'));
        
        // ACF
        add_filter('acf/get_post_types',                                            array($this, 'filter_post_type'), 10, 2);
        add_filter('acf/pre_load_post_id',                                          array($this, 'validate_post_id'), 10, 2);
        add_filter('gettext',                                                       array($this, 'error_translation'), 99, 3);
        
        // Shortcode
        add_shortcode('acfe_form',                                                  array($this, 'add_shortcode'));
		
		// Fields
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
            )
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
        
        add_action('load-edit.php',     array($this, 'load_list'));
        add_action('load-post.php',     array($this, 'load_post'));
        add_action('load-post-new.php', array($this, 'load_post_new'));
        
        add_action('load-post.php',     array($this, 'load'));
        add_action('load-post-new.php', array($this, 'load'));
        
    }
    
    function load_list(){
        
        // Posts per page
        add_filter('edit_posts_per_page', function(){
            return 999;
        });
        
        // Order
        add_action('pre_get_posts', function($query){
            
            if(!$query->is_main_query())
                return;
            
            if(!acf_maybe_get($_REQUEST,'orderby'))
                $query->set('orderby', 'name');
            
            if(!acf_maybe_get($_REQUEST,'order'))
                $query->set('order', 'ASC');
            
        });
        
        // Columns
        add_filter('manage_edit-' . $this->post_type . '_columns',         array($this, 'admin_columns'));
        add_action('manage_' . $this->post_type . '_posts_custom_column',  array($this, 'admin_columns_html'), 10, 2);
        
    }
    
    function load_post(){
    
        // vars
        $this->fields_groups = $this->get_fields_groups();
        
        add_action('add_meta_boxes',        array($this, 'add_meta_boxes'));
        
        add_filter('acf/pre_render_fields', array($this, 'render_integration'), 10, 2);
        
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
            $this->post_type, 
            
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
    
    function map_field_value($field_key, $acf = false){
        
        if(!$acf)
            $acf = $_POST['acf'];

        if(!$acf)
            return false;
        
        $data = $this->map_fields_values($acf);
        
        // Field key
        if(acf_is_field_key($content)){
            
            if(empty($data))
                return false;
            
            foreach($data as $field){
                
                if($field['key'] !== $content)
                    continue;
                
                return $field['value'];
                
            }
            
        }
        
        // Content
        else{
            
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
        
    }
    
    function map_field_get_value($content){
        
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
    
    function admin_columns($columns){
        
        if(isset($columns['date']))
            unset($columns['date']);
        
        $columns['name'] = __('Name');
        $columns['field_groups'] = __('Field groups', 'acf');
        $columns['actions'] = __('Actions');
        $columns['shortcode'] = __('Shortcode');
        
        return $columns;
        
    }
    
    function admin_columns_html($column, $post_id){
        
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

function acfe_form_render_fields($content, $post_id, $args){
    
	return acf()->acfe_form->render_fields($content, $post_id, $args);
	
}

function acfe_form_map_field_value($field, $acf){
    
	return acf()->acfe_form->map_field_value($field, $acf);
	
}

function acfe_form_map_field_get_value($field){
    
	return acf()->acfe_form->map_field_get_value($field);
	
}

function acfe_form_filter_meta($meta, $acf){
    
	return acf()->acfe_form->filter_meta($meta, $acf);
	
}