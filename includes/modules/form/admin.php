<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form')):

class acfe_form{
    
    public $post_type = '';
    public $fields_groups = array();
    
    public $posts = array();
    public $users = array();
    
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
        add_filter('acf/prepare_field/name=acfe_form_actions',                      array($this, 'prepare_actions'));
        add_filter('acf/prepare_field/name=acfe_form_field_groups',                 array($this, 'field_groups_choices'));
        add_filter('acf/prepare_field/name=acfe_form_email_files',                  array($this, 'prepare_email_files'));
        
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
        
        // Posts
        $this->posts = array(
            'acfe_form_post_save_target',
            'acfe_form_post_save_post_parent',
            'acfe_form_post_load_source',
        );
        
        foreach($this->posts as $tag){
            
            add_filter('acf/prepare_field/name=' . $tag,                            array($this, 'prepare_value_post'));
            
        }
        
        // Users
        $this->users = array(
            'acfe_form_post_save_post_author',
            'acfe_form_user_save_target',
            'acfe_form_user_load_source',
        );
        
        foreach($this->users as $tag){
            
            add_filter('acf/prepare_field/name=' . $tag,                            array($this, 'prepare_value_user'));
            
        }
        
        // Terms
        $this->terms = array(
            'acfe_form_term_save_target',
            'acfe_form_term_save_parent',
            'acfe_form_term_load_source',
        );
        
        foreach($this->terms as $tag){
            
            add_filter('acf/prepare_field/name=' . $tag,                            array($this, 'prepare_value_term'));
            
        }
        
        // Ajax
        add_action('wp_ajax_acf/fields/select/query',                               array($this, 'ajax_query_post'), 5);
        add_action('wp_ajax_nopriv_acf/fields/select/query',                        array($this, 'ajax_query_post'), 5);
        
        add_action('wp_ajax_acf/fields/select/query',                               array($this, 'ajax_query_user'), 5);
        add_action('wp_ajax_nopriv_acf/fields/select/query',                        array($this, 'ajax_query_user'), 5);
        
        add_action('wp_ajax_acf/fields/select/query',                               array($this, 'ajax_query_term'), 5);
        add_action('wp_ajax_nopriv_acf/fields/select/query',                        array($this, 'ajax_query_term'), 5);
        
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
        
        // Misc actions
        add_action('post_submitbox_misc_actions', array($this, 'misc_actions'));
        
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
        $form_id = $args['form_id'];
        $form_name = $args['form_name'];
        
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
    
    // Post Object & Relationship
    function format_value_post_object($value, $_value, $post_id, $field){
        
        $value = acf_get_array($_value);
        $array = array();
        
        foreach($value as $p_id){
            
            $array[] = get_the_title($p_id);
            
        }
        
        return implode(', ', $array);
        
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
            
            $return[] = $this->format_value_array($v);
            
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
    
    function map_fields_values($array, &$data = array()){
        
        if(empty($array))
            return false;
            
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
            
            if(is_array($value)){
                
                $this->map_fields_values($value, $data);
                
            }
            
        }
        
        return $data;
        
    }
    
