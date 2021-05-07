<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_front')):

class acfe_form_front{
    
    function __construct(){
        
        // vars
        $this->fields = array(
            '_validate_email' => array(
                'prefix'    => 'acf',
                'name'      => '_validate_email',
                'key'       => '_validate_email',
                'label'     => __('Validate Email', 'acf'),
                'type'      => 'text',
                'value'     => '',
                'wrapper'   => array('style' => 'display:none !important;')
            )
        );
        
        // Submit
        add_action('wp',                        array($this, 'check_submit_form'));
        
        // Shortcode
        add_shortcode('acfe_form',              array($this, 'add_shortcode'));
        
        // Validation
        add_action('acf/validate_save_post',    array($this, 'validate_save_post'), 1);
        
    }
    
    function validate_save_post(){
        
        if(!acfe_is_front())
            return;
    
        if(acf_maybe_get_POST('_acf_screen') !== 'acfe_form')
            return;
        
        $form = acfe_form_decrypt_args();
        
        if(!$form)
            return;
        
        $post_id = acf_maybe_get($form, 'post_id', false);
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        if(!$form_name || !$form_id)
            return;
        
        foreach($this->fields as $k => $field){
            
            // bail early if no in $_POST
            if(!isset($_POST['acf'][ $k ]))
                continue;
            
            // register
            acf_add_local_field($field);
            
        }
        
        // Honeypot
        if(!empty($acf['_validate_email'])){
            
            acf_add_validation_error('', __('Spam Detected', 'acf'));
            
        }
        
        // Validation
        acfe_setup_meta($_POST['acf'], 'acfe/form/validation', true);
        
            $rows = array();
        
            // Actions
            if(have_rows('acfe_form_actions', $form_id)):
                
                while(have_rows('acfe_form_actions', $form_id)): the_row();
                    
                    $action = get_row_layout();
                    
                    $alias = get_sub_field('acfe_form_custom_alias');
                    
                    // Custom Action
                    if($action === 'custom'){
                     
                        $action = get_sub_field('acfe_form_custom_action');
                        $alias = '';
                        
                    }
    
                    $rows[] = array(
                        'action' => $action,
                        'alias' => $alias,
                    );
                
                endwhile;
            endif;
            
            // Do Action
            foreach($rows as $row){
                
                $action = $row['action'];
                $alias = $row['alias'];
    
                do_action('acfe/form/validation/' . $action,                         $form, $post_id, $alias);
                do_action('acfe/form/validation/' . $action . '/form=' . $form_name, $form, $post_id, $alias);
    
                if(!empty($alias))
                    do_action('acfe/form/validation/' . $action . '/action=' . $alias, $form, $post_id, $alias);
                
            }
        
            do_action('acfe/form/validation',                       $form, $post_id);
            do_action('acfe/form/validation/form=' . $form_name,    $form, $post_id);
        
        acfe_reset_meta();
        
    }
    
    function check_submit_form(){
        
        // Verify nonce.
        if(!acf_verify_nonce('acfe_form'))
            return;
        
        $form = acfe_form_decrypt_args();
        
        if(!$form)
            return;
        
        // ACF
        $_POST['acf'] = isset($_POST['acf']) ? $_POST['acf'] : array();
        
        // Run kses on all $_POST data.
        if($form['kses'] && isset($_POST['acf'])){
            
            $_POST['acf'] = wp_kses_post_deep($_POST['acf']);
            
        }
        
        // Validate data and show errors.
        acf_validate_save_post(true);
        
        // Submit form.
        $this->submit_form($form);
        
    }
    
