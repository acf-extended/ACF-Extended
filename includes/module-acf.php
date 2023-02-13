<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_acf')):

class acfe_module_acf{
    
    // vars
    public $values = array();
    
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acf/include_fields',      array($this, 'include_fields'));
        add_action('acf/validate_save_post',  array($this, 'validate_save_post'), 1);
        add_action('acf/validate_save_post',  array($this, 'after_validate_save_post'));
        add_filter('acf/pre_load_value',      array($this, 'pre_load_value'), 10, 3);
        add_action('acf/save_post',           array($this, 'save_post'), 1);
        add_action('acf/include_admin_tools', array($this, 'include_admin_tools'), 15);
        add_action('acf/include_admin_tools', array($this, 'include_admin_tools_sort'), 99);
        add_filter('acf/get_post_types',      array($this, 'get_post_types'), 10, 2);
        //add_filter('wp_insert_post_data',     array($this, 'wp_insert_post_data'), 10, 2);
    
    }
    
    /**
     * include_fields
     *
     * acf/include_fields
     */
    function include_fields(){
        
        // loop modules
        foreach(acfe_get_modules() as $module){
            
            // trigger include items
            $module->do_module_action('acfe/module/include_items');
            
        }
        
    }
    
    /**
     * validate_save_post
     *
     * acf/validate_save_post:1
     */
    function validate_save_post(){
        
        // get form data
        $post_id = acf_get_form_data('post_id');
    
        // get module
        $module = acfe_get_module_by_item($post_id);
    
        // validate module
        if(!$module){
            return;
        }
        
        // register field groups
        foreach($module->get_field_groups() as $field_group){
            acf_add_local_field_group($field_group);
        }
        
        // item
        $item = $module->get_item($post_id);
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/module/validate_save_post', true);
    
        // validate module values
        foreach($module->validate as $name){
        
            if(method_exists($module, "validate_{$name}")){
                
                // use field_name
                $key = "field_{$name}";
                $field = acf_get_field($key);
                $value = get_field($key);
                $valid = $module->{"validate_{$name}"}($value, $item);
            
                // empty required
                if($field && $field['required'] && empty($value) && !is_numeric($value)){
                    $valid = sprintf(__('%s value is required', 'acf'), $field['label']);
                }
            
                // allow $valid to be a custom error message
                if(!empty($valid) && is_string($valid)){
                    acfe_add_validation_error($key, "acfe:{$valid}");
                }
            
            }
        
        }
        
        // actions
        $module->do_module_action('acfe/module/validate_save_item', $item);
        
        // reset meta
        acfe_reset_meta();
        
    }
    
    
    /**
     * after_validate_save_post
     *
     * acf/validate_save_post
     *
     * disable errors on module validation to avoid collision with user field validation names
     */
    function after_validate_save_post(){
    
        // get form data
        $post_id = acf_get_form_data('post_id');
    
        // get module
        $module = acfe_get_module_by_item($post_id);
    
        // validate module
        if(!$module){
            return;
        }
        
        // get errors
        $errors = acf_get_array(acf()->validation->get_errors());
        
        // remove non acfe errors
        // this will remove errors set by developers that use field name such as "name"
        // note that this will also remove native acf validation message such as "required value"
        foreach(array_keys($errors) as $key){
            
            if(!acfe_starts_with($errors[ $key ]['message'], 'acfe:')){
                unset($errors[ $key ]);
            }
            
        }
        
        // cleanup acfe error messages
        foreach(array_keys($errors) as $key){
            $errors[ $key ]['message'] = str_replace('acfe:', '', $errors[ $key ]['message']);
        }
        
        // add new errors
        acf()->validation->errors = $errors;
        
    }
    
    /**
     * pre_load_value
     *
     * acf/pre_load_value
     *
     * @param $null
     * @param $post_id
     * @param $field
     *
     * @return mixed|null
     */
    function pre_load_value($null, $post_id, $field){
        
        // get module
        $module = acfe_get_module_by_item($post_id);
    
        // validate module
        if(!$module){
            return $null;
        }
        
        // load only one time
        if(empty($this->values)){
            
            // item
            $item = $module->get_item($post_id);
            
            // validate item
            if(empty($item)){
                return $null;
            }
            
            // remove unused keys
            acf_extract_vars($item, array('ID', '_valid'));
    
            foreach(array_keys($item) as $k){
                
                $v = $item[ $k ];
                $_field = acf_get_field($k);
        
                if(!$_field){
                    continue;
                }
        
                // encode value
                if(acf_maybe_get($_field, 'encode_value')){
                    $with_keys = !acf_is_sequential_array($v);
                    $item[ $k ] = acf_encode_choices($v, $with_keys);
                }
        
                // unparse type
                if(acf_maybe_get($_field, 'unparse_type')){
                    $item[ $k ] = acfe_unparse_types($v);
                }
        
            }
            
            // filters
            $item = $module->apply_module_filters('acfe/module/prepare_load_item', $item);
            
            // prefix keys like "name" with "field_name" for acf loading values
            $acf = acfe_prefix_array_keys($item, 'field_', array('acf_fc_layout'));
            
            // set values
            $this->values = $acf;
            
        }
        
        return acf_maybe_get($this->values, $field['key'], $null);
        
    }
    
    
    /**
     * save_post
     *
     * acf/save_post:1
     *
     * @param $post_id
     */
    function save_post($post_id){
    
        // get module
        $module = acfe_get_module_by_item($post_id);
    
        // validate module
        if(!$module){
            return;
        }
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/module/save_post', true);
        
        // defaults vars
        // $item['name'] already set in get_fields() (field is mandatory in field groups)
        $item = array(
            'ID'    => $post_id,
            'name'  => '',
            'label' => acf_maybe_get_POST('post_title'),
        );
    
        // alias of get_fields
        $fields = get_field_objects();
        $meta = array();
    
        // bail early
        if($fields){
            foreach($fields as $k => $field){
            
                $meta[ $k ] = $field['value'];
            
                // encode value
                if(acf_maybe_get($field, 'encode_value')){
                    $with_keys = strpos($field['value'], ' : ') === false;
                    $meta[ $k ] = acf_decode_choices($field['value'], $with_keys);
                }
                
                // group with
                if(acf_maybe_get($field, 'group_with')){
                    $meta[ $field['group_with'] ][ $k ] = $field['value'];
                }
            
            }
        }
    
        $item = array_merge($item, $meta);
        
        // filters
        $item = $module->apply_module_filters('acfe/module/prepare_save_item', $item);
    
        // field exists
        if($fields){
            foreach($fields as $k => $field){
            
                // cleanup key
                if(acf_maybe_get($field, 'cleanup_key')){
                    unset($item[ $k ]);
                }
                
                // group with
                if(acf_maybe_get($field, 'group_with')){
                    unset($item[ $k ]);
                }
            
            }
        }
    
        // cleanup empty labels
        if(!empty($item['labels'])){
            foreach($item['labels'] as $key => $label){
            
                // cleanup label if empty
                if(empty($label)){
                    unset($item['labels'][ $key ]);
                }
            
            }
        }
        
        // reset meta
        acfe_reset_meta();
        
        // update
        $module->update_item($item);
        
        // bypass acf native values update
        $_POST['acf'] = array();
        
    }
    
    
    /**
     * include_admin_tools
     *
     * acf/include_admin_tools:15
     */
    function include_admin_tools(){
        
        foreach(acfe_get_modules() as $module){
    
            // get tool names
            $export_tool = $module->get_export_tool();
            $import_tool = $module->get_import_tool();
    
            // reigster acf tools
            acf()->admin_tools->tools[ $export_tool ] = new acfe_module_export($module);
            acf()->admin_tools->tools[ $import_tool ] = new acfe_module_import($module);
            
        }
        
    }
    
    
    /**
     * include_admin_tools_sort
     *
     * acf/include_admin_tools:99
     *
     * Sort ACF tools
     */
    function include_admin_tools_sort(){
        
        $sort = array(
            'export',
            'import',
            'acfe_module_post_type_export',
            'acfe_module_post_type_import',
            'acfe_module_taxonomy_export',
            'acfe_module_taxonomy_import',
            'acfe_module_block_type_export',
            'acfe_module_block_type_import',
            'acfe_module_options_page_export',
            'acfe_module_options_page_import',
            'acfe_module_template_export',
            'acfe_module_template_import',
        );
        
        uksort(acf()->admin_tools->tools, function($a, $b) use($sort){
            foreach($sort as $value){
                if($a === $value){return 0;}
                if($b === $value){return 1;}
            }
        });
        
    }
    
    
    /**
     * get_post_types
     *
     * acf/get_post_types
     *
     * remove reserved post types
     *
     * @param $post_types
     * @param $args
     *
     * @return mixed
     */
    function get_post_types($post_types, $args){
        
        foreach($post_types as $k => $post_type){
            if(acfe_is_post_type_reserved($post_type)){
                unset($post_types[ $k ]);
            }
        }
        
        return $post_types;
        
    }
    
    
    /**
     * wp_insert_post_data
     *
     * force field_name as post_name. This has been disabled as it cause problem when updating names and generating sync files
     *
     * @param $args
     * @param $post_array
     *
     * @return mixed
     */
    function wp_insert_post_data($args, $post_array){
        
        // get post id
        $post_id = acf_maybe_get($post_array, 'ID');
        
        // get module
        $module = acfe_get_module_by_item($post_id);
        
        // validate module
        if(!$module){
            return $args;
        }
        
        if(!isset($post_array['acf'])){
            return $args;
        }
        
        $name = acf_maybe_get($post_array['acf'], 'field_name');
        $args['post_name'] = sanitize_title($name);
        
        return $args;
        
    }
    
}

acf_new_instance('acfe_module_acf');

endif;