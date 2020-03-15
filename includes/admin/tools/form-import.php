<?php

if(!defined('ABSPATH'))
	exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms'))
	return;

if(!class_exists('ACFE_Admin_Tool_Import_Form')):

class ACFE_Admin_Tool_Import_Form extends ACF_Admin_Tool{
    
    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_form_import';
        $this->title = __('Import Forms');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        ?>
        <p><?php _e('Import Forms', 'acf'); ?></p>

        <div class="acf-fields">
            <?php
            
            acf_render_field_wrap(array(
                'label'		=> __('Select File', 'acf'),
                'type'		=> 'file',
                'name'		=> 'acf_import_file',
                'value'		=> false,
                'uploader'	=> 'basic',
            ));
            
            ?>
        </div>

        <p class="acf-submit">
            <button type="submit" name="action" class="button button-primary"><?php _e('Import File'); ?></button>
        </p>
        <?php
        
    }
    
    function submit(){
        
        // Check file size.
        if(empty($_FILES['acf_import_file']['size']))
            return acf_add_admin_notice(__("No file selected", 'acf'), 'warning');
        
        // Get file data.
        $file = $_FILES['acf_import_file'];
        
        // Check errors.
        if($file['error'])
            return acf_add_admin_notice(__("Error uploading file. Please try again", 'acf'), 'warning');
        
        // Check file type.
        if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'json')
            return acf_add_admin_notice(__("Incorrect file type", 'acf'), 'warning');
        
        // Read JSON.
        $json = file_get_contents($file['tmp_name']);
        $json = json_decode($json, true);
        
        // Check if empty.
        if(!$json || !is_array($json))
            return acf_add_admin_notice(__("Import file empty", 'acf'), 'warning');
        
        $ids = array();
        
        // Loop over json
        foreach($json as $args){
            
            // Check if already exists
            if(get_page_by_path($args['acfe_form_name'], OBJECT, 'acfe-form')){
                
                acf_add_admin_notice(__("Form {$args['title']} already exists. Import aborted."), 'warning');
                
                continue;
                
            }
            
            // Import
            $post_id = $this->import($args);
            
            // Insert error
            if(!$post_id){
                
                acf_add_admin_notice(__("Something went wrong with the form {$args['title']}. Import aborted."), 'warning');
                
                continue;
                
            }
            
            // append message
            $ids[] = $post_id;
            
        }
        
        if(empty($ids))
            return false;
        
        // Count total
        $total = count($ids);
        
        // Generate text
        $text = sprintf(_n('1 form imported', '%s forms imported', $total, 'acf'), $total);
        
        // Add links to text
        $links = array();
        foreach($ids as $id){
            
            $links[] = '<a href="' . get_edit_post_link($id) . '">' . get_the_title($id) . '</a>';
            
        }
        
        $text .= ': ' . implode(', ', $links);
        
        // Add notice
        acf_add_admin_notice($text, 'success');
        
    }
    
    function import($args){
        
        // Vars
        $title = acf_extract_var($args, 'title');
        $name = $args['acfe_form_name'];
        
        // Insert post
        $post_id = wp_insert_post(array(
            'post_title'    => $title,
            'post_name'     => $name,
            'post_type'     => 'acfe-form',
            'post_status'   => 'publish'
        ));
        
        // Insert error
        if(is_wp_error($post_id))
            return false;
        
        acf_enable_filter('local');
        
        // Import Compatibility
        $args = $this->import_compatibility($args);
        
        // Update Values
        acf_update_values($args, $post_id);
        
        acf_disable_filter('local');
        
        return $post_id;
        
    }
    
    function import_compatibility($args){
        
        // ACF Extended: 0.8.5 Compatibility - Step 1
        // Groups upgrade
        $has_upgraded = false;
        
        $rules = array(
            
            // Post: title
            array(
                'group'             => 'field_acfe_form_post_save_post_title_group',
                'sub_field'         => 'field_acfe_form_post_save_post_title',
                'sub_field_custom'  => 'field_acfe_form_post_save_post_title_custom',
            ),
            
            // Post: name
            array(
                'group'             => 'field_acfe_form_post_save_post_name_group',
                'sub_field'         => 'field_acfe_form_post_save_post_name',
                'sub_field_custom'  => 'field_acfe_form_post_save_post_name_custom',
            ),
            
            // Term: name
            array(
                'group'             => 'field_acfe_form_term_save_name_group',
                'sub_field'         => 'field_acfe_form_term_save_name',
                'sub_field_custom'  => 'field_acfe_form_term_save_name_custom',
            ),
            
            // Term: slug
            array(
                'group'             => 'field_acfe_form_term_save_slug_group',
                'sub_field'         => 'field_acfe_form_term_save_slug',
                'sub_field_custom'  => 'field_acfe_form_term_save_slug_custom',
            ),
            
            // User: e-mail
            array(
                'group'             => 'field_acfe_form_user_save_email_group',
                'sub_field'         => 'field_acfe_form_user_save_email',
                'sub_field_custom'  => 'field_acfe_form_user_save_email_custom',
            ),
            
            // User: username
            array(
                'group'             => 'field_acfe_form_user_save_username_group',
                'sub_field'         => 'field_acfe_form_user_save_username',
                'sub_field_custom'  => 'field_acfe_form_user_save_username_custom',
            ),
            
            // User: password
            array(
                'group'             => 'field_acfe_form_user_save_password_group',
                'sub_field'         => 'field_acfe_form_user_save_password',
                'sub_field_custom'  => 'field_acfe_form_user_save_password_custom',
            ),
            
            // User: first name
            array(
                'group'             => 'field_acfe_form_user_save_first_name_group',
                'sub_field'         => 'field_acfe_form_user_save_first_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_first_name_custom',
            ),
            
            // User: last name
            array(
                'group'             => 'field_acfe_form_user_save_last_name_group',
                'sub_field'         => 'field_acfe_form_user_save_last_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_last_name_custom',
            ),
            
            // User: nickname
            array(
                'group'             => 'field_acfe_form_user_save_nickname_group',
                'sub_field'         => 'field_acfe_form_user_save_nickname',
                'sub_field_custom'  => 'field_acfe_form_user_save_nickname_custom',
            ),
            
            // User: display name
            array(
                'group'             => 'field_acfe_form_user_save_display_name_group',
                'sub_field'         => 'field_acfe_form_user_save_display_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_display_name_custom',
            ),
            
            // User: website
            array(
                'group'             => 'field_acfe_form_user_save_website_group',
                'sub_field'         => 'field_acfe_form_user_save_website',
                'sub_field_custom'  => 'field_acfe_form_user_save_website_custom',
            ),
        
        );
        
        foreach($args['acfe_form_actions'] as &$row){
            
            foreach($rules as $rule){
                
                if(!acf_maybe_get($row, $rule['group']))
                    continue;
                
                $value = null;
                $group = $row[$rule['group']];
                
                if(acf_maybe_get($group, $rule['sub_field']) === 'custom'){
                    
                    $value = acf_maybe_get($group, $rule['sub_field_custom']);
                    
                }else{
                    
                    $value = acf_maybe_get($group, $rule['sub_field']);
                    
                }
                
                unset($row[$rule['group']]);
                
                $row[$rule['sub_field']] = $value;
                
                $has_upgraded = true;
                
            }
            
        }
        
        // ACF Extended: 0.8.5 Compatibility - Step 2
        // Field mapping upgrade
        if($has_upgraded){
            
            // Rules
            $rules = array(
                
                array(
                    'load_values' => 'field_acfe_form_post_load_values',
                    'fields' => array(
                        'field_acfe_form_post_map_post_type'       => 'field_acfe_form_post_save_post_type',
                        'field_acfe_form_post_map_post_status'     => 'field_acfe_form_post_save_post_status',
                        'field_acfe_form_post_map_post_title'      => 'field_acfe_form_post_save_post_title',
                        'field_acfe_form_post_map_post_name'       => 'field_acfe_form_post_save_post_name',
                        'field_acfe_form_post_map_post_content'    => 'field_acfe_form_post_save_post_content',
                        'field_acfe_form_post_map_post_author'     => 'field_acfe_form_post_save_post_author',
                        'field_acfe_form_post_map_post_parent'     => 'field_acfe_form_post_save_post_parent',
                        'field_acfe_form_post_map_post_terms'      => 'field_acfe_form_post_save_post_terms',
                    )
                ),
                
                array(
                    'load_values' => 'field_acfe_form_term_load_values',
                    'fields' => array(
                        'field_acfe_form_term_map_name'            => 'field_acfe_form_term_save_name',
                        'field_acfe_form_term_map_slug'            => 'field_acfe_form_term_save_slug',
                        'field_acfe_form_term_map_taxonomy'        => 'field_acfe_form_term_save_taxonomy',
                        'field_acfe_form_term_map_parent'          => 'field_acfe_form_term_save_parent',
                        'field_acfe_form_term_map_description'     => 'field_acfe_form_term_save_description',
                    )
                ),
                
                array(
                    'load_values' => 'field_acfe_form_user_load_values',
                    'fields' => array(
                        'field_acfe_form_user_map_email'        => 'field_acfe_form_user_save_email',
                        'field_acfe_form_user_map_username'     => 'field_acfe_form_user_save_username',
                        'field_acfe_form_user_map_password'     => 'field_acfe_form_user_save_password',
                        'field_acfe_form_user_map_first_name'   => 'field_acfe_form_user_save_first_name',
                        'field_acfe_form_user_map_last_name'    => 'field_acfe_form_user_save_last_name',
                        'field_acfe_form_user_map_nickname'     => 'field_acfe_form_user_save_nickname',
                        'field_acfe_form_user_map_display_name' => 'field_acfe_form_user_save_display_name',
                        'field_acfe_form_user_map_website'      => 'field_acfe_form_user_save_website',
                        'field_acfe_form_user_map_description'  => 'field_acfe_form_user_save_description',
                        'field_acfe_form_user_map_role'         => 'field_acfe_form_user_save_role',
                    )
                ),
            
            );
            
            foreach($args['acfe_form_actions'] as &$row){
                
                foreach($rules as $rule){
                    
                    $load_values = acf_maybe_get($row, $rule['load_values']);
                    $fields = $rule['fields'];
                    
                    if(!empty($load_values))
                        continue;
                    
                    foreach($fields as $map => $save){
                        
                        $map_value = acf_maybe_get($row, $map);
                        
                        if(empty($map_value))
                            continue;
	
	                    if($save === 'field_acfe_form_post_save_post_content'){
		
		                    $row['field_acfe_form_post_save_post_content_group'][$save] = $map_value;
		
	                    }
	
	                    elseif($save === 'field_acfe_form_term_save_description'){
		
		                    $row['field_acfe_form_term_save_description_group'][$save] = $map_value;
		
	                    }

                        elseif($save === 'field_acfe_form_user_save_description'){
	
	                        $row['field_acfe_form_user_save_description_group'][$save] = $map_value;
		
	                    }
	                    
	                    else{
		
		                    $row[$save] = $map_value;
	                       
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        return $args;
        
    }
    
    function import_external($data){
        
        $ids = array();
        
        // Loop over args
        foreach($data as $args){
            
            // Check if already exists
            $form_exists = get_page_by_path($args['acfe_form_name'], OBJECT, 'acfe-form');
            
            if($form_exists)
                continue;
            
            // Import
            $post_id = $this->import($args);
            
            // Insert error
            if(!$post_id)
                continue;
            
            $name = get_field('acfe_form_name', $post_id);
            
            $return = array(
                'id'    => $post_id,
                'name'  => $name,
            );
            
            // append message
            $ids[] = $return;
            
        }
        
        if(empty($ids))
            return false;
        
        return $ids;
        
    }
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Import_Form');

endif;

/*
 * ACFE: Import Dynamic Form
 *
 * $args: Accepts array() or json
 * return: A list of form_id & form_name
 */
function acfe_import_dynamic_form($args = false){
	
	if(!$args)
		return false;
	
	// json
	if(is_string($args)){
		
		$args = json_decode($args, true);
		
	}
	
	if(!is_array($args) || empty($args))
		return false;
	
	// Tool
	$tool = acf()->admin_tools->get_tool('ACFE_Admin_Tool_Import_Form');
	
	return $tool->import_external($args);
	
}