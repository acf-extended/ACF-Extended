<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_front_render')):

class acfe_module_form_front_render{
    
    /**
     * __construct
     */
    function __construct(){
        // ...
    }
    
    
    /**
     * form_render_success
     *
     * acfe/form/render_success
     *
     * @param $form
     */
    function render_success($form){
        
        // get message
        $message = $form['success']['message'];
        
        // success message
        if($message){
            
            // html message
            if($form['success']['wrapper']){
                $message = sprintf($form['success']['wrapper'], wpautop(wp_unslash($message)));
            }
            
            echo $message;
            
        }
        
    }
    
    
    /**
     * prepare_form
     *
     * acfe/form/prepare_form
     *
     * @param $form
     *
     * @return mixed
     */
    function prepare_form($form){
        
        if(!$form){
            return false;
        }
        
        // hide form on success
        if(acfe_is_form_success($form['name']) && $form['success']['hide_form']){
            return false;
        }
        
        // field values
        // we must inject values earlier than 10 so custom check in acf/prepare_field can be done
        // this fix an issue with Select 'custom value' which is checked on acf/prepare_field/type=select
        add_filter('acf/prepare_field', array($this, 'prepare_field_values'), 9);
        
        // field settings
        add_filter('acf/prepare_field', array($this, 'prepare_field_settings'), 15);
        
        // field attributes
        add_filter('acf/prepare_field', array($this, 'prepare_field_attributes'), 15);
        
        // uploader (always set in case of multiple forms on the page)
        acf_disable_filter('acfe/form/uploader');
        
        if($form['settings']['uploader'] !== 'default'){
            
            acf_enable_filter('acfe/form/uploader');
            acf_update_setting('uploader', $form['settings']['uploader']);
            
        }
    
        // generate render
        if($form['render']){
            
            // added mapped fields to context
            // this allow {render:field_name} to first check fields of the mapped field groups
            $mapped_fields = $this->get_form_fields_keys($form);
            acfe_add_context('mapped_fields', $mapped_fields);
            
            // check if render has {render:submit} tag
            $has_render_submit = false;
        
            // array render
            if(is_array($form['render'])){
            
                $html = array_map(function($row){
                    return "{render:$row}";
                }, $form['render']);
            
                $html = implode('', $html);
            
                // assign new render
                $form['render'] = $html;
            
            // function render
            }elseif(is_callable($form['render'])){
            
                ob_start();
                    call_user_func_array($form['render'], array($form));
                $html = ob_get_clean();
            
                // assign new render
                $form['render'] = $html;
            
            }
        
            // check render
            if(is_string($form['render'])){
            
                // check {render:submit} exists
                if(strpos($form['render'], '{render:submit}') !== false){
                    $has_render_submit = true;
                }
            
            }
        
            // parse template tags
            $form['render'] = acfe_parse_tags($form['render']);
            
            // render is empty even after tags parsing
            // we must set it to true to render nothing
            // and avoid rendering default field group
            if(empty($form['render'])){
                $form['render'] = true;
            }
            
            // render has the {render:submit} tag
            // disallow default submit button after the form render
            if($has_render_submit){
                $form['attributes']['submit'] = false;
            }
        
        }
        
        return $form;
        
    }
    
    
    /**
     * render_before_form
     *
     * acfe/form/render_before_form
     *
     * @param $form
     */
    function render_before_form($form){
        
        /**
         * form wrapper open
         */
        $element = $form['attributes']['form']['element'];
        $is_preview = acfe_is_dynamic_preview();
        
        // remove <form>
        if($is_preview){
            $element = 'div';
        }
        
        // disabled required + fields names
        if($is_preview){
            add_filter('acf/prepare_field', array($this, 'disable_fields'));
        }
        
        // atts
        $atts = array(
            'action'                 => '',
            'method'                 => 'post',
            'class'                  => 'acfe-form',
            'id'                     => $form['attributes']['form']['id'],
            'data-fields-class'      => $form['attributes']['fields']['class'],
            'data-hide-error'        => $form['validation']['hide_error'],
            'data-hide-unload'       => $form['validation']['hide_unload'],
            'data-hide-revalidation' => $form['validation']['hide_revalidation'],
            'data-errors-position'   => $form['validation']['errors_position'],
            'data-errors-class'      => $form['validation']['errors_class'],
        );
        
        // append "-success" class
        if(acfe_is_form_success($form['name'])){
            
            // get submitted form
            $submitted_form = acfe_form_decrypt_args();
            
            // compare to loaded form
            if($submitted_form === $form){
                $atts['class'] .= ' -success';
            }
            
        }
        
        // form class
        if($form['attributes']['form']['class']){
            $atts['class'] .= ' ' . $form['attributes']['form']['class'];
        }
        
        // unset method & action for <div> element
        if($element === 'div'){
            unset($atts['method'], $atts['action']);
        }
        
        $atts = apply_filters("acfe/form/render_form_atts",                      $atts, $form);
        $atts = apply_filters("acfe/form/render_form_atts/form={$form['name']}", $atts, $form);
        
        // esc atts
        $atts = acf_esc_attrs($atts);
        
        // <form class="acfe-form">
        echo "<{$element} {$atts}>";
        
        // form data
        // do not set form data in preview mode
        if(!$is_preview){
            
            acf_form_data(array(
                'screen'  => 'acfe_form',
                'post_id' => $form['post_id'],
                'form'    => acf_encrypt(json_encode($form))
            ));
            
        }
        
    }
    
    
    /**
     * render_before_fields
     *
     * acfe/form/render_before_fields
     *
     * @param $form
     */
    function render_before_fields($form){
    
        /**
         * fields wrapper open
         */
        $atts = array('class' => 'acf-fields acf-form-fields');
    
        if($form['attributes']['fields']['label'] !== 'hidden'){
            $atts['class'] .= " -{$form['attributes']['fields']['label']}";
        }
    
        $atts = acf_esc_attrs($atts);
    
        // <div class="acf-fields acf-form-fields">
        echo "<div {$atts}>";
        
    }
    
    
    /**
     * render_fields
     *
     * acfe/form/render_fields
     *
     * @param $form
     */
    function render_fields($form){
    
        // honeypot
        // ACF will then automatically validate that _validate_email field
        // in /advanced-custom-fields-pro/includes/forms/form-front.php:218
        if($form['settings']['honeypot']){
    
            // register local _validate_email
            acf_add_local_field(array(
                'prefix'    => 'acf',
                'name'      => '_validate_email',
                'key'       => '_validate_email',
                'label'     => __('Validate Email', 'acf'),
                'type'      => 'text',
                'value'     => '',
                'wrapper'   => array('style' => 'display:none !important;')
            ));
            
            $honeypot = array(acf_get_field('_validate_email'));
            acf_render_fields($honeypot, $form['uniqid'], $form['attributes']['fields']['element'], $form['attributes']['fields']['instruction']);
            
        }
        
        // custom render
        if($form['render']){
            echo $form['render'];
            
        // default render
        }else{
            
            $fields = $this->get_allowed_fields($form);
            acf_render_fields($fields, $form['uniqid'], $form['attributes']['fields']['element'], $form['attributes']['fields']['instruction']);
            
        }
        
    }
    
    
    /**
     * render_after_fields
     *
     * acfe/form/render_after_fields
     *
     * @param $form
     */
    function render_after_fields($form){
    
        /**
         * fields wrapper close
         */
        echo '</div>';
        
    }
    
    
    /**
     * render_submit
     *
     * acfe/form/render_submit
     *
     * @param $form
     */
    function render_submit($form){
        
        // form submit
        if($form['attributes']['submit']): ?>
            <div class="acf-form-submit">
                
                <?php printf($form['attributes']['submit']['button'], $form['attributes']['submit']['value']); ?>
                <?php echo $form['attributes']['submit']['spinner']; ?>

            </div>
        <?php endif;
        
    }
    
    
    /**
     * render_after_form
     *
     * acfe/form/render_after_form
     *
     * @param $form
     */
    function render_after_form($form){
    
        /**
         * form wrapper close
         */
        $element = $form['attributes']['form']['element'];
        $is_preview = acfe_is_dynamic_preview();
    
        // remove <form>
        if($is_preview){
            $element = 'div';
        }
        
        // </form>
        echo "</{$element}>";
    
        // re-enable required + fields names
        if($is_preview){
            remove_filter('acf/prepare_field', array($this, 'disable_fields'));
        }
        
    }
    
    
    /**
     * prepare_field_values
     *
     * acf/prepare_field:9
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field_values($field){
        
        // hidden by code
        if(!$field){
            return $field;
        }
        
        // get form context
        $form = acfe_get_context('form');
        if(!$form){
            return $field;
        }
        
        // mapping not set
        if(!isset($form['map'][ $field['key'] ]['value'])){
            return $field;
        }
        
        $field['value'] = $form['map'][ $field['key'] ]['value'];
        
        return $field;
        
    }
    
    
    /**
     * prepare_field_settings
     *
     * acf/prepare_field:15
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field_settings($field){
        
        // hidden by code
        if(!$field){
            return $field;
        }
        
        // get form context
        $form = acfe_get_context('form');
        if(!$form){
            return $field;
        }
        
        // mapping not set
        if(!isset($form['map'][ $field['key'] ])){
            return $field;
        }
        
        // hidden in mapping
        if($form['map'][ $field['key'] ] === false){
            return false;
        }
        
        // value already injected in prepare_field_values()
        unset($form['map'][ $field['key'] ]['value']);
        
        // merge
        return array_merge($field, $form['map'][ $field['key'] ]);
        
    }
    
    
    /**
     * prepare_field_attributes
     *
     * acf/prepare_field:15
     *
     * @param $field
     *
     * @return array|mixed
     */
    function prepare_field_attributes($field){
        
        // hidden by code
        if(!$field){
            return $field;
        }
        
        // get form context
        $form = acfe_get_context('form');
        if(!$form){
            return $field;
        }
        
        if($form['attributes']['fields']['wrapper_class']){
            $field['wrapper']['class'] .= ' ' . $form['attributes']['fields']['wrapper_class'];
        }
        
        if($form['attributes']['fields']['class']){
            $field['class'] .= ' ' . $form['attributes']['fields']['class'];
        }
        
        if($form['attributes']['fields']['label'] === 'hidden'){
            $field['label'] = false;
        }
        
        return $field;
        
    }
    
    
    /**
     * disable_fields
     *
     * @param $field
     *
     * @return mixed
     */
    function disable_fields($field){
        
        $field['name'] = '';
        $field['required'] = false;
        
        return $field;
        
    }
    
    
    /**
     * get_allowed_field_groups
     *
     * @param $form
     *
     * @return array
     */
    function get_allowed_field_groups($form){
    
        // vars
        $field_groups = array();
    
        // post field groups
        // deprecated
        if(acf_maybe_get($form, 'post_field_groups')){
    
            _deprecated_function('ACF Extended: "Post Field Groups" Form setting', '0.8.7.5');
        
            // Override Field Groups
            $post_field_groups = acf_get_field_groups(array(
                'post_id' => $form['post_field_groups']
            ));
        
            // re-assign post field groups
            $form['field_groups'] = wp_list_pluck($post_field_groups, 'key');
        
        }
    
        // form field groups
        foreach($form['field_groups'] as $key){
        
            // make sure field group exists
            $field_group = acf_get_field_group($key);
        
            if($field_group){
                $field_groups[] = $field_group;
            }
        
        }
    
        // apply field groups rules
        if($field_groups && $form['settings']['location']){
        
            $location = array(
                'post_id'   => $form['post_id'],
                'post_type' => get_post_type($form['post_id']),
            );
        
            $filtered = array();
            
            // loop
            foreach($field_groups as $field_group){
            
                // check field group is valid
                if(isset($field_group['location'])){
                
                    // temporarly active field group for visibility check
                    $field_group['active'] = true;
                
                    // fitler field group
                    if(acf_get_field_group_visibility($field_group, $location)){
                        $filtered[] = $field_group;
                    }
                
                }
            
            }
        
            // assign new filtered field groups
            $field_groups = $filtered;
        
        }
        
        // return
        return $field_groups;
    
    }
    
    
    /**
     * get_allowed_fields
     *
     * @param $form
     *
     * @return array
     */
    function get_allowed_fields($form){
    
        // vars
        $fields = array();
    
        // get allowed field groups (filtered)
        $field_groups = $this->get_allowed_field_groups($form);
    
        foreach($field_groups as $field_group){
            
            $_fields = acf_get_fields($field_group);
            
            foreach(array_keys($_fields) as $i){
                $fields[] = acf_extract_var($_fields, $i);
            }
        
        }
        
        return $fields;
        
    }
    
    
    /**
     * get_form_fields_keys
     *
     * Used to determine if the {render:field_name} is within mapped field groups
     *
     * @param $form
     *
     * @return array
     */
    function get_form_fields_keys($form){
        
        $results = array();
        
        // form field groups
        foreach($form['field_groups'] as $key){
            
            // make sure field group exists
            $field_group = acf_get_field_group($key);
            
            // found field group
            if($field_group){
                
                // get fields
                $fields = acf_get_fields($field_group);
                
                // found fields
                if(!empty($fields)){
                    
                    // merge results
                    $results = array_merge($results, wp_list_pluck($fields, 'key', 'name'));
                }
                
            }
            
        }
        
        return $results;
        
    }
    
}

acf_new_instance('acfe_module_form_front_render');

endif;