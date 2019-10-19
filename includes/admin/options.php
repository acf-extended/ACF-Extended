<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/options'))
    return;

/**
 * Options WP List Table
 * 
 */
require_once(ACFE_PATH . 'includes/admin/options.class.php');

/**
 * Options Menu
 * 
 */
add_action('admin_menu', 'acfe_options_menu');
function acfe_options_menu(){
    
    $hook = add_submenu_page(
        'options-general.php', 
        __('Options'), 
        __('Options'), 
        acf_get_setting('capability'), 
        'acfe-options'
    );
    
}

/**
 * Options Screen
 * 
 */
add_filter('set-screen-option', 'acfe_options_screen', 10, 3);
function acfe_options_screen($status, $option, $value){
    
    return $value;
    
}

/**
 * Options Enqueue
 * 
 */
add_action('admin_print_scripts-settings_page_acfe-options', 'acfe_options_enqueue');
function acfe_options_enqueue(){
    
    wp_enqueue_style('acf-input');
    wp_enqueue_script('acf-input');
    wp_enqueue_style('acf-extended', plugins_url('assets/acf-extended.css', ACFE_FILE), false, null);
    
}

/**
 * Options Load
 * 
 */
add_action('load-settings_page_acfe-options', 'acfe_options_load');
function acfe_options_load(){
    
    // Messages
    if(isset($_REQUEST['message']) && !empty($_REQUEST['message']))
        do_action('acfe/options/load/message=' . $_REQUEST['message']);
    
    // Default Action
    $action = 'list';
    
    // Request Action
    if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] != '-1')
        $action = $_REQUEST['action'];
    
    // Request Action2
    elseif(isset($_REQUEST['action2']) && !empty($_REQUEST['action2']) && $_REQUEST['action2'] != '-1')
        $action = $_REQUEST['action2'];
    
    // Do Action: Specific
    do_action('acfe/options/load/action=' . $action, $action);
    
    // Do Action
    do_action('acfe/options/load', $action);
    
}

/**
 * Options HTML
 * 
 */
add_action('settings_page_acfe-options', 'acfe_options_html');
function acfe_options_html(){
    
    // Default Action
    $action = 'list';
    
    // Request Action
    if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] != '-1')
        $action = $_REQUEST['action'];
    
    // Do Action: Specific
    do_action('acfe/options/html/action=' . $action, $action);
    
    // Do Action
    do_action('acfe/options/html', $action);

}

/**
 * Options List: Load
 * 
 */
add_action('acfe/options/load/action=list', 'acfe_options_load_list');
function acfe_options_load_list(){
    
    add_screen_option('per_page', array(
        'label'     => 'Options',
        'default'   => 100,
        'option'    => 'options_per_page'
    ));
    
}

/**
 * Options List: HTML
 * 
 */
add_filter('acfe/options/html/action=list', 'acfe_options_html_list');
function acfe_options_html_list(){
    
    acf_get_view(ACFE_PATH . '/includes/admin/views/html-options-list.php');
    
}

/**
 * Options Delete: Load
 * 
 */
add_action('acfe/options/load/action=delete', 'acfe_options_load_delete');
function acfe_options_load_delete(){
    
    $nonce = esc_attr($_REQUEST['_wpnonce']);
        
    if(!wp_verify_nonce($nonce, 'acfe_options_delete_option'))
        wp_die('Cheatin’, huh?');
        
    acfe_options_delete_option(absint($_GET['option']));
    
    wp_redirect(sprintf('?page=%s&message=deleted', esc_attr($_REQUEST['page'])));
    exit;
    
}

/**
 * Options Delete: Message
 * 
 */
add_action('acfe/options/load/message=deleted', 'acfe_options_load_delete_message');
function acfe_options_load_delete_message(){
    
    acf_add_admin_notice(__('Option has been deleted'), 'success');
    
}

/**
 * Options Bulk Delete: Load
 * 
 */
add_action('acfe/options/load/action=bulk-delete', 'acfe_options_load_bulk_delete');
function acfe_options_load_bulk_delete(){
    
    $nonce = esc_attr($_REQUEST['_wpnonce']);
    
    if(!wp_verify_nonce($nonce, 'bulk-options'))
        wp_die('Cheatin’, huh?');
    
    $delete_ids = esc_sql($_REQUEST['bulk-delete']);
    
    foreach($delete_ids as $id){
        
        acfe_options_delete_option($id);
        
    }
    
    wp_redirect(sprintf('?page=%s&message=bulk-deleted', esc_attr($_REQUEST['page'])));
    exit;
    
}

