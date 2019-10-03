<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_options_pages', true))
    return;

if(!class_exists('ACFE_Admin_Tool_Export_DOP')):

class ACFE_Admin_Tool_Export_DOP extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dop_export';
        $this->title = __('Export Options Pages');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        // vars
		$choices = array();
        
        $dynamic_options_pages = get_option('acfe_dynamic_options_pages', array());
        
		if($dynamic_options_pages){
			foreach($dynamic_options_pages as $options_page_name => $args){
                
				$choices[$options_page_name] = esc_html($args['page_title']);
                
			}	
		}
        
        ?>
        <p><?php _e('Export Options Pages', 'acf'); ?></p>
        
        <div class="acf-fields">
            <?php 
            
            if(!empty($choices)){
            
                // render
                acf_render_field_wrap(array(
                    'label'		=> __('Select Options Pages', 'acf'),
                    'type'		=> 'checkbox',
                    'name'		=> 'keys',
                    'prefix'	=> false,
                    'value'		=> false,
                    'toggle'	=> true,
                    'choices'	=> $choices,
                ));
            
            }
            
            else{
                
                echo '<div style="padding:15px 12px;">';
                    _e('No options page available.');
                echo '</div>'; 
                
            }
            
            ?>
        </div>
        
        <?php 
        
        $disabled = '';
        if(empty($choices))
            $disabled = 'disabled="disabled"';
        
        ?>
        
        <p class="acf-submit">
            <button type="submit" name="action" class="button button-primary" value="download" <?php echo $disabled; ?>><?php _e('Export File'); ?></button>
        </p>
        <?php
        
    }
    
    function load(){
        
        // check $_GET
		if($this->is_active() && acf_maybe_get_GET('keys')){
            
            $this->submit();
            
		}
        
    }
    
    function submit(){
        
        $json = $this->get_selected();
        
        // validate
		if($json === false)
			return acf_add_admin_notice(__('No options page selected'), 'warning');
        
        $keys = array();
        foreach($json as $key => $args){
            
            $keys[] = $key;
            
        }
        
        // Prefix
        $prefix = (count($keys) > 1) ? 'options-pages' : 'options-page';
        
        // Slugs
        $slugs = implode('-', $keys);
        
        // Date
        $date = date('Y-m-d');
        
        // file
		$file_name = 'acfe-export-' .  $prefix  . '-' . $slugs . '-' .  $date . '.json';
        
        // headers
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename={$file_name}");
		header("Content-Type: application/json; charset=utf-8");
		
		// return
		echo acf_json_encode($json);
		die;
        
    }
    
	function get_selected(){
		
		// vars
		$selected = $this->get_selected_keys();
		$json = array();
        
		if(!$selected)
            return false;
        
        $dynamic_options_pages = get_option('acfe_dynamic_options_pages', array());
        if(empty($dynamic_options_pages))
            return false;
		
		// construct JSON
		foreach($selected as $key){
            
            if(!isset($dynamic_options_pages[$key]))
                continue;
			
			// add to json array
			$json[$key] = $dynamic_options_pages[$key];
			
		}
		
		// return
		return $json;
		
	}
    
	function get_selected_keys(){
		
		// check $_POST
		if($keys = acf_maybe_get_POST('keys'))
			return (array) $keys;
		
		// check $_GET
		if($keys = acf_maybe_get_GET('keys')){
			$keys = str_replace(' ', '+', $keys);
			return explode('+', $keys);
		}
		
		// return
		return false;
		
	}
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Export_DOP');

endif;