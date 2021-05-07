<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if((!acfe_is_dev() && !acfe_is_super_dev()) || !acf_current_user_can_admin())
    return;

if(!class_exists('acfe_dev')):

class acfe_dev{
    
    public $wp_meta = array();
    public $acf_meta = array();
    
    function __construct(){
        
        // Script debug
        if(!defined('SCRIPT_DEBUG'))
            define('SCRIPT_DEBUG', true);
        
        // Additional Enqueue
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Post
        add_action('load-post.php',         array($this, 'load_post'));
        add_action('load-post-new.php',     array($this, 'load_post'));
        
        // Term
        add_action('load-term.php',         array($this, 'load_term'));
        
        // User
        add_action('show_user_profile',     array($this, 'load_user'), 20);
        add_action('edit_user_profile',     array($this, 'load_user'), 20);
        
        // Options
        add_action('acf/options_page/submitbox_before_major_actions',   array($this, 'load_options'));
        
        add_action('wp_ajax_acfe/delete_meta',                          array($this, 'ajax_delete_meta'));
        add_action('wp_ajax_acfe/bulk_delete_meta',                     array($this, 'ajax_bulk_delete_meta'));
        
    }
 
    /*
     * Enqueue Scripts
     */
    function admin_enqueue_scripts(){
        
        // bail early if not valid screen
        if(!acf_is_screen(array('profile-network', 'user-edit-network', 'user-network'))){
            return;
        }
        
        // enqueue
        acf_enqueue_scripts();
        
    }
    
    /*
     * Post
     */
    function load_post(){
        
        global $typenow;
        
        // Remove WP post meta box
        remove_meta_box('postcustom', false, 'normal');
        
        $reserved = acfe_get_setting('reserved_post_types', array());
        
        if(!acfe_is_super_dev() && in_array($typenow, $reserved))
            return;
        
        // actions
        add_action('add_meta_boxes', array($this, 'edit_post'), 10, 2);
        
    }
    
    function edit_post($post_type, $post){
    
        // Get Post ID
        $post_id = $post->ID;
        
        // Add Meta Boxes
        $this->add_meta_boxes($post_id, $post_type);
        
    }
    
    /*
     * Term
     */
    function load_term(){
        
        $screen = get_current_screen();
        $taxonomy = $screen->taxonomy;
        
        // actions
        add_action("{$taxonomy}_edit_form", array($this, 'edit_term'), 10, 2);
        
    }
    
    function edit_term($term, $taxonomy){
        
        // Get Term ID
        $post_id = 'term_' . $term->term_id;
        
        // Add Meta Boxes
        $this->add_meta_boxes($post_id, "edit-{$taxonomy}");
        
    }
    
    /*
     * User
     */
    function load_user($user){
        
        $post_id = 'user_' . $user->ID;
        
        // Add Meta Boxes
        $this->add_meta_boxes($post_id, array('profile', 'user-edit'));
        
    }
    
    /*
     * Admin
     */
    function load_options($page){
        
        $this->add_meta_boxes($page['post_id'], 'acf_options_page');
        
    }
    
    /*
     * Add Meta Boxes
     */
    function add_meta_boxes($post_id, $object_type){
        
        // Get Meta
        $this->get_meta($post_id);
        
        do_action('acfe/dev/add_meta_boxes', $post_id, $object_type);
        
        $render_bulk = false;
        
        // WP Metabox
        if(!empty($this->wp_meta)){
            
            if(empty($this->acf_meta))
                $render_bulk = true;
            
            $id = 'acfe-wp-custom-fields';
            $title = 'WP Custom Fields';
            
            if($object_type === 'acf_options_page'){
                $title = 'WP Options Meta';
            }
            
            $title .= '<span class="acfe_dev_meta_count">' . count($this->wp_meta) . '</span>';
            $context = 'normal';
            $priority = 'low';
            
            add_meta_box($id, $title, array($this, 'render_meta_box'), $object_type, $context, $priority, array('table_type' => 'wp', 'object_type' => $object_type, 'render_bulk' => $render_bulk));
            
        }
        
        // ACF Metabox
        if(!empty($this->acf_meta)){
            
            if(!$render_bulk)
                $render_bulk = true;
            
            $id = 'acfe-acf-custom-fields';
            $title = 'ACF Custom Fields';
            
            if($object_type === 'acf_options_page'){
                $title = 'ACF Options Meta';
            }
            
            $title .= '<span class="acfe_dev_meta_count">' . count($this->acf_meta) . '</span>';
            $context = 'normal';
            $priority = 'low';
            
            add_meta_box($id, $title, array($this, 'render_meta_box'), $object_type, $context, $priority, array('table_type' => 'acf', 'object_type' => $object_type, 'render_bulk' => $render_bulk));
            
        }
        
    }

