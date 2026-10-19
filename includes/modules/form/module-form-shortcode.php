<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_shortcode')):

class acfe_module_form_shortcode{

    /**
     * construct
     */
    function __construct(){
    
        // shortcode
        add_action('init',                                  array($this, 'init'));
        add_filter('mce_external_plugins',                  array($this, 'mce_plugins'));
        add_filter('mce_css',                               array($this, 'mce_css'));

        // shortcode
        add_filter('acfe/form/shortcode/allowed_args',      array($this, 'allowed_args'), 5);

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
        $atts = acfe_as_array($atts);
        
        // parse int|true|false|null
        $atts = acfe_parse_types($atts);
        
        // allowed array keys
        $args = $this->get_args();
        
        // allow array atts
        foreach(array_keys($atts) as $key){

            // get value
            $value = $atts[ $key ];

            // clear template tags
            $value = acfe_clear_tags($value);

            // allowed/disallowed: remove from atts
            if(isset($args['allowed'][ $key ]) || isset($args['disallowed'][ $key ])){
                unset($atts[ $key ]);
            }

            // allowed: add the correct path to atts
            if(isset($args['allowed'][ $key ])){
                acfe_set($atts, $args['allowed'][ $key ], $value);
            }
            
        }

        // escape success message
        if(!empty($atts['success']['message'])){
            $atts['success']['message'] =  acf_esc_html($atts['success']['message']);
        }
        
        // render
        ob_start();

        acfe_form_shortcode($atts);
        
        return ob_get_clean();
        
    }


    /**
     * allowed_args
     *
     * @return string[]
     */
    function allowed_args($args){

        $args[] = 'ID';
        $args[] = 'name';
        $args[] = 'attributes.submit.value';
        $args[] = 'attributes.form.class';
        $args[] = 'attributes.form.id';
        $args[] = 'success.message';

        return $args;

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

        // suffix
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        // add script
        $plugins['acfe_form'] = acfe_get_url('assets/inc/tinymce/acfe-form' . $suffix . '.js');

        // return
        return $plugins;
        
    }


    /**
     * mce_css
     *
     * @param $css
     *
     * @return string
     */
    function mce_css($css){

        // suffix
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        // add stylesheet
        $css .= !empty($css) ? ',' : '';
        $css .= acfe_get_url('assets/inc/tinymce/acfe-form' . $suffix . '.css');

        // return
        return $css;

    }
    
    
    /**
     * ajax_shortcode
     *
     * wp_ajax_acfe/form/shortcode
     */
    function ajax_shortcode(){
        
        // validate
        if(!acfe_verify_ajax()){
            die();
        }
        
        // vars
        $args = acf_maybe_get_POST('args', array());
        $title = '';
        
        // loop thru args
        foreach(array('name', 'id') as $key){
            if(acfe_get($args, $key)){
                $title = acfe_get($args, $key);
                break;
            }
        }

        // sanitize title
        $title = is_numeric($title) ? "#{$title}" : "\"{$title}\"";
        $title = esc_attr($title);
        
        ob_start();
        ?>
        <div><?php _e('Form', 'acfe'); ?> <?php echo $title; ?></div>
        <?php echo ob_get_clean();
        
        die();
        
    }


