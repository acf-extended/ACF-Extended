<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_front')):

class acfe_form_front extends acf_form_front{
    
    function __construct(){
        
        // Enqueue / Redirect
        add_action('template_redirect',         array($this, 'acfe_init'));
        
        // Validation
        add_action('acf/validate_save_post',    array($this, 'acfe_validate'), 4);
        
        // Submit
        add_action('acf/submit_form',           array($this, 'acfe_submit'), 5, 2);
        
        //parent::__construct();
        
    }
    
    function acfe_init(){
        
        if(!acf_maybe_get_POST('_acf_form') || !acf_maybe_get_POST('_acf_nonce'))
			return;
        
        acf()->form_front->check_submit_form();
        
    }
    
    function acfe_validate(){
        
        if(!acfe_form_is_front())
            return;
        
		if(!acf_maybe_get_POST('_acf_form'))
            return;
        
    	$form = json_decode(acf_decrypt($_POST['_acf_form']), true);
        
        if(empty($form))
            return;
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        $post_id = acf_maybe_get($form, '_acf_post_id');
        
        if(!$form_name || !$form_id)
            return;
        
        acf_setup_meta($_POST['acf'], 'acfe_form_validation', true);
        
            do_action('acfe/form/validation',                       $form, $post_id);
            do_action('acfe/form/validation/name=' . $form_name,    $form, $post_id);
            do_action('acfe/form/validation/id=' . $form_id,        $form, $post_id);
        
        acf_reset_meta('acfe_form_validation');
        
    }
    
    // Form submission
    function acfe_submit($form, $post_id){
        
        if(!acfe_form_is_front())
            return;
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        if(!$form_name || !$form_id)
            return;
        
        $acf = array();
        
        // ACF $_POST
        if(isset($_POST['acf']))
            $acf = $_POST['acf'];
        
        acf_setup_meta($acf, 'acfe_form_submit', true);
        
            do_action('acfe/form/submit',                       $form, $post_id, $acf);
            do_action('acfe/form/submit/name=' . $form_name,    $form, $post_id, $acf);
            do_action('acfe/form/submit/id=' . $form_id,        $form, $post_id, $acf);
            
            // Actions
            if(have_rows('acfe_form_actions', $form_id)):
                
                while(have_rows('acfe_form_actions', $form_id)): the_row();
                
                    do_action('acfe/form/submit/action/' . get_row_layout(),                           $form, $post_id, $acf);
                    do_action('acfe/form/submit/action/' . get_row_layout() . '/name=' . $form_name,   $form, $post_id, $acf);
                    do_action('acfe/form/submit/action/' . get_row_layout() . '/id=' . $form_id,       $form, $post_id, $acf);
                
                endwhile;
            endif;
        
        acf_reset_meta('acfe_form_submit');
        
    }
    
