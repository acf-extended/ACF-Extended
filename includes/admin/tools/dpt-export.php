<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_post_types'))
    return;

if(!class_exists('ACFE_Admin_Tool_Export_DPT')):

class ACFE_Admin_Tool_Export_DPT extends ACF_Admin_Tool{
    
    public $action = false;
    public $data = array();

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dpt_export';
        $this->title = __('Export Post Types');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        // Single
        if($this->is_active()){
            
            $this->html_single();
            
        
        // Archive
        }else{
            
            $this->html_archive();
            
        }
        
    }
    
    function html_archive(){
        
        // vars
        $choices = array();
	
	    $dynamic_post_types = acfe_settings('modules.dynamic_post_type.data');
        
        if($dynamic_post_types){
            foreach($dynamic_post_types as $post_type_name => $args){
                
                $choices[$post_type_name] = esc_html($args['label']);
                
            }	
        }
        
        ?>
        <p><?php _e('Export Post Types', 'acf'); ?></p>
        
        <div class="acf-fields">
            <?php 
            
            if(!empty($choices)){
            
                // render
                acf_render_field_wrap(array(
                    'label'		=> __('Select Post Types', 'acf'),
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
                    _e('No dynamic post type available.');
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
            <button type="submit" name="action" class="button button-primary" value="json" <?php echo $disabled; ?>><?php _e('Export File'); ?></button>
            <button type="submit" name="action" class="button" value="php" <?php echo $disabled; ?>><?php _e('Generate PHP'); ?></button>
        </p>
        <?php
        
    }
    
    function html_single(){
        
        ?>
        <div class="acf-postbox-columns">
            <div class="acf-postbox-main">
                
                <?php
                // prevent default translation and fake __() within string
                acf_update_setting('l10n_var_export', true);
                
                $str_replace = array(
                    "  "			=> "\t",
                    "'!!__(!!\'"	=> "__('",
                    "!!\', !!\'"	=> "', '",
                    "!!\')!!'"		=> "')",
                    "array ("		=> "array("
                );
                
                $preg_replace = array(
                    '/([\t\r\n]+?)array/'	=> 'array',
                    '/[0-9]+ => array/'		=> 'array'
                );


                ?>
                <p><?php _e("The following code can be used to register a post type. Simply copy and paste the following code to your theme's functions.php file or include it within an external file.", 'acf'); ?></p>
                
                <div id="acf-admin-tool-export">
                
                    <textarea id="acf-export-textarea" readonly="true"><?php
                    
                    foreach($this->data as $post_type => $args){
                                
                        // code
                        $code = var_export($args, true);
                        
                        
                        // change double spaces to tabs
                        $code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
                        
                        
                        // correctly formats "=> array("
                        $code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
                        
                        
                        // esc_textarea
                        $code = esc_textarea( $code );
                        
                        
                        // echo
                        echo "register_post_type('{$post_type}', {$code});" . "\r\n" . "\r\n";
                    
                    }
                    
                    ?></textarea>
                
                </div>
                
                <p class="acf-submit">
                    <a class="button" id="acf-export-copy"><?php _e( 'Copy to clipboard', 'acf' ); ?></a>
                </p>
                <script type="text/javascript">
                (function($){
                    
                    // vars
                    var $a = $('#acf-export-copy');
                    var $textarea = $('#acf-export-textarea');
                    
                    
                    // remove $a if 'copy' is not supported
                    if( !document.queryCommandSupported('copy') ) {
                        return $a.remove();
                    }
                    
                    
                    // event
                    $a.on('click', function( e ){
                        
                        // prevent default
                        e.preventDefault();
                        
                        
                        // select
                        $textarea.get(0).select();
                        
                        
                        // try
                        try {
                            
                            // copy
                            var copy = document.execCommand('copy');
                            if( !copy ) return;
                            
                            
                            // tooltip
                            acf.newTooltip({
                                text: 		"<?php _e('Copied', 'acf' ); ?>",
                                timeout:	250,
                                target: 	$(this),
                            });
                            
                        } catch (err) {
                            
                            // do nothing
                            
                        }
                        
                    });
                
                })(jQuery);
                </script>
            </div>
        </div>
        <?php
    
    }
    
    function load(){
        
		if($this->is_active()){
            
            $this->action = $this->get_action();
            $this->data = $this->get_selected();
            
            // Json submit
            if($this->action === 'json')
                $this->submit();

	    	// add notice
	    	if(!empty($this->data)){
                
		    	$count = count($this->data);
		    	$text = sprintf(_n( 'Exported 1 post type.', 'Exported %s post types.', $count, 'acf' ), $count);
                
		    	acf_add_admin_notice($text, 'success');
                
	    	}
            
		}
        
    }
    
    function submit(){
        
        $this->action = $this->get_action();
        $this->data = $this->get_selected();
        
        // validate
		if($this->data === false)
			return acf_add_admin_notice(__('No post types selected'), 'warning');
        
        $keys = array();
        foreach($this->data as $key => $args){
            
            $keys[] = $key;
            
        }
        
        if($this->action === 'json'){
        
            // Prefix
            $prefix = (count($keys) > 1) ? 'post-types' : 'post-type';
            
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
            echo acf_json_encode($this->data);
            die;
        
        }
        
        elseif($this->action === 'php'){
            
            // url
            $url = add_query_arg(array(
                'keys' => implode('+', $keys),
                'action' => 'php'
            ), $this->get_url());
            
            // redirect
            wp_redirect($url);
            exit;
            
        }
        
    }
    
	function get_selected(){
		
		// vars
		$selected = $this->get_selected_keys();
        
		if(!$selected)
            return false;
		
		$dynamic_post_types = acfe_settings('modules.dynamic_post_type.data');
		
        if(empty($dynamic_post_types))
            return false;
		
        $data = array();
        
		// construct Data
		foreach($selected as $key){
            
            if(!isset($dynamic_post_types[$key]))
                continue;
			
			// add to data array
			$data[$key] = $dynamic_post_types[$key];
			
		}
		
		// return
		return $data;
		
	}
    
	function get_selected_keys(){
		
		// check $_POST
		if($keys = acf_maybe_get_POST('keys')){
            
			return (array) $keys;
            
        }
		
		// check $_GET
		if($keys = acf_maybe_get_GET('keys')){
            
			$keys = str_replace(' ', '+', $keys);
			return explode('+', $keys);
            
		}
		
		// return
		return false;
		
	}
    
    function get_action(){
        
        // init
        $type = 'json';

        // check GET / POST
        if(($action = acf_maybe_get_GET('action')) || ($action = acf_maybe_get_POST('action'))){
            
            if(in_array($action, array('json', 'php')))
                $type = $action;
            
        }
        
        // return
        return $type;
		
	}
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Export_DPT');

endif;