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
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field($field){
        
        // vars
        $allow_custom = acf_maybe_get($field, 'allow_custom');
        $ajax = acf_maybe_get($field, 'ajax');
    
        // allow custom
        if($allow_custom){
        
            $value = acf_maybe_get($field, 'value');
            $value = acf_get_array($value);
        
            foreach($value as $v){
            
                // append custom value to choices
                if(!isset($field['choices'][ $v ])){
                    $field['choices'][ $v ] = $v;
                    $field['custom_choices'][ $v ] = $v;
                }
            }
        
        }
        
        // group choices using '## title'
        if(!$ajax && is_array($field['choices'])){
    
            $found = false;
            $choices = array();
            
            // loop choices
            foreach($field['choices'] as $k => $choice){
        
                if(is_string($choice)){
            
                    $choice = trim($choice);
            
                    if(strpos($choice, '##') === 0){
                
                        $choice = substr($choice, 2);
                        $choice = trim($choice);
                
                        $found = $choice;
                        $choices[ $choice ] = array();
                
                    }elseif(!empty($found)){
    
                        $choices[ $found ][ $k ] = $choice;
                
                    }
            
                }
        
            }
            
            // assign found choices
            if(!empty($choices)){
                $field['choices'] = $choices;
            }

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