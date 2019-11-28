<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_front')):

class acfe_form_front{
    
    function __construct(){
        
        // Validation
        add_action('acf/validate_save_post',    array($this, 'validate_save_post'), 1);
        
        // Submit
        add_action('wp',                        array($this, 'check_submit_form'));
        
        add_shortcode('acfe_form',              array($this, 'add_shortcode'));
        
    }
    
    function validate_save_post(){
        
        if(!acfe_form_is_front())
            return;
        
        $form = acfe_form_decrypt_args();
        
        if(!$form)
            return;
        
        $post_id = acf_maybe_get($form, 'post_id', false);
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        if(!$form_name || !$form_id)
            return;
        
        // Honeypot
		if(!empty($acf['_validate_email'])){
			
			acf_add_validation_error('', __('Spam Detected', 'acf'));
			
		}
        
        // Validation
        acf_setup_meta($_POST['acf'], 'acfe_form_validation', true);
        
            do_action('acfe/form/validation',                       $form, $post_id);
            do_action('acfe/form/validation/form=' . $form_name,    $form, $post_id);
        
        acf_reset_meta('acfe_form_validation');
        
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
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        acf_save_post(false);
        
        unset($_FILES);
        
        acf_setup_meta($_POST['acf'], 'acfe_form_submit', true);
            
            // Actions
            if(have_rows('acfe_form_actions', $form_id)):
                
                while(have_rows('acfe_form_actions', $form_id)): the_row();
                
                    $action = get_row_layout();
                    
                    // Custom Action
                    if($action === 'custom'){
                        
                        $custom_action = get_sub_field('acfe_form_custom_action');
                        
                        do_action('acfe/form/submit/' . $custom_action,                         $form, $post_id);
                        do_action('acfe/form/submit/' . $custom_action . '/form=' . $form_name, $form, $post_id);
                        
                    }
                    
                    // ACFE Actions
                    else{
                    
                        $alias = get_sub_field('acfe_form_custom_alias');
                        
                        do_action('acfe/form/prepare/' . $action,                         $form, $post_id, $alias);
                        do_action('acfe/form/prepare/' . $action . '/form=' . $form_name, $form, $post_id, $alias);
                    
                    }
                
                endwhile;
            endif;
            
            do_action('acfe/form/submit',                       $form, $post_id);
            do_action('acfe/form/submit/form=' . $form_name,    $form, $post_id);
        
        acf_reset_meta('acfe_form_submit');
		
		// vars
		$return = acf_maybe_get($form, 'return', '');
		
		// redirect
		if($return){
			
			// update %placeholders%
			$return = str_replace('%post_id%', $post_id, $return);
			$return = str_replace('%post_url%', get_permalink($post_id), $return);
			
			// redirect
			wp_redirect($return);
			exit;
			
		}
		
	}
    
