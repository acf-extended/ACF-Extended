<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form')):

class acfe_module_form extends acfe_module{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name         = 'form';
        $this->plural       = 'forms';
        $this->setting      = 'modules/forms';
        
        $this->post_type    = 'acfe-form';
        $this->args         = array(
            'label'             => __('Forms', 'acfe'),
            'show_in_menu'      => 'edit.php?post_type=acf-field-group',
            'hierarchical'      => true,
            'menu_icon'         => 'dashicons-layout',
            'labels'            => array(
                'name'          => __('Forms', 'acfe'),
                'singular_name' => __('Form', 'acfe'),
                'menu_name'     => __('Forms', 'acfe'),
                'edit_item'     => __('Edit Form', 'acfe'),
                'add_new'       => __('New Form', 'acfe'),
                'add_new_item'  => __('New Form', 'acfe'),
                'enter_title'   => __('Form Title', 'acfe'),
            ),
        );
        
        $this->messages     = array(
            'export_title'              => __('Export Forms', 'acfe'),
            'export_description'        => __('Export Forms', 'acfe'),
            'export_select'             => __('Select Forms', 'acfe'),
            'export_not_found'          => __('No form available.', 'acfe'),
            'export_not_selected'       => __('No forms selected', 'acfe'),
            'export_success_single'     => __('1 form exported', 'acfe'),
            'export_success_multiple'   => __('%s forms exported', 'acfe'),
            'export_instructions'       => __('It is recommended to include this code within the <code>acfe/init</code> hook.', 'acfe'),
            'import_title'              => __('Import Forms', 'acfe'),
            'import_description'        => __('Import Forms', 'acfe'),
            'import_success_single'     => __('1 form imported', 'acfe'),
            'import_success_multiple'   => __('%s forms imported', 'acfe'),
        );
    
        $this->export_files = array(
            'single'    => 'form',
            'multiple'  => 'forms',
        );
    
        $this->validate = array('name');
    
        $this->columns  = array(
            'acfe-name'         => __('Name', 'acfe'),
            'acfe-field-groups' => __('Field Groups', 'acfe'),
            'acfe-actions'      => __('Actions', 'acfe'),
            'acfe-shortcode'    => __('Shortcode', 'acfe'),
        );
    
        $this->item     = array(
            'name'          => '',
            'title'         => '',
            'active'        => true,
            'field_groups'  => array(),
            'settings'      => array(
                'location' => false,
                'honeypot' => true,
                'kses'     => true,
                'uploader' => 'default',
            ),
            'attributes'    => array(
                'form' => array(
                    'element' => 'form',
                    'class'   => '',
                    'id'      => '',
                ),
                'fields' => array(
                    'element'       => 'div',
                    'wrapper_class' => '',
                    'class'         => '',
                    'label'         => 'top',
                    'instruction'   => 'label',
                ),
                'submit' => array(
                    'value'   => __('Submit', 'acfe'),
                    'button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
                    'spinner' => '<span class="acf-spinner"></span>',
                )
            ),
            'validation'    => array(
                'hide_error'        => false,
                'hide_revalidation' => false,
                'hide_unload'       => false,
                'errors_position'   => 'above',
                'errors_class'      => '',
                'messages'          => array(
                    'failure' => __('Validation failed', 'acf'),
                    'success' => __('Validation successful', 'acf'),
                    'error'   => __('1 field requires attention', 'acf'),
                    'errors'  => __('%d fields require attention', 'acf'),
                ),
            ),
            'success'       => array(
                'hide_form' => false,
                'scroll'    => false,
                'message'   => __('Form updated', 'acfe'),
                'wrapper'   => '<div id="message" class="updated">%s</div>',
            ),
            'actions'       => array(),
            'render'        => '',
        );
    
        $this->alias    = array(
            'title' => 'label',
        );
        
        $this->l10n = array(
            'title',
            'attributes.submit.value',
            'validation.messages.failure',
            'validation.messages.success',
            'validation.messages.error',
            'validation.messages.errors',
            'success.message'
        );
        
