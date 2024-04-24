<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_shortcode')):

class acfe_module_form_shortcode{
    
    function __construct(){
    
        // shortcode
        add_action('init',                                  array($this, 'init'));
        add_filter('mce_external_plugins',                  array($this, 'mce_plugins'));
    
        // ajax
        add_action('wp_ajax_acfe/form/shortcode',           array($this, 'ajax_shortcode'), 20);
        add_action('wp_ajax_nopriv_acfe/form/shortcode',    array($this, 'ajax_shortcode'), 20);
        
    }
    
    
    /**
     * init
     */
    function init(){
    
        add_shortcode('acfe_form', array($this, 'render_shortcode'));
        
    }
    
    
    /**
     * render_shortcode
     *
     * @param $atts
     *
     * @return false|string
     */
    function render_shortcode($atts){
        
        // attributes array
        $atts = acf_get_array($atts);
        
        // parse int|true|false|null
        $atts = acfe_parse_types($atts);
        
        // allowed array keys
        $allowed_arrays = array(
            'form_attributes'   => 'form_attributes',   // deprecated
            'fields_attributes' => 'fields_attributes', // deprecated
            'settings'          => 'settings',
            'attributes_form'   => 'attributes.form',
            'attributes_fields' => 'attributes.fields',
            'attributes_submit' => 'attributes.submit',
            'validation'        => 'validation',
            'success'           => 'success',
        );
        
        // allow array atts
        foreach(array_keys($atts) as $key){
            
            // sub array compatibility
            foreach($allowed_arrays as $flat => $path){
                
                // check found allowed
                if(!acfe_starts_with($key, "{$flat}_")){
                    continue;
                }
                
                // grab value
                $value = $atts[ $key ];
                unset($atts[ $key ]);
                
                // explode
                $explode = explode("{$flat}_", $key);
                $parent = array_shift($explode);
                $path_key = array_shift($explode);
                
                if(!$path_key){
                    continue;
                }
                
                // set attribute
                acfe_array_set($atts, "{$path}.{$path_key}", $value);
                
            }
            
        }
        
        // disallowed shortcode settings (deprecated)
        unset($atts['html_submit_button'], $atts['html_submit_spinner'], $atts['html_updated_message']);
        
        // disallowed shortcode settings
        acfe_array_unset($atts, array('attributes.submit.button', 'attributes.submit.spinner', 'success.wrapper'));
        
        // render
        ob_start();
        
        acfe_form($atts);
        
        return ob_get_clean();
        
    }
    
    
    /**
     * mce_plugins
     *
     * mce_external_plugins
     *
     * TinyMCE plugins
     *
     * @param $plugins
     *
     * @return mixed
     */
    function mce_plugins($plugins){
        
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        $plugins['acfe_form'] = acfe_get_url('assets/inc/tinymce/acfe-form' . $suffix . '.js');
        
        return $plugins;
        
    }
    
    
    /**
     * ajax_shortcode
     *
     * wp_ajax_acfe/form/shortcode
     */
    function ajax_shortcode(){
        
        // validate
        if(!acf_verify_ajax()){
            die();
        }
        
        // vars
        $args = acf_maybe_get_POST('args', array());
        $title = '';
        
        // loop thru args
        foreach(array('name', 'id') as $key){
            if(acf_maybe_get($args, $key)){
                $title = acf_maybe_get($args, $key);
                break;
            }
        }
        
        $title = is_numeric($title) ? "#{$title}" : "\"{$title}\"";
        
        ob_start();
        ?>
        <div style="border:1px solid #ddd; padding:100px 25px; background:#f8f8f8; text-align:center;">
            <?php _e('Form', 'acfe'); ?> <?php echo $title; ?>
        </div>
        <?php echo ob_get_clean();
        
        die();
        
    }
    
}

acf_new_instance('acfe_module_form_shortcode');

endif;