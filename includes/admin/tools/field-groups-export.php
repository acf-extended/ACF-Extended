<?php 

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_groups_export')):

class acfe_field_groups_export extends ACF_Admin_Tool{
    
    function initialize(){
    
        $this->name = 'acfe-export';
        
    }
    
    function load(){
        
        $action = $this->get_action();
        
        if($action === 'json'){
            
            acf()->admin_tools->get_tool('export')->submit_download();
            
        }
        
        // active
        if($this->is_active()){
            
            // get selected keys
            $selected = acf()->admin_tools->get_tool('export')->get_selected_keys();
            
            // add notice
            if($selected){
                
                $count = count($selected);
                $text = sprintf( _n('Exported 1 field group.', 'Exported %s field groups.', $count, 'acf' ), $count);
                
                acf_add_admin_notice( $text, 'success' );
                
            }
        }
        
    }
    
    function get_action(){
    
        // vars
        $action = acfe_maybe_get_REQUEST('action');
    
        // check allowed
        if(!in_array($action, array('json', 'php')))
            $action = false;
    
        // return
        return $action;
        
    }
    
}

acf_register_admin_tool('acfe_field_groups_export');

endif;