        $this->add_action('admin_menu', array($this, 'admin_menu'), 999);
        $this->add_action('register_post_type_args', array($this, 'register_post_type_args'), 10, 2);
        
        $this->add_module_action('acfe/module/prepare_item_for_export', array($this, 'prepare_for_export'));
        
    }
    
    
    /**
     * admin_menu
     */
    function admin_menu(){
        
        global $menu;
        
        // get setting
        $top_level = acfe_get_setting('modules/forms/top_level');
        
        // bail early
        if(!$top_level){
            return;
        }
        
        // vars
        $acf_key = false;
        $form_key = false;
        
        // loop menu
        foreach($menu as $key => $item){
            
            switch($item[2]){
                
                case "edit.php?post_type={$this->post_type}": {
                    $form_key = $key;
                    break;
                }
                
                case 'edit.php?post_type=acf-field-group': {
                    $acf_key = $key;
                    break;
                }
                
            }
            
            // check processed
            if($acf_key && $form_key){
                break;
            }
            
        }
        
        // acf menu & form menu found
        if($acf_key && $form_key){
            
            // add form menu right before ACF
            $menu[ strval($acf_key - 0.001) ] = $menu[ $form_key ];
            
            // delete old form menu
            unset($menu[ $form_key ]);
            
        }
        
    }
    
    
    /**
     * register_post_type_args
     *
     * @param $args
     * @param $post_type
     *
     * @return mixed
     */
    function register_post_type_args($args, $post_type){
        
        // set as top level
        if($post_type === $this->post_type){
            if(acfe_get_setting('modules/forms/top_level')){
                $args['show_in_menu'] = true;
            }
        }
        
        // return
        return $args;
    
    }
    
    
    /**
     * load_post
     *
     * acfe/module/load_post
     */
    function load_post(){
        
        global $item;
        
        // deregister selectWoo
        // in case third party plugin enqueue it
        // this cause issues with the Select2 ajax field
        wp_deregister_script('selectWoo');
        wp_register_script('selectWoo', false);
        
        $field_groups = array();
        
        if($item['field_groups']){
            
            foreach($item['field_groups'] as $key){
                
                $field_group = acf_get_field_group($key);
                
                if($field_group){
                    $field_groups[] = $field_group;
                }
                
            }
            
        }
        
        acf_disable_filter('clone');
        
        acfe_add_field_groups_metabox(array(
            'id'            => 'acfe-field-groups',
            'title'         => __('Field Groups', 'acf'),
            'screen'        => $this->post_type,
            'field_groups'  => $field_groups,
        ));
        
        add_meta_box('acfe-form-integration', __('Integration', 'acfe'), array($this, 'meta_box_side'), $this->post_type, 'side');
        
        add_filter('acf/prepare_field/type=wysiwyg', function($field){
            
            $field['delay'] = false;
            $field['acfe_wysiwyg_auto_init'] = false;
            
            return $field;
            
        }, 15);
        
    }
    
    
    /**
     * meta_box_side
     *
     * @param $post
     */
    function meta_box_side($post){
        
        global $item;
        
        $form_id = $item['ID'];
        $form_name = $item['name'];
        $form_title = $item['title'];
        
        ?>

        <div class="acf-field">

            <div class="acf-label">
                <label><?php _e('Documentation', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">

                <ul style="list-style:inside; margin-top:0;">
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms" target="_blank"><?php _e('Forms', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/integration" target="_blank"><?php _e('Integration', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/form-cheatsheet" target="_blank"><?php _e('Template Tags', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/form-hooks" target="_blank"><?php _e('Hooks', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/form-helpers" target="_blank"><?php _e('Helpers', 'acfe'); ?></a></li>
                </ul>

            </div>
            
            <div class="acf-label">
                <label><?php _e('Guides', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">

                <ul style="list-style:inside; margin:0;">
                    <li><a href="https://www.acf-extended.com/guides/form-title-content-fields" target="_blank"><?php _e('Dummy Title & Content Fields', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/guides/hide-a-field-on-front-end" target="_blank"><?php _e('Hide a Field on Front-End', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/guides/passing-data-to-a-form" target="_blank"><?php _e('Passing Data to a Form', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/guides/using-actions-output-data" target="_blank"><?php _e('Using Actions Output Data', 'acfe'); ?></a></li>
                </ul>

            </div>

        </div>
        
        <?php if($item['name']){ ?>
        
        <div class="acf-field">

            <div class="acf-label">
                <label><?php _e('Shortcodes', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">

                <code>[acfe_form ID="<?php echo $form_id; ?>"]</code><br /><br />
                <code>[acfe_form name="<?php echo $form_name; ?>"]</code>

            </div>

        </div>

        <div class="acf-field">

            <div class="acf-label">
                <label><?php _e('PHP code', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">
                
                <pre>&lt;?php get_header(); ?&gt;

&lt;!-- <?php echo $form_title; ?> --&gt;
&lt;?php acfe_form(&apos;<?php echo $form_name; ?>&apos;); ?&gt;

&lt;?php get_footer(); ?&gt;</pre>

            </div>

        </div>
        
        <?php } ?>

        <script type="text/javascript">
            if(typeof acf !== 'undefined'){

                acf.newPostbox(<?php echo wp_json_encode(array(
                    'id'    => 'acfe-form-integration',
                    'key'   => '',
                    'style' => 'default',
                    'label' => 'top',
                    'edit'  => false
                )); ?>);

            }
        </script>
        <?php
    }
    
    
    /**
     * validate_item
     *
     * @param $item
     *
     * @return array|mixed
     */
    function validate_item($item = array()){
        
        // process parent
        // $item = parent::validate_item($item);
        
        // already valid
        if(is_array($item) && !empty($item['_valid'])){
            return $item;
        }
        
        // convert
        $item['ID']     = (int) acf_maybe_get($item, 'ID', 0);
        $item['active'] = (bool) acf_maybe_get($item, 'active', true);
        $item['_valid'] = true;
        
        // default item
        $defaults = wp_parse_args($this->item, array(
            'ID'    => 0,
            'name'  => '',
            'label' => '',
        ));
        
        // parse defaults
        $item = acfe_parse_args_r($item, $defaults);
        
        // process alias
        foreach($this->alias as $k => $alias){
            if(!empty($item[ $alias ])){
                
                // set 'page_title' = 'label'
                $item[ $k ] = $item[ $alias ];
                
            }
        }
        
        // validate keys types
        $item['field_groups'] = acf_get_array($item['field_groups']);
        $item['actions'] = acf_get_array($item['actions']);
        
        // validate actions
        $item = $this->validate_actions($item);
        
        // filters
        $item = $this->apply_module_filters('acfe/module/validate_item', $item);
        
        return $item;
        
    }
    
    
    /**
     * validate_actions
     *
     * @param $item
     *
     * @return mixed
     */
    function validate_actions($item){
    
        // extract actions
        $actions = $item['actions'];
        $item['actions'] = array();
    
        // loop actions
        foreach($actions as $action){
        
            // get instance
            $instance = acfe_get_form_action_type($action['action']);
        
            // validate action
            if($instance){
                $item['actions'][] = $instance->validate_item($action);
            }
        
        }
        
        return $item;
        
    }
    
    
    /**
     * prepare_item_for_export
     *
     * @param $item
     *
     * @return array|mixed
     */
    function prepare_for_export($item = array()){
        
        // extract actions
        $actions = $item['actions'];
        $item['actions'] = array();
        
        // loop actions
        foreach($actions as $action){
            
            // get instance
            $instance = acfe_get_form_action_type($action['action']);
            
            // prepare action for export
            if($instance){
                $item['actions'][] = $instance->prepare_action_for_export($action);
            }
            
        }
        
        return $item;
        
    }
    
    
    /**
     * prepare_load_item
     *
     * acfe/module/prepare_load_item
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_load_item($item){
    
        // settings
        foreach(array_keys($item['settings']) as $key){
            $item[ $key ] = $item['settings'][ $key ];
        }
    
        // attributes: submit
        if(isset($item['attributes']['submit']) && !empty($item['attributes']['submit'])){
        
            $item['submit'] = true;
        
            foreach(array_keys($item['attributes']['submit']) as $key){
                $item[ "submit_{$key}" ] = $item['attributes']['submit'][ $key ];
            }
            
            unset($item['attributes']['submit']);
        
        }
        
        // attributes
        foreach(array_keys($item['attributes']) as $key){
            $item[ $key ] = $item['attributes'][ $key ];
        }
        
        // attributes: form
        foreach(array('element', 'class', 'id') as $key){
            $item['form']["form_$key"] = $item['form'][ $key ];
            unset($item['form'][ $key ]);
        }
        
        // attributes: fields
        foreach(array('element', 'wrapper_class', 'class', 'label', 'instruction') as $key){
            $item['fields']["fields_$key"] = $item['fields'][ $key ];
            unset($item['fields'][ $key ]);
        }
        
        // validation
        foreach(array_keys($item['validation']) as $key){
            $item[ $key ] = $item['validation'][ $key ];
        }
        
        // validation: messages
        foreach(array('failure', 'success', 'error', 'errors') as $key){
            $item['messages']["messages_$key"] = $item['validation']['messages'][ $key ];
            unset($item['validation']['messages'][ $key ]);
        }
    
        // success
        foreach(array_keys($item['success']) as $key){
            $item[ "success_{$key}" ] = $item['success'][ $key ];
        }
        
        // cleanup
        unset($item['settings']);
        unset($item['attributes']);
        unset($item['validation']);
        unset($item['success']);
        
        // prepare load action
        $item = $this->prepare_load_actions($item);
        
        return $item;
        
    }
    
    
    /**
     * prepare_load_actions
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_load_actions($item){
    
        // extract actions
        $actions = $item['actions'];
        $item['actions'] = array();
        
        // loop actions
        foreach($actions as $action){
            
            // get instance
            $instance = acfe_get_form_action_type($action['action']);
            
            if($instance){
                
                // layout name
                $action = array_merge(array('acf_fc_layout' => $action['action']), $action);
    
                // prepare action
                $action = $instance->prepare_load_action($action);
                
                // prefix all array keys
                if($instance->prefix){
                    $action = acfe_prefix_array_keys($action, "{$instance->prefix}_", array('acf_fc_layout'));
                }
                
                // append
                $item['actions'][] = $action;
                
            }
            
        }
        
        return $item;
        
    }
    
    
    /**
     * prepare_save_item
     *
     * acfe/module/prepare_save_item
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_item($item){
        
        $item['actions'] = acf_get_array($item['actions']);
    
        // attributes: submit
        if($item['attributes']['submit']){
        
            unset($item['attributes']['submit']);
    
            foreach(array('submit_value', 'submit_button', 'submit_spinner') as $key){
        
                $new_key = str_replace('submit_', '', $key);
        
                $item['attributes']['submit'][ $new_key ] = $item['attributes'][ $key ];
                unset($item['attributes'][ $key ]);
            }
        
        }
    
        // success
        foreach(array_keys($item['success']) as $key){
        
            $new_key = str_replace('success_', '', $key);
        
            $item['success'][ $new_key ] = $item['success'][ $key ];
            unset($item['success'][ $key ]);
        }
        
        // prepare save action
        $item = $this->prepare_save_actions($item);
        
        return $item;
        
    }
    
    
    /**
     * prepare_save_actions
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_actions($item){
    
        // extract actions
        $actions = $item['actions'];
        $item['actions'] = array();
        
        // loop actions
        foreach($actions as $action){
            
            // get instance
            $instance = acfe_get_form_action_type($action['acf_fc_layout']);
            
            // prepare save action
            if($instance){
                $item['actions'][] = $instance->prepare_save_action($action);
            }
        
        }
        
        return $item;
        
    }
    
    
    /**
     * validate_save_item
     *
     * acfe/module/validate_save_item
     *
     * @param $item
     */
    function validate_save_item($item){
        
        $actions = acfe_get_form_action_types();
        
        foreach($actions as $action){
    
            foreach($action->validate as $name){
        
                if(method_exists($action, "validate_{$name}")){
    
                    $key = "field_{$action->prefix}_{$name}";
                    $field = acf_get_field($key);
    
                    add_filter("acf/validate_value/key={$key}", function($valid, $value) use($action, $name, $field, $item){
                        
                        $return = $action->{"validate_{$name}"}($value, $item);
    
                        // empty required
                        if($field && $field['required'] && empty($value) && !is_numeric($value)){
                            $return = sprintf(__('%s value is required', 'acf'), $field['label']);
                        }
    
                        // allow $return to be a custom error message
                        if(!empty($return) && is_string($return)){
                            $valid = "acfe:{$return}";
                        }
                        
                        return $valid;
                        
                    }, 10, 2);
            
                }
        
            }
        
        }
        
    }
    
    
    /**
     * validate_name
     *
     * @param $value
     * @param $item
     *
     * @return false|string|void
     */
    function validate_name($value, $item){
        
        // editing current options page
        if($item['name'] === $value){
            return false;
        }
        
        // check sibiling forms (could be disabled)
        $sibiling_item = $this->get_item($value);
        
        if($sibiling_item && $sibiling_item['ID'] !== $item['ID']){
            return __('This form name already exists', 'acfe');
        }
        
        return false;
        
    }
    
    
    /**
     * edit_column_acfe_name
     *
     * @param $item
     */
    function edit_column_acfe_name($item){
        echo '<code style="font-size: 12px;">' . $item['name'] . '</code>';
    }
    
    
    /**
     * edit_column_acfe_field_groups
     *
     * @param $item
     */
    function edit_column_acfe_field_groups($item){
    
        $text = '—';
        $field_groups = array();
        
        foreach($item['field_groups'] as $key){
            
            $field_group = acf_get_field_group($key);
            
            if($field_group){
                
                // no field group ID found
                if(empty($field_group['ID'])){
                    
                    // get raw field group from db
                    $raw_field_group = acf_get_raw_field_group($field_group['key']);
                    
                    // raw field group found
                    if($raw_field_group && !empty($raw_field_group['ID'])){
                        $field_group['ID'] = $raw_field_group['ID'];
                    }
                    
                }
                
                if(!empty($field_group['ID'])){
                    $field_groups[] = "<a href='" . admin_url("post.php?post={$field_group['ID']}&action=edit") . "'>{$field_group['title']}</a>";
                }else{
                    $field_groups[] = $field_group['title'];
                }
                
            }else{
                
                $field_groups[] = '<code style="font-size: 12px;">' .$key . '</code>';
            }
            
        }
        
        if($field_groups){
            $text = implode(', ', $field_groups);
        }
        
        echo $text;
        
    }
    
    
    /**
     * edit_column_acfe_actions
     *
     * @param $item
     */
    function edit_column_acfe_actions($item){
        
        $text = '—';
        $actions = array();
        
        foreach($item['actions'] as $item){
            $actions[] = ucfirst($item['action']);
        }
        
        if($actions){
            $text = implode(', ', $actions);
        }
        
        echo $text;
        
    }
    
    
    /**
     * edit_column_acfe_shortcode
     *
     * @param $item
     */
    function edit_column_acfe_shortcode($item){
        echo "<code style='font-size: 12px;'>[acfe_form name=\"{$item['name']}\"]</code>";
    }
    
    
    /**
     * export_code
     *
     * @param $return
     * @param $code
     * @param $args
     *
     * @return string
     */
    function export_code($code, $args){
        return "acfe_register_form({$code});";
    }
    
}

acfe_register_module('acfe_module_form');

endif;

function acfe_register_form($item){
    acfe_get_module('form')->add_local_item($item);
}