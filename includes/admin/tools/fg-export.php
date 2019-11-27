<?php 

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Admin_Tool_Export_FG')):

class ACFE_Admin_Tool_Export_FG extends ACF_Admin_Tool{
    
    function initialize(){
		
		// vars
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
            if( $selected ) {
                $count = count($selected);
                $text = sprintf( _n( 'Exported 1 field group.', 'Exported %s field groups.', $count, 'acf' ), $count );
                acf_add_admin_notice( $text, 'success' );
            }
        }
        
    }
    
    function get_action(){
        
        // init
        $type = false;

        // check GET / POST
        if(($action = acf_maybe_get_GET('action')) || ($action = acf_maybe_get_POST('action'))){
            
            if(in_array($action, array('json', 'php')))
                $type = $action;
            
        }
        
        // return
        return $type;
		
	}
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Export_FG');

endif;