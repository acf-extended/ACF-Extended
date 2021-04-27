<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_dynamic_forms_hooks')):

class acfe_dynamic_forms_hooks{
    
    /*
     * Construct
     */
    function __construct(){
    
        // Fields
        add_filter('acf/load_value/name=acfe_form_custom_html_enable',              array($this, 'prepare_custom_html'), 10, 3);
        add_filter('acf/prepare_field/name=acfe_form_actions',                      array($this, 'prepare_actions'));
        add_filter('acf/prepare_field/name=acfe_form_field_groups',                 array($this, 'field_groups_choices'));
        add_filter('acf/prepare_field/name=acfe_form_return',                       array($this, 'form_return_deprecated'));
    
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
        
    }
    
    function prepare_custom_html($value, $post_id, $field){
        
        $custom_html = trim(get_field('acfe_form_custom_html', $post_id));
        
        if($value === false && !empty($custom_html))
            $value = true;
        
        return $value;
        
    }
    
    function prepare_actions($field){
        
        if(empty(acf_get_instance('acfe_dynamic_forms_helpers')->get_field_groups())){
            
            $field['instructions'] .= '<br /><u>No field groups are currently mapped</u>';
            
        }
        
        return $field;
        
    }
    
    /*
     * Field Groups Choices
     */
    function field_groups_choices($field){
        
        // Vars
        $field_groups = acf_get_field_groups();
        $hidden = acfe_get_setting('reserved_field_groups', array());
        
        foreach($field_groups as $field_group){
            
            if(in_array($field_group['key'], $hidden))
                continue;
            
            $field['choices'][$field_group['key']] = $field_group['title'];
            
        }
        
        return $field;
        
    }
    
    function form_return_deprecated($field){
        
        if(empty($field['value']))
            return false;
        
        return $field;
        
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
        
        $value = acf_get_array($_value);
        $array = array();
        
        foreach($value as $v){
            
            $array[] = get_the_title($v);
            
        }
        
        return implode(', ', $array);
        
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
    
    // Google Map
    function format_value_google_map($value, $_value, $post_id, $field){
        
        if(is_string($value)){
            
            $value = json_decode(wp_unslash($value), true);
            
        }
        
        $value = acf_get_array($value);
        
        $address = acf_maybe_get($value, 'address');
        
        return $address;
        
    }
    
}

acf_new_instance('acfe_dynamic_forms_hooks');

endif;