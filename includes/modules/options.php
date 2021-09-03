<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/options'))
    return;

if(!class_exists('acfe_module_options')):

class acfe_module_options{
    
    var $action = 'list';
    
    /*
     * Construct
     */
    function __construct(){
        
        // WP List Table
        acfe_include('includes/modules/options.class.php');
        
        // Actions
        add_filter('set-screen-option', array($this, 'acfe_options_screen'), 10, 3);
        add_action('admin_menu',        array($this, 'admin_menu'));
        add_action('acf/save_post',     array($this, 'save_post'), 5);
        
    }
    
    function acfe_options_screen($status, $option, $value){
        
        if($option !== 'options_per_page') return $status;
        
        return $value;
        
    }
    
    /*
     * Admin menu
     */
    function admin_menu(){
        
        if(!acf_get_setting('show_admin')) return;
    
        $page = add_submenu_page('options-general.php', __('Options'), __('Options'), acf_get_setting('capability'), 'acfe-options', array($this, 'admin_html'));
    
        add_action("load-{$page}", array($this, 'admin_load'));
        
    }
    
    /*
     * Admin load
     */
    function admin_load(){
    
        // Messages
        if($message = acf_maybe_get_GET('message')){
            
            // deleted bulk-deleted updated added
            
            if($message === 'deleted'){
    
                acf_add_admin_notice(__('Option has been deleted'), 'success');
                
            }elseif($message === 'bulk-deleted'){
    
                acf_add_admin_notice(__('Options have been deleted'), 'success');
            
            }elseif($message === 'updated'){
    
                acf_add_admin_notice(__('Option has been updated'), 'success');
            
            }elseif($message === 'added'){
    
                acf_add_admin_notice(__('Option has been added'), 'success');
            
            }
            
        }
    
        // default: list
        $this->action = 'list';
    
        // edit or delete
        if(acfe_maybe_get_REQUEST('action', '-1') !== '-1'){
    
            $this->action = $_REQUEST['action'];
            
        // bulk-delete
        }elseif(acfe_maybe_get_REQUEST('action2', '-1') !== '-1'){
    
            $this->action = $_REQUEST['action2'];
            
        }
        
        if($this->action === 'list'){
            
            $this->load_list();
            
        }elseif($this->action === 'edit' || $this->action === 'add'){
    
            $this->load_edit();
            
        }elseif($this->action === 'delete'){
    
            $this->load_delete();
            
        }elseif($this->action === 'bulk-delete'){
    
            $this->load_bulk_delete();
            
        }
    
        // Enqueue
        acf_enqueue_scripts();
        
    }
    
    /*
     * Admin html
     */
    function admin_html(){
        
        if($this->action === 'list'){
        
            $this->html_list();
        
        }elseif($this->action === 'edit' || $this->action === 'add'){
        
            $this->html_edit();
        
        }
        
    }
    
    /*
     * Load: List
     */
    function load_list(){
    
        add_screen_option('per_page', array(
            'label'     => 'Options',
            'default'   => 100,
            'option'    => 'options_per_page'
        ));
        
    }
    
    /*
     * Load: Edit
     */
    function load_edit(){
    
        // Nonce
        if(acf_verify_nonce('acfe-options-edit')){
        
            // Save data
            if(acf_validate_save_post(true)){
            
                acf_save_post('acfe_options_edit');
            
                $redirect = add_query_arg(array('message' => 'updated'));
            
                if($this->action === 'add'){
                    
                    $redirect = sprintf('?page=%s&message=added', esc_attr($_REQUEST['page']));
                    
                }
            
                wp_redirect($redirect);
                exit;
            
            }
        
        }
    
        // Actions
        add_action('acf/input/admin_head', array($this, 'add_metaboxes'));
    
        // Add columns support
        add_screen_option('layout_columns', array(
            'max'     => 2,
            'default' => 2,
        ));
    
    }
    
    /*
     * Load: Delete
     */
    function load_delete(){
    
        // nonce
        $nonce = esc_attr($_REQUEST['_wpnonce']);
    
        // verify
        if(!wp_verify_nonce($nonce, 'acfe_options_delete_option')){
            wp_die('Cheatin’, huh?');
        }
    
        // delete
        $this->delete_option(absint($_GET['option']));
    
        // redirect
        wp_redirect(sprintf('?page=%s&message=deleted', esc_attr($_REQUEST['page'])));
        exit;
    
    }
    
