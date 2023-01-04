<?php 

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dynamic_module_export')):

class acfe_dynamic_module_export extends ACF_Admin_Tool{
    
    public $instance;
    public $action;
    public $data = array();
    
    public $description;
    public $select;
    public $default_action;
    public $allowed_actions = array();
    public $file;
    public $files;
    public $messages = array();
    
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
        $choices = $this->instance->export_choices();
        
        ?>
        
        <?php if(acfe_is_acf_6()): ?>
        
            <div class="acf-postbox-header">
                <h2 class="acf-postbox-title"><?php echo $this->description; ?></h2>
            </div>
            <div class="acf-postbox-inner">
            
        <?php else: ?>
        
            <p><?php echo $this->description; ?></p>
        
        <?php endif; ?>
        
        <div class="acf-fields">
            <?php 
            
            if(!empty($choices)){
            
                // render
                acf_render_field_wrap(array(
                    'label'     => $this->select,
                    'type'      => 'checkbox',
                    'name'      => 'keys',
                    'prefix'    => false,
                    'value'     => false,
                    'toggle'    => true,
                    'choices'   => $choices,
                    'class'     => 'acfe-module-export-choices'
                ));
            
            }
            
            else{
                
                echo '<div style="padding:15px 12px;">';
                    echo $this->messages['not_found'];
                echo '</div>'; 
                
            }
            
            ?>
        </div>
        
        <?php $disabled = empty($choices) ? 'disabled="disabled"' : ''; ?>
        
        <p class="acf-submit">
            
            <?php if(in_array('json', $this->allowed_actions)){ ?>
                <button type="submit" name="action" class="button button-primary" value="json" <?php echo $disabled; ?>><?php _e('Export File'); ?></button>
            <?php } ?>
            
            <?php if(in_array('php', $this->allowed_actions)){ ?>
                <button type="submit" name="action" class="button" value="php" <?php echo $disabled; ?>><?php _e('Generate PHP'); ?></button>
            <?php } ?>
            
        </p>
        
        <?php if(acfe_is_acf_6()): ?>
            </div>
        <?php endif; ?>
        
        <?php
        
    }
    
    function html_single(){
        
        ?>
        <div class="acf-postbox-columns">
            <div class="acf-postbox-main">
                
                <p><?php _e("You can copy and paste the following code to your theme's functions.php file or include it within an external file.", 'acf'); ?></p>
                
                <div id="acf-admin-tool-export">
                    <textarea id="acf-export-textarea" readonly="true"><?php $this->instance->export_php($this->data); ?></textarea>
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
    
    function load(){
        
        if(!$this->is_active())
            return;
            
        $this->action = $this->get_action();
        $this->data = $this->get_data();
        
        // Json
        if($this->action === 'json'){
            
            $this->submit();
            
        }
        
        // PHP
        elseif($this->action === 'php'){
    
            // add notice
            if(!empty($this->data)){
        
                $count = count($this->data);
                $text = sprintf(_n($this->messages['success_single'], $this->messages['success_multiple'], $count, 'acf' ), $count);
        
                acf_add_admin_notice($text, 'success');
        
            }
            
        }
        
    }
    
    function submit(){
        
        $this->action = $this->get_action();
        $this->data = $this->get_data();
        $keys = array_keys($this->data);
        
        // validate
        if(!$this->data){
            return acf_add_admin_notice($this->messages['not_selected'], 'warning');
        }
        
        // Json
        if($this->action === 'json'){
        
            // Prefix
            $prefix = (count($keys) > 1) ? $this->file : $this->files;
            
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
        
        }
        
        // PHP
        elseif($this->action === 'php'){
            
            // url
            $url = add_query_arg(array(
                'keys' => implode('+', $keys),
                'action' => 'php'
            ), $this->get_url());
            
            // redirect
            wp_redirect($url);
            
        }
    
        exit;
        
    }
    
    function get_data(){
        
        // vars
        $keys = $this->get_keys();
        $data = array();
        
        foreach($keys as $key){
      
            // export
            $args = $this->instance->export_data($key);
            
            if(!$args)
                continue;
            
            $data[$key] = $args;
            
        }
        
        return $data;
        
    }
    
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
    
    function get_action(){
        
        // vars
        $default = $this->default_action;
        $action = acfe_maybe_get_REQUEST('action', $default);
        
        // check allowed
        if(!in_array($action, $this->allowed_actions))
            $action = $default;
        
        // return
        return $action;
        
    }
    
}

endif;