    function render_meta_box($post, $metabox){
        
        $table_type = $metabox['args']['table_type'];
        $object_type = $metabox['args']['object_type'];
        $render_bulk = $metabox['args']['render_bulk'];
        
        $is_options = ($object_type === 'acf_options_page');
        $is_acf = ($table_type === 'acf');
        
        $metas = $this->wp_meta;
        
        if($is_acf)
            $metas = $this->acf_meta;
        
        ?>
        <table class="wp-list-table widefat fixed striped" style="border:0;">
        
            <thead>
                <tr>
                    
                    <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                        <td scope="col" class="check-column"><input type="checkbox" /></td>
                    <?php } ?>
                    
                    <th scope="col" style="width:30%;">Name</th>
                    <th scope="col" style="width:auto;">Value</th>
                    
                    <?php if($is_acf){ ?>
                        <th scope="col" style="width:100px;">Field Type</th>
                        <th scope="col" style="width:120px;">Field group</th>
                    <?php } ?>
                    
                    <?php if($is_options){ ?>
                        <th scope="col" style="width:65px;">Autoload</th>
                    <?php } ?>
                    
                </tr>
            </thead>

            <tbody>
                
                <?php foreach($metas as $meta){ ?>
                
                    <?php
                    
                    // WP Meta
                    $meta_key = $meta['key'];
                    $meta_id = $meta['id'];
                    $value = $this->render_meta_value($meta['value']);
                    $type = $meta['type'];
    
                    // ACF Meta
                    if($is_acf){
    
                        $field_type = acf_maybe_get($meta, 'field_type');
                        
                    }
                    
                    
                    $field_group = acf_maybe_get($meta, 'field_group');
                    
                    $nonce = wp_create_nonce('acfe_delete_meta_' . $meta_id);
                    ?>
                
                    <tr class="acfe_dev_meta_<?php echo $is_options ? $meta_key : $meta_id; ?>">
                        
                        <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                            <th scope="row" class="check-column">
                                <input type="checkbox" class="acfe_bulk_delete_meta" value="<?php echo $is_options ? $meta_key : $meta_id; ?>" />
                            </th>
                        <?php } ?>
                        
                        <td>
                            <strong><?php echo esc_attr($meta_key); ?></strong>
            
                            <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                                
                                <div class="row-actions">
                                    
                                    <?php if($is_options){ ?>
                                        <span class="edit">
                                            <a href="<?php echo admin_url('options-general.php?page=acfe-options&action=edit&option=' . $meta_id); ?>"><?php _e('Edit'); ?></a> |
                                        </span>
                                    <?php } ?>
                                    
                                    <span class="delete">
                                        <a href="#" class="acfe_delete_meta" data-meta-id="<?php echo $meta_id; ?>" data-meta-key="<?php echo $meta_key; ?>" data-type="<?php echo $type; ?>" data-nonce="<?php echo $nonce; ?>"><?php _e('Delete'); ?></a>
                                    </span>
                                    
                                </div>
                                
