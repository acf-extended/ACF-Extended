<?php 

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_export')):

class acfe_module_export extends ACF_Admin_Tool{
    
    // vars
    public $module;
    public $action;
    public $data = array();
    
    /**
     * construct
     *
     * @param $module
     */
    function __construct($module){
        
        // module
        $this->module = $module;
    
        // vars
        $this->name = $this->module->get_export_tool();
        $this->title = $this->module->get_message('export_title');
        
        parent::__construct();
        
    }
    
    
    /**
     * html
     *
     * @return void
     */
    function html(){
        
        // single
        if($this->is_active()){
            
            $this->html_single();
            
        // archive
        }else{
            
            $this->html_archive();
            
        }
        
    }
    
    
    /**
     * html_archive
     */
    function html_archive(){
        
        if(!method_exists($this->module, 'get_items')){
            return;
        }
        
        // vars
        $choices = array();
        $items = $this->module->get_raw_items();
    
        if($items){
            foreach($items as $item){
                
                $choices[ $item['name'] ] = esc_html($item['label']);
                
            }
        }
        
        ?>
        
        <?php if(acfe_is_acf_6()): ?>
        
            <div class="acf-postbox-header">
                <h2 class="acf-postbox-title"><?php echo $this->module->get_message('export_description'); ?></h2>
            </div>
            <div class="acf-postbox-inner">
            
        <?php else: ?>
        
            <p><?php echo $this->module->get_message('export_description'); ?></p>
        
        <?php endif; ?>
        
        <div class="acf-fields">
            <?php 
            
            if(!empty($choices)){
            
                // render
                acf_render_field_wrap(array(
                    'label'     => $this->module->get_message('export_select'),
                    'type'      => 'checkbox',
                    'name'      => 'keys',
                    'prefix'    => false,
                    'value'     => false,
                    'toggle'    => true,
                    'choices'   => $choices,
                    'class'     => 'acfe-module-export-choices'
                ));
            
            }else{
                
                echo '<div style="padding:15px 12px;">';
                    echo $this->module->get_message('export_not_found');
                echo '</div>'; 
                
            }
            
            ?>
        </div>
        
        <?php $disabled = empty($choices) ? 'disabled="disabled"' : ''; ?>
        
        <p class="acf-submit">
            
            <?php if(in_array('json', $this->module->export_actions)){ ?>
                <button type="submit" name="action" class="button button-primary" value="json" <?php echo $disabled; ?>><?php _e('Export File'); ?></button>
            <?php } ?>
            
            <?php if(in_array('php', $this->module->export_actions)){ ?>
                <button type="submit" name="action" class="button" value="php" <?php echo $disabled; ?>><?php _e('Generate PHP'); ?></button>
            <?php } ?>
            
        </p>
        
        <?php if(acfe_is_acf_6()): ?>
            </div>
        <?php endif; ?>
        
        <?php
        
    }
    
    
    /**
     * html_single
     */
    function html_single(){
    
        // enqueue
        wp_enqueue_script('code-editor');
        wp_enqueue_style('code-editor');
        
        ?>
        
        <?php if(acfe_is_acf_6()): ?>
            <div class="acf-postbox-header">
                <h2 class="acf-postbox-title"><?php echo $this->module->get_message('export_description'); ?></h2>
            </div>
        <?php endif; ?>
        
        <div class="acf-postbox-columns" style="margin-top: 0;margin-right: 280px;margin-bottom: 0;margin-left: 0;padding: 0;">
            <div class="acf-postbox-main">
                
                <?php
                $instructions = array(
                    __("You can copy and paste the following code to your theme's <code>functions.php</code> file or include it within an external file.", 'acfe')
                );
                
                if($this->module->get_message('export_instructions')){
                    $instructions[] = $this->module->get_message('export_instructions');
                }
                ?>
                
                <p><?php echo implode(' ', $instructions); ?></p>
                
                <div id="acf-admin-tool-export">
                    <textarea id="acf-export-textarea" readonly="true"><?php
    
                        foreach($this->data as $item){
    
                            // translation
                            $l10n = acf_get_setting('l10n');
                            $l10n_textdomain = acf_get_setting('l10n_textdomain');
    
                            if($l10n && $l10n_textdomain){
        
                                acf_update_setting('l10n_var_export', true);
        
                                $item = $this->module->translate_item($item);
        
                                acf_update_setting('l10n_var_export', false);
        
                            }
    
                            // cleanup keys
                            $item = $this->module->prepare_item_for_export($item);
                            
                            // var export
                            $code = acfe_var_export($item);
        
                            // echo
                            echo $this->module->export_code($code, $item) . "\r\n" . "\r\n";
        
                        }
                        
                        ?></textarea>
                </div>
                
                <p class="acf-submit">
                    <a class="button" id="acf-export-copy"><?php _e('Copy to clipboard', 'acf'); ?></a>
                </p>
                <script type="text/javascript">
                (function($){

                    if(typeof acf === 'undefined'){
                        return;
                    }
                    
                    // acf 6.0 add display block;
                    $('#acf-admin-tools #normal-sortables').css('display', 'block');
                    
                    acf.addAction('ready', function(){

                        // elements
                        var $a = $('#acf-export-copy');
                        var $textarea = $('#acf-export-textarea');

                        // initialize code mirror
                        var edit = wp.codeEditor.initialize($textarea.get(0), {

                            codemirror: $.extend(wp.codeEditor.defaultSettings.codemirror, {
                                lineNumbers:      true,
                                lineWrapping:     true,
                                styleActiveLine:  false,
                                continueComments: true,
                                indentUnit:       4,
                                tabSize:          1,
                                indentWithTabs:   false,
                                mode:             'text/x-php',
                                extraKeys:        {
                                    'Tab':       function(cm){cm.execCommand('indentMore')},
                                    'Shift-Tab': function(cm){cm.execCommand('indentLess')},
                                },
                            })

                        });

                        // set height
                        edit.codemirror.getScrollerElement().style.minHeight = 15 * 18.5 + 'px';

                        if(!document.queryCommandSupported('copy')){
                            return $a.remove();
                        }

                        $a.on('click', function(e){

                            e.preventDefault();
                            var $this = $(this);

                            // copy
                            navigator.clipboard.writeText(edit.codemirror.getValue()).then(function(){

                                // tooltip
                                acf.newTooltip({
                                    text:       "<?php _e('Copied', 'acf'); ?>",
                                    timeout:    250,
                                    target:     $this,
                                });

                            });

                        });
                        
                    });
                
                })(jQuery);
                </script>
            </div>

            <div class="acf-postbox-side">

                <div class="acf-panel acf-panel-selection -open">
                    <h3 class="acf-panel-title"><?php echo $this->module->get_message('export_select'); ?> <i class="dashicons dashicons-arrow-down"></i></h3>
                    <div class="acf-panel-inside">
                        
                        <?php
                        // vars
                        $choices = array();
                        $selected = $this->get_keys();
                        $items = $this->module->get_items();

                        if($items){
                            foreach($items as $item){
                                $choices[ $item['name'] ] = esc_html($item['label']);
                            }
                        }

                        // render
                        acf_render_field_wrap(array(
                            'type'    => 'checkbox',
                            'name'    => 'keys',
                            'prefix'  => false,
                            'value'   => $selected,
                            'toggle'  => true,
                            'choices' => $choices,
                        ));
                        ?>
                        
                    </div>
                </div>
                
                <p class="acf-submit">
    
                    <?php if(in_array('json', $this->module->export_actions)){ ?>
                        <button type="submit" name="action" class="button button-primary" value="json"><?php _e('Export File'); ?></button>
                    <?php } ?>
    
                    <?php if(in_array('php', $this->module->export_actions)){ ?>
                        <button type="submit" name="action" class="button" value="php"><?php _e('Generate PHP'); ?></button>
                    <?php } ?>
                    
                </p>
            </div>
            
        </div>
        <?php
    
    }
    
    
    /**
     * load
     *
     * @return void
     */
    function load(){
        
        if(!$this->is_active()){
            return;
        }
        
        $this->action = $this->get_action();
        $this->data = $this->get_data();
        
        // Json
        if($this->action === 'json'){
            
            $this->submit();
    
        // PHP
        }elseif($this->action === 'php'){
    
            // add notice
            if(!empty($this->data)){
        
                $count = count($this->data);
                
                $text = $this->module->get_message('export_success_single');
                
                if($count > 1){
                   $text = sprintf($this->module->get_message('export_success_multiple'), $count);
                }
        
                acf_add_admin_notice($text, 'success');
        
            }
            
        }
        
    }
    
    
    /**
     * submit
     *
     * @return ACF_Admin_Notice|n|void
     */
    function submit(){
        
        // vars
        $this->action = $this->get_action();
        $this->data = $this->get_data();
        $keys = wp_list_pluck($this->data, 'name');
        
        // validate
        if(!$this->data){
            return acf_add_admin_notice($this->module->get_message('export_not_selected'), 'warning');
        }
        
        // Json
        if($this->action === 'json'){
        
            // prefix
            $prefix = (count($this->data) > 1) ? $this->module->export_files['multiple'] : $this->module->export_files['single'];
            
            // slugs
            $slugs = implode('-', $keys);
            
            // date
            $date = date('Y-m-d');
            
            // file
            $file_name = 'acfe-export-' .  $prefix  . '-' . $slugs . '-' .  $date . '.json';
            
            // data
            $data = array();
            foreach($this->data as $item){
                
                // cleanup keys
                $item = $this->module->prepare_item_for_export($item);
                
                // append to data
                $data[] = $item;
                
            }
            
            // headers
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename={$file_name}");
            header("Content-Type: application/json; charset=utf-8");
            
            // return
            echo acf_json_encode($data);
    
        // PHP
        }elseif($this->action === 'php'){
            
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
    
    
    /**
     * get_data
     *
     * @return array
     */
    function get_data(){
        
        // vars
        $keys = $this->get_keys();
        $data = array();
        
        foreach($keys as $name){
      
            // get item
            $item = $this->module->get_raw_item($name);
            
            if($item){
                $data[] = $item;
            }
            
        }
        
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
    
        // $_GET
        }elseif($keys_get){
            
            $keys_get = str_replace(' ', '+', $keys_get);
            $keys = explode('+', $keys_get);
            
        }
        
        return $keys;
        
    }
    
    
    /**
     * get_action
     *
     * @return false|mixed|null
     */
    function get_action(){
        
        // vars
        $action = acfe_maybe_get_REQUEST('action');
        
        // check allowed
        if(!in_array($action, $this->module->export_actions)){
            return current($this->module->export_actions);
        }
        
        // return
        return $action;
        
    }
    
}

endif;