    /*
     * Load: Bulk Delete
     */
    function load_bulk_delete(){
    
        // nonce
        $nonce = esc_attr($_REQUEST['_wpnonce']);
        
        // verify
        if(!wp_verify_nonce($nonce, 'bulk-options')){
            wp_die('Cheatin’, huh?');
        }
    
        // ids
        $delete_ids = esc_sql($_REQUEST['bulk-delete']);
    
        // loop
        foreach($delete_ids as $id){
        
            // delete
            $this->delete_option($id);
        
        }
    
        wp_redirect(sprintf('?page=%s&message=bulk-deleted', esc_attr($_REQUEST['page'])));
        exit;
    
    }
    
    /*
     * HTML: List
     */
    function html_list(){
    
        acfe_get_view('html-options-list');
        
    }
    
    /*
     * HTML: Edit
     */
    function html_edit(){
    
        acfe_get_view('html-options-edit');
    
    }
    
    /*
     * Save Post
     */
    function save_post($post_id){
        
        // Validate
        if($post_id !== 'acfe_options_edit') return;
        
        // Vars
        $option_name = wp_unslash($_POST['acf']['field_acfe_options_edit_name']);
        $option_value = wp_unslash($_POST['acf']['field_acfe_options_edit_value']);
        $autoload = $_POST['acf']['field_acfe_options_edit_autoload'];
        
        // Value serialized?
        $option_value = maybe_unserialize($option_value);
        
        // Update
        update_option($option_name, $option_value, $autoload);
        
        // Flush ACF
        $_POST['acf'] = array();
        
    }
    
    /*
     * Delete Option
     */
    function delete_option($id){
        
        global $wpdb;
        
        $wpdb->delete("{$wpdb->options}", array('option_id' => $id), array('%d'));
        
    }
    