    function validate_form($param){
        
        $form_name = false;
        $form_id = false;
        
        // String
        if(is_string($param)){
            
            $form = get_page_by_path($param, OBJECT, 'acfe-form');
            if(!$form)
                return false;
            
            // Form
            $form_id = $form->ID;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        // Int
        elseif(is_int($param)){
            
            if(get_post_type($param) !== 'acfe-form')
                return false;
            
            // Form
            $form_id = $param;
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        if(!$form_name || !$form_id)
            return false;
        
        // Defaults
        $defaults = array(
            
            // General
			'form_name'             => $form_name,
			'form_id'               => $form_id,
			'post_id'               => acf_get_valid_post_id(),
			'field_groups'          => false,
			'post_field_groups'     => false,
			'form'                  => true,
			'form_attributes'       => array(),
			'fields_attributes'     => array(),
			'html_before_fields'	=> '',
			'custom_html'           => '',
			'html_after_fields'		=> '',
			'form_submit'           => true,
			'submit_value'			=> __('Update', 'acf'),
			'html_submit_button'	=> '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
			'html_submit_spinner'	=> '<span class="acf-spinner"></span>',
            
            // Submission
            'hide_error'            => '',
            'hide_unload'           => '',
            'errors_position'       => 'above',
            'errors_class'          => '',
            'updated_message'		=> __('Post updated', 'acf'),
            'html_updated_message'  => '<div id="message" class="updated">%s</div>',
            'updated_hide_form'	    => false,
            'return'				=> '',
            
            // Mapping
            'map'                   => array(),
            
            // Advanced
            'honeypot'				=> true,
            'kses'					=> true,
            'uploader'				=> 'basic',
            'field_el'				=> 'div',
			'label_placement'		=> 'top',
			'instruction_placement'	=> 'label'
            
		);
        
        $defaults['form_attributes'] = wp_parse_args($defaults['form_attributes'], array(
			'id'					=> '',
			'class'					=> 'acfe-form',
			'action'				=> '',
			'method'				=> 'post',
			'data-fields-class'     => '',
			'data-hide-error'       => '',
			'data-errors-position'  => '',
			'data-errors-class'     => '',
		));
        
        $defaults['fields_attributes'] = wp_parse_args($defaults['fields_attributes'], array(
			'wrapper_class'         => '',
			'class'                 => '',
		));

        // Field Groups
        $defaults['field_groups'] = get_field('acfe_form_field_groups', $form_id);
        $defaults['post_field_groups'] = get_field('acfe_form_post_field_groups', $form_id);
        
        // General
        $defaults['form'] = get_field('acfe_form_form_element', $form_id);
        
        $form_attributes = get_field('acfe_form_attributes', $form_id);
        
        if(!empty($form_attributes['acfe_form_attributes_class']))
            $defaults['form_attributes']['class'] .= ' ' . $form_attributes['acfe_form_attributes_class'];
        
        if(!empty($form_attributes['acfe_form_attributes_id']))
            $defaults['form_attributes']['id'] = $form_attributes['acfe_form_attributes_id'];
        
        $acfe_form_fields_attributes = get_field('acfe_form_fields_attributes', $form_id);
        
        if(isset($acfe_form_fields_attributes['acfe_form_fields_wrapper_class']))
            $defaults['fields_attributes']['wrapper_class'] = $acfe_form_fields_attributes['acfe_form_fields_wrapper_class'];
        
        if(isset($acfe_form_fields_attributes['acfe_form_fields_class']))
            $defaults['fields_attributes']['class'] = $acfe_form_fields_attributes['acfe_form_fields_class'];
        
        $defaults['html_before_fields'] = get_field('acfe_form_html_before_fields', $form_id);
        $defaults['custom_html'] = get_field('acfe_form_custom_html', $form_id);
        $defaults['html_after_fields'] = get_field('acfe_form_html_after_fields', $form_id);
        $defaults['form_submit'] = get_field('acfe_form_form_submit', $form_id);
        $defaults['submit_value'] = get_field('acfe_form_submit_value', $form_id);
        $defaults['html_submit_button'] = get_field('acfe_form_html_submit_button', $form_id);
        $defaults['html_submit_spinner'] = get_field('acfe_form_html_submit_spinner', $form_id);
        
        // Validation
        $defaults['errors_position'] = get_field('acfe_form_errors_position', $form_id);
        $defaults['errors_class'] = get_field('acfe_form_errors_class', $form_id);
        $defaults['hide_error'] = get_field('acfe_form_hide_error', $form_id);
        $defaults['hide_unload'] = get_field('acfe_form_hide_unload', $form_id);
        
        // Submission
        $defaults['updated_message'] = get_field('acfe_form_updated_message', $form_id);
        $defaults['html_updated_message'] = get_field('acfe_form_html_updated_message', $form_id);
        $defaults['updated_hide_form'] = get_field('acfe_form_updated_hide_form', $form_id);
        $defaults['return'] = get_field('acfe_form_return', $form_id);
        
        // Advanced
        $defaults['honeypot'] = get_field('acfe_form_honeypot', $form_id);
        $defaults['kses'] = get_field('acfe_form_kses', $form_id);
        $defaults['uploader'] = get_field('acfe_form_uploader', $form_id);
        $defaults['form_field_el'] = get_field('acfe_form_form_field_el', $form_id);
        $defaults['label_placement'] = get_field('acfe_form_label_placement', $form_id);
        $defaults['field_el'] = get_field('acf-field_acfe_form_form_field_el', $form_id);
        $defaults['instruction_placement'] = get_field('acfe_form_instruction_placement', $form_id);
        
        //$args = wp_parse_args($param, $defaults);
        $args = $defaults;
        
        // Override
        if(!empty($args['fields_attributes']['class']))
            $args['form_attributes']['data-fields-class'] = $args['fields_attributes']['class'];
        
        if(!empty($args['hide_error']))
            $args['form_attributes']['data-hide-error'] = $args['hide_error'];
        
        if(!empty($args['hide_unload']))
            $args['form_attributes']['data-hide-unload'] = $args['hide_unload'];
        
        if(!empty($args['errors_position']))
            $args['form_attributes']['data-errors-position'] = $args['errors_position'];
        
        if(!empty($args['errors_class']))
            $args['form_attributes']['data-errors-class'] = $args['errors_class'];
        
        // Args
        $args = apply_filters('acfe/form/load',                       $args, $args['post_id']);
        $args = apply_filters('acfe/form/load/form=' . $form_name,    $args, $args['post_id']);
        
        // Load
        if(have_rows('acfe_form_actions', $form_id)):
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                $action = get_row_layout();
                
                // Custom Action
                if($action === 'custom'){
                    
                    $action = get_sub_field('acfe_form_custom_action');
                    
                }
                
                $alias = get_sub_field('acfe_form_custom_alias');
                
                if(!empty($alias))
                    $args = apply_filters('acfe/form/load/action=' . $alias,  $args, $args['post_id']);
            
                $args = apply_filters('acfe/form/load/' . $action,                          $args, $args['post_id'], $alias);
                $args = apply_filters('acfe/form/load/' . $action . '/form=' . $form_name,  $args, $args['post_id'], $alias);
                
            endwhile;
        endif;
        
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
        
        // vars
        $field_groups = array();
        $fields = array();
        
        // Post Field groups
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
        
        // honeypot
        if($args['honeypot']){
            
            $fields[] = acf_get_field('_validate_email');
            
        }
        
        // updated message
        if(acf_maybe_get_POST('_acf_form')){
            
            $form = json_decode(acf_decrypt($_POST['_acf_form']), true);
            
            if(acf_maybe_get($form, 'form_name') === $args['form_name']){
                
                ?>
                <script>
                jQuery(document).ready(function($){
                    
                    if(typeof acf !== 'undefined'){
                    
                        acf.doAction('acfe/form/submit/success');
                        acf.doAction('acfe/form/submit/success/name=<?php echo $args['form_name']; ?>');
                        
                    }

                });
                </script>
                <?php
            
                if(!empty($args['updated_message'])){
                    
                    $message = $args['updated_message'];
                    
                    if(acf_maybe_get_POST('acf'))
                        $message = acfe_form_map_field_value($args['updated_message'], $_POST['acf']);
                    
                    if(!empty($args['html_updated_message'])){
                        
                        printf($args['html_updated_message'], $message);
                        
                    }else{
                        
                        echo $message;
                        
                    }
                    
                }
                
                if($args['updated_hide_form']){
                    
                    ?>
                    <script>
                    if(window.history.replaceState){
                        window.history.replaceState(null, null, window.location.href);
                    }
                    </script>
                    <?php
                    
                    return;
                    
                }
            
            }
            
        }
        
        if(!empty($args['fields_attributes']['wrapper_class']) || !empty($args['fields_attributes']['class'])){
        
            add_filter('acf/prepare_field', function($field) use($args){
                
                if(!$field)
                    return $field;
                
                if(!empty($args['fields_attributes']['wrapper_class']))
                    $field['wrapper']['class'] .= ' ' . $args['fields_attributes']['wrapper_class'];
                
                if(!empty($args['fields_attributes']['class']))
                    $field['class'] .= ' ' . $args['fields_attributes']['class'];
                
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
        
        // uploader (always set incase of multiple forms on the page)
        acf_update_setting('uploader', $args['uploader']);
        
        // display form
        if($args['form']): ?>
        
        <form <?php acf_esc_attr_e($args['form_attributes']); ?>>
            
        <?php endif; 
                
            // render post data
            acf_form_data(array( 
                'screen'	=> 'acfe_form',
                'post_id'	=> $args['post_id'],
                'form'		=> acf_encrypt(json_encode($args))
            ));
            
            ?>
            <div class="acf-fields acf-form-fields -<?php echo $args['label_placement']; ?>">
            
                <?php
                
                // html before fields
                echo $args['html_before_fields'];
                
                // Custom HTML
                if(!empty($args['custom_html'])){
                    
                    echo acfe_form_render_fields($args['custom_html'], false, $args);
                
                }
                
                // Normal Render
                else{
                    
                    acf_render_fields($fields, false, $args['field_el'], $args['instruction_placement']);
                    
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
        
        <?php if($args['form']): ?>
        </form>
        <?php endif; ?>
        <script>
        if(window.history.replaceState){
            window.history.replaceState(null, null, window.location.href);
        }
        </script>
        <?php
        
    }
    
    function add_shortcode($atts){

        $atts = shortcode_atts(array(
            'name'  => false,
            'ID'    => false
        ), $atts, 'acfe_form');
        
        if(!empty($atts['name'])){
            
            ob_start();
            
                acfe_form($atts['name']);
            
            return ob_get_clean();
            
        }
        
        if(!empty($atts['ID'])){
            
            ob_start();
            
                acfe_form($atts['ID']);
            
            return ob_get_clean();
            
        }
    
    }
    
}

acfe()->form_front = new acfe_form_front();

endif;

function acfe_form($args = array()){
    
    acfe()->form_front->render_form($args);
    
}