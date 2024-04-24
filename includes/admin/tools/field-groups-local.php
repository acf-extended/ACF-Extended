<?php 

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_groups_local_export')):

class acfe_field_groups_local_export extends ACF_Admin_Tool{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
        
        // vars
        $this->title = __('Export Local Field Groups');
        $this->name = 'acfe-fg-local';
        
    }
    
    
    /**
     * load
     *
     * @return ACF_Admin_Notice|n|void
     */
    function load(){
        
        if($ids = acf_maybe_get_GET('acfe-fg-local-sync')){
            
            $ids = explode(' ', $ids);
        
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
            
            $data = $this->get_data();
            $keys = $this->get_keys();
            $action = $this->get_action();
            
            // validate
            if(empty($data)){
                return acf_add_admin_notice(__('No field group selected'), 'warning');
            }
            
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
                echo acf_json_encode($data);
                die;
                
            }
            
            // Sync
            elseif($action === 'sync'){
                
                // Remeber imported field group ids.
                $ids = array();
                
                // Loop over json
                foreach($data as $field_group){
                    
                    // Search database for existing field group.
                    $post = acf_get_field_group_post($field_group['key']);
                    
                    if($post){
                        $field_group['ID'] = $post->ID;
                    }
                    
                    // remove inline callbacks
                    add_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'), 20);
                    
                    // Import field group.
                    $field_group = acf_import_field_group($field_group);
                    
                    // reset filter
                    remove_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'), 20);
                    
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
    
    
    /**
     * prepare_field_for_import
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field_for_import($field){
        
        // remove inline callback during import
        unset($field['callback']);
        
        // dyanmic render
        if($field['type'] === 'acfe_dynamic_render'){
            unset($field['render']);
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * html
     *
     * @return void
     */
    function html(){
        
        if($this->is_active()){
            
            $data = $this->get_data();
            
            ?>
            <div class="acf-postbox-columns">
                <div class="acf-postbox-main">
                    
                    <?php
                    
                    $str_replace = array(
                        "  "            => "\t",
                        "'!!__(!!\'"    => "__('",
                        "!!\', !!\'"    => "', '",
                        "!!\')!!'"      => "')",
                        "array ("       => "array("
                    );
                    
                    $preg_replace = array(
                        '/([\t\r\n]+?)array/'   => 'array',
                        '/[0-9]+ => array/'     => 'array'
                    );
                    
                    ?>
                    <p><?php _e("The following code can be used to register a local version of the selected field group(s). A local field group can provide many benefits such as faster load times, version control & dynamic fields/settings. Simply copy and paste the following code to your theme's functions.php file or include it within an external file.", 'acf'); ?></p>
                    
                    <div id="acf-admin-tool-export">
                        
                        
                    
                        <textarea id="acf-export-textarea" readonly="true"><?php
    
                        acf_update_setting('l10n_var_export', true);
                        
                        echo "if( function_exists('acf_add_local_field_group') ):" . "\r\n" . "\r\n";
                        
                        foreach($data as $field_group){
                                    
                            // code
                            $code = var_export($field_group, true);
                            
                            // change double spaces to tabs
                            $code = str_replace(array_keys($str_replace), array_values($str_replace), $code);
                            
                            // correctly formats "=> array("
                            $code = preg_replace(array_keys($preg_replace), array_values($preg_replace), $code);
                            
                            // esc_textarea
                            $code = esc_textarea($code);
                            
                            // echo
                            echo "acf_add_local_field_group({$code});" . "\r\n" . "\r\n";
                        
                        }
                        
                        echo "endif;";
    
                        acf_update_setting('l10n_var_export', false);
                        
                        ?></textarea>
                    
                    </div>
                    
                    <p class="acf-submit">
                        <a class="button" id="acf-export-copy"><?php _e( 'Copy to clipboard', 'acf' ); ?></a>
                    </p>

                    <script type="text/javascript">
                        (function($){

                            var $a = $('#acf-export-copy');
                            var $textarea = $('#acf-export-textarea');

                            if(!document.queryCommandSupported('copy')){
                                return $a.remove();
                            }

                            $a.on('click', function(e){

                                e.preventDefault();

                                $textarea.get(0).select();

                                try{

                                    // copy
                                    var copy = document.execCommand('copy');
                                    if(!copy)
                                        return;

                                    // tooltip
                                    acf.newTooltip({
                                        text:       "<?php _e('Copied', 'acf' ); ?>",
                                        timeout:    250,
                                        target:     $(this),
                                    });

                                }catch(err){
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
    
    
    /**
     * get_data
     *
     * @return array
     */
    function get_data(){
        
        // vars
        $data = array();
        $keys = $this->get_keys();
        
        if(!$keys)
            return $data;
        
        // Enable filters
        acf_enable_filters();
        
        // Disable fitler: clone
        acf_disable_filter('clone');
        
        // Get desync PHP Field Groups
        $desync_php_field_groups = acfe_get_desync_php_field_groups();
        
        foreach($desync_php_field_groups as $file_key => $file_path){
            
            require_once($file_path);
            
        }
        
        foreach($keys as $field_group_key){
            
            $field_group = acf_get_field_group($field_group_key);
            
            // validate field group
            if(empty($field_group)){
                continue;
            }
            
            // load fields
            $field_group['fields'] = acf_get_fields($field_group);

            // prepare for export
            $field_group = acf_prepare_field_group_for_export($field_group);
    
            $data[] = $field_group;
            
        }
        
        // return
        return $data;
        
    }
    
    
    /**
     * get_keys
     *
     * @return array|false|string[]
     */
    function get_keys(){
        
        // vars
        $keys_post = acf_maybe_get_POST('keys');
        $keys_get = acf_maybe_get_GET('keys');
        $keys = array();
        
        // $_POST
        if($keys_post){
            $keys = (array) $keys_post;
        }
        
        // $_GET
        elseif($keys_get){
            
            $keys_get = str_replace(' ', '+', $keys_get);
            $keys = explode('+', $keys_get);
            
        }
        
        return $keys;
        
    }
    
    
    /**
     * get_action
     *
     * @return mixed|string|null
     */
    function get_action(){
    
        // vars
        $default = 'json';
        $action = acfe_maybe_get_REQUEST('action', $default);
    
        // check allowed
        if(!in_array($action, array('json', 'php', 'sync'))){
            $action = $default;
        }
    
        // return
        return $action;
        
    }
    
}

// register tool
acf_register_admin_tool('acfe_field_groups_local_export');

endif;