<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_format')):

class acfe_module_form_format{
    
    /**
     * __construct
     */
    function __construct(){
        
        add_filter('acfe/form/format_value/type=post_object',            array($this, 'format_value_post_object'), 5, 4);
        add_filter('acfe/form/format_value/type=relationship',           array($this, 'format_value_post_object'), 5, 4);
        add_filter('acfe/form/format_value/type=user',                   array($this, 'format_value_user'), 5, 4);
        add_filter('acfe/form/format_value/type=taxonomy',               array($this, 'format_value_taxonomy'), 5, 4);
        add_filter('acfe/form/format_value/type=acfe_taxonomy_terms',    array($this, 'format_value_taxonomy'), 5, 4);
        add_filter('acfe/form/format_value/type=image',                  array($this, 'format_value_file'), 5, 4);
        add_filter('acfe/form/format_value/type=file',                   array($this, 'format_value_file'), 5, 4);
        add_filter('acfe/form/format_value/type=gallery',                array($this, 'format_value_file'), 5, 4);
        add_filter('acfe/form/format_value/type=select',                 array($this, 'format_value_select'), 5, 4);
        add_filter('acfe/form/format_value/type=checkbox',               array($this, 'format_value_select'), 5, 4);
        add_filter('acfe/form/format_value/type=radio',                  array($this, 'format_value_select'), 5, 4);
        add_filter('acfe/form/format_value/type=google_map',             array($this, 'format_value_google_map'), 5, 4);
        add_filter('acfe/form/format_value/type=repeater',               array($this, 'format_value_repeater'), 5, 4);
        add_filter('acfe/form/format_value/type=flexible_content',       array($this, 'format_value_repeater'), 5, 4);
        add_filter('acfe/form/format_value/type=group',                  array($this, 'format_value_group'), 5, 4);
        add_filter('acfe/form/format_value/type=acfe_date_range_picker', array($this, 'format_value_date_range_picker'), 5, 4);
        add_filter('acfe/form/format_value/type=acfe_address',           array($this, 'format_value_address'), 5, 4);
        
    }
    
    
    /**
     * format_value_post_object
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_post_object($formatted, $unformatted, $post_id, $field){
        
        // vars
        $value = acf_get_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $p_id){
            $array[] = get_the_title($p_id);
        }
        
        // merge
        return implode(', ', $array);
        
    }
    
    
    /**
     * format_value_user
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_user($formatted, $unformatted, $post_id, $field){
        
        // vars
        $value = acf_get_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $user_id){
            
            // get user data
            $user_data = get_userdata($user_id);
            
            // validate
            if($user_data){
                $array[] = $user_data->user_nicename;
            }
            
        }
        
        // merge
        return implode(', ', $array);
        
    }
    
    
    /**
     * format_value_taxonomy
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_taxonomy($formatted, $unformatted, $post_id, $field){
        
        // vars
        $value = acf_get_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $term_id){
            
            // get term
            $term = get_term($term_id);
            
            // validate
            if($term && !is_wp_error($term)){
                $array[] = $term->name;
            }
            
        }
        
        // merge
        return implode(', ', $array);
        
    }
    
    
    /**
     * format_value_file
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_file($formatted, $unformatted, $post_id, $field){
        
        $value = acf_get_array($unformatted);
        $array = array();
        
        foreach($value as $v){
            $array[] = get_the_title($v);
        }
        
        return implode(', ', $array);
        
    }
    
    
    /**
     * format_value_select
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_select($formatted, $unformatted, $post_id, $field){
        
        // vars
        $value = acf_get_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $v){
            $array[] = acf_maybe_get($field['choices'], $v, $v);
        }
        
        // merge
        return implode(', ', $array);
        
    }
    
    
    /**
     * format_value_google_map
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return mixed|null
     */
    function format_value_google_map($formatted, $unformatted, $post_id, $field){
        
        if(is_string($formatted)){
            $formatted = json_decode(wp_unslash($formatted), true);
        }
        
        $formatted = acf_get_array($formatted);
        
        return acf_maybe_get($formatted, 'address');
        
    }
    
    
    /**
     * format_value_repeater
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_repeater($formatted, $unformatted, $post_id, $field){
        
        // vars
        $value = acf_get_array($unformatted);
        $return = '';
        
        // loop values
        foreach($value as $i => $sub_fields){
            
            $array = array();
            $return .= "<br/>\n- ";
            
            // loop subfields keys
            foreach($sub_fields as $key => $val){
                
                // get subfield
                $sub_field = acf_get_field($key);
                
                // validate
                if($sub_field){
                    
                    // label
                    $label = !empty($sub_field['label']) ? $sub_field['label'] : $sub_field['name'];
                    
                    // value
                    $sub_field['name'] = $field['name'] . '_' . $i . '_' . $sub_field['name'];
                    $val = $this->format_value($val, $sub_field);
                    
                    // append
                    $array[] = "{$label}: {$val}";
                    
                }
                
            }
            
            // merge
            $return .= implode(', ', $array);
            
        }
        
        // return
        return $return;
        
    }
    
    
    /**
     * format_value_group
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value_group($formatted, $unformatted, $post_id, $field){
        
        // vars
        $value = acf_get_array($unformatted);
        $array = array();
        $return = '';
        
        // loop subfields keys
        foreach($value as $key => $val){
            
            // get sub field
            $sub_field = acf_get_field($key);
            
            // validate
            if($sub_field){
                
                // label
                $label = !empty($sub_field['label']) ? $sub_field['label'] : $sub_field['name'];
                
                // format value
                $sub_field['name'] = $field['name'] . '_' . $sub_field['name'];
                $val = $this->format_value($val, $sub_field);
                
                // append
                $array[] = "{$label}: {$val}";
                
            }
            
        }
        
        // merge
        $return .= implode(', ', $array);
        
        // return
        return $return;
        
    }
    
    
    /**
     * format_value_date_range_picker
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return mixed|string
     */
    function format_value_date_range_picker($formatted, $unformatted, $post_id, $field){
    
        if(!empty($formatted) && is_array($formatted)){
    
            $start = acf_maybe_get($formatted, 'start');
            $end = acf_maybe_get($formatted, 'end');
    
            return "{$start} - {$end}";
        
        }
        
        return $formatted;
        
    }
    
    
    /**
     * format_value_address
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     *
     * @return mixed|string
     */
    function format_value_address($formatted, $unformatted, $post_id, $field){
    
        if(!empty($formatted) && is_array($formatted)){
            return acf_maybe_get($formatted, 'address');
        }
        
        return $formatted;
        
    }
    
    
    /**
     * format_value_array
     *
     * @param $value
     *
     * @return mixed|string
     */
    function format_value_array($value){
        
        // bail early
        if(!is_array($value)){
            return $value;
        }
        
        $return = array();
        
        foreach($value as $i => $v){
            
            $key = !is_numeric($i) ? "$i: " : '';
            
            if(is_object($v)){
                $v = (array) $v;
            }
            
            $return[] = $key . $this->format_value_array($v);
            
        }
        
        return implode(', ', $return);
        
    }
    
    
    /**
     * format_value
     *
     * @param $unformatted
     * @param $field
     *
     * @return mixed|string|null
     */
    function format_value($unformatted, $field){
        
        // vars
        $post_id = acf_get_valid_post_id();
        $field_name = $field['name'];
        
        // check & delete store
        // this fix an issue where different group subfields with same name will output same value
        // this is because group subfields have singular name. ie: 'textarea' instead of 'group_textarea'
        $store = acf_get_store('values');
        if($store->has("$post_id:$field_name:formatted")){
            $store->remove("$post_id:$field_name:formatted");
        }
        
        // format value
        $formatted = acf_format_value($unformatted, $post_id, $field);
        
        // pass thru filters
        $formatted = apply_filters("acfe/form/format_value",                        $formatted, $unformatted, $post_id, $field);
        $formatted = apply_filters("acfe/form/format_value/type={$field['type']}",  $formatted, $unformatted, $post_id, $field);
        $formatted = apply_filters("acfe/form/format_value/key={$field['key']}",    $formatted, $unformatted, $post_id, $field);
        $formatted = apply_filters("acfe/form/format_value/name={$field['name']}",  $formatted, $unformatted, $post_id, $field);
        
        if(is_object($formatted)){
            $formatted = (array) $formatted;
        }
        
        // format array value
        if(is_array($formatted)){
            $formatted = $this->format_value_array($formatted);
        }
        
        return $formatted;
        
    }
    
}

acf_new_instance('acfe_module_form_format');

endif;


/**
 * acfe_form_format_value
 *
 * @param $value
 * @param $field
 * @param $deprecated
 *
 * @return mixed
 */
function acfe_form_format_value($value, $field, $deprecated = null){
    
    // deprecated
    if($deprecated !== null){
        _deprecated_function('ACF Extended: acfe_form_format_value($value, $field, $deprecated) 3rd argument', '0.8.8', 'pass field array as 2nd argument');
        $field = $deprecated; // second argument was $post_id
    }
    
    return acf_get_instance('acfe_module_form_format')->format_value($value, $field);
    
}