    /*
     * Add Metaboxes
     */
    function add_metaboxes(){
        
        $option = array(
            'option_id'     => 0,
            'option_name'   => '',
            'option_value'  => '',
            'autoload'      => 'no',
        );
    
        $option_id = absint(acfe_maybe_get_REQUEST('option'));
        
        if($option_id){
            
            global $wpdb;
            
            $get_option = $wpdb->get_row("SELECT * FROM {$wpdb->options} WHERE option_id = '$option_id'", 'ARRAY_A');
            
            if(!empty($get_option)){
                $option = $get_option;
            }
            
        }
        
        $field_group = array(
            'ID'                    => 0,
            'key'                   => 'group_acfe_options_edit',
            'style'                 => 'default',
            'label_placement'       => 'left',
            'instruction_placement' => 'label',
            'fields'                => array()
        );
        
        $fields = array();
        
        $fields[] = array(
            'label'             => __('Name'),
            'key'               => 'field_acfe_options_edit_name',
            'name'              => 'field_acfe_options_edit_name',
            'type'              => 'text',
            'prefix'            => 'acf',
            'instructions'      => '',
            'required'          => true,
            'conditional_logic' => false,
            'default_value'     => '',
            'placeholder'       => '',
            'prepend'           => '',
            'append'            => '',
            'maxlength'         => '',
            'value'             => $option['option_name'],
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
        );
        
        // Serialized || HTML
        if(is_serialized($option['option_value']) || $option['option_value'] != strip_tags($option['option_value'])){
            
            $type = 'serialized';
            $instructions = 'Use this <a href="https://duzun.me/playground/serialize" target="_blank">online tool</a> to unserialize/seriliaze data.';
            
            if($option['option_value'] != strip_tags($option['option_value'])){
                
                $type = 'HTML';
                $instructions = '';
                
            }
            
            $fields[] = array(
                'label'             => __('Value <code style="font-size:11px;float:right; line-height:1.2; margin-top:1px;">' . $type . '</code>'),
                'key'               => 'field_acfe_options_edit_value',
                'name'              => 'field_acfe_options_edit_value',
                'type'              => 'textarea',
                'prefix'            => 'acf',
                'instructions'      => $instructions,
                'required'          => false,
                'conditional_logic' => false,
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'value'             => $option['option_value'],
                'class'             => 'code',
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
            );
            
        }
        
        // Serialized || HTML
        elseif(acfe_is_json($option['option_value'])){
            
            $type = 'json';
            $instructions = 'Use this <a href="http://solutions.weblite.ca/php2json/" target="_blank">online tool</a> to decode/encode json.';
            
            $fields[] = array(
                'label'             => __('Value <code style="font-size:11px;float:right; line-height:1.2; margin-top:1px;">' . $type . '</code>'),
                'key'               => 'field_acfe_options_edit_value',
                'name'              => 'field_acfe_options_edit_value',
                'type'              => 'textarea',
                'prefix'            => 'acf',
                'instructions'      => $instructions,
                'required'          => false,
                'conditional_logic' => false,
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'value'             => $option['option_value'],
                'class'             => 'code',
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
            );
            
        }
        
        // String
        else{
            
            $type = '';
            if(!empty($option['option_value']))
                $type = '<code style="font-size:11px;float:right; line-height:1.2; margin-top:1px;">string</code>';
            
            $fields[] = array(
                'label'             => __('Value ' . $type),
                'key'               => 'field_acfe_options_edit_value',
                'name'              => 'field_acfe_options_edit_value',
                'type'              => 'textarea',
                'prefix'            => 'acf',
                'instructions'      => '',
                'required'          => false,
                'conditional_logic' => false,
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'value'             => $option['option_value'],
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
            );
            
        }
        
        $fields[] = array(
            'label'             => __('Autoload'),
            'key'               => 'field_acfe_options_edit_autoload',
            'name'              => 'field_acfe_options_edit_autoload',
            'type'              => 'select',
            'prefix'            => 'acf',
            'instructions'      => '',
            'required'          => true,
            'conditional_logic' => false,
            'default_value'     => '',
            'placeholder'       => '',
            'prepend'           => '',
            'append'            => '',
            'maxlength'         => '',
            'value'             => $option['autoload'],
            'choices'           => array(
                'no'    => __('No'),
                'yes'   => __('Yes'),
            ),
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
        );
        
        $field_group['fields'] = $fields;
        
        $metabox_submit_title = __('Submit','acf');
        $metabox_main_title = __('Add Option');
        
        if(!empty($option['option_id'])){
            
            $metabox_submit_title = __('Edit','acf');
            $metabox_main_title = __('Edit Option');
            
        }
        
        // Submit Metabox
        add_meta_box('submitdiv', $metabox_submit_title, function($post, $args) use($option){
            
            $delete_nonce = wp_create_nonce('acfe_options_delete_option');
            
            ?>
            <div id="major-publishing-actions">
                
                <?php if(!empty($option['option_id'])){ ?>

                    <div id="delete-action">
                        <a class="submitdelete deletion" style="color:#a00;" href="<?php echo sprintf('?page=%s&action=%s&option=%s&_wpnonce=%s', esc_attr($_REQUEST['page']), 'delete', $option['option_id'], $delete_nonce); ?>">
                            <?php _e('Delete'); ?>
                        </a>
                    </div>
                
                <?php } ?>

                <div id="publishing-action">
                    <span class="spinner"></span>
                    <input type="submit" accesskey="p" value="<?php _e('Update'); ?>" class="button button-primary button-large" id="publish" name="publish">
                </div>

                <div class="clear"></div>

            </div>
            <?php
        }, 'acf_options_page', 'side', 'high');
        
        // Main Metabox
        add_meta_box('acf-group_acfe_options_edit', $metabox_main_title, function($post, $args){
            
            // extract args
            extract($args); // all variables from the add_meta_box function
            extract($args); // all variables from the args argument
            
            // vars
            $o = array(
                'id'            => $id,
                'key'           => $field_group['key'],
                'style'         => $field_group['style'],
                'label'         => $field_group['label_placement'],
                'editLink'      => '',
                'editTitle'     => __('Edit field group', 'acf'),
                'visibility'    => true
            );
            
            // load fields
            $fields = $field_group['fields'];
            
            // render
            acf_render_fields($fields, 'acfe-options-edit', 'div', $field_group['instruction_placement']);
            
            ?>
            <script type="text/javascript">
                if(typeof acf !== 'undefined'){

                    acf.newPostbox(<?php echo json_encode($o); ?>);

                }
            </script>
            <?php
            
        }, 'acf_options_page', 'normal', 'high', array('field_group' => $field_group));
        
    }
    
}

new acfe_module_options();

endif;