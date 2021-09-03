<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_data')):

class acfe_field_data{
    
    function __construct(){
    
        add_action('acf/render_field_settings',             array($this, 'render_field_settings'), 992);
        add_filter('acf/render_field/name=acfe_field_data', array($this, 'render_field'));
        
    }
    
    function render_field_settings($field){
    
        $id = acf_maybe_get($field, 'ID');
    
        if(!$id || $id === 'acfcloneindex')
            return;
        
        acf_render_field_setting($field, array(
            'label'         => false,
            'instructions'  => '',
            'type'          => 'acfe_dynamic_render',
            'required'      => false,
            'name'          => 'acfe_field_data',
            'key'           => 'acfe_field_data',
            'value'         => $id,
        ), true);
        
    }
    
    function render_field($field){
    
        $id = $field['value'];
        
        // validate
        if(!$id) return;
        
        // Field
        $field = acf_get_field($id);
        $field = array_map(function($value){
            
            if(is_array($value)) return $value;
            
            return esc_html($value);
            
        }, $field);
        
        $field_debug = $field ? '<pre>' . print_r($field, true) . '</pre>' : '<pre>' . __('Field data unavailable', 'acfe') . '</pre>';
        
        // Post
        $post = get_post($id, ARRAY_A);
        $post = array_map(function($value){
            
            if(is_array($value)) return $value;
            
            return esc_html($value);
            
        }, $post);
        
        $post_debug = $post ? '<pre style="margin-top:15px;">' . print_r($post, true) . '</pre>' : '<pre>' . __('Post object unavailable', 'acfe') . '</pre>';
        
        // title
        $title = __('Field Data', 'acf');
        
        // field label
        if($field){
            
            $label = acf_maybe_get($field, 'label');
            $name = acf_maybe_get($field, 'name');
            
            $title = $label ? $label : $name;
            
        }
    
        ?>
        <a href="#" class="button acfe-data-button" data-acfe-modal="<?php echo $id; ?>" data-acfe-modal-title="<?php echo $title; ?>" data-acfe-modal-footer="<?php _e('Close', 'acf'); ?>" style="margin-left:5px;">
            <?php _e('Data', 'acf'); ?>
        </a>
        <div class="acfe-modal" data-acfe-modal="<?php echo $id; ?>">
            <div class="acfe-modal-spacer"><?php echo $field_debug . $post_debug; ?></div>
        </div>
        <?php
    }
    
}

new acfe_field_data();

endif;