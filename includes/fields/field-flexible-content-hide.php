<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_hide')):

class acfe_field_flexible_content_hide{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',              array($this, 'defaults_field'), 7);
        add_action('acfe/flexible/render_field_settings',       array($this, 'render_field_settings'), 7);
        
        add_filter('acfe/flexible/validate_field',              array($this, 'validate_hide'));
        add_filter('acfe/flexible/layouts/icons',               array($this, 'layout_icons'), 60, 3);
        add_filter('acfe/flexible/layouts/more_action_buttons', array($this, 'more_action_buttons'), 10, 2);
        add_filter('acfe/flexible/layout_renamed',              array($this, 'layout_renamed'), 20, 3);
        add_filter('acfe/flexible/layout_disabled',             array($this, 'layout_disabled'), 20, 3);
        add_filter('acfe/flexible/action_buttons',              array($this, 'action_buttons'), 10, 3);
        add_filter('acfe/flexible/action_buttons',              array($this, 'action_buttons_late'), 50, 3);
        
    }
    
    
    /**
     * defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_remove_button'] = array();
        $field['acfe_flexible_remove_top_actions'] = array();
        
        return $field;
        
    }
    
    
    /**
     * validate_hide
     *
     * @param $field
     *
     * @return mixed
     */
    function validate_hide($field){
        
        $field['acfe_flexible_remove_button'] = acf_get_array($field['acfe_flexible_remove_button']);
        $field['acfe_flexible_remove_top_actions'] = acf_get_array($field['acfe_flexible_remove_top_actions']);
        
        return $field;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        $hide_choices = array(
            'add'       => __('Hide "Add Row"', 'acfe'),
            'collapse'  => __('Hide "Collapse"', 'acfe'),
            'delete'    => __('Hide "Delete"', 'acfe'),
            'duplicate' => __('Hide "Duplicate"', 'acfe'),
        );
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            $hide_choices['rename'] = __('Hide "Rename"', 'acfe');
            $hide_choices['disable'] = __('Hide "Disable"', 'acfe');
            $hide_choices['top_actions'] = __('Hide "Top Actions"', 'acfe');
        }
        
        // pre ACF-5.9
        if(!acfe_is_acf_59()){
            acfe_unset($hide_choices, 'duplicate');
        }
    
        // Hide Buttons
        acf_render_field_setting($field, array(
            'label'         => __('Hide Buttons', 'acfe'),
            'name'          => 'acfe_flexible_remove_button',
            'key'           => 'acfe_flexible_remove_button',
            'instructions'  => '<a href="https://www.acf-extended.com/features/fields/flexible-content/advanced-settings#hide-buttons" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'checkbox',
            'default_value'     => '',
            'layout'            => 'horizontal',
            'choices'           => $hide_choices,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            
            // Hide Top Actions
            acf_render_field_setting($field, array(
                'label'         => __('Hide Top Actions', 'acfe'),
                'name'          => 'acfe_flexible_remove_top_actions',
                'key'           => 'acfe_flexible_remove_top_actions',
                'instructions'  => '<a href="https://www.acf-extended.com/features/fields/flexible-content/advanced-settings#hide-top-actions" target="_blank">' . __('See documentation', 'acfe') . '</a>',
                'type'              => 'checkbox',
                'default_value'     => '',
                'layout'            => 'horizontal',
                'choices'           => array(
                    'expand'   => __('Hide "Expand All"', 'acfe'),
                    'collapse' => __('Hide "Collapse All"', 'acfe'),
                    'add'      => __('Hide "Add Row"', 'acfe'),
                ),
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'acfe_flexible_advanced',
                            'operator'  => '==',
                            'value'     => '1',
                        ),
                        array(
                            'field'     => 'acfe_flexible_remove_button',
                            'operator'  => '!=',
                            'value'     => 'top_actions',
                        ),
                    )
                )
            ));
            
        }
        
    }
    
    
    /**
     * layout_icons
     *
     * Remove the icons from the layout controls
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layout_icons($icons, $layout, $field){
    
        if(in_array('add', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'add');
        }
    
        if(in_array('duplicate', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'duplicate');
        }
    
        if(in_array('delete', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'delete');
        }
        
        if(in_array('collapse', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'collapse');
        }
        
        // get more actions buttons (rename, disable...)
        $more_actions_buttons = acf_get_instance('acfe_field_flexible_content_popup')->get_popup_actions_buttons($field);
        
        if(empty($more_actions_buttons)){
            acfe_unset($icons, 'more');
        }
        
        return $icons;
        
    }
    
    
    /**
     * more_action_buttons
     *
     * Remove the buttons from the layout more actions popup
     *
     * @param $buttons
     * @param $field
     *
     * @return void
     */
    function more_action_buttons($buttons, $field){
        
        if(in_array('rename', $field['acfe_flexible_remove_button'])){
            acfe_unset($buttons, 'rename');
        }
        
        if(in_array('disable', $field['acfe_flexible_remove_button'])){
            acfe_unset($buttons, 'disable');
        }
        
        return $buttons;
    
    }
    
    
    /**
     * layout_renamed
     *
     * @param $renamed
     * @param $field
     * @param $i
     *
     * @return mixed|string
     */
    function layout_renamed($renamed, $field, $i){
        
        // never show renamed layout
        if(in_array('rename', $field['acfe_flexible_remove_button'])){
            return '';
        }
        
        return $renamed;
    
    }
    
    
    /**
     * layout_disabled
     *
     * @param $disabled
     * @param $field
     * @param $i
     *
     * @return false|mixed
     */
    function layout_disabled($disabled, $field, $i){
        
        // never show disabled layout
        if(in_array('disable', $field['acfe_flexible_remove_button'])){
            return false;
        }
        
        return $disabled;
    
    }
    
    
    /**
     * action_buttons
     *
     * @param $buttons
     * @param $field
     * @param $position
     *
     * @return array|mixed
     */
    function action_buttons($buttons, $field, $position){
        
        // remove add on both top & bottom
        if(in_array('add', $field['acfe_flexible_remove_button'])){
            acfe_unset($buttons, 'add');
        }
        
        // remove top actions
        if($position === 'top' && in_array('top_actions', $field['acfe_flexible_remove_button'])){
            return array(); // return empty array to hide the whole top actions
        }
        
        // remove add on top only
        if($position === 'top' && in_array('add', $field['acfe_flexible_remove_top_actions'])){
            acfe_unset($buttons, 'add');
        }
        
        if(in_array('expand', $field['acfe_flexible_remove_top_actions'])){
            acfe_unset($buttons, 'expand');
        }
        
        if(in_array('collapse', $field['acfe_flexible_remove_top_actions'])){
            acfe_unset($buttons, 'collapse');
        }
        
        return $buttons;
        
    }
    
    
    /**
     * action_buttons_late
     *
     * @param $buttons
     * @param $field
     * @param $position
     *
     * @return array|mixed
     */
    function action_buttons_late($buttons, $field, $position){
        
        // if separator is first or last, remove it
        if($this->array_key_first($buttons) === 'separator' || $this->array_key_last($buttons) === 'separator'){
            acfe_unset($buttons, 'separator');
        }
        
        return $buttons;
        
    }
    
    
    /**
     * array_key_first
     *
     * @param $array
     *
     * @return int|string|null
     */
    function array_key_first($array){
        
        if(!is_array($array) || empty($array)){
            return null;
        }
        
        foreach($array as $k => $v){
            return $k;
        }
        
        return null;
    }
    
    
    /**
     * array_key_last
     *
     * @param $array
     *
     * @return int|string|null
     */
    function array_key_last($array){
        
        if(!is_array($array) || empty($array)){
            return null;
        }
        
        $last = null;
        foreach($array as $k => $v){
            $last = $k;
        }
        
        return $last;
    }
    
}

acf_new_instance('acfe_field_flexible_content_hide');

endif;