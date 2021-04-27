<?php 

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_module_import')):

class acfe_module_import extends ACF_Admin_Tool{
    
    public $hook;
    public $description;
    public $instance;
    public $messages = array();
    
    function html(){
        
        ?>
        <p><?php echo $this->description; ?></p>
        
        <div class="acf-fields">
            <?php 
            
            acf_render_field_wrap(array(
                'label'     => __('Select File', 'acf'),
                'type'      => 'file',
                'name'      => 'acf_import_file',
                'value'     => false,
                'uploader'  => 'basic',
            ));
            
            ?>
        </div>
        
        <p class="acf-submit">
            <button type="submit" name="action" class="button button-primary"><?php _e('Import File'); ?></button>
        </p>
        <?php
        
    }
    
    function submit(){
    
        // Validate
        $json = $this->validate_file();
        
        if(!$json)
            return;
        
        $ids = array();
        
        // Loop over json
        foreach($json as $name => $args){
        
            // Import
            $post_id = $this->instance->import($name, $args);
            
            // Insert error
            if(is_wp_error($post_id)){
            
                acf_add_admin_notice($post_id->get_error_message(), 'warning');
                continue;
            
            }
            
            // append message
            $ids[] = $post_id;
            
        }
        
        if(empty($ids))
            return;
        
        // Count total
        $total = count($ids);
        
        // Generate text
        $text = sprintf(_n($this->messages['success_single'], $this->messages['success_multiple'], $total, 'acf'), $total);
        
        // Add links to text
        $links = array();
        foreach($ids as $id){
            $links[] = '<a href="' . get_edit_post_link($id) . '">' . get_the_title($id) . '</a>';
        }
        
        $text .= ': ' . implode(', ', $links);
        
        // Add notice
        acf_add_admin_notice($text, 'success');
        
        // Do Action
        do_action("acfe/{$this->hook}/import", $ids, $json);
        
    }
    
    function validate_file(){
        
        // Check file size.
        if(empty($_FILES['acf_import_file']['size'])){
            
            acf_add_admin_notice(__("No file selected", 'acf'), 'warning');
            return false;
            
        }
        
        // Get file data.
        $file = $_FILES['acf_import_file'];
        
        // Check errors.
        if($file['error']){
            
            acf_add_admin_notice(__("Error uploading file. Please try again", 'acf'), 'warning');
            return false;
            
        }
        
        // Check file type.
        if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'json'){
            
            acf_add_admin_notice(__("Incorrect file type", 'acf'), 'warning');
            return false;
            
        }
        
        // Read JSON.
        $json = file_get_contents($file['tmp_name']);
        $json = json_decode($json, true);
        
        // Check if empty.
        if(!$json || !is_array($json)){
            
            acf_add_admin_notice(__("Import file empty", 'acf'), 'warning');
            return false;
            
        }
        
        return $json;
        
    }
    
}

endif;