    function map_field_value($content, $acf = false, $post_id = 0){
        
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
                                
                            $content = str_replace('{field:' . $field_key . '}', $this->format_value($field['value'], $post_id, $field['field']), $content);
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
                        
                        $label = !empty($field['label']) ? $field['label'] : $field['name'];
                        
                        $content_html .= $label . ': ' . $this->format_value($field['value'], $post_id, $field['field']) . "<br/>\n";
                        
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
    
    /**
     * Perform custom validation on a field's value
     * Reference: <a href="https://www.advancedcustomfields.com/resources/acf-validate_value/" target="_blank">https://www.advancedcustomfields.com/resources/acf-validate_value/</a>
     */
    
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

add_action(&apos;acfe/form/validation/form=<?php echo $form_name; ?>&apos;, &apos;my_<?php echo $_form_name; ?>_validation&apos;, 10, 2);
function my_<?php echo $_form_name; ?>_validation($form, $post_id){
    
    /**
     * @array       $form       Form arguments
     * @int/string  $post_id    Current post id
     */
    
    
    /**
     * Get the form input value named '<?php echo $field['name']; ?>'
     * This is the value entered by the user during the form submission
     */
    $<?php echo $_field_name; ?> = get_field(&apos;<?php echo $field['name']; ?>&apos;);
    $<?php echo $_field_name; ?>_unformatted = get_field(&apos;<?php echo $field['name']; ?>&apos;, false, false);
    
    if($<?php echo $_field_name; ?> === &apos;Hello&apos;){
        
        acfe_add_validation_error(&apos;<?php echo $field['name']; ?>&apos;, &apos;Hello is not allowed&apos;);
        
    }
    
    
    /**
     * Get the field value '<?php echo $field['name']; ?>' from the post ID 145
     */
    $post_<?php echo $_field_name; ?> = get_field(&apos;<?php echo $field['name']; ?>&apos;, 145);
    $post_<?php echo $_field_name; ?>_unformatted = get_field(&apos;<?php echo $field['name']; ?>&apos;, 145, false);
    
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

add_action(&apos;acfe/form/submit/form=<?php echo $form_name; ?>&apos;, &apos;my_<?php echo $_form_name; ?>_submit&apos;, 10, 2);
function my_<?php echo $_form_name; ?>_submit($form, $post_id){
    
    /**
     * @array       $form       Form arguments
     * @int/string  $post_id    Current post id
     */
    
    
    /**
     * Get the form input value named '<?php echo $field['name']; ?>'
     * This is the value entered by the user during the form submission
     */
    $<?php echo $_field_name; ?> = get_field(&apos;<?php echo $field['name']; ?>&apos;);
    $<?php echo $_field_name; ?>_unformatted = get_field(&apos;<?php echo $field['name']; ?>&apos;, false, false);
    
    if($<?php echo $_field_name; ?> === &apos;do_something&apos;){
        
        // Do something
        
    }
    
    
    /**
     * Get the field value '<?php echo $field['name']; ?>' from the post ID 145
     */
    $post_<?php echo $_field_name; ?> = get_field(&apos;<?php echo $field['name']; ?>&apos;, 145);
    $post_<?php echo $_field_name; ?>_unformatted = get_field(&apos;<?php echo $field['name']; ?>&apos;, 145, false);
    
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
            
            echo '<code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">[acfe_form name="' . get_field('acfe_form_name', $post_id) . '"]</code>';
            
        }
        
    }
    
    function ajax_query_post(){
        
        if(!acf_verify_ajax())
            die();
        
        // get choices
        $response = $this->get_ajax_query_post($_POST);
        
        // return
        if(!$response)
            return;
        
        // return ajax
        acf_send_ajax_results($response);
        
    }
    
    function get_ajax_query_post($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'		=> 0,
            's'				=> '',
            'field_key'		=> '',
            'paged'			=> 1
        ));
        
        // load field
        $field = acf_get_field($options['field_key']);
        if(!$field)
            return false;
        
        // Target field name
        if(!in_array($field['name'], $this->posts))
            return false;
        
        $field_type = acf_get_field_type('post_object');
        
        // vars
        $results = array();
        $args = array();
        $s = false;
        $is_search = false;
        
        // paged
        $args['posts_per_page'] = 20;
        $args['paged'] = $options['paged'];
        
        // search
        if($options['s'] !== ''){
            
            // strip slashes (search may be integer)
            $s = wp_unslash( strval($options['s']) );
            
            // update vars
            $args['s'] = $s;
            $is_search = true;
            
        }
        
        // post_type
        $args['post_type'] = acf_get_post_types();
        
        // get posts grouped by post type
        $groups = acf_get_grouped_posts($args);
        
        // bail early if no posts
        if(empty($groups))
            return false;
        
        if(!$is_search && $args['paged'] === 1){
            
            $results[] = array(
                'text'		=> 'Generic',
                'children'	=> array(
                    array(
                        'id'    => 'current_post',
                        'text'  => 'Current Post',
                    )
                )
            );
        
        }
        
        // loop
        foreach(array_keys($groups) as $group_title){
            
            // vars
            $posts = acf_extract_var($groups, $group_title);
            
            // data
            $data = array(
                'text'		=> $group_title,
                'children'	=> array()
            );
            
            // convert post objects to post titles
            foreach(array_keys($posts) as $post_id){
                
                $posts[$post_id] = $field_type->get_post_title($posts[$post_id], $field, $options['post_id'], $is_search);
                
            }
            
            // order posts by search
            if($is_search && empty($args['orderby'])){
                
                $posts = acf_order_by_search($posts, $args['s']);
                
            }
            
            // append to $data
            foreach(array_keys($posts) as $post_id){
                
                $data['children'][] = $field_type->get_post_result($post_id, $posts[$post_id]);
                
            }
            
            // append to $results
            $results[] = $data;
            
        }
        
        
        // optgroup or single
        $post_type = acf_get_array($args['post_type']);
        