    function acfe_prepare($param){
        
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
        
        // Array
        elseif(is_array($param)){
            
            $param = wp_parse_args($param, array(
                'acfe_form_id'      => false,
                'acfe_form_name'    => false
            ));
            
            if(!$param['acfe_form_id'] && !$param['acfe_form_name'])
                return false;
            
            $valid = false;
            
            if($param['acfe_form_id']){
                
                if(get_post_type((int)$param['acfe_form_id']) === 'acfe-form'){
                    
                    // Form
                    $form_id = $param['acfe_form_id'];
                    $form_name = get_field('acfe_form_name', $form_id);
                    
                    $param['acfe_form_name'] = $form_name;
                    
                    $valid = true;
                    
                }
                
            }
            
            if(!$valid && $param['acfe_form_name']){
                
                $get_form = get_posts(array(
                    'post_type'         => 'acfe-form',
                    'posts_per_page'    => 1,
                    'fields'            => 'ids',
                    'post_name__in'     => array($param['acfe_form_name'])
                ));
                
                if(!empty($get_form)){
                    
                    // Form
                    $form_id = $get_form[0];
                    $form_name = $param['acfe_form_name'];
                    
                    $param['acfe_form_id'] = $form_id;
                    
                    $valid = true;
                    
                }
                
            }
            
            if(!$valid)
                return false;
            
        }
        
        // ACF Args
        $args = array();
        
        // ACFE Form
        $args['acfe_form_id'] = $form_id;
        $args['acfe_form_name'] = $form_name;
        
        // Field Groups
        $args['field_groups'] = get_field('acfe_form_field_groups', $form_id);
        
        // General
        $args['form'] = get_field('acfe_form_form_element', $form_id);
        $args['form_attributes']['class'] = 'acfe-form';
        $args['form_attributes']['id'] = '';
        
        if(!empty($args['form'])){
            
            $form_attributes = get_field('acfe_form_attributes', $form_id);
            
            $args['form_attributes']['class'] .= ' ' . $form_attributes['acfe_form_attributes_class'];
            $args['form_attributes']['id'] = $form_attributes['acfe_form_attributes_id'];
            
        }
        
        $acfe_form_fields_attributes = get_field('acfe_form_fields_attributes', $form_id);
        
        $args['fields_wrapper_class'] = $acfe_form_fields_attributes['acfe_form_fields_wrapper_class'];
        $args['fields_class'] = $acfe_form_fields_attributes['acfe_form_fields_class'];
        
        if(!empty($args['fields_class']))
            $args['form_attributes']['data-acfe-form-fields-class'] = $args['fields_class'];
        
        $args['html_before_fields'] = get_field('acfe_form_html_before_fields', $form_id);
        $args['custom_html'] = get_field('acfe_form_custom_html', $form_id);
        $args['html_after_fields'] = get_field('acfe_form_html_after_fields', $form_id);
        $args['form_submit'] = get_field('acfe_form_form_submit', $form_id);
        $args['submit_value'] = get_field('acfe_form_submit_value', $form_id);
        $args['html_submit_button'] = get_field('acfe_form_html_submit_button', $form_id);
        $args['html_submit_spinner'] = get_field('acfe_form_html_submit_spinner', $form_id);
        
        // Validation
        $args['errors_position'] = get_field('acfe_form_errors_position', $form_id);
        
        if(!empty($args['errors_position']))
            $args['form_attributes']['data-acfe-form-errors-position'] = $args['errors_position'];
        
        $args['errors_class'] = get_field('acfe_form_errors_class', $form_id);
        
        if(!empty($args['errors_class']))
            $args['form_attributes']['data-acfe-form-errors-class'] = $args['errors_class'];
        
        // Submission
        $args['updated_message'] = get_field('acfe_form_updated_message', $form_id);
        $args['html_updated_message'] = get_field('acfe_form_html_updated_message', $form_id);
        $args['updated_hide_form'] = get_field('acfe_form_updated_hide_form', $form_id);
        $args['return'] = get_field('acfe_form_return', $form_id);
        
        if(empty($args['return']))
            $args['return'] = add_query_arg('updated', 'true', acf_get_current_url());
        
        // Advanced
        $args['honeypot'] = get_field('acfe_form_honeypot', $form_id);
        $args['kses'] = get_field('acfe_form_kses', $form_id);
        $args['uploader'] = get_field('acfe_form_uploader', $form_id);
        $args['form_field_el'] = get_field('acfe_form_form_field_el', $form_id);
        $args['label_placement'] = get_field('acfe_form_label_placement', $form_id);
        $args['instruction_placement'] = get_field('acfe_form_instruction_placement', $form_id);
        
        // Default behavior: No save, no update.
        $args['post_id'] = null;
        
        // Fields mapping
        $args['map'] = array();
        
        // Actions
        if(have_rows('acfe_form_actions', $form_id)):
            while(have_rows('acfe_form_actions', $form_id)): the_row();
            
                $args = apply_filters('acfe/form/load/action/' . get_row_layout(),                          $args);
                $args = apply_filters('acfe/form/load/action/' . get_row_layout() . '/name=' . $form_name,  $args);
                $args = apply_filters('acfe/form/load/action/' . get_row_layout() . '/id=' . $form_id,      $args);
                
            endwhile;
        endif;
        
        $args['map'] = apply_filters('acfe/form/load/fields',                       $args['map'], $args);
        $args['map'] = apply_filters('acfe/form/load/fields/name=' . $form_name,    $args['map'], $args);
        $args['map'] = apply_filters('acfe/form/load/fields/id=' . $form_id,        $args['map'], $args);
        
        // Let user bypass default form settings
        if(is_array($param)){
            
            $args = array_replace_recursive($args, $param);
            
        }
        
        // ACF Form
        acf_form($args);
        
    }
    
