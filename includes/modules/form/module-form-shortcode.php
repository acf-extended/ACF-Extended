<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_shortcode')):

class acfe_form_shortcode{
    
    function __construct(){
        
        // shortcode
        add_shortcode('acfe_form',                          array($this, 'render_shortcode'));
        
        // ajax
        add_action('wp_ajax_acfe/form/shortcode',           array($this, 'ajax_shortcode'), 20);
        add_action('wp_ajax_nopriv_acfe/form/shortcode',    array($this, 'ajax_shortcode'), 20);
        
    }
    
    function render_shortcode($atts){
        
        // bail early on gutenberg screen
        // avoid bug  with media modal css in wp back-end
        if(acfe_is_block_editor()){
            return false;
        }
        
        // attributes array
        $atts = acf_get_array($atts);
        
        // allow array atts
        foreach(array_keys($atts) as $key){
            
            // sub array compatibility
            foreach(array('form_attributes_', 'fields_attributes_') as $allowed){
                
                // check found allowed
                if(!acfe_starts_with($key, $allowed)) continue;
                
                // explode
                $explode = explode($allowed, $key);
                $sub_key = $explode[1];
                
                // set attributes array
                $atts[ substr($allowed, 0, -1) ][ $sub_key ] = $atts[ $key ];
                unset($atts[ $key ]);
                
            }
            
        }
        
        // render
        ob_start();
        
        acfe_form($atts);
        
        return ob_get_clean();
        
    }
    
    function ajax_shortcode(){
    
        // validate
        if(!acf_verify_ajax()) die;
        
        // vars
        $args = acf_maybe_get_POST('args', array());
        $title = '';
    
        // loop thru args
        foreach(array('name', 'id') as $key){
        
            if(!acf_maybe_get($args, $key)) continue;
        
            $title = acf_maybe_get($args, $key);
            break;
        
        }
    
        $title = is_numeric($title) ? "#{$title}" : "\"{$title}\"";
    
        ob_start();
        ?>
        <div style="border:1px solid #ddd; padding:100px 25px; background:#f8f8f8; text-align:center;">
            <?php _e('Form', 'acfe'); ?> <?php echo $title; ?>
        </div>
        <?php echo ob_get_clean();
        die;
    
    }
    
}

acf_new_instance('acfe_form_shortcode');

endif;