<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_field_groups')):

class acfe_module_form_field_groups{
    
    /*
     * Construct
     */
    function __construct(){
        
        // register field groups
        add_filter('acfe/module/register_field_groups/module=form',               array($this, 'register_field_groups'), 10, 2);
        
        // deprecated
        add_filter('acf/prepare_field/key=field_success_return',                  array($this, 'prepare_success_return'));
    
        // checkbox choices
        // todo: use attribute instead?
        add_filter('acf/prepare_field/key=field_option_action_save_acf_fields',   array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_option_action_load_acf_fields',   array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_post_action_save_acf_fields',     array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_post_action_load_acf_fields',     array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_term_action_save_acf_fields',     array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_term_action_load_acf_fields',     array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_user_action_save_acf_fields',     array($this, 'checkbox_choices'));
        add_filter('acf/prepare_field/key=field_user_action_load_acf_fields',     array($this, 'checkbox_choices'));
        add_action('wp_ajax_acfe/form/map_checkbox_ajax',                         array($this, 'ajax_checkbox_choices'));
        add_action('wp_ajax_acfe/form/field_groups_metabox',                      array($this, 'ajax_field_groups_metabox'));
        
        // select choices
        add_filter('acf/prepare_field/type=select',                               array($this, 'select_choices'), 15);
        add_filter('acf/prepare_field/type=acfe_post_types',                      array($this, 'select_choices'), 15);
        add_filter('acf/prepare_field/type=acfe_post_statuses',                   array($this, 'select_choices'), 15);
        add_filter('acf/prepare_field/type=acfe_taxonomy_terms',                  array($this, 'select_choices'), 15);
        add_filter('acf/prepare_field/type=acfe_taxonomies',                      array($this, 'select_choices'), 15);
        add_filter('acf/prepare_field/type=acfe_user_roles',                      array($this, 'select_choices'), 15);
        add_action('wp_ajax_acfe/form/map_field_ajax',                            array($this, 'ajax_select_choices'));
        
        // field groups choices
        add_filter('acf/prepare_field/key=field_field_groups',                    array($this, 'field_groups_choices'), 15);
        add_action('wp_ajax_acfe/form/map_field_groups_ajax',                     array($this, 'ajax_field_groups_choices'));
        
        // post object
        add_action('wp_ajax_acf/fields/post_object/query',                        array($this, 'ajax_post_object_choices'), 5);
        add_action('wp_ajax_acf/fields/acfe_taxonomy_terms/query',                array($this, 'ajax_taxonomy_terms_choices'), 5);
        add_action('wp_ajax_acf/fields/user/query',                               array($this, 'ajax_post_author_choices'), 5);
        
        
    }
    
    
    /**
     * ajax_post_object_choices
     */
    function ajax_post_object_choices(){
    
        // added in js
        if(!acf_maybe_get_POST('is_form') || !acf_maybe_get_POST('field_key')){
            return;
        }
        
        acf_add_local_field(array(
            'key' => acf_maybe_get_POST('field_key'),
            'label' => '',
            'name' => '',
            'type' => 'post_object',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'id',
            'default_value' => '',
        ));
    
    }
    
    
    /**
     * ajax_taxonomy_terms_choices
     */
    function ajax_taxonomy_terms_choices(){
    
        // added in js
        if(!acf_maybe_get_POST('is_form') || !acf_maybe_get_POST('field_key')){
            return;
        }
        
        acf_add_local_field(array(
            'key' => acf_maybe_get_POST('field_key'),
            'label' => '',
            'name' => 'target_custom',
            'type' => 'acfe_taxonomy_terms',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'field_type' => 'select',
            'return_format' => 'id',
            'ui' => true,
            'ajax' => true,
            'default_value' => '',
        ));
    
    }
    
    
    /**
     * ajax_post_author_choices
     */
    function ajax_post_author_choices(){
    
        // added in js
        if(!acf_maybe_get_POST('is_form') || !acf_maybe_get_POST('field_key')){
            return;
        }
        
        acf_add_local_field(array(
            'key' => acf_maybe_get_POST('field_key'),
            'label' => '',
            'name' => '',
            'type' => 'user',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'id',
            'default_value' => '',
        ));
    
    }
    
    
    /**
     * checkbox_choices
     *
     * @param $field
     *
     * @return mixed
     */
    function checkbox_choices($field){
    
        // global
        global $item;
    
        // prepare field groups
        $field_groups = $item['field_groups'];
        $field_groups = array_filter($field_groups, 'acf_get_field_group');
        $field_groups = array_unique($field_groups);
        
        // vars
        $choices = array();
        
        // loop
        foreach($field_groups as $field_group){
            
            // field group
            $field_group_obj = acf_get_field_group($field_group);
            
            // get fields
            $fields = $this->get_fields($field_group, true);
            
            // append
            foreach($fields as $_field){
                $choices[ $field_group_obj['title'] ][ $_field['key'] ] = $_field['label'];
            }
            
        }
        
        $field['choices'] = $choices;
        
        return $field;
        
    }
    
    
    /**
     * ajax_field_groups_metabox
     */
    function ajax_field_groups_metabox(){
        
        // validate
        if(!acf_verify_ajax()){
            die();
        }
        
        // default options
        $options = acf_parse_args($_POST, array(
            'field_groups' => array(),
        ));
        
        $field_groups = array();
        
        // loop field groups
        foreach($options['field_groups'] as $key){
            
            // get local field group if any
            $field_group = acf_get_field_group($key);
            
            if($field_group){
                
                // store
                $field_groups[] = $field_group;
                
            }
            
        }
        
        if($field_groups){
            
            acf_disable_filter('clone');
            
            acfe_render_field_groups_details($field_groups);
            
        }
        
        die;
        
    }
    
    
    /**
     * ajax_checkbox_choices
     */
    function ajax_checkbox_choices(){
    
        // validate
        if(!acf_verify_ajax()){
            die();
        }
    
        // default options
        $options = acf_parse_args($_POST, array(
            'post_id'       => 0,
            'name'          => '',
            '_name'         => '',
            'key'           => '',
            'value'         => array(),
            'field_groups'  => array(),
        ));
        
        $field = array(
            'key' => $options['key'],
            'label' => 'Save ACF fields',
            'name' => $options['name'],
            'type' => 'checkbox',
            'instructions' => __('Which ACF fields should be saved as metadata', 'acfe'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(),
            'allow_custom' => 0,
            'default_value' => array(),
            'value' => $options['value'],
            'class' => '',
            'layout' => 'vertical',
            'toggle' => 0,
            'return_format' => 'value',
            'save_custom' => 0,
            '_name' => $options['_name'],
        );
    
        // prepare field groups
        $field_groups = $options['field_groups'];
        $field_groups = array_filter($field_groups, 'acf_get_field_group');
        $field_groups = array_unique($field_groups);
    
        $choices = array();
        $labels = array();
    
        foreach($field_groups as $field_group){
            
            // field group
            $field_group_obj = acf_get_field_group($field_group);
            
            // get fields
            $fields = $this->get_fields($field_group, true);
            
            if(!empty($fields)){
                
                // append
                foreach($fields as $_field){
                    $choices[ $field_group_obj['title'] ][ $_field['key'] ] = $_field['label'];
                }
                
                $labels[] = $field_group_obj['title'];
                
            }
        
        }
    
        $field['choices'] = $choices;
        
        $attrs = array('data-labels' => $labels);
        echo '<div ' . acf_esc_attrs($attrs) . '></div>';
        
        acf_get_field_type('checkbox')->render_field($field);
        
        die;
        
    }
    
    /**
     * field_groups_choices
     *
     * acf/prepare_field/key=field_field_groups
     *
     * @param $field
     *
     * @return mixed
     */
    function field_groups_choices($field){
        
        // validate
        if(acf_maybe_get($field, 'ajax_action') !== 'acfe/form/map_field_groups_ajax'){
            return $field;
        }
        
        // append to choices
        foreach(acf_get_field_groups() as $field_group){
            $field['choices'][ $field_group['key'] ] = $field_group['key'];
        }
        
        // loop choices
        foreach(array_keys($field['choices']) as $key){
            
            // get value
            $value = $field['choices'][ $key ];
            
            // check field key
            if(is_string($value) && acf_is_field_group_key($value)){
                
                // get field group
                $choice_field_group = acf_get_field_group($value);
                
                // update title
                if($choice_field_group){
                    $field['choices'][ $key ] = $choice_field_group['title'];
                }
                
            }
            
        }
        
        // encode custom choices
        if(isset($field['custom_choices'])){
            
            // remove custom choice if already in choices
            foreach(array_keys($field['custom_choices']) as $key){
                if(isset($field['choices'][ $key ])){
                    unset($field['custom_choices'][ $key ]);
                }
            }
            
            $field['wrapper']['data-custom-choices'] = json_encode($field['custom_choices']);
            
        }
        
        return $field;
        
    }
    
    
    /**
     * ajax_field_groups_choices
     *
     * wp_ajax_acfe/form/map_field_groups_ajax
     */
    function ajax_field_groups_choices(){
        
        $nonce = acf_request_arg('nonce', '');
        $key   = acf_request_arg('field_key', '');
        
        // Back-compat for field settings.
        if(!acf_is_field_key($key)){
            $nonce = '';
            $key   = '';
        }
    
        // validate
        if(!acf_verify_ajax($nonce, $key)){
            die();
        }
    
        // default options
        $options = acf_parse_args($_POST, array(
            'post_id'        => 0,
            's'              => '',
            'value'          => '',
            'choices'        => array(),
            'custom_choices' => array(),
            'field_key'      => '',
            'paged'          => 1,
        ));
    
        // vars
        $results = array();
        $values = array();
        $search = null;
    
        // search
        if($options['s'] !== ''){
        
            // strip slashes (search may be integer)
            $search = strval($options['s']);
            $search = wp_unslash($search);
        
        }
    
        // custom choices
        if($options['custom_choices']){
        
            $children = array();
        
            foreach($options['custom_choices'] as $key => $value){
            
                // ensure value is a string
                $value = strval($value);
            
                // append to collection
                $values[] = $key;
            
                // if searching, but doesn't exist
                if(is_string($search) && stripos($value, $search) === false && stripos($key, $search) === false){
                    continue;
                }
            
                $children[] = array(
                    'id'   => $key,
                    'text' => $value,
                );
            
            }
        
            if($children){
                $results[] = array(
                    'text'     => __('Custom', 'acfe'),
                    'children' => $children
                );
            }
        
        }
    
        // field groups
        $children = array();
        
        foreach(acf_get_field_groups() as $field_group){
            
            // vars
            $key = $field_group['key'];
            $value = $field_group['title'];
    
            // ensure value is a string
            $value = strval($value);
    
            // append to collection
            $values[] = $key;
    
            // if searching, but doesn't exist
            if(is_string($search) && stripos($value, $search) === false && stripos($key, $search) === false){
                continue;
            }
    
            $children[] = array(
                'id'   => $key,
                'text' => $value,
            );
        
        }
    
        if($children){
            $results[] = array(
                'text'     => 'Field Groups',
                'children' => $children
            );
        }
    
        // custom value
        $children = array();
        $value_array = acf_get_array($options['value']);
        $value_array[] = '';
        
        foreach($value_array as $value){
        
            $key = $value;
            $value = strval($value);
        
            // if search
            if(is_string($search)){
            
                // search already exists in custom values
                if($search === $value && !in_array($value, $values)){
                
                    $children[] = array(
                        'id'   => $key,
                        'text' => $this->get_field_label($value)
                    );
                
                // search not found in any value, generate choice
                }elseif(!in_array($search, $values)){
                
                    $_key = $search;
                    $_value = $search;
                
                    // reset children to avoid duplicate append
                    $children = array();
                    $children[] = array(
                        'id'   => $_key,
                        'text' => $this->get_field_label($_value)
                    );
                
                }
            
            // no search and value not found in choices, generate custom choice
            }elseif($value && !in_array($value, $values)){
            
                $children[] = array(
                    'id'   => $key,
                    'text' => $this->get_field_label($value)
                );
            
            }
        
        }
    
        if($children){
            array_unshift($results, array(
                'text'     => __('Custom', 'acfe'),
                'children' => $children
            ));
        }
    
        // response
        $response = array(
            'results' => $results,
        );
    
        // return
        acf_send_ajax_results($response);
        
    }
    
    
    /**
     * select_choices
     *
     * acf/prepare_field/type=select
     *
     * @param $field
     *
     * @return mixed
     */
    function select_choices($field){
        
        // validate
        if(acf_maybe_get($field, 'ajax_action') !== 'acfe/form/map_field_ajax'){
            return $field;
        }
        
        $is_load = isset($field['wrapper']['data-related-field']) && !empty($field['wrapper']['data-related-field']);
        
        // global
        global $item;
        
        // prepare field groups
        $field_groups = $item['field_groups'];
        $field_groups = array_filter($field_groups, 'acf_get_field_group');
        $field_groups = array_unique($field_groups);
        
        // loop field groups
        foreach($field_groups as $field_group){
            
            // get fields
            $fields = $this->get_fields($field_group);
            
            // append
            foreach($fields as $_field){
                
                $key = $is_load ? $_field['key'] : "{field:{$_field['key']}}";
                
                $field['choices'][ $key ] = $_field['label'];
                
            }
            
        }
        
        $ajax_choices = array();
        
        // loop choices
        foreach(array_keys($field['choices']) as $key){
            
            // get value
            $value = $field['choices'][ $key ];
            
            // check field key
            if(is_string($key) && $this->is_field_key_tag($key, $is_load)){
                
                if(is_string($value) && acf_is_field_key($value)){
                    $field['choices'][ $key ] = $this->get_field_label($value);
                }
                
                continue;
                
            }
            
            $ajax_choices[ $key ] = $value;
            
        }
        
        // encode choices
        if($ajax_choices){
            
            // remove custom choices from choice
            if(isset($field['custom_choices'])){
                foreach(array_keys($ajax_choices) as $key){
                    if(isset($field['custom_choices'][ $key ])){
                        unset($ajax_choices[ $key ]);
                    }
                }
            }
            
            $field['wrapper']['data-choices'] = json_encode($ajax_choices);
            
        }
    
        // encode custom choices
        if(isset($field['custom_choices'])){
        
            // remove custom choice if already in choices
            foreach(array_keys($field['custom_choices']) as $key){
                if(isset($field['choices'][ $key ])){
                    unset($field['custom_choices'][ $key ]);
                }
            }
        
            $field['wrapper']['data-custom-choices'] = json_encode($field['custom_choices']);
        
        }
        
        // todo: remove exception here
        if($field['_name'] === 'save_post_terms'){
            
            foreach(array_keys($field['choices']) as $taxonomy){

                $terms = $field['choices'][ $taxonomy ];
                if(is_array($terms)){

                    unset($field['choices'][ $taxonomy ]);

                    foreach($terms as $key => $value){
                        $field['choices'][ $key ] = $value;
                    }

                }
            }
            
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * ajax_select_choices
     *
     * wp_ajax_acfe/form/map_field_ajax
     */
    function ajax_select_choices(){
        
        $nonce = acf_request_arg('nonce', '');
        $key   = acf_request_arg('field_key', '');
        
        // Back-compat for field settings.
        if(!acf_is_field_key($key)){
            $nonce = '';
            $key   = '';
        }
        
        // validate
        if(!acf_verify_ajax($nonce, $key)){
            die();
        }
    
        // default options
        $options = acf_parse_args($_POST, array(
            'post_id'        => 0,
            's'              => '',
            'value'          => '',
            'choices'        => array(),
            'custom_choices' => array(),
            'field_key'      => '',
            'field_label'    => '',
            'field_groups'   => array(),
            'is_load'        => false,
            'paged'          => 1,
        ));
    
        // vars
        $is_load = (bool) $options['is_load'];
        $results = array();
        $values = array();
        $search = null;
    
        // search
        if($options['s'] !== ''){
        
            // strip slashes (search may be integer)
            $search = strval($options['s']);
            $search = wp_unslash($search);
        
        }
    
        // custom choices
        if($options['custom_choices']){
        
            $children = array();
        
            foreach($options['custom_choices'] as $key => $value){
            
                // ensure value is a string
                $value = strval($value);
            
                // append to collection
                $values[] = $key;
            
                // if searching, but doesn't exist
                if(is_string($search) && stripos($value, $search) === false && stripos($key, $search) === false){
                    continue;
                }
            
                $children[] = array(
                    'id'   => $key,
                    'text' => $value,
                );
            
            }
        
            if($children){
                $results[] = array(
                    'text'     => __('Custom', 'acfe'),
                    'children' => $children
                );
            }
        
        }
    
        // generic choices
        if($options['choices']){
        
            $children = array();
            $is_array = false;
        
            foreach($options['choices'] as $key => $value){
            
                // value is array (multi dimentional choices)
                if(is_array($value)){
                
                    $is_array = true;
                    $sub_children = array();
                
                    foreach($value as $sub_key => $sub_value){
                    
                        // ensure value is a string
                        $sub_value = strval($sub_value);
                    
                        // append to collection
                        $values[] = $sub_key;
                    
                        // if searching, but doesn't exist
                        if(is_string($search) && stripos($sub_value, $search) === false && stripos($sub_key, $search) === false){
                            continue;
                        }
                    
                        $sub_children[] = array(
                            'id'   => $sub_key,
                            'text' => $sub_value,
                        );
                    
                    }
                
                    if($sub_children){
                        $results[] = array(
                            'text'     => $key,
                            'children' => $sub_children
                        );
                    }
                
                // normal value
                }else{
                
                    // ensure value is a string
                    $value = strval($value);
                
                    // append to collection
                    $values[] = $key;
                
                    // if searching, but doesn't exist
                    if(is_string($search) && stripos($value, $search) === false && stripos($key, $search) === false){
                        continue;
                    }
                
                    $children[] = array(
                        'id'   => $key,
                        'text' => $value,
                    );
                
                }
            
            }
        
            if(!$is_array && $children){
                $results[] = array(
                    'text'     => $options['field_label'],
                    'children' => $children
                );
            }
        
        }
    
        // field groups fields
        foreach($options['field_groups'] as $field_group_key){
        
            // vars
            $field_group = acf_get_field_group($field_group_key);
            
            // get fields
            $fields = $this->get_fields($field_group_key);
        
            $children = array();
            foreach($fields as $field){
                
                $key = $is_load ? $field['key'] : "{field:{$field['key']}}";
                $value = $field['field']['label'];
            
                // ensure value is a string
                $value = strval($value);
                $value = empty($value) ? $field['key'] : "{$value} ({$field['key']})";
            
                // append to collection
                $values[] = $key;
            
                // if searching, but doesn't exist
                if(is_string($search) && stripos($value, $search) === false && stripos($key, $search) === false){
                    continue;
                }
            
                $children[] = array(
                    'id'   => $key,
                    'text' => $field['label'],
                );
            
            }
        
            if($children){
                $results[] = array(
                    'text'     => $field_group['title'],
                    'children' => $children
                );
            }
        
        }
    
        // custom value
        $children = array();
        $value_array = acf_get_array($options['value']);
        $value_array[] = ''; // we must append empty string to let search being a custom value (in case a value was never selected before)
    
        foreach($value_array as $value){
            
            $key = $value;
            $value = strval($value);
        
            // if search
            if(is_string($search)){
            
                // search already exists in custom values
                if($search === $value && !in_array($value, $values)){
                
                    $children[] = array(
                        'id'   => $key,
                        'text' => $this->get_field_label($value)
                    );
                
                // search not found in any value, generate choice
                }elseif(!in_array($search, $values)){
                
                    $_key = $search;
                    $_value = $search;
                
                    // reset children to avoid duplicate append
                    $children = array();
                    $children[] = array(
                        'id'   => $_key,
                        'text' => $this->get_field_label($_value)
                    );
                
                }
            
            // no search and value not found in choices, generate custom choice
            }elseif($value && !in_array($value, $values)){
            
                $children[] = array(
                    'id'   => $key,
                    'text' => $this->get_field_label($value)
                );
            
            }
        
        }
    
        if($children){
            array_unshift($results, array(
                'text'     => __('Custom', 'acfe'),
                'children' => $children
            ));
        }
    
        // response
        $response = array(
            'results' => $results,
        );
        
        // return
        acf_send_ajax_results($response);
        
    }
    
    
    /**
     * get_field_label
     *
     * @param $value
     *
     * @return mixed|string
     */
    function get_field_label($value){
        
        if(is_string($value) && acf_is_field_key($value)){
            
            $field = acf_get_field($value);
            
            if($field){
                
                $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
                
                $label = '';
                $label = str_repeat('- ', $ancestors) . $label;
                $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
                $label .= $field['required'] ? ' *' : '';
                
                $value = "{$label} ({$value})";
                
            }
            
        }
        
        return $value;
        
    }
    
    
    /**
     * get_fields_details
     *
     * @param $field
     *
     * @return mixed
     */
    function get_fields_details($field){
        
        // disallow tab, message, accordion
        if(in_array($field['type'], array('tab', 'message', 'accordion'))){
            return false;
        }
        
        // disallow subfields for repeater/flexible content
        if(in_array($field['type'], array('repeater', 'flexible_content'))){
            $field['sub_fields'] = array();
        }
        
        // get parent field
        if(is_string($field['parent']) && acf_is_field_key($field['parent'])){
            
            // get field
            $parent = acf_get_field($field['parent']);
            
            // parent = group
            if($parent && $parent['type'] === 'group' && !empty($parent['label'])){
                
                // prepend with group label
                $field['label'] = "{$parent['label']}: {$field['label']}";
                
            }
            
        }
        
        return $field;
        
    }
    
    
    /**
     * get_fields
     *
     * @param $field_group
     *
     * @return array|string[]
     */
    function get_fields($field_group, $show_top_level = false){
        
        acf_disable_filter('clone');
        
        $fields = acf_get_fields($field_group);
        
        if(!empty($fields)){
            
            // vars
            $all_fields = acfe_get_fields_details_recursive($fields, array($this, 'get_fields_details'));
            $fields = array();
            
            // check the first field is a child of the field group
            // this fix an issue where acf_get_fields($field_group) return orphan fields in ajax query
            // if field group has no fields and is local field group
            if(!empty($all_fields) && acf_is_local_field_group($field_group)){
                if(isset($all_fields[0]['field']) && acf_maybe_get($all_fields[0]['field'], 'parent') !== $field_group){
                    return $fields;
                }
            }
            
            // filtered fields
            foreach($all_fields as $field){
                
                // do not show group, clone
                if(in_array($field['field']['type'], array('group'))){
                    continue;
                }
                
                // do not show top level (repeater, flexible content)
                if(!$show_top_level){
                    if(in_array($field['field']['type'], array('clone', 'repeater', 'flexible_content'))){
                        continue;
                    }
                }
                
                // filtered
                $fields[] = $field;
                
            }
            
            // loop fields
            foreach(array_keys($fields) as $key){
                
                $field = $fields[ $key ];
                
                // add (field_abcdef123456) to field label
                $fields[ $key ]['label'] = "{$field['label']} ({$field['key']})";
                
            }
            
        }
        
        // return
        return $fields;
        
    }
    
    
    /**
     * is_field_key_tag
     *
     * @param $key
     * @param $is_load
     *
     * @return bool
     */
    function is_field_key_tag($key, $is_load = false){
        
        if($is_load){
            return is_string($key) && !empty($key) && acf_is_field_key($key);
        }
        
        return is_string($key) && !empty($key) && preg_match('/^{field:field_[a-zA-Z0-9_]+}$/', $key);
        
    }
    
    
    /**
     * prepare_success_return
     *
     * @param $field
     *
     * @return false|mixed
     */
    function prepare_success_return($field){
        
        if(empty($field['value'])){
            return false;
        }
    
        _deprecated_function('ACF Extended: "Redirection" Forms setting', '0.8.7.5', "the Redirect Action (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms)");
        
        return $field;
        
    }
    
    
    /*
     * Register Field Groups
     */
    function register_field_groups($field_groups, $module){
    
        $layouts = array();
        $actions = acfe_get_form_action_types();
        
        foreach($actions as $action){
            
            $layout = $action->get_layout();
            $layouts[ $layout['key'] ] = $layout;
            
        }
        
        $field_groups[] = array(
            'key' => 'group_acfe_form',
            'title' => __('Form', 'acfe'),
    
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $module->post_type,
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
                
                /**
                 * general
                 */
                array(
                    'key' => 'field_tab_general',
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
                        'data-no-preference' => true,
                    ),
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_name',
                    'label' => __('Name', 'acfe'),
                    'name' => 'name',
                    'type' => 'acfe_slug',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_field_groups',
                    'label' => __('Field Groups', 'acfe'),
                    'name' => 'field_groups',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(),
                    'default_value' => array(),
                    'allow_null' => 1,
                    'multiple' => 1,
                    'ui' => 1,
                    'ajax' => 1,
                    'return_format' => 'value',
                    'allow_custom' => 1,
                    'placeholder' => '',
                    'ajax_action' => 'acfe/form/map_field_groups_ajax'
                ),
                array(
                    'key' => 'field_actions',
                    'label' => __('Actions', 'acfe'),
                    'name' => 'actions',
                    'type' => 'flexible_content',
                    'instructions' => __('Add actions on form submission', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_flexible_stylised_button' => 1,
                    'layouts' => $layouts,
                    'button_label' => __('Add action', 'acfe'),
                    'min' => '',
                    'max' => '',
                ),
                
                /**
                 * settings
                 */
                array(
                    'key' => 'field_tab_settings',
                    'label' => __('Settings', 'acfe'),
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
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_location',
                    'label' => __('Field groups locations rules', 'acfe'),
                    'name' => 'location',
                    'type' => 'true_false',
                    'instructions' => __('Apply field groups locations rules on front-end display', 'acfe'),
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'settings',
                ),
                array(
                    'key' => 'field_honeypot',
                    'label' => __('Honeypot', 'acfe'),
                    'name' => 'honeypot',
                    'type' => 'true_false',
                    'instructions' => __('Whether to include a hidden input field to capture non human form submission. Defaults to true.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'settings',
                ),
                array(
                    'key' => 'field_kses',
                    'label' => __('Kses', 'acfe'),
                    'name' => 'kses',
                    'type' => 'true_false',
                    'instructions' => __('Whether or not to sanitize all $_POST data with the wp_kses_post() function. Defaults to true.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'settings',
                ),
                array(
                    'key' => 'field_uploader',
                    'label' => __('Uploader', 'acfe'),
                    'name' => 'uploader',
                    'type' => 'radio',
                    'instructions' => __('Whether to use the WP uploader or a basic input for image and file fields. Defaults to \'wp\'.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'default' => __('Default', 'acfe'),
                        'wp'      => __('WordPress', 'acfe'),
                        'basic'   => __('Browser', 'acfe'),
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'default',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                    'group_with' => 'settings',
                ),
    
                /**
                 * attributes
                 */
                array(
                    'key' => 'field_tab_attributes',
                    'label' => __('Attributes', 'acfe'),
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
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_form',
                    'label' => __('Form attributes', 'acfe'),
                    'name' => 'form',
                    'type' => 'group',
                    'instructions' => __('Attributes settings related to the form.', 'acfe'),
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(),
                    'group_with' => 'attributes',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_form_element',
                            'label' => '',
                            'name' => 'element',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => 'form',
                            'placeholder' => '',
                            'prepend' => 'element',
                            'choices' => array(
                                'form' => '<form>',
                                'div' => '<div>',
                            )
                        ),
                        array(
                            'key' => 'field_form_class',
                            'label' => '',
                            'name' => 'class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'form class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_form_id',
                            'label' => '',
                            'name' => 'id',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'form id',
                            'append' => '',
                            'maxlength' => '',
                        ),
        
                    ),
                ),
                array(
                    'key' => 'field_fields',
                    'label' => __('Fields attributes', 'acfe'),
                    'name' => 'fields',
                    'type' => 'group',
                    'instructions' => __('Attributes settings related to the fields.', 'acfe'),
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(),
                    'group_with' => 'attributes',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_fields_element',
                            'label' => '',
                            'name' => 'element',
                            'type' => 'select',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'prepend' => 'element',
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
                            'return_format' => 'value',
                            'save_other_choice' => 0,
                        ),
                        array(
                            'key' => 'field_fields_wrapper_class',
                            'label' => '',
                            'name' => 'wrapper_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'wrap class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_fields_class',
                            'label' => '',
                            'name' => 'class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'field class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_fields_label',
                            'label' => '',
                            'name' => 'label',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'choices' => array(
                                'top'    => __('Top', 'acfe'),
                                'left'   => __('Left', 'acfe'),
                                'hidden' => __('Hidden', 'acfe'),
                            ),
                            'allow_null' => 0,
                            'other_choice' => 0,
                            'default_value' => 'top',
                            'return_format' => 'value',
                            'prepend' => 'label',
                        ),
                        array(
                            'key' => 'field_fields_instruction',
                            'label' => '',
                            'name' => 'instruction',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '33.33',
                                'class' => '',
                                'id' => '',
                            ),
                            'choices' => array(
                                'label'       => __('Label', 'acfe'),
                                'field'       => __('Field', 'acfe'),
                                'above_field' => __('Above field', 'acfe'),
                                'tooltip'     => __('Tooltip', 'acfe'),
                            ),
                            'allow_null' => 0,
                            'other_choice' => 0,
                            'default_value' => 'label',
                            'return_format' => 'value',
                            'prepend' => 'instruction',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_submit',
                    'label' => __('Submit button', 'acfe'),
                    'name' => 'submit',
                    'type' => 'true_false',
                    'instructions' => __('Whether or not to create a form submit button. Defaults to true', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'attributes',
                ),
                array(
                    'key' => 'field_submit_value',
                    'label' => __('Submit value', 'acfe'),
                    'name' => 'submit_value',
                    'type' => 'text',
                    'instructions' => __('The text displayed on the submit button', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_submit',
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
                    'default_value' => 'Submit',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'group_with' => 'attributes',
                ),
                array(
                    'key' => 'field_submit_button',
                    'label' => __('Submit button', 'acfe'),
                    'name' => 'submit_button',
                    'type' => 'acfe_code_editor',
                    'instructions' => __('HTML used to render the submit button.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_submit',
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
                    'default_value' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                    'group_with' => 'attributes',
                ),
                array(
                    'key' => 'field_submit_spinner',
                    'label' => __('Submit spinner', 'acfe'),
                    'name' => 'submit_spinner',
                    'type' => 'acfe_code_editor',
                    'instructions' => __('HTML used to render the submit button loading spinner.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_submit',
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
                    'default_value' => '<span class="acf-spinner"></span>',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                    'group_with' => 'attributes',
                ),
                
                /**
                 * render
                 */
                array(
                    'key' => 'field_tab_render',
                    'label' => __('Render', 'acfe'),
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
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_render',
                    'label' => __('Form render', 'acfe'),
                    'name' => 'render',
                    'type' => 'acfe_code_editor',
                    'instructions' => __('Render customized form HTML. Leave empty to render form normally.', 'acfe') . '<br /><br />' .
                                      __('Render field group:', 'acfe') . '<br /><code>{render:group_abc123}</code><br/><br/>' .
                                      __('Render field:', 'acfe') . '<br /><code>{render:field_abc123}</code><br/><code>{render:my_field}</code><br/><br/>' .
                                      __('Render all fields:' ,'acfe') . '<br /><code>{render:fields}</code><br/><br/>' .
                                      __('Render submit button:', 'acfe') . '<br /><code>{render:submit}</code>',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 18,
                    'conditional_logic' => array(),
                ),
    
                /**
                 * validation
                 */
                array(
                    'key' => 'field_tab_validation',
                    'label' => __('Validation', 'acfe'),
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
                    'placement' => 'top',
                    'endpoint' => 0,
                    'group_with' => 'validation',
                ),
                array(
                    'key' => 'field_hide_error',
                    'label' => __('Hide general error', 'acfe'),
                    'name' => 'hide_error',
                    'type' => 'true_false',
                    'instructions' => __('Hide the general error message: "Validation failed. 1 field requires attention"', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'validation',
                ),
                array(
                    'key' => 'field_hide_revalidation',
                    'label' => __('Hide successful re-validation', 'acfe'),
                    'name' => 'hide_revalidation',
                    'type' => 'true_false',
                    'instructions' => __('Hide "Validation successful" notice when an error has been previously thrown', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_hide_error',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        )
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'validation',
                ),
                array(
                    'key' => 'field_hide_unload',
                    'label' => __('Hide confirmation on exit', 'acfe'),
                    'name' => 'hide_unload',
                    'type' => 'true_false',
                    'instructions' => __('Do not prompt user on page refresh', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'validation',
                ),
                array(
                    'key' => 'field_messages',
                    'label' => __('General error messages', 'acfe'),
                    'name' => 'messages',
                    'type' => 'group',
                    'instructions' => __('Customize general error messages.', 'acfe'),
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_hide_error',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        )
                    ),
                    'group_with' => 'validation',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_messages_failure',
                            'label' => '',
                            'name' => 'failure',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '50',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => 'Validation failed',
                            'placeholder' => '',
                            'prepend' => 'failure',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_messages_error',
                            'label' => '',
                            'name' => 'error',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '50',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '1 field requires attention',
                            'placeholder' => '',
                            'prepend' => 'error',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_messages_success',
                            'label' => '',
                            'name' => 'success',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '50',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => 'Validation successful',
                            'placeholder' => '',
                            'prepend' => 'success',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_messages_errors',
                            'label' => '',
                            'name' => 'errors',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '50',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '%d fields require attention',
                            'placeholder' => '',
                            'prepend' => 'errors',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    
                    ),
                ),
                array(
                    'key' => 'field_errors_position',
                    'label' => __('Fields errors position', 'acfe'),
                    'name' => 'errors_position',
                    'type' => 'radio',
                    'instructions' => __('Choose where to display field errors', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'above' => __('Above fields', 'acfe'),
                        'below' => __('Below fields', 'acfe'),
                        'group' => __('Group errors', 'acfe'),
                        'hide'  => __('Hide errors', 'acfe'),
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'above',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                    'group_with' => 'validation',
                ),
                array(
                    'key' => 'field_errors_class',
                    'label' => __('Fields errors class', 'acfe'),
                    'name' => 'errors_class',
                    'type' => 'text',
                    'instructions' => __('Add class to the error message', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_errors_position',
                                'operator' => '!=',
                                'value' => 'hide',
                            ),
                        )
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'group_with' => 'validation',
                ),
    
                /**
                 * success
                 */
                array(
                    'key' => 'field_tab_success',
                    'label' => __('Success', 'acfe'),
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
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_success_return',
                    'label' => __('Redirection', 'acfe'),
                    'name' => 'success_return',
                    'type' => 'text',
                    'instructions' => __('The URL to be redirected to after the form is submitted.', 'acfe') . '<br/><br/><u>' . __('This setting is deprecated, use the new "Redirect Action" instead.', 'acfe') . '</u>',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-enable-switch' => true
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'group_with' => 'success',
                ),
                array(
                    'key' => 'field_success_hide_form',
                    'label' => __('Hide form', 'acfe'),
                    'name' => 'success_hide_form',
                    'type' => 'true_false',
                    'instructions' => __('Hide form on successful submission', 'acfe'),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_success_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'success',
                ),
                array(
                    'key' => 'field_success_scroll',
                    'label' => __('Scroll to message', 'acfe'),
                    'name' => 'success_scroll',
                    'type' => 'true_false',
                    'instructions' => __('Scroll to message on success', 'acfe'),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_success_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'group_with' => 'success',
                ),
                array(
                    'key' => 'field_success_message',
                    'label' => __('Success message', 'acfe'),
                    'name' => 'success_message',
                    'type' => 'wysiwyg',
                    'instructions' => __('The message displayed above the form after the submission.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_success_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'default_value' => __('Form updated', 'acfe'),
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                    'group_with' => 'success',
                ),
                array(
                    'key' => 'field_success_wrapper',
                    'label' => __('Success wrapper HTML', 'acfe'),
                    'name' => 'success_wrapper',
                    'type' => 'acfe_code_editor',
                    'instructions' => __('HTML used to render the updated message.', 'acfe') . '<br />' .
                                      __('If used, you have to include the following code <code>%s</code> to print the actual "Success message" above.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_success_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'default_value' => '<div id="message" class="updated">%s</div>',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                    'group_with' => 'success',
                ),
                
            ),
        );
        
        return $field_groups;
        
    }
    
}

acf_new_instance('acfe_module_form_field_groups');

endif;