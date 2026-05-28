<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_select')):

class acfe_field_select extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'select';
        $this->defaults = array(
            'allow_custom'       => 0,
            'placeholder'        => '',
            'search_placeholder' => '',
        );
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){

        // allow custom
        acf_render_field_setting($field, array(
            'label'             => __('Allow Custom','acf'),
            'instructions'      => __("Allow 'custom' values to be added", 'acf'),
            'name'              => 'allow_custom',
            'type'              => 'true_false',
            'ui'                => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                ),
            )
        ));
    
        // Placeholder
        acf_render_field_setting($field, array(
            'label'             => __('Placeholder','acf'),
            'instructions'      => __('Appears within the input','acf'),
            'type'              => 'text',
            'name'              => 'placeholder',
            'placeholder'       => _x('Select', 'verb', 'acf'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                ),
                array(
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                ),
                array(
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                ),
            )
        ));
    
        // Search Placeholder
        acf_render_field_setting($field, array(
            'label'             => __('Search Input Placeholder','acf'),
            'instructions'      => __('Appears within the search input','acf'),
            'type'              => 'text',
            'name'              => 'search_placeholder',
            'placeholder'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                ),
            )
        ));

    }
    
    
    /**
     * load_field
     *
     * @param $field
     *
     * @return mixed
     */
    function load_field($field){
        
        // ajax enabled
        if(!empty($field['ajax'])){
            $field = acfe_strip_choices_label($field); // strip '## title' grouping for ajax
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field($field){
    
        // allow custom
        if(!empty($field['allow_custom'])){
        
            $value = acfe_get($field, 'value');
            $value = acfe_as_array($value);
        
            foreach($value as $v){
            
                // append custom value to choices
                if(!isset($field['choices'][ $v ])){
                    $field['choices'][ $v ] = $v;
                    $field['custom_choices'][ $v ] = $v;
                }
            }
        
        }
        
        // group choices using '## title'
        if(empty($field['ajax'])){
            $field = acfe_decode_choices_label($field);
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function field_wrapper_attributes($wrapper, $field){
        
        // search placeholder
        if($field['search_placeholder']){
            $wrapper['data-acfe-search-placeholder'] = $field['search_placeholder'];
        }
        
        // allow custom
        if($field['allow_custom']){
            $wrapper['data-acfe-allow-custom'] = 1;
        }
        
        // return
        return $wrapper;
        
    }
    
    
    /**
     * format_front_value
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     * @param $form
     *
     * @return string
     */
    function format_front_value($formatted, $unformatted, $post_id, $field, $form){
        
        // vars
        $value = acfe_as_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $v){
            $array[] = acfe_get($field['choices'], $v, $v);
        }
        
        // merge
        return implode(', ', $array);
        
    }
    
    
    /**
     * validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return false
     */
    function validate_front_value($valid, $value, $field, $input, $form){
        
        // bail early
        if(!$this->pre_validate_front_value($valid, $value, $field, $form)){
            return $valid;
        }
        
        // custom value allowed
        if(!empty($field['create_options']) || !empty($field['allow_custom'])){
            return $valid;
        }
        
        // vars
        $value = acfe_as_array($value);
        $choices = acfe_as_array($field['choices']);
        
        // handle ajax choices
        if(!empty($field['ajax'])){
            
            if(method_exists($this->instance, 'get_ajax_query')){
                
                // perform select ajax query
                $query = $this->instance->get_ajax_query(array(
                    'field_key' => $field['key'],
                    'post_id'   => $form['post_id'],
                ));
                
                // empty query
                if(empty($query)){
                    return false;
                }
                
                // get results
                // expecting array('results' => array( array('id' => '', 'text' => '') ))
                $results = acfe_get($query, 'results');
                $results = acfe_as_array($results);
                
                // no results
                if(empty($results)){
                    return false;
                }
                
                // reset choices
                $choices = array();
                
                // loop results and assign choices
                foreach($results as $result){
                    if(isset($result['id'], $result['text'])){
                        $choices[ $result['id'] ] = $result['text'];
                    }
                }
                
            }
            
        }
        
        // empty choices
        if(empty($choices)){
            return false; // value is always invalid as there no choice is allowed
        }
        
        // check values against choices
        if(!empty(array_diff($value, array_keys($choices)))){
            return false;
        }
        
        // return
        return $valid;
        
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     *
     * @return mixed
     */
    function translate_field($field){
        
        $field['placeholder'] = acf_translate($field['placeholder']);
        $field['search_placeholder'] = acf_translate($field['search_placeholder']);
        
        return $field;
        
    }
    
}

acf_new_instance('acfe_field_select');

endif;