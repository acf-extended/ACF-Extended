<?php 

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_import')):

class acfe_module_import extends ACF_Admin_Tool{
    
    // vars
    public $module;
    
    /**
     * construct
     *
     * @param $module
     */
    function __construct($module){
        
        // module
        $this->module = $module;
    
        // vars
        $this->name = $this->module->get_import_tool();
        $this->title = $this->module->get_message('import_title');
        
        parent::__construct();
        
    }
    
    
    /**
     * html
     *
     * @return void
     */
    function html(){
        
        ?>
        <?php if(acfe_is_acf_6()): ?>

            <div class="acf-postbox-header">
                <h2 class="acf-postbox-title"><?php echo $this->module->get_message('import_description'); ?></h2>
            </div>
            <div class="acf-postbox-inner">
    
        <?php else: ?>

            <p><?php echo $this->module->get_message('import_description'); ?></p>
    
        <?php endif; ?>
        
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
            <button type="submit" name="action" class="button button-primary"><?php _e('Import File', 'acf'); ?></button>
        </p>
        
        <?php if(acfe_is_acf_6()): ?>
            </div>
        <?php endif; ?>
        
        <?php
        
    }
    
    
    /**
     * submit
     *
     * @return void
     */
    function submit(){
    
        // Validate
        $json = $this->validate_file();
        
        if(!$json){
            return;
        }
        
        $ids = array();
        
        // Loop over json
        foreach($json as $key => $item){
            
            // prior 0.9
            // old import had name as key
            if(!is_numeric($key) && !isset($item['name'])){
                $item['name'] = $key;
            }
    
            // validate
            // todo: remove
            //$item = $this->module->validate_item($item);
            //$item = $this->module->prepare_item_for_import($item);
            
            // search database for existing item
            $post = $this->module->get_item_post($item['name']);
            if($post){
                $item['ID'] = $post->ID;
            }
            
            // import item
            $item = $this->module->import_item($item);
            
            // append message
            $ids[] = $item['ID'];
            
        }
        
        if(empty($ids)){
            return;
        }
        
        // Count total
        $total = count($ids);
    
        $text = $this->module->get_message('import_success_single');
    
        if($total > 1){
            $text = sprintf($this->module->get_message('import_success_multiple'), $total);
        }
        
        // Add links to text
        $links = array();
        foreach($ids as $id){
            $links[] = '<a href="' . get_edit_post_link($id) . '">' . get_the_title($id) . '</a>';
        }
        
        $text .= ': ' . implode(', ', $links);
        
        // Add notice
        acf_add_admin_notice($text, 'success');
        
    }
    
    
    /**
     * validate_file
     *
     * @return array|false
     */
    function validate_file(){
        
        // Check file size.
        if(empty($_FILES['acf_import_file']['size'])){
            
            acf_add_admin_notice(__('No file selected', 'acf'), 'warning');
            return false;
            
        }
        
        // Get file data.
        $file = $_FILES['acf_import_file'];
        
        // Check errors.
        if($file['error']){
            
            acf_add_admin_notice(__('Error uploading file. Please try again', 'acf'), 'warning');
            return false;
            
        }
        
        // Check file type.
        if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'json'){
            
            acf_add_admin_notice(__('Incorrect file type', 'acf'), 'warning');
            return false;
            
        }
        
        // Read JSON.
        $json = file_get_contents($file['tmp_name']);
        $json = json_decode($json, true);
        
        // Check if empty.
        if(!$json || !is_array($json)){
            
            acf_add_admin_notice(__('Import file empty', 'acf'), 'warning');
            return false;
            
        }
        
        return $json;
        
    }
    
}

endif;