    function submit_form($form){
        
        // vars
        $post_id = acf_maybe_get($form, 'post_id', false);
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Upload
        acf_save_post(false);
        
        // Unset Files
        if(isset($_FILES))
            unset($_FILES);
    
        /*
         * Fix Elementor + YOAST infinite loop
         *
         * https://github.com/elementor/elementor/issues/10998
         * https://github.com/Yoast/wordpress-seo/issues/14643
         */
        remove_shortcode('acfe_form');
        
        acfe_setup_meta($_POST['acf'], 'acfe/form/submit', true);
            
            // Actions
            if(have_rows('acfe_form_actions', $form_id)):
                
                while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                    $action = get_row_layout();
                    
                    $alias = get_sub_field('acfe_form_custom_alias');
                    
                    do_action('acfe/form/make/' . $action, $form, $post_id, $alias);
                    
                endwhile;
            endif;
            
            do_action('acfe/form/submit',                       $form, $post_id);
            do_action('acfe/form/submit/form=' . $form_name,    $form, $post_id);
        
        acfe_reset_meta();
    
        add_shortcode('acfe_form', array($this, 'add_shortcode'));
        
        // vars
        $return = acf_maybe_get($form, 'return', '');
        
        // redirect
        if($return){
    
            _deprecated_function('ACF Extended - Dynamic Forms: "Redirection" setting', '0.8.7.5', "the new Redirect Action (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms)");
            
            $return = acfe_form_map_field_value($return, $post_id, $form);
            
            // redirect
            wp_redirect($return);
            exit;
            
        }
        
    }
    