/**
 * Options Bulk Delete: Message
 * 
 */
add_action('acfe/options/load/message=bulk-deleted', 'acfe_options_load_bulk_delete_message');
function acfe_options_load_bulk_delete_message(){
    
    acf_add_admin_notice(__('Options have been deleted'), 'success');
    
}

/**
 * Options Delete: Function
 * 
 */
function acfe_options_delete_option($id){
        
    global $wpdb;

    $wpdb->delete(
        "{$wpdb->options}",
        array('option_id' => $id),
        array('%d')
    );
    
}

/**
 * Options Edit: Load
 * 
 */
add_action('acfe/options/load/action=edit', 'acfe_options_load_edit');
add_action('acfe/options/load/action=add', 'acfe_options_load_edit');
function acfe_options_load_edit($action){
    
    // Nonce
    if(acf_verify_nonce('acfe-options-edit')){
    
        // Save data
        if(acf_validate_save_post(true)){
            
            acf_save_post('acfe_options_edit');
            
            $redirect = add_query_arg(array('message' => 'updated'));
            
            if($action === 'add')
                $redirect = sprintf('?page=%s&message=added', esc_attr($_REQUEST['page']));
            
            wp_redirect($redirect);
            exit;
            
        }
        
    }
    
    // Load acf scripts
    acf_enqueue_scripts();
    
    // Actions
    add_action('acf/input/admin_head', 'acfe_options_edit_metabox');
    
    // Add columns support
    add_screen_option('layout_columns', array(
        'max'	=> 2,
        'default' => 2
    ));
    
}

/**
 * Options Edit: HTML
 * 
 */
add_filter('acfe/options/html/action=edit', 'acfe_options_html_edit');
add_filter('acfe/options/html/action=add', 'acfe_options_html_edit');
function acfe_options_html_edit(){
    
    acf_get_view(ACFE_PATH . '/includes/admin/views/html-options-edit.php');
    
}

/**
 * Options Edit: Metabox
 * 
 */
function acfe_options_edit_metabox(){
    
    $option = array(
        'option_id'     => 0,
        'option_name'   => '',
        'option_value'  => '',
        'autoload'      => 'no',
    );
    
    if(isset($_REQUEST['option']) && !empty($_REQUEST['option'])){
        
        $option_id = absint($_REQUEST['option']);
        
        global $wpdb;
        
        $get_option = $wpdb->get_row("SELECT * FROM {$wpdb->options} WHERE option_id = '$option_id'", 'ARRAY_A');
        if(!empty($get_option))
            $option = $get_option;
    
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
        
        $type = 'serilized';
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
            'type'              => 'text',
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
                    <a class="submitdelete deletion" style="color:#a00;" href="<?php echo sprintf('?page=%s&action=%s&option=%s&_wpnonce=%s', esc_attr($_REQUEST['page']), 'delete', $option['option_id'], $delete_nonce); ?>"><?php _e('Delete'); ?></a>
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
            'id'			=> $id,
            'key'			=> $field_group['key'],
            'style'			=> $field_group['style'],
            'label'			=> $field_group['label_placement'],
            'editLink'		=> '',
            'editTitle'		=> __('Edit field group', 'acf'),
            'visibility'	=> true
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

/**
 * Options Edit: Save
 * 
 */
add_action('acf/save_post', 'acfe_options_edit_save_post', 5);
function acfe_options_edit_save_post($post_id){
    
    // Validate
    if($post_id !== 'acfe_options_edit')
        return;
    
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

/**
 * Options Edit: Message
 * 
 */
add_action('acfe/options/load/message=updated', 'acfe_options_load_edit_message');
function acfe_options_load_edit_message(){
    
    acf_add_admin_notice(__('Option has been updated'), 'success');
    
}

/**
 * Options Add: Message
 * 
 */
add_action('acfe/options/load/message=added', 'acfe_options_load_add_message');
function acfe_options_load_add_message(){
    
    acf_add_admin_notice(__('Option has been added'), 'success');
    
}