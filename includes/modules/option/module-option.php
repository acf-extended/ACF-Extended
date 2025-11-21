<?php

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(!acf_get_setting('acfe/modules/options')){
    return;
}

if(!class_exists('acfe_module_options')):

class acfe_module_options{
    
    // vars
    var $action = 'list';
    
    /**
     * Construct
     */
    function __construct(){
        
        acfe_include('includes/modules/option/module-option-table.php');
        
        add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
        add_action('admin_menu',        array($this, 'admin_menu'));
        add_action('acf/save_post',     array($this, 'save_post'), 5);
        
    }
    
    
    /**
     * set_screen_option
     *
     * @param $status
     * @param $option
     * @param $value
     *
     * @return mixed
     */
    function set_screen_option($status, $option, $value){
        
        if($option === 'options_per_page'){
            return $value;
        }
        
        return $status;
        
    }
    
    
    /**
     * admin_menu
     */
    function admin_menu(){
        
        if(acf_get_setting('show_admin')){
            
            $page = add_submenu_page('options-general.php', __('Options', 'acfe'), __('Options', 'acfe'), acf_get_setting('capability'), 'acfe-options', array($this, 'admin_html'));
    
            add_action("load-{$page}", array($this, 'admin_load'));
            
        }
        
    }
    
    
    /**
     * admin_load
     */
    function admin_load(){
    
        // messages
        if($message = acf_maybe_get_GET('message')){
            
            switch($message){
                
                case 'deleted': {
                    acf_add_admin_notice(__('Option has been deleted', 'acfe'), 'success');
                    break;
                }
                
                case 'bulk-deleted': {
                    acf_add_admin_notice(__('Options have been deleted', 'acfe'), 'success');
                    break;
                }
    
                case 'updated': {
                    acf_add_admin_notice(__('Option has been updated', 'acfe'), 'success');
                    break;
                }
    
                case 'added': {
                    acf_add_admin_notice(__('Option has been added', 'acfe'), 'success');
                    break;
                }
                
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
        
        // load
        switch($this->action){
            
            case 'list': {
                $this->load_list();
                break;
            }
            
            case 'edit':
            case 'add': {
                $this->load_edit();
                break;
            }
            
            case 'delete': {
                $this->load_delete();
                break;
            }
            
            case 'bulk-delete': {
                $this->load_bulk_delete();
                break;
            }
            
        }
    
        // enqueue
        acf_enqueue_scripts();
        
    }
    
    
    /**
     * admin_html
     */
    function admin_html(){
        
        if($this->action === 'list'){
            $this->html_list();
        
        }elseif($this->action === 'edit' || $this->action === 'add'){
            $this->html_edit();
        }
        
    }
    
    
    /**
     * load_list
     */
    function load_list(){
    
        add_screen_option('per_page', array(
            'label'   => 'Options',
            'default' => 100,
            'option'  => 'options_per_page'
        ));
        
    }
    
    
    /**
     * load_edit
     */
    function load_edit(){
    
        // nonce
        if(acf_verify_nonce('acfe-options-edit')){
        
            // save data
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
    
        // actions
        add_action('acf/input/admin_head', array($this, 'add_metaboxes'));
    
        // add columns support
        add_screen_option('layout_columns', array(
            'max'     => 2,
            'default' => 2,
        ));
    
    }
    
    
    /**
     * load_delete
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
    
    
    /**
     * load_bulk_delete
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
            $this->delete_option($id);
        }
    
        wp_redirect(sprintf('?page=%s&message=bulk-deleted', esc_attr($_REQUEST['page'])));
        exit;
    
    }
    
    
    /**
     * html_list
     */
    function html_list(){
        acfe_get_view('html-options-list');
    }
    
    
    /**
     * html_edit
     */
    function html_edit(){
        acfe_get_view('html-options-edit');
    }
    
    
    /**
     * save_post
     *
     * @param $post_id
     */
    function save_post($post_id){
        
        // validate
        if($post_id !== 'acfe_options_edit'){
            return;
        }
        
        // vars
        $option_name = wp_unslash($_POST['acf']['field_acfe_options_edit_name']);
        $option_value = wp_unslash($_POST['acf']['field_acfe_options_edit_value']);
        $autoload = $_POST['acf']['field_acfe_options_edit_autoload'];
        
        // value serialized?
        $option_value = maybe_unserialize($option_value);
        
        // update
        update_option($option_name, $option_value, $autoload);
        
        // flush acf
        $_POST['acf'] = array();
        
    }
    
    
    /**
     * delete_option
     *
     * @param $id
     */
    function delete_option($id){
        
        global $wpdb;
        
        $wpdb->delete("{$wpdb->options}", array('option_id' => $id), array('%d'));
        
    }
    
    
    /**
     * add_metaboxes
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
        
        $fields = array();
        
        $fields[] = array(
            'label'             => __('Name', 'acfe'),
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
        
        // serialized || html
        if(is_serialized($option['option_value']) || $option['option_value'] != strip_tags($option['option_value'])){
            
            $class = 'code';
            $type = 'serialized';
            $instructions = 'Use this <a href="https://duzun.me/playground/serialize" target="_blank">online tool</a> to unserialize/seriliaze data.';
            
            if($option['option_value'] != strip_tags($option['option_value'])){
                $type = 'HTML';
                $instructions = '';
            }
            
            $instructions = '<code>' . $type . '</code>' . $instructions;
            
        }
        
        // json
        elseif(acfe_is_json($option['option_value'])){
            
            $class = 'code';
            $type = 'json';
            $instructions = 'Use this <a href="http://solutions.weblite.ca/php2json/" target="_blank">online tool</a> to decode/encode json.';
            $instructions = '<code>' . $type . '</code>' . $instructions;
            
        }
        
        // string
        else{
            
            $class = '';
            $instructions = '';
            if(!empty($option['option_value'])){
                $instructions = '<code>string</code>';
            }
            
        }
        
        $fields[] = array(
            'label'             => __('Value', 'acfe'),
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
            'class'             => $class,
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
        );
        
        $fields[] = array(
            'label'             => __('Autoload', 'acfe'),
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
                'no'    => __('No', 'acfe'),
                'yes'   => __('Yes', 'acfe'),
            ),
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
        );
        
        
        // prepare field group
        $field_group = array(
            'ID'                    => 0,
            'key'                   => 'group_acfe_options_edit',
            'style'                 => 'default',
            'label_placement'       => 'left',
            'instruction_placement' => 'label',
            'fields'                => $fields,
        );
        
        $metabox_submit_title = __('Submit', 'acf');
        $metabox_main_title = __('Add Option', 'acfe');
        
        if(!empty($option['option_id'])){
            
            $metabox_submit_title = __('Edit', 'acf');
            $metabox_main_title = __('Edit Option', 'acfe');
            
        }
        
        // submit Metabox
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
        
        // main metabox
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