                            <?php } ?>
                            
                        </td>
                        
                        <td><?php echo $value; ?></td>
                        
                        <?php if($is_acf){ ?>
                            <td><?php echo $field_type; ?></td>
                            <td><?php echo $field_group; ?></td>
                        <?php } ?>
    
                        <?php if($is_options){ ?>
                            <td><?php echo $meta['autoload']; ?></td>
                        <?php } ?>
                        
                    </tr>
                    
                <?php } ?>

            </tbody>

        </table>
        
        <?php if(current_user_can(acf_get_setting('capability')) && $render_bulk){ ?>
            
            <div class="acfe_dev_bulk_actions tablenav bottom">
    
                <div class="alignleft actions bulkactions">
                    
                    <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e('Select bulk action'); ?></label>
                    
                    <input type="hidden" class="acfe_bulk_delete_meta_type" value="<?php echo $type; ?>" />
                    
                    <?php $nonce = wp_create_nonce('acfe_bulk_delete_meta'); ?>
                    <input type="hidden" class="acfe_bulk_delete_meta_nonce" value="<?php echo $nonce; ?>" />
                    
                    <select class="acfe_bulk_delete_meta_action">
                        <option value="-1"><?php _e('Bulk Actions'); ?></option>
                        <option value="delete"><?php _e('Delete'); ?></option>
                    </select>
                    
                    <input type="submit" id="acfe_bulk_delete_meta_submit" class="button action" value="<?php _e('Apply'); ?>">
                    
                </div>
                
                <br class="clear">
                
            </div>
            
        <?php } ?>
        
        <?php
        
    }
    
    function render_meta_value($value){
        
        $return = '';
        
        // Empty
        if(empty($value) && !is_numeric($value)){
    
            $css = 'color:#aaa;';
            $value = '(' . __('empty', 'acf') . ')';
    
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre; ' . $css . '">' . print_r($value, true) . '</pre>';
            
        }
        
        // Serialized
        elseif(is_serialized($value)){
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r(maybe_unserialize($value), true) . '</pre>';
            $return .= '<pre style="max-height:200px; overflow:auto; white-space: unset; margin-top:10px; max-width:100%;">' . print_r($value, true) . '</pre>';
            
        }
        
        // HTML
        elseif($value != strip_tags($value)){
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r(htmlentities($value), true) . '</pre>';
            
        }
        
        // Json
        elseif(acfe_is_json($value)){
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r(json_decode($value), true) . '</pre>';
            $return .= '<pre style="max-height:200px; overflow:auto; white-space: unset; margin-top:10px; max-width:100%;">' . print_r($value, true) . '</pre>';
            
        }
        
        // String
        else{
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r($value, true) . '</pre>';
            
        }
        
        return $return;
        
    }
    
    function get_meta($post_id = 0){
        
        if(!$post_id)
            $post_id = acf_get_valid_post_id();
        
        if(empty($post_id))
            return;
        
        $info = acf_get_post_id_info($post_id);
        
        global $wpdb;
        
        // Post
        if($info['type'] === 'post'){
            
            $get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d ", $info['id']));
            
        }
        
        // Term
        elseif($info['type'] === 'term'){
            
            $get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->termmeta WHERE term_id = %d ", $info['id']));
            
        }
        
        // User
        elseif($info['type'] === 'user'){
            
            $get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE user_id = %d ", $info['id']));
            
        }
        
        // Option
        elseif($info['type'] === 'option'){
            
            $id = $info['id'];
            
            $search = "{$id}_%";
            $_search = "_{$id}_%";
            $search_single = "{$id}";
            
            $search = str_replace('_', '\_', $search);
            $_search = str_replace('_', '\_', $_search);
            
            $get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s OR option_name = %s", $search, $_search, $search_single));
            
        }
        
        if(empty($get_meta))
            return;
        
        $wp_meta = array();
    
        // Option
        if($info['type'] === 'option'){
    
            usort($get_meta, function($a, $b){
                return strcmp($a->option_name, $b->option_name);
            });
    
            foreach($get_meta as $meta){
        
                $wp_meta[$meta->option_name] = array(
                    'id'        => $meta->option_id,
                    'key'       => $meta->option_name,
                    'value'     => $meta->option_value,
                    'autoload'  => $meta->autoload,
                    'type'      => $info['type'],
                );
        
            }
        
        // Post / Term
        }elseif($info['type'] === 'post' || $info['type'] === 'term'){
    
            usort($get_meta, function($a, $b){
                return strcmp($a->meta_key, $b->meta_key);
            });
    
            foreach($get_meta as $meta){
        
                $wp_meta[$meta->meta_key] = array(
                    'id'    => $meta->meta_id,
                    'key'   => $meta->meta_key,
                    'value' => $meta->meta_value,
                    'type'  => $info['type'],
                );
        
            }
            
        // User
        }elseif($info['type'] === 'user'){
    
            usort($get_meta, function($a, $b){
                return strcmp($a->meta_key, $b->meta_key);
            });
    
            foreach($get_meta as $meta){
        
                $wp_meta[$meta->meta_key] = array(
                    'id'    => $meta->umeta_id,
                    'key'   => $meta->meta_key,
                    'value' => $meta->meta_value,
                    'type'  => $info['type'],
                );
        
            }
    
        }
        
        $acf_meta = array();
        
        foreach($wp_meta as $key => $meta){
            
            // ACF Meta
            if(isset($wp_meta["_$key"])){
                
                $field = false;
                $field_type_display = false;
                $field_group_display = false;
                
                $field_key = $wp_meta["_$key"]['value'];
                
                // Value = field_abcde123456?
                if(acf_is_field_key($field_key)){
                    
                    $field = acf_get_field($field_key);
    
                    if(!$field){
    
                        $field_type_display = '<em>Undefined</em>';
                        $field_group_display = '<em>Undefined</em>';
                        
                        // Check clone: field_123456abcdef_field_123456abcfed
                        $count = substr_count($field_key, 'field_');
    
                        if($count === 2){
    
                            $keys = explode('field_', $field_key);
    
                            $field_1 = 'field_' . substr($keys[1], 0, -1);
                            $field_2 = 'field_' . $keys[2];
    
                            $field = acf_get_field($field_2);
                            
                        }
                        
                    }
                    
                    if($field){
    
                        $field_type = acf_get_field_type($field['type']);
                        $field_type_display = '<em>Undefined</em>';
    
                        if(isset($field_type->label))
                            $field_type_display = $field_type->label;
    
                        $field_group = acfe_get_field_group_from_field($field);
                        $field_group_display = '<em>Undefined</em>';
    
                        if($field_group){
        
                            $field_group_display = $field_group['title'];
        
                            if(!empty($field_group['ID'])){
            
                                $post_status = get_post_status($field_group['ID']);
            
                                if($post_status === 'publish' || $post_status === 'acf-disabled'){
                
                                    $field_group_display = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
                
                                }
            
                            }
        
                        }
                        
                    }
                    
                }
                
                $_meta = $wp_meta["_$key"];
                $_meta['field_type'] = $field_type_display;
                $_meta['field_group'] = $field_group_display;
                
                $acf_meta[] = $_meta;
    
                $_meta = $wp_meta[$key];
                $_meta['field_type'] = $field_type_display;
                $_meta['field_group'] = $field_group_display;
    
                $acf_meta[] = $_meta;
                
                // Unset WP Meta
                unset($wp_meta["_$key"]);
                unset($wp_meta[$key]);
                
            }
            
        }
        
        $this->wp_meta = $wp_meta;
        $this->acf_meta = $acf_meta;
        
    }
    
    function ajax_delete_meta(){
        
        // Vars
        $id = acf_maybe_get_POST('id');
        $key = acf_maybe_get_POST('key');
        $type = acf_maybe_get_POST('type');
        
        // Check vars
        if(!$id || !$key || !$type)
            wp_die(0);
        
        // Check referer
        check_ajax_referer("acfe_delete_meta_$id");
    
        if(!current_user_can(acf_get_setting('capability'))){
            wp_die(-1);
        }
    
        // Delete option
        if($type === 'option'){
            
            if(delete_option($key))
                wp_die(1);
        
        // Delete meta
        }else{
            
            if(delete_metadata_by_mid($type, $id))
                wp_die(1);
            
        }
    
        wp_die(0);
        
    }
    
    function ajax_bulk_delete_meta(){
        
        // Vars
        $ids = acf_maybe_get_POST('ids');
        $type = acf_maybe_get_POST('type');
        
        // Check vars
        if(!$ids || !$type)
            wp_die(0);
        
        // Check referer
        check_ajax_referer('acfe_bulk_delete_meta');
    
        if(!current_user_can(acf_get_setting('capability'))){
            wp_die(-1);
        }
    
        // Delete option
        if($type === 'option'){
            
            foreach($ids as $key){
                
                delete_option($key);
                
            }
            
            wp_die(1);
        
        // Delete meta
        }else{
    
            foreach($ids as $id){
    
                delete_metadata_by_mid($type, $id);
        
            }
    
            wp_die(1);
            
        }
    
        wp_die(0);
        
    }
    
}

new acfe_dev();

endif;