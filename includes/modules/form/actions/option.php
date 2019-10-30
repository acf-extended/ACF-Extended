<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_option')):

class acfe_form_option{
    
    function __construct(){
        
        add_filter('acfe/form/load/option',     array($this, 'load'), 1);
        add_action('acfe/form/submit/option',   array($this, 'submit'), 1, 3);
        
    }
    
    function load($args){
        
        $form_name = acf_maybe_get($args, 'form_name');
        $form_id = acf_maybe_get($args, 'form_id');
        
        if(!get_sub_field('acfe_form_option_load'))
            return $args;
        
        $_option_name_group = get_sub_field('acfe_form_option_name_group');
        $_option_name = $_option_name_group['acfe_form_option_name'];
        $_option_name_custom = $_option_name_group['acfe_form_option_name_custom'];
        
        // var
        $_post_id = $args['post_id'];
        
        // Custom
        if($_option_name === 'custom'){
            
            $_post_id = acfe_form_map_field_get_value($_option_name_custom);
            
        // Field
        }elseif(acf_is_field_key($_option_name)){
            
            $_post_id = get_field($_option_name);
        
        }
        
        $_post_id = apply_filters('acfe/form/load/option_name',                      $_post_id, $args);
        $_post_id = apply_filters('acfe/form/load/option_name/name=' . $form_name,   $_post_id, $args);
        $_post_id = apply_filters('acfe/form/load/option_name/id=' . $form_id,       $_post_id, $args);
        
        // ID
        $args['post_id'] = $_post_id;
        
        return $args;
        
    }
    
    function submit($form, $post_id, $acf){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        $_option_name_group = get_sub_field('acfe_form_option_name_group');
        $_option_name = $_option_name_group['acfe_form_option_name'];
        $_option_name_custom = $_option_name_group['acfe_form_option_name_custom'];
        
        // var
        $_post_id = false;
        
        // Current post
        if($_option_name === 'custom'){
            
            $_post_id = acfe_form_map_field_value($_option_name_custom, $acf);
            
        // Field
        }elseif(acf_is_field_key($_option_name)){
            
            $_post_id = acfe_form_map_field_value($_option_name, $acf);
            
        }
        
        do_action('acfe/form/submit/option',                       $form, $_post_id);
        do_action('acfe/form/submit/option/name=' . $form_name,    $form, $_post_id);
        do_action('acfe/form/submit/option/id=' . $form_id,        $form, $_post_id);
        
        // Meta save
        $_meta = get_sub_field('acfe_form_option_meta');
        
        $data = acfe_form_filter_meta($_meta, $acf);
        
        if(!empty($data)){
            
            // Save meta fields
            acf_save_post($_post_id, $data);
        
        }
        
    }
    
}

new acfe_form_option();

endif;