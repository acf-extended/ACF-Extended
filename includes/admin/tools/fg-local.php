<?php 

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Admin_Tool_FG_Local_Export')):

class ACFE_Admin_Tool_FG_Local extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->title = __('Export Local Field Groups');
        $this->name = 'acfe-fg-local';
        $this->icon = 'dashicons-upload';
        
    }
    
    function load(){
        
        if($ids = acf_maybe_get_GET('acfe-fg-local-sync')){
            
            $ids = explode('+', $ids);
        
            // Count number of imported field groups.
            $total = count($ids);
            
            // Generate text.
            $text = sprintf( _n( 'Imported 1 field group', 'Imported %s field groups', $total, 'acf' ), $total );		
            
            // Add links to text.
            $links = array();
            foreach( $ids as $id ) {
                $links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
            }
            $text .= ' ' . implode( ', ', $links );
            
            // Add notice
            acf_add_admin_notice($text, 'success');
            
        }
        
        if($this->is_active()){
            
            $array = $this->get_selected();
            $keys = $this->get_selected_keys();
            $action = $this->get_action();
            
            // validate
            if($array === false)
                return acf_add_admin_notice(__('No field group selected'), 'warning');
            
            // Json
            if($action === 'json'){
                
                // Slugs
                $slugs = implode('-', $keys);
                
                // Date
                $date = date('Y-m-d');
                
                // file
                $file_name = 'acfe-export-local-' . $slugs . '-' .  $date . '.json';
                
                // headers
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename={$file_name}");
                header("Content-Type: application/json; charset=utf-8");
                
                // return
                echo acf_json_encode($array);
                die;
                
            }
            
            // Sync
            elseif($action === 'sync'){
                
                if(isset($array['key']))
                    $array = array($array);
                
                // Remeber imported field group ids.
                $ids = array();
                
                // Loop over json
                foreach( $array as $field_group ) {
                    
                    // Search database for existing field group.
                    $post = acf_get_field_group_post( $field_group['key'] );
                    if( $post ) {
                        $field_group['ID'] = $post->ID;
                    }
                    
                    // Import field group.
                    $field_group = acf_import_field_group( $field_group );
                    
                    // append message
                    $ids[] = $field_group['ID'];
                    
                }
                
                // url
                $url = add_query_arg('acfe-fg-local-sync', implode('+', $ids), acf_get_admin_tools_url());
                
                // redirect
                wp_redirect($url);
                exit;
                
            }
        
        }
        
    }
    
    function html(){
		
		if($this->is_active()){
            
            $array = $this->get_selected();
            $action = $this->get_action();
            
            ?>
            <div class="acf-postbox-columns">
                <div class="acf-postbox-main">
                    
                    <?php
                    // prevent default translation and fake __() within string
                    acf_update_setting('l10n_var_export', true);
                    
                    // vars
                    $json = $array;
                    
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
                    <p><?php _e("The following code can be used to register a local version of the selected field group(s). A local field group can provide many benefits such as faster load times, version control & dynamic fields/settings. Simply copy and paste the following code to your theme's functions.php file or include it within an external file.", 'acf'); ?></p>
                    
                    <div id="acf-admin-tool-export">
                    
                        <textarea id="acf-export-textarea" readonly="true"><?php
                        
                        echo "if( function_exists('acf_add_local_field_group') ):" . "\r\n" . "\r\n";
                        
                        foreach( $json as $field_group ) {
                                    
                            // code
                            $code = var_export($field_group, true);
                            
                            
                            // change double spaces to tabs
                            $code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
                            
                            
                            // correctly formats "=> array("
                            $code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
                            
                            
                            // esc_textarea
                            $code = esc_textarea( $code );
                            
                            
                            // echo
                            echo "acf_add_local_field_group({$code});" . "\r\n" . "\r\n";
                        
                        }
                        
                        echo "endif;";
                        
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
        
	}
    
	function get_selected(){
		
		// vars
		$selected = $this->get_selected_keys();
		
		if(!$selected)
            return false;
        
        // Return
        $array = array();
        
        // Enable filters
        acf_enable_filters();
        
        // Disable fitler: clone
        acf_disable_filter('clone');
        
        foreach($selected as $field_group_key){
            
            $field_group = acf_get_field_group($field_group_key);
            
            // validate field group
            if(empty($field_group))
                continue;
            
            // load fields
            $field_group['fields'] = acf_get_fields($field_group);

            // prepare for export
            $field_group = acf_prepare_field_group_for_export($field_group);
            
            $array[] = $field_group;
            
        }
		
		// return
		return $array;
		
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
    
    function get_action(){
        
        // check $_POST
		if($action = acf_maybe_get_POST('action'))
            return $action;
		
		// check $_GET
		if($action = acf_maybe_get_GET('action'))
            return $action;
		
		// return
		return 'json';
		
	}
    
}

acf_register_admin_tool('ACFE_Admin_Tool_FG_Local');

endif;