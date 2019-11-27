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
    	foreach($json as $form_name => $args){
            
            // Check if already exists
            if(get_page_by_path($form_name, OBJECT, 'acfe-form')){
                
                acf_add_admin_notice(__("Form {$args['title']} already exists. Import aborted."), 'warning');
                continue;
                
            }
            
            // Vars
            $title = acf_extract_var($args, 'title');
            $name = $form_name;
            
            // Insert post
            $post_id = wp_insert_post(array(
                'post_title'    => $title,
                'post_name'     => $name,
                'post_type'     => 'acfe-form',
                'post_status'   => 'publish'
            ));
            
            // Insert error
            if(is_wp_error($post_id)){
                
                acf_add_admin_notice(__("Something went wrong with the form {$title}. Import aborted."), 'warning');
                continue;
                
            }
            
            acf_enable_filter('local');
            
            acf_update_values($args, $post_id);
            
            acf_disable_filter('local');
            
	    	// append message
	    	$ids[] = $post_id;
            
    	}
        
        if(empty($ids))
            return;
    	
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
        
        // Flush permalinks
        flush_rewrite_rules();
        
    }
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Import_Form');

endif;