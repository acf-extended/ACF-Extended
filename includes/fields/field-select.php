<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_select')):

class acfe_field_select{
    
    function __construct(){
        
        // Actions
        add_action('acf/render_field_settings/type=select',         array($this, 'field_settings'));
        
        // Filters
        add_filter('acf/prepare_field/type=select',                 array($this, 'prepare_field'));
        add_filter('acfe/field_wrapper_attributes/type=select',     array($this, 'field_wrapper'), 10, 2);

        add_action('current_screen', array($this, 'current_screen'));
        
    }

    function current_screen(){

        if(!acfe_is_admin_screen())
            return;

        add_filter('acf/prepare_field/name=choices', array($this, 'prepare_field_choices'), 5);

    }

    function prepare_field_choices($field){

        $wrapper = $field['wrapper'];

        if(acf_maybe_get($wrapper, 'data-setting') !== 'select')
            return $field;

        $field['instructions'] .= '<br/><br/>You may use "## Title" to create a group of options.';

        return $field;

    }

    function field_settings($field){

        // allow custom
        acf_render_field_setting($field, array(
            'label'             => __('Allow Custom','acf'),
            'instructions'      => '',
            'name'              => 'allow_custom',
            'type'              => 'true_false',
            'ui'                => 1,
            'message'           => __("Allow 'custom' values to be added", 'acf'),
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
    
    function prepare_field($field){
        
        // Allow Custom
        if(acf_maybe_get($field, 'allow_custom')){
            
            if($value = acf_maybe_get($field, 'value')){
                
                $value = acf_get_array($value);
                
                foreach($value as $v){
                    
                    if(isset($field['choices'][$v]))
                        continue;
                    
                    $field['choices'][$v] = $v;
                    
                }
                
            }
            
        }

        if(!acf_maybe_get($field, 'ajax')){

            if(is_array($field['choices'])){

                $found = false;
                $found_array = array();

                foreach($field['choices'] as $k => $choice){

                    if(is_string($choice)){
                    
                        $choice = trim($choice);
                        
                        if(strpos($choice, '##') === 0){
                        
                            $choice = substr($choice, 2);
                            $choice = trim($choice);
                            
                            $found = $choice;
                            $found_array[$choice] = array();
                        
                        }elseif(!empty($found)){
                        
                            $found_array[$found][$k] = $choice;
                        
                        }
                    
                    }

                }

                if(!empty($found_array)){

                    $field['choices'] = $found_array;

                }

            }

        }
        
        return $field;
        
    }
    
    function field_wrapper($wrapper, $field){
        
        // Search placeholder
        if($search_placeholder = acf_maybe_get($field, 'search_placeholder')){
            
            $wrapper['data-acfe-search-placeholder'] = $search_placeholder;
            
        }
        
        // Allow Custom
        if(acf_maybe_get($field, 'allow_custom')){
            
            $wrapper['data-acfe-allow-custom'] = 1;
            
        }
        
        return $wrapper;
        
    }
    
}

new acfe_field_select();

endif;