    function validate_form($param){
    
        $form_id = false;
        $form_name = false;
        $param_array = array();
    
        if(is_array($param)){
            
            $param_array = $param;
        
            if(acf_maybe_get($param, 'id')){
    
                $param = acf_maybe_get($param, 'id');
            
            }elseif(acf_maybe_get($param, 'ID')){
    
                $param = acf_maybe_get($param, 'ID');
    
            }elseif(acf_maybe_get($param, 'name')){
    
                $param = acf_maybe_get($param, 'name');
            
            }else{
                
                return false;
                
            }
        
        }
        
        // ID
        if(is_numeric($param)){
    
            if(get_post_type($param) !== 'acfe-form')
                return false;
    
            // Form
            $form_id = $param;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // Name
        elseif(is_string($param)){
    
            $form = get_page_by_path($param, OBJECT, 'acfe-form');
            if(!$form)
                return false;
    
            // Form
            $form_id = $form->ID;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // Bail early
        if(!$form_name || !$form_id)
            return false;
    
        // Filters
        $register = true;
        $register = apply_filters("acfe/form/register",                     $register, $form_name, $form_id);
        $register = apply_filters("acfe/form/register/name={$form_name}",   $register, $form_name, $form_id);
        $register = apply_filters("acfe/form/register/id={$form_id}",       $register, $form_name, $form_id);
    
        if($register === false)
            return false;
        
        // Unset
        acfe_unset($param_array, 'id');
        acfe_unset($param_array, 'ID');
        acfe_unset($param_array, 'name');
    
        // Form Attributes
        $form_attributes = get_field('acfe_form_attributes', $form_id);
        $fields_attributes = get_field('acfe_form_fields_attributes', $form_id);
        
        // Defaults
        $defaults = array(
            
            // General
            'ID'                    => $form_id,
            'name'                  => $form_name,
            'title'                 => get_the_title($form_id),
            
            // Settings
            'post_id'               => acf_get_valid_post_id(),
            'field_groups'          => get_field('acfe_form_field_groups',          $form_id),
            'field_groups_rules'    => get_field('acfe_form_field_groups_rules',    $form_id),
            'post_field_groups'     => get_field('acfe_form_post_field_groups',     $form_id), // Deprecated
            'form'                  => get_field('acfe_form_form_element',          $form_id),
            'html_before_fields'    => get_field('acfe_form_html_before_fields',    $form_id),
            'custom_html_enabled'   => get_field('acfe_form_custom_html_enable',    $form_id),
            'custom_html'           => get_field('acfe_form_custom_html',           $form_id),
            'html_after_fields'     => get_field('acfe_form_html_after_fields',     $form_id),
            'form_submit'           => get_field('acfe_form_form_submit',           $form_id),
            'submit_value'          => get_field('acfe_form_submit_value',          $form_id),
            'html_submit_button'    => get_field('acfe_form_html_submit_button',    $form_id),
            'html_submit_spinner'   => get_field('acfe_form_html_submit_spinner',   $form_id),
            
            // Submission
            'hide_error'            => get_field('acfe_form_hide_error',            $form_id),
            'hide_unload'           => get_field('acfe_form_hide_unload',           $form_id),
            'hide_revalidation'     => get_field('acfe_form_hide_revalidation',     $form_id),
            'errors_position'       => get_field('acfe_form_errors_position',       $form_id),
            'errors_class'          => get_field('acfe_form_errors_class',          $form_id),
            'updated_message'       => get_field('acfe_form_updated_message',       $form_id),
            'html_updated_message'  => get_field('acfe_form_html_updated_message',  $form_id),
            'updated_hide_form'     => get_field('acfe_form_updated_hide_form',     $form_id),
            'return'                => get_field('acfe_form_return',                $form_id), // Deprecated
            
            // Advanced
            'honeypot'              => get_field('acfe_form_honeypot',              $form_id),
            'kses'                  => get_field('acfe_form_kses',                  $form_id),
            'uploader'              => get_field('acfe_form_uploader',              $form_id),
            'field_el'              => get_field('acfe_form_form_field_el',         $form_id),
            'label_placement'       => get_field('acfe_form_label_placement',       $form_id),
            'instruction_placement' => get_field('acfe_form_instruction_placement', $form_id),

            // Mapping
            'map'                   => array(),
            
            // Form Attributes
            'form_attributes'       => array(
                'id'                    => acf_maybe_get($form_attributes, 'acfe_form_attributes_id'),
                'class'                 => 'acfe-form ' . acf_maybe_get($form_attributes, 'acfe_form_attributes_class'),
                'action'                => '',
                'method'                => 'post',
                'data-fields-class'     => '',
                'data-hide-error'       => '',
                'data-hide-unload'      => '',
                'data-hide-revalidation'=> '',
                'data-errors-position'  => '',
                'data-errors-class'     => '',
            ),
            
            // Fields Attributes
            'fields_attributes'     => array(
                'wrapper_class'         => acf_maybe_get($fields_attributes, 'acfe_form_fields_wrapper_class'),
                'class'                 => acf_maybe_get($fields_attributes, 'acfe_form_fields_class'),
            ),
            
        );
        
        // Override
        $args = wp_parse_args($param_array, $defaults);
        
        if(acf_maybe_get($param_array, 'form_attributes'))
            $args['form_attributes'] = wp_parse_args($param_array['form_attributes'], $defaults['form_attributes']);
        
        if(acf_maybe_get($param_array, 'fields_attributes'))
            $args['fields_attributes'] = wp_parse_args($param_array['fields_attributes'], $defaults['fields_attributes']);
        
        // Advanced Override
        $args['form_attributes']['data-fields-class'] = $args['fields_attributes']['class'];
        $args['form_attributes']['data-hide-error'] = $args['hide_error'];
        $args['form_attributes']['data-hide-unload'] = $args['hide_unload'];
        $args['form_attributes']['data-hide-revalidation'] = $args['hide_revalidation'];
        $args['form_attributes']['data-errors-position'] = $args['errors_position'];
        $args['form_attributes']['data-errors-class'] = $args['errors_class'];
        
        if(acf_maybe_get_POST('acf')){
    
            acfe_setup_meta($_POST['acf'], 'acfe/form/load', true);
            
        }
        
        // Args
        $args = apply_filters('acfe/form/load',                       $args, $args['post_id']);
        $args = apply_filters('acfe/form/load/form=' . $form_name,    $args, $args['post_id']);
        
        // Load
        if(have_rows('acfe_form_actions', $form_id)):
            while(have_rows('acfe_form_actions', $form_id)): the_row();
             
                $action = get_row_layout();
                
                $alias = get_sub_field('acfe_form_custom_alias');
                
                // Custom Action
                if($action === 'custom'){
                 
                    $action = get_sub_field('acfe_form_custom_action');
                    $alias = '';
                    
                }
                
                $args = apply_filters('acfe/form/load/' . $action,                              $args, $args['post_id'], $alias);
                $args = apply_filters('acfe/form/load/' . $action . '/form=' . $form_name,      $args, $args['post_id'], $alias);
                
                if(!empty($alias))
                    $args = apply_filters('acfe/form/load/' . $action . '/action=' . $alias,    $args, $args['post_id'], $alias);
                
            endwhile;
        endif;
        
        if(acf_maybe_get_POST('acf')){
    
            acfe_reset_meta();
            
        }
        
        return $args;
        
    }
    
    /*
     * ACFE Form: render_form
     *
     */
    function render_form($args = array()){
        
        $args = $this->validate_form($args);
        
        // bail early if no args
        if(!$args)
            return false;
        
        // load acf scripts
        acf_enqueue_scripts();
        
        // Vars
        $field_groups = array();
        $fields = array();
        
        // Check Flexible Preview & Block Type Preview
        $is_dynamic_preview = acfe_is_dynamic_preview();
        
        if($is_dynamic_preview){
            
            // Disabled required + fields names
            add_filter('acf/prepare_field', array($this, 'disable_fields'));
            
        }
        
        // Register local fields.
        foreach($this->fields as $k => $field){
            
            acf_add_local_field($field);
            
        }
        
        // honeypot
        if($args['honeypot']){
            
            $fields[] = acf_get_field('_validate_email');
            
        }
        
        // Updated message
        if(acfe_is_form_success($args['name'])){
            
            // Trigger Success JS
            echo '<div class="acfe-form-success" data-form-name="' . $args['name'] . '" data-form-id="' . $args['ID'] . '"></div>';
    
            if(!empty($args['updated_message'])){
        
                $message = $args['updated_message'];
        
                if(acf_maybe_get_POST('acf')){
            
                    $message = acfe_form_map_field_value($args['updated_message'], $args['post_id'], $args);
            
                }
        
                if(!empty($args['html_updated_message'])){
            
                    printf($args['html_updated_message'], wp_unslash($message));
            
                }else{
            
                    echo $message;
            
                }
        
            }
            
            // Hide form
            if($args['updated_hide_form']){
                
                return false;
                
            }
            
        }
        
        if(!empty($args['fields_attributes']['wrapper_class']) || !empty($args['fields_attributes']['class']) || $args['label_placement'] === 'hidden'){
        
            add_filter('acf/prepare_field', function($field) use($args){
                
                if(!$field)
                    return $field;
                
                if(!empty($args['fields_attributes']['wrapper_class']))
                    $field['wrapper']['class'] .= ' ' . $args['fields_attributes']['wrapper_class'];
                
                if(!empty($args['fields_attributes']['class']))
                    $field['class'] .= ' ' . $args['fields_attributes']['class'];
    
                if($args['label_placement'] === 'hidden')
                    $field['label'] = false;
                
                return $field;
                
            });
        
        }
        
        
        if(!empty($args['map'])){
            
            foreach($args['map'] as $field_key => $array){
                
                add_filter('acf/prepare_field/key=' . $field_key, function($field) use($array){
                    
                    if(!$field)
                        return $field;
                    
                    $field = array_merge($field, $array);
                    
                    return $field;
                    
                });
                
            }
            
        }
        
        // uploader (always set in case of multiple forms on the page)
        acf_disable_filter('acfe/form/uploader');
        
        if($args['uploader'] !== 'default'){
    
            acf_enable_filter('acfe/form/uploader');
            acf_update_setting('uploader', $args['uploader']);
         
        }
        
        // Remove <form> in Dynamic Preview
        if($is_dynamic_preview)
            $args['form'] = false;
        
        $wrapper = $args['form'] ? 'form' : 'div';

        ?>
    
        <?php
        do_action("acfe/form/render/before_form",                       $args);
        do_action("acfe/form/render/before_form/id={$args['ID']}",      $args);
        do_action("acfe/form/render/before_form/name={$args['name']}",  $args);
        ?>
        
        <<?php echo $wrapper; ?> <?php acf_esc_attr_e($args['form_attributes']); ?>>
    
        <?php
        do_action("acfe/form/render/before_fields",                         $args);
        do_action("acfe/form/render/before_fields/id={$args['ID']}",        $args);
        do_action("acfe/form/render/before_fields/name={$args['name']}",    $args);
        ?>
            
        <?php
        
            if(!$is_dynamic_preview){
                
                // render post data
                acf_form_data(array(
                    'screen'    => 'acfe_form',
                    'post_id'    => $args['post_id'],
                    'form'        => acf_encrypt(json_encode($args))
                ));
                
            }
            
            $label_placement = false;
            if($args['label_placement'] !== 'hidden')
                $label_placement = '-' . $args['label_placement'];
            
            ?>
            <div class="acf-fields acf-form-fields <?php echo $label_placement; ?>">
            
                <?php
                
                // html before fields
                echo $args['html_before_fields'];
                
                // Custom HTML
                if(!empty($args['custom_html_enabled']) && !empty($args['custom_html'])){
    
                    acf_render_fields($fields, false, $args['field_el'], $args['instruction_placement']);
                    
                    echo acfe_form_render_fields($args['custom_html'], $args['post_id'], $args);
                
                }
                
                // Normal Render
                else{
    
                    // Post Field groups (Deprecated)
                    if($args['post_field_groups']){
        
                        // Override Field Groups
                        $post_field_groups = acf_get_field_groups(array(
                            'post_id' => $args['post_field_groups']
                        ));
        
                        $args['field_groups'] = wp_list_pluck($post_field_groups, 'key');
        
                    }
    
                    // Field groups
                    if($args['field_groups']){
        
                        foreach($args['field_groups'] as $selector){
            
                            // Bypass Author Module
                            if($selector === 'group_acfe_author')
                                continue;
            
                            $field_groups[] = acf_get_field_group($selector);
            
                        }
        
                    }
    
                    // Apply Field Groups Rules
                    if($args['field_groups_rules']){
        
                        if(!empty($field_groups)){
            
                            $post_id = get_the_ID();
            
                            $filter = array(
                                'post_id'   => $post_id,
                                'post_type' => get_post_type($post_id),
                            );
            
                            $filtered = array();
            
                            foreach($field_groups as $field_group){
                                
                                // Deleted field group
                                if(!isset($field_group['location']))
                                    continue;
                
                                // Force active
                                $field_group['active'] = true;
                
                                if(acf_get_field_group_visibility($field_group, $filter)){
                    
                                    $filtered[] = $field_group;
                    
                                }
                
                            }
            
                            $field_groups = $filtered;
            
                        }
        
                    }
    
                    //load fields based on field groups
                    if(!empty($field_groups)){
        
                        foreach($field_groups as $field_group){
            
                            $field_group_fields = acf_get_fields($field_group);
            
                            if(!empty($field_group_fields)){
                
                                foreach(array_keys($field_group_fields) as $i){
                    
                                    $fields[] = acf_extract_var($field_group_fields, $i);
                    
                                }
                
                            }
            
                        }
        
                    }
                    
                    acf_render_fields($fields, acf_uniqid('acfe_form'), $args['field_el'], $args['instruction_placement']);
                    
                }
                
                // html after fields
                echo $args['html_after_fields'];
                
                ?>
                
            </div>
            
            <?php if($args['form_submit']): ?>
            
                <div class="acf-form-submit">
                    
                    <?php printf($args['html_submit_button'], $args['submit_value']); ?>
                    <?php echo $args['html_submit_spinner']; ?>
                    
                </div>
            
            <?php endif; ?>
            
            <?php
            do_action("acfe/form/render/after_fields",                      $args);
            do_action("acfe/form/render/after_fields/id={$args['ID']}",     $args);
            do_action("acfe/form/render/after_fields/name={$args['name']}", $args);
            ?>
        
        </<?php echo $wrapper; ?>>
    
        <?php
        do_action("acfe/form/render/after_form",                        $args);
        do_action("acfe/form/render/after_form/id={$args['ID']}",       $args);
        do_action("acfe/form/render/after_form/name={$args['name']}",   $args);
        ?>
        
        <?php
    
        if($is_dynamic_preview){
            
            remove_filter('acf/prepare_field', array($this, 'disable_fields'));
        
        }
    
        return false;
        
    }
    
    function add_shortcode($atts){

        $atts = shortcode_atts(array(
            'name'  => false,
            'id'    => false,
            'ID'    => false,
        ), $atts, 'acfe_form');
        
        if(!empty($atts['name'])){
            
            ob_start();
            
                acfe_form($atts['name']);
            
            return ob_get_clean();
            
        }
        
        if(!empty($atts['id'])){
            
            ob_start();
            
                acfe_form($atts['id']);
            
            return ob_get_clean();
            
        }
    
        if(!empty($atts['ID'])){
        
            ob_start();
        
                acfe_form($atts['ID']);
        
            return ob_get_clean();
        
        }
        
        return false;
    
    }
    
    function disable_fields($field){
    
        $field['name'] = '';
        $field['required'] = false;
    
        return $field;
        
    }
    
}

acf_new_instance('acfe_form_front');

endif;

function acfe_form($args = array()){
    
    acf_get_instance('acfe_form_front')->render_form($args);
    
}