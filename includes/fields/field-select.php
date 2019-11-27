<?php

if(!defined('ABSPATH'))
    exit;

add_action('acf/render_field_settings/type=select', 'acfe_field_select_settings');
function acfe_field_select_settings($field){
    
    // placeholder
    acf_render_field_setting($field, array(
        'label'			=> __('Placeholder Text','acf'),
        'instructions'	=> __('Appears within the input','acf'),
        'type'			=> 'text',
        'name'			=> 'placeholder',
        'placeholder'   => _x('Select', 'verb', 'acf'),
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'ui',
                    'operator'  => '==',
                    'value'     => '1',
                )
            ),
            array(
                array(
                    'field'     => 'allow_null',
                    'operator'  => '==',
                    'value'     => '1',
                )
            ),
        )
    ));
    
}