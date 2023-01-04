<?php

if(!defined('ABSPATH')){
    exit;
}

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
        
        // shortcode
        add_shortcode('acfe_form',                          array($this, 'render_shortcode'));
        
        // save
        add_action('acf/validate_save_post',                array($this, 'validate_save_post'), 1);
        add_action('wp',                                    array($this, 'save_post'));
        
        // ajax
        add_action('wp_ajax_acfe/form/shortcode',           array($this, 'ajax_shortcode'), 20);
        add_action('wp_ajax_nopriv_acfe/form/shortcode',    array($this, 'ajax_shortcode'), 20);
        
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
    
    function validate_save_post(){
        
        // validate front-end
        if(!acfe_is_front()){
            return;
        }
        
        // validate screen
        if(acf_maybe_get_POST('_acf_screen') !== 'acfe_form'){
            return;
        }
        
        // decrypt
        if(!$form = acfe_form_decrypt_args()){
            return;
        }
        
        $post_id = acf_maybe_get($form, 'post_id', false);
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // bail early not valid form
        if(!$form_name || !$form_id){
            return;
        }
        
        // local fields
        foreach($this->fields as $k => $field){
            
            // bail early if no in $_POST
            if(!isset($_POST['acf'][ $k ])) continue;
            
            // register
            acf_add_local_field($field);
            
        }
        
        // honeypot
        if(!empty($acf['_validate_email'])){
            acf_add_validation_error('', __('Spam Detected', 'acf'));
        }
        
        // set form data for validation
        acf_set_form_data('acfe/form', $form);
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/form/validation', true);
        
        // loop
        if(have_rows('acfe_form_actions', $form_id)):
            while(have_rows('acfe_form_actions', $form_id)): the_row();
                
                // vars
                $action = get_row_layout();
                $alias = get_sub_field('acfe_form_custom_alias');
                
                // custom action
                if($action === 'custom'){
                    $action = get_sub_field('acfe_form_custom_action');
                    $alias = '';
                }
                
                // actions
                do_action("acfe/form/validation/{$action}",                     $form, $post_id, $alias);
                do_action("acfe/form/validation/{$action}/form={$form_name}",   $form, $post_id, $alias);
                
                if(!empty($alias)){
                    do_action("acfe/form/validation/{$action}/action={$alias}", $form, $post_id, $alias);
                }
            
            endwhile;
        endif;
        
        // actions
        do_action("acfe/form/validation",                   $form, $post_id);
        do_action("acfe/form/validation/form={$form_name}", $form, $post_id);
        
        // reset
        acfe_reset_meta();
        
        // unset form data
        acf_set_form_data('acfe/form', null);
        
    }
    
    function save_post(){
        
        // verify nonce
        if(!acf_verify_nonce('acfe_form')){
            return;
        }
        
        // decrypt
        if(!$form = acfe_form_decrypt_args()){
            return;
        }
        
        // ACF
        $_POST['acf'] = acf_maybe_get_POST('acf', array());
        
        // run kses on all $_POST data
        if($form['kses']){
            $_POST['acf'] = wp_kses_post_deep($_POST['acf']);
        }
        
        // validate save post
        acf_validate_save_post(true);
    
        // vars
        $post_id = acf_maybe_get($form, 'post_id', false);
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // remove save post action
        add_filter('acf/pre_update_value', '__return_false', 99);
    
        // upload files but do not save post
        acf_save_post(false);
        
        // restore save post action
        remove_filter('acf/pre_update_value', '__return_false', 99);
    
        // unset files to avoid duplicate upload
        unset($_FILES);
        
        // remove shortcode (temp)
        // https://github.com/elementor/elementor/issues/10998
        // https://github.com/Yoast/wordpress-seo/issues/14643
        remove_shortcode('acfe_form');
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/form/submit', true);
    
        // loop
        if(have_rows('acfe_form_actions', $form_id)):
        
            while(have_rows('acfe_form_actions', $form_id)): the_row();
                
                // vars
                $action = get_row_layout();
                $alias = get_sub_field('acfe_form_custom_alias');
                
                // action
                do_action("acfe/form/make/{$action}", $form, $post_id, $alias);
        
            endwhile;
        endif;
        
        // actions
        do_action("acfe/form/submit",                   $form, $post_id);
        do_action("acfe/form/submit/form={$form_name}", $form, $post_id);
        
        // reset
        acfe_reset_meta();
        
        // re-add shortcode
        add_shortcode('acfe_form', array($this, 'render_shortcode'));
    
        // return (deprecated)
        if($return = acf_maybe_get($form, 'return', '')){
            
            // notice
            _deprecated_function('ACF Extended - Dynamic Forms: "Redirection" setting', '0.8.7.5', "the new Redirect Action (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms)");
            
            // map values
            $return = acfe_form_map_field_value($return, $post_id, $form);
        
            // redirect
            wp_redirect($return);
            exit;
        
        }
        
    }
    
    function validate_form($param){
        
        // get form
        $array = $this->get_form($param);
        
        // bail early
        if(!$array){
            return false;
        }
        
        // vars
        $form_id = $array['ID'];
        $form_name = $array['name'];
    
        // filters
        $register = true;
        $register = apply_filters("acfe/form/register",                     $register, $form_name, $form_id);
        $register = apply_filters("acfe/form/register/name={$form_name}",   $register, $form_name, $form_id);
        $register = apply_filters("acfe/form/register/id={$form_id}",       $register, $form_name, $form_id);
    
        if($register === false){
            return false;
        }
    
        // Form Attributes
        $form_attributes = get_field('acfe_form_attributes', $form_id);
        $fields_attributes = get_field('acfe_form_fields_attributes', $form_id);
        
        // Defaults
        $defaults = array(
            
            // General
            'ID'                    => '',
            'name'                  => '',
            'title'                 => '',
            
            // Settings
            'post_id'               => acf_get_valid_post_id(),
            'field_groups'          => get_field('acfe_form_field_groups',          $form_id),
            'field_groups_rules'    => get_field('acfe_form_field_groups_rules',    $form_id),
            'post_field_groups'     => get_field('acfe_form_post_field_groups',     $form_id), // deprecated
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
            'return'                => get_field('acfe_form_return',                $form_id), // deprecated
            
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
                'class'                 => acf_maybe_get($form_attributes, 'acfe_form_attributes_class'),
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
        
        // parse args
        $args = wp_parse_args($array, $defaults);
        
        if(acf_maybe_get($array, 'form_attributes')){
            $args['form_attributes'] = wp_parse_args($array['form_attributes'], $defaults['form_attributes']);
        }
        
        if(acf_maybe_get($array, 'fields_attributes')){
            $args['fields_attributes'] = wp_parse_args($array['fields_attributes'], $defaults['fields_attributes']);
        }
        
        // advanced override
        $args['form_attributes']['class'] = 'acfe-form ' . $args['form_attributes']['class'];
        $args['form_attributes']['data-fields-class'] = $args['fields_attributes']['class'];
        $args['form_attributes']['data-hide-error'] = $args['hide_error'];
        $args['form_attributes']['data-hide-unload'] = $args['hide_unload'];
        $args['form_attributes']['data-hide-revalidation'] = $args['hide_revalidation'];
        $args['form_attributes']['data-errors-position'] = $args['errors_position'];
        $args['form_attributes']['data-errors-class'] = $args['errors_class'];
        
        if(acf_maybe_get_POST('acf')){
            acfe_setup_meta($_POST['acf'], 'acfe/form/load', true);
        }
        
        // post id
        $post_id = $args['post_id'];
        
        // arguments
        $args = apply_filters("acfe/form/load",                     $args, $post_id);
        $args = apply_filters("acfe/form/load/form={$form_name}",   $args, $post_id);
        
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
                
                $args = apply_filters("acfe/form/load/{$action}",                       $args, $post_id, $alias);
                $args = apply_filters("acfe/form/load/{$action}/form={$form_name}",     $args, $post_id, $alias);
                
                if(!empty($alias)){
                    $args = apply_filters("acfe/form/load/{$action}/action={$alias}",   $args, $post_id, $alias);
                }
                
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
        
        // bail early if no args
        if(!$args = $this->validate_form($args)){
            return;
        }
    
        // success message
        $this->form_success($args);
        
        // enqueue acf
        acf_enqueue_scripts();
    
        // hide form on success
        if($this->form_success_hide($args)){
            return;
        }
        
        $fields = $this->prepare_fields($args);
        
        $this->form_uploader($args);
        
        do_action("acfe/form/render/before_form",                       $args);
        do_action("acfe/form/render/before_form/id={$args['ID']}",      $args);
        do_action("acfe/form/render/before_form/name={$args['name']}",  $args);
        
        $this->form_wrapper($args);

        do_action("acfe/form/render/before_fields",                     $args);
        do_action("acfe/form/render/before_fields/id={$args['ID']}",    $args);
        do_action("acfe/form/render/before_fields/name={$args['name']}",$args);
        
        $this->form_data($args);
    
        $this->fields_wrapper($args);
        
        $this->render_fields($args, $fields);
        
        $this->fields_wrapper($args, false);
        
        do_action("acfe/form/render/after_fields",                      $args);
        do_action("acfe/form/render/after_fields/id={$args['ID']}",     $args);
        do_action("acfe/form/render/after_fields/name={$args['name']}", $args);
    
        $this->form_wrapper($args, false);
        
        do_action("acfe/form/render/after_form",                        $args);
        do_action("acfe/form/render/after_form/id={$args['ID']}",       $args);
        do_action("acfe/form/render/after_form/name={$args['name']}",   $args);
        
    }
    
    function form_success($args){
        
        // validate
        if(!acfe_is_form_success($args['name'])) return;
    
        // hooks
        do_action("acfe/form/success",                      $args);
        do_action("acfe/form/success/id={$args['ID']}",     $args);
        do_action("acfe/form/success/name={$args['name']}", $args);
    
        // add javascript success
        add_filter('acfe/localize_data', function($data) use($args){
        
            $data['acfe_form_success'][] = array(
                'name' => $args['name'],
                'id'   => $args['ID'],
            );
        
            return $data;
        
        });
        
        // get updated message
        $message = $args['updated_message'];
    
        // on success message
        if($message){
    
            // map message with values in $_POST
            if(acf_maybe_get_POST('acf')){
                $message = acfe_form_map_field_value($message, $args['post_id'], $args);
            }
    
            // html
            if($args['html_updated_message']){
                $message = sprintf($args['html_updated_message'], wp_unslash($message));
            }
            
            // echo
            echo $message;
            
        }
        
    }
    
    function form_success_hide($args){
    
        // hide form on success
        if(acfe_is_form_success($args['name']) && $args['updated_hide_form']){
            return true;
        }
        
        // show
        return false;
        
    }
    
    function prepare_fields($args){
    
        // vars
        $fields = array();
    
        // register local fields
        foreach($this->fields as $field){
            acf_add_local_field($field);
        }
    
        // honeypot
        if($args['honeypot']){
            $fields[] = acf_get_field('_validate_email');
        }
        
        // field attributes
        if($args['fields_attributes']['wrapper_class'] || $args['fields_attributes']['class'] || $args['label_placement'] === 'hidden'){
        
            add_filter('acf/prepare_field', function($field) use($args){
            
                if(!$field){
                    return $field;
                }
                
                if($args['fields_attributes']['wrapper_class']){
                    $field['wrapper']['class'] .= ' ' . $args['fields_attributes']['wrapper_class'];
                }
                
                if($args['fields_attributes']['class']){
                    $field['class'] .= ' ' . $args['fields_attributes']['class'];
                }
                
                if($args['label_placement'] === 'hidden'){
                    $field['label'] = false;
                }
            
                return $field;
            
            });
        
        }
    
        // form map values
        foreach($args['map'] as $key => $_field){
        
            add_filter("acf/prepare_field/key={$key}", function($field) use($_field){
    
                // hide field
                if(!$field || !$_field){
                    return false;
                }
            
                return array_merge($field, $_field);
            
            });
        
        }
        
        return $fields;
        
    }
    
    function form_uploader($args){
    
        // uploader (always set in case of multiple forms on the page)
        acf_disable_filter('acfe/form/uploader');
    
        if($args['uploader'] !== 'default'){
        
            acf_enable_filter('acfe/form/uploader');
            acf_update_setting('uploader', $args['uploader']);
        
        }
        
    }
    
    function form_wrapper($args, $open = true){
    
        // preview mode
        $is_preview = acfe_is_dynamic_preview();
    
        // remove <form>
        if($is_preview){
            $args['form'] = false;
        }
        
        // wrapper
        $wrapper = $args['form'] ? 'form' : 'div';
    
        // open
        if($open){
    
            // disabled required + fields names
            if($is_preview){
                add_filter('acf/prepare_field', array($this, 'disable_fields'));
            }
            
            $atts = acf_esc_attrs($args['form_attributes']);
    
            // <form class="acfe-form">
            echo "<{$wrapper} {$atts}>";
        
        // close
        }else{
    
            // </form>
            echo "</{$wrapper}>";
    
            // re-enable required + fields names
            if($is_preview){
                remove_filter('acf/prepare_field', array($this, 'disable_fields'));
            }
        
        }
        
    }
    
    function fields_wrapper($args, $open = true){
        
        // open
        if($open){
    
            $atts = array(
                'class' => 'acf-fields acf-form-fields'
            );
    
            if($args['label_placement'] !== 'hidden'){
                $atts['class'] .= " -{$args['label_placement']}";
            }
    
            $atts = acf_esc_attrs($atts);
    
            // <div class="acf-fields acf-form-fields">
            echo "<div {$atts}>";
    
            // html before fields
            echo $args['html_before_fields'];
            
        // close
        }else{
    
            // html after fields
            echo $args['html_after_fields'];
    
            echo '</div>';
    
            // form submit
            if($args['form_submit']): ?>
            <div class="acf-form-submit">
        
                <?php printf($args['html_submit_button'], $args['submit_value']); ?>
                <?php echo $args['html_submit_spinner']; ?>

            </div>
            <?php endif;
            
        }
    
    }
    
    function form_data($args){
    
        // bail early in preview mode
        if(acfe_is_dynamic_preview()) return;
    
        // render form data
        acf_form_data(array(
            'screen'  => 'acfe_form',
            'post_id' => $args['post_id'],
            'form'    => acf_encrypt(json_encode($args))
        ));
    
    }
    
    function render_fields($args, $fields){
    
        // custom html render
        if($args['custom_html_enabled'] && $args['custom_html']){
        
            // render honeypot
            acf_render_fields($fields, false, $args['field_el'], $args['instruction_placement']);
            
            // render custom html render
            echo acfe_form_render_fields($args['custom_html'], $args['post_id'], $args);
            
            return;
            
        }
        
        // vars
        $field_groups = array();
        $args['field_groups'] = acf_get_array($args['field_groups']);
    
        // post field groups (deprecated, use apply field groups rules instead)
        if($args['post_field_groups']){
        
            // Override Field Groups
            $post_field_groups = acf_get_field_groups(array(
                'post_id' => $args['post_field_groups']
            ));
            
            // re-assign post field groups
            $args['field_groups'] = wp_list_pluck($post_field_groups, 'key');
        
        }
    
        // form field groups
        foreach($args['field_groups'] as $key){
            
            // validate field group exists
            $field_group = acf_get_field_group($key);
            
            if($field_group){
                $field_groups[] = $field_group;
            }
        
        }
    
        // apply field groups rules
        if($args['field_groups_rules'] && $field_groups){
    
            $post_id = get_the_ID();
    
            $location = array(
                'post_id'   => $post_id,
                'post_type' => get_post_type($post_id),
            );
    
            $filtered = array();
    
            foreach($field_groups as $field_group){
        
                // Deleted field group
                if(!isset($field_group['location'])) continue;
        
                // Force active
                $field_group['active'] = true;
        
                // fitler field groups
                if(acf_get_field_group_visibility($field_group, $location)){
                    $filtered[] = $field_group;
                }
        
            }
    
            // assign new filtered field groups
            $field_groups = $filtered;
        
        }
    
        // get field groups fields
        foreach($field_groups as $field_group){
        
            $_fields = acf_get_fields($field_group);
        
            foreach(array_keys($_fields) as $i){
            
                $fields[] = acf_extract_var($_fields, $i);
            
            }
        
        }
    
        // render fields
        acf_render_fields($fields, acf_uniqid('acfe_form'), $args['field_el'], $args['instruction_placement']);
        
    }
    
    function render_shortcode($atts){
        
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
    
    function disable_fields($field){
    
        $field['name'] = '';
        $field['required'] = false;
    
        return $field;
        
    }
    
    function get_form($param){
        
        $form_id = false;
        $form_name = false;
        $array = array();
        
        // check array
        if(is_array($param)){
            
            // save params
            $array = $param;
            $param = false;
            
            // check keys
            foreach(array('id', 'ID', 'name') as $key){
                
                if(!acf_maybe_get($array, $key)) continue;
                
                $param = acf_maybe_get($array, $key);
                break;
                
            }
            
            // key not found
            if(!$param){
                return false;
            }
            
            // unset keys
            unset($array['id']);
            unset($array['ID']);
            unset($array['name']);
            
        }
        
        // check id
        if(is_numeric($param)){
            
            // check post type
            if(get_post_type($param) !== 'acfe-form'){
                return false;
            }
            
            // vars
            $form_id = $param;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // check name
        elseif(is_string($param)){
            
            if(!$form = get_page_by_path($param, OBJECT, 'acfe-form')){
                return false;
            }
            
            // vars
            $form_id = $form->ID;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // bail early
        if(!$form_name || !$form_id){
            return false;
        }
        
        // set default params
        $array['ID'] = $form_id;
        $array['name'] = $form_name;
        $array['title'] = get_the_title($form_id);
        
        return $array;
        
    }
    
}

acf_new_instance('acfe_form_front');

endif;

function acfe_form($args = array()){
    
    acf_get_instance('acfe_form_front')->render_form($args);
    
}