    /**
     * get_args
     *
     * @return array
     */
    function get_args(){

        // prepare args
        $args = array(
            'ID'          => 'ID',
            'post_id'     => 'post_id',
            'uniqid'      => 'uniqid',
            'cid'         => 'cid',
            'map'         => 'map',
            'map_default' => 'map_default',
        );

        // get module
        $module = acfe_get_module('form');

        // get item args paths
        $paths = acfe_array_keys_dot($module->item);

        // collapse dot notation to underscore notation
        foreach($paths as $path){

            // collapse
            $key = str_replace('.', '_', $path);

            // append
            $args[ $key ] = $path;

        }

        /**
         * $args = array(
         *     'ID'                              => 'ID',
         *     'name'                            => 'name',
         *     'title'                           => 'title',
         *     'active'                          => 'active',
         *     'field_groups'                    => 'field_groups',
         *     'settings'                        => 'settings',
         *     'attributes'                      => 'attributes',
         *     'validation'                      => 'validation',
         *     'success'                         => 'success',
         *     'actions'                         => 'actions',
         *     'render'                          => 'render',
         *     'settings_location'               => 'settings.location',
         *     'settings_honeypot'               => 'settings.honeypot',
         *     'settings_kses'                   => 'settings.kses',
         *     'settings_uploader'               => 'settings.uploader',
         *     'attributes_form'                 => 'attributes.form',
         *     'attributes_fields'               => 'attributes.fields',
         *     'attributes_submit'               => 'attributes.submit',
         *     'validation_hide_error'           => 'validation.hide_error',
         *     'validation_hide_revalidation'    => 'validation.hide_revalidation',
         *     'validation_hide_unload'          => 'validation.hide_unload',
         *     'validation_errors_position'      => 'validation.errors_position',
         *     'validation_errors_class'         => 'validation.errors_class',
         *     'validation_messages'             => 'validation.messages',
         *     'success_hide_form'               => 'success.hide_form',
         *     'success_scroll'                  => 'success.scroll',
         *     'success_shortcode'               => 'success.shortcode',
         *     'success_message'                 => 'success.message',
         *     'success_wrapper'                 => 'success.wrapper',
         *     'attributes_form_element'         => 'attributes.form.element',
         *     'attributes_form_class'           => 'attributes.form.class',
         *     'attributes_form_id'              => 'attributes.form.id',
         *     'attributes_fields_element'       => 'attributes.fields.element',
         *     'attributes_fields_wrapper_class' => 'attributes.fields.wrapper_class',
         *     'attributes_fields_class'         => 'attributes.fields.class',
         *     'attributes_fields_label'         => 'attributes.fields.label',
         *     'attributes_fields_instruction'   => 'attributes.fields.instruction',
         *     'attributes_submit_value'         => 'attributes.submit.value',
         *     'attributes_submit_button'        => 'attributes.submit.button',
         *     'attributes_submit_spinner'       => 'attributes.submit.spinner',
         *     'validation_messages_failure'     => 'validation.messages.failure',
         *     'validation_messages_success'     => 'validation.messages.success',
         *     'validation_messages_error'       => 'validation.messages.error',
         *     'validation_messages_errors'      => 'validation.messages.errors',
         * );
         */

        // append deprecated args
        $args = array_merge($args, array(
            // render
            'custom_html_enabled'             => 'render',
            'custom_html'                     => 'render',
            'html_before_fields'              => 'render',
            'html_after_fields'               => 'render',

            // attributes
            'form_submit'                     => null,
            'submit_value'                    => 'attributes.submit.value',
            'html_submit_button'              => 'attributes.submit.button',
            'html_submit_spinner'             => 'attributes.submit.spinner',
            'field_el'                        => 'attributes.fields.element',
            'fields_attributes'               => 'attributes.fields',
            'fields_attributes_wrapper_class' => 'attributes.fields.wrapper_class',
            'fields_attributes_class'         => 'attributes.fields.class',
            'form'                            => 'attributes.form.element',
            'form_attributes'                 => 'attributes.form',
            'form_attributes_id'              => 'attributes.form.id',
            'form_attributes_class'           => 'attributes.form.class',

            // validation
            'hide_error'                      => 'validation.hide_error',
            'hide_unload'                     => 'validation.hide_unload',
            'hide_revalidation'               => 'validation.hide_revalidation',
            'errors_position'                 => 'validation.errors_position',
            'errors_class'                    => 'validation.errors_class',

            // success
            'updated_message'                 => 'success.message',
            'html_updated_message'            => 'success.wrapper',
            'updated_hide_form'               => 'success.hide_form',

            // settings
            'field_groups_rules'              => 'settings.location',
            'honeypot'                        => 'settings.honeypot',
            'kses'                            => 'settings.kses',
            'uploader'                        => 'settings.uploader',

            // attributes (placement)
            'label_placement'                 => 'attributes.fields.label',
            'instruction_placement'           => 'attributes.fields.instruction',
        ));

        // get allowed paths
        $allowed_args = (array) apply_filters('acfe/form/shortcode/allowed_args', array());

        // prepare allowed/disallowed arrays
        $allowed = array();
        $disallowed = array();

        // loop
        foreach($args as $key => $path){

            // allowed
            if(in_array($path, $allowed_args, true)){
                $allowed[ $key ] = $path;

            // disallowed
            }else{
                $disallowed[ $key ] = $path;
            }

        }

        // return
        return array(
            'allowed'    => $allowed,
            'disallowed' => $disallowed,
        );

    }
    
}

acf_new_instance('acfe_module_form_shortcode');

endif;