    /*
     * ACF Form: render_form()
     *
     */
    function render_form($args = array()){
        
        // array
		if(is_array($args)){
			
			$args = $this->validate_form($args);
			
		}
        
        // id
        else{
			
			$args = $this->get_form($args);
			
		}
        
        
		// bail early if no args
		if(!$args)
            return false;
		
        // load acf scripts
		acf_enqueue_scripts();
        
		// load values from this post
		$post_id = $args['post_id'];
		
        
		// dont load values for 'new_post'
		if($post_id === 'new_post')
            $post_id = false;
		
        
		// register local fields
		foreach($this->fields as $k => $field){
			
			acf_add_local_field($field);
			
		}
		
		// vars
		$field_groups = array();
		$fields = array();
		
		
		// post_title
		if($args['post_title']){
			
			// load local field
			$_post_title = acf_get_field('_post_title');
			$_post_title['value'] = $post_id ? get_post_field('post_title', $post_id) : '';
            
			// append
			$fields[] = $_post_title;
			
		}
		
		
		// post_content
		if($args['post_content']){
			
			// load local field
			$_post_content = acf_get_field('_post_content');
			$_post_content['value'] = $post_id ? get_post_field('post_content', $post_id) : '';
			
			// append
			$fields[] = $_post_content;
            
		}
		
        
        // Custom HTML
		if(acf_maybe_get($args, 'custom_html')){
			
			$field_groups = false;
            
		}
        
		// specific fields
		elseif($args['fields']){
			
			foreach($args['fields'] as $selector){
				
				// append field ($strict = false to allow for better compatibility with field names)
				$fields[] = acf_maybe_get_field($selector, $post_id, false);
				
			}
			
		}
        
        // Field groups
        elseif($args['field_groups']){
			
			foreach($args['field_groups'] as $selector){
			
				$field_groups[] = acf_get_field_group($selector);
				
			}
			
		}
        
        // New post: field groups
        elseif($args['post_id'] == 'new_post'){
			
			$field_groups = acf_get_field_groups($args['new_post']);
            
		}
        
        // Current post: field groups
        else{
			
			$field_groups = acf_get_field_groups(array(
				'post_id' => $args['post_id']
			));
			
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
		if(!empty($_GET['updated'])){
            
            if($args['updated_message']){
                
                if(!empty($args['html_updated_message'])){
                    
                    printf($args['html_updated_message'], $args['updated_message']);
                    
                }
                
                else{
                    
                    echo $args['updated_message'];
                    
                }
                
            }
            
            if(acf_maybe_get($args, 'updated_hide_form'))
                return;
			
		}
        
        add_filter('acf/prepare_field', function($field) use($args){
            
            $field['wrapper']['class'] .= ' ' . $args['fields_wrapper_class'];
            $field['class'] .= ' ' . $args['fields_class'];
            
            return $field;
            
        });
        
        
        if(acf_maybe_get($args, 'map')){
            
            foreach($args['map'] as $field_key => $array){
                
                add_filter('acf/prepare_field/key=' . $field_key, function($field) use($array){
                    
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
                'screen'	=> 'acf_form',
                'post_id'	=> $args['post_id'],
                'form'		=> acf_encrypt(json_encode($args))
            ));
            
            ?>
            
            <div class="acf-fields acf-form-fields -<?php echo $args['label_placement']; ?>">
            
                <?php
                
                // html before fields
                echo $args['html_before_fields'];
                
                // Custom HTML
                if(isset($args['custom_html']) && !empty($args['custom_html'])){
                    
                    echo acfe_form_render_fields($args['custom_html'], $post_id, $args);
                
                }
                
                // Normal Render
                else{
                    
                    acf_render_fields($fields, $post_id, $args['field_el'], $args['instruction_placement']);
                    
                }
                
                // html after fields
                echo $args['html_after_fields'];
                
                ?>
                
            </div>
            
            <?php if((!isset($args['form_submit']) && $args['form']) || (isset($args['form_submit']) && !empty($args['form_submit']))): ?>
            
                <div class="acf-form-submit">
                    
                    <?php printf($args['html_submit_button'], $args['submit_value']); ?>
                    <?php echo $args['html_submit_spinner']; ?>
                    
                </div>
            
            <?php endif; ?>
		
        <?php if($args['form']): ?>
		</form>
		<?php endif;
        
    }
    
}

acf()->form_front = new acfe_form_front();

endif;

function acfe_form($args = array()){
	
	acf()->form_front->acfe_prepare($args);
	
}