        if(count($post_type) == 1){
            
            $results = $results[0]['children'];
            
        }
        
        // vars
        $response = array(
            'results'	=> $results,
            'limit'		=> $args['posts_per_page']
        );
        
        // return
        return $response;
        
    }
    
    function prepare_value_post($field){
        
        if(!acf_maybe_get($field, 'value'))
            return $field;
        
        $field['choices'] = array();
        $field['choices']['current_post'] = 'Current Post';
        
        $field_type = acf_get_field_type('post_object');
        $field['post_type'] = acf_get_post_types();
        
        // load posts
		$posts = $field_type->get_posts($field['value'], $field);
		
		if($posts){
				
			foreach(array_keys($posts) as $i){
				
				// vars
				$post = acf_extract_var($posts, $i);
				
				// append to choices
				$field['choices'][$post->ID] = $field_type->get_post_title( $post, $field );
				
			}
			
		}
        
        return $field;
        
    }
    
    function ajax_query_user(){
        
        if(!acf_verify_ajax())
            die();
        
        // get choices
        $response = $this->get_ajax_query_user($_POST);
        
        // return
        if(!$response)
            return;
        
        // return ajax
        acf_send_ajax_results($response);
        
    }
    
    function get_ajax_query_user($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'		=> 0,
            's'				=> '',
            'field_key'		=> '',
            'paged'			=> 1
        ));
        
        
        // load field
        $field = acf_get_field($options['field_key']);
        if(!$field)
            return false;
        
        // Target field name
        if(!in_array($field['name'], $this->users))
            return false;
        
        $field_type = acf_get_field_type('user');
        
        // vars
        $results = array();
        $args = array();
        $s = false;
        $is_search = false;
        
        // paged
        $args['users_per_page'] = 20;
        $args['paged'] = $options['paged'];
        
        // search
        if($options['s'] !== ''){
            
            // strip slashes (search may be integer)
            $s = wp_unslash( strval($options['s']) );
            
            // update vars
            $args['s'] = $s;
            $is_search = true;
            
        }
        
        // role
        if(!empty($field['role'])){
        
            $args['role'] = acf_get_array( $field['role'] );
            
        }
        
        // search
        if($is_search){
            
            // append to $args
            $args['search'] = '*' . $options['s'] . '*';
            
            // add reference
            $field_type->field = $field;
            
            // add filter to modify search colums
            add_filter('user_search_columns', array($field_type, 'user_search_columns'), 10, 3);
            
        }
        
        // get users
        $groups = acf_get_grouped_users($args);
        
        // Current user
        if(!$is_search && $args['paged'] === 1){
            
            $results[] = array(
                'text'		=> 'Generic',
                'children'	=> array(
                    array(
                        'id'    => 'current_user',
                        'text'  => 'Current User',
                    ),
                    array(
                        'id'    => 'current_post_author',
                        'text'  => 'Current Post Author',
                    ),
                )
            );
        
        }
        
        // loop
        if(!empty($groups)){
            
            foreach(array_keys($groups) as $group_title){
                
                // vars
                $users = acf_extract_var( $groups, $group_title );
                $data = array(
                    'text'		=> $group_title,
                    'children'	=> array()
                );
                
                // append users
                foreach( array_keys($users) as $user_id ) {
                    
                    $users[ $user_id ] = $field_type->get_result( $users[ $user_id ], $field, $options['post_id'] );
                    
                };
                
                // order by search
                if( $is_search && empty($args['orderby']) ) {
                    
                    $users = acf_order_by_search( $users, $args['s'] );
                    
                }
                
                // append to $data
                foreach( $users as $id => $title ) {
                    
                    $data['children'][] = array(
                        'id'	=> $id,
                        'text'	=> $title
                    );
                    
                }
                
                // append to $r
                $results[] = $data;
                
            }
            
            // optgroup or single
            if(!empty($args['role']) && count($args['role']) == 1){
                
                $results = $results[0]['children'];
                
            }
        }
        
        // vars
        $response = array(
            'results'	=> $results,
            'limit'		=> $args['users_per_page']
        );
        
        // return
        return $response;
        
    }
    
    function prepare_value_user($field){
        
        if(!acf_maybe_get($field, 'value'))
            return $field;
        
        $field['choices'] = array();
        $field['choices']['current_user'] = 'Current User';
        $field['choices']['current_post_author'] = 'Current Post Author';
        
        $field_type = acf_get_field_type('user');
        
        // Clean value into an array of IDs.
        $user_ids = array_map('intval', acf_array($field['value']));
        
        // Find users in database (ensures all results are real).
        $users = acf_get_users(array(
            'include' => $user_ids
        ));
        
        // Append.
        if($users){
            
            foreach($users as $user){
                $field['choices'][$user->ID] = $field_type->get_result($user, $field);
            }
            
        }
        
        return $field;
        
    }
    
    function ajax_query_term(){
        
        if(!acf_verify_ajax())
            die();
        
        // get choices
        $response = $this->get_ajax_query_term($_POST);
        
        // return
        if(!$response)
            return;
        
        // return ajax
        acf_send_ajax_results($response);
        
    }
    
    function get_ajax_query_term($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'		=> 0,
            's'				=> '',
            'field_key'		=> '',
            'paged'			=> 1
        ));
        
        // load field
        $field = acf_get_field($options['field_key']);
        if(!$field)
            return false;
        
        // Target field name
        if(!in_array($field['name'], $this->terms))
            return false;
        
        // vars
        $results = array();
        $args = array();
        $s = false;
        $is_search = false;
        
        // paged
        $args['posts_per_page'] = 20;
        $args['paged'] = $options['paged'];
        
        // search
        if($options['s'] !== ''){
            
            // strip slashes (search may be integer)
            $s = wp_unslash( strval($options['s']) );
            
            // update vars
            $args['s'] = $s;
            $is_search = true;
            
        }
        
        $terms_args = array(
            'number' => $args['posts_per_page'],
            'offset' => ($args['paged'] - 1) * $args['posts_per_page'],
        );
        
        // get grouped terms
        $terms = acf_get_grouped_terms($terms_args);
        $groups = acf_get_choices_from_grouped_terms($terms, 'name');
        
        // bail early if no posts
        if(empty($groups))
            return false;
        
        if(!$is_search && $args['paged'] === 1){
            
            $results[] = array(
                'text'		=> 'Generic',
                'children'	=> array(
                    array(
                        'id'    => 'current_term',
                        'text'  => 'Current Term',
                    )
                )
            );
        
        }
        
        // loop
        foreach(array_keys($groups) as $group_title){
            
            // vars
            $terms = acf_extract_var($groups, $group_title);
            
            // data
            $data = array(
                'text'		=> $group_title,
                'children'	=> array()
            );
            
            if($is_search && empty($args['orderby'])){
                
                $terms = acf_order_by_search($terms, $args['s']);
                
            }
            
            // append to $data
            foreach($terms as $term_id => $name){
                
                $data['children'][] = array(
                    'id' => $term_id, 
                    'text' => $name
                );
                
            }
            
            // append to $results
            $results[] = $data;
            
        }
        
        // vars
        $response = array(
            'results'	=> $results,
            'limit'		=> $args['posts_per_page']
        );
        
        // return
        return $response;
        
    }
    
    function prepare_value_term($field){
        
        if(!acf_maybe_get($field, 'value'))
            return $field;
        
        $value = $field['value'];
        
        $field['choices'] = array();
        $field['choices']['current_term'] = 'Current Term';
        
        if(is_array($value))
            $value = $value[0];
        
        $term = get_term($value);
        
        if($term){
            
            $field['choices'][$term->term_id] = $term->name;
            
        }
        
        return $field;
        
    }
    
}

// initialize
acfe()->acfe_form = new acfe_form();

endif;

function acfe_form_render_fields($content, $post_id, $args){
    
    return acfe()->acfe_form->render_fields($content, $post_id, $args);
    
}

function acfe_form_map_field_value($field, $acf, $post_id = 0){
    
    return acfe()->acfe_form->map_field_value($field, $acf, $post_id);
    
}

function acfe_form_map_field_get_value($field){
    
    return acfe()->acfe_form->map_field_get_value($field);
    
}

function acfe_form_filter_meta($meta, $acf){
    
    return acfe()->acfe_form->filter_meta($meta, $acf);
    
}