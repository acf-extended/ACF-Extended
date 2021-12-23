<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_dev')):

class acfe_dev{
    
    public $wp_meta = array();
    public $acf_meta = array();
    
    function __construct(){
    
        // check settings
        if((!acfe_is_dev() && !acfe_is_super_dev()) || !acf_current_user_can_admin()){
            return;
        }
        
        // network enqueue
        add_action('admin_enqueue_scripts',             array($this, 'admin_enqueue_scripts'));
        
        // load
        add_action('acfe/load_post',                    array($this, 'load_post'));
        add_action('acfe/load_post',                    array($this, 'clean_meta'));
        add_action('acfe/load_posts',                   array($this, 'clean_meta'));
        add_action('acfe/load_term',                    array($this, 'clean_meta'));
        add_action('acfe/load_terms',                   array($this, 'clean_meta'));
        add_action('acfe/load_user',                    array($this, 'clean_meta'));
        add_action('acfe/load_users',                   array($this, 'clean_meta'));
        add_action('acfe/load_settings',                array($this, 'clean_meta'));
        add_action('acfe/load_option',                  array($this, 'clean_meta'));
        add_action('acfe/load_attachments',             array($this, 'clean_meta'));
        
        // add meta boxes
        add_action('acfe/add_post_meta_boxes',          array($this, 'add_post_meta_boxes'), 10, 2);
        add_action('acfe/add_posts_meta_boxes',         array($this, 'add_posts_meta_boxes'));
        add_action('acfe/add_term_meta_boxes',          array($this, 'add_term_meta_boxes'), 10, 2);
        add_action('acfe/add_terms_meta_boxes',         array($this, 'add_terms_meta_boxes'));
        add_action('acfe/add_user_meta_boxes',          array($this, 'add_user_meta_boxes'));
        add_action('acfe/add_option_meta_boxes',        array($this, 'add_option_meta_boxes'));
        
        add_action('acfe/add_settings_meta_boxes',      array($this, 'add_settings_meta_boxes'));
        add_action('acfe/add_attachments_meta_boxes',   array($this, 'add_attachments_meta_boxes'));
        add_action('acfe/add_users_meta_boxes',         array($this, 'add_users_meta_boxes'));
        
        // ajax
        add_action('wp_ajax_acfe/delete_meta',          array($this, 'ajax_delete_meta'));
        add_action('wp_ajax_acfe/bulk_delete_meta',     array($this, 'ajax_bulk_delete_meta'));
        
    }
 
    /*
     * Enqueue Scripts
     */
    function admin_enqueue_scripts(){
        
        // bail early if not network screen
        if(!acf_is_screen(array('profile-network', 'user-edit-network', 'user-network'))){
            return;
        }
        
        // enqueue
        acf_enqueue_scripts();
        
    }
    
    /*
     * Load Post
     */
    function load_post(){
        
        // force remove wp post meta metabox
        remove_meta_box('postcustom', false, 'normal');
        
    }
    
    /*
     * Clean Meta
     */
    function clean_meta(){
        
        $post_id = acfe_maybe_get_REQUEST('acfe_dev_clean');
        $nonce = acfe_maybe_get_REQUEST('acfe_dev_clean_nonce');
        
        if($post_id && wp_verify_nonce($nonce, 'acfe_dev_clean')){
            
            $deleted = acfe_delete_orphan_meta($post_id);
            
            set_transient('acfe_dev_clean', $deleted, 3600); // 1 hour
            
            // remove args
            $url = remove_query_arg(array(
                'acfe_dev_clean',
                'acfe_dev_clean_nonce'
            ));
            
            // add message
            $url = add_query_arg(array(
                'message' => 'acfe_dev_clean'
            ), $url);
            
            // redirect
            wp_redirect($url);
            exit;
            
        }
        
        // success message
        if(acf_maybe_get_GET('message') === 'acfe_dev_clean'){
            
            $deleted = acf_get_array(get_transient('acfe_dev_clean'));
            $count = count($deleted);
            
            if(isset($deleted['single_meta']) || isset($deleted['normal'])){
                
                $count = 0;
                $count += count(acf_maybe_get($deleted, 'single_meta', array()));
                $count += count(acf_maybe_get($deleted, 'normal', array()));
                
            }
            
            if(!$deleted){
                
                acf_add_admin_notice(__('No orphan meta found', 'acfe'), 'warning');
                
            }else{
                
                $link = ' <a href="#" data-acfe-modal="clean-meta-debug" data-acfe-modal-title="' . __('Deleted meta', 'acfe') . '" data-acfe-modal-footer="' . __('Close', 'acfe') . '">' . __('View', 'acfe') . '</a>';
                
                add_action('admin_footer', function() use($deleted){
                    ?>
                    <div class="acfe-modal" data-acfe-modal="clean-meta-debug">
                        <div class="acfe-modal-spacer">
                            <pre><?php print_r($deleted); ?></pre>
                        </div>
                    </div>
                    <?php
                });
                
                acf_add_admin_notice("{$count} meta cleaned.{$link}", 'success');
                
            }
            
            delete_transient('acfe_dev_clean');
            
        }
        
    }
    
    /*
     * Post Meta Boxes
     */
    function add_post_meta_boxes($post_type, $post){
        
        // check restricted post types
        if(acfe_is_post_type_reserved_dev($post_type)) return;
        
        // post id
        $post_id = $post->ID;
        
        // add meta boxes
        $this->add_meta_boxes($post_id, $post_type);
        
        if(acfe_is_single_meta_enabled($post_id) && !acf_is_filter_enabled('acfe/dev/clean_metabox')){
    
            add_meta_box('acfe-clean-meta', 'Single Meta', array($this, 'render_clean_metabox'), $post_type, 'side', 'core', array('post_id' => $post_id));
            
        }
        
    }
    
    /*
     * Post Type List
     */
    function add_posts_meta_boxes($post_type){
        
        // post id
        $post_id = "{$post_type}_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
    
        if(acfe_is_single_meta_enabled($post_id) && !acf_is_filter_enabled('acfe/dev/clean_metabox')){
            
            // enable sidebar
            acf_enable_filter('acfe/post_type_list/side');
            acf_enable_filter('acfe/post_type_list/submitdiv');
            
            add_meta_box('acfe-clean-meta', 'Single Meta', array($this, 'render_clean_metabox'), 'edit', 'side', 'core', array('post_id' => $post_id));
        
        }
        
    }
    
    /*
     * Term Meta Boxes
     */
    function add_term_meta_boxes($taxonomy, $term){
    
        // post id
        $post_id = "term_{$term->term_id}";
    
        // add meta boxes
        $this->add_meta_boxes($post_id, "edit-{$taxonomy}");
    
        if(acfe_is_single_meta_enabled($post_id) && !acf_is_filter_enabled('acfe/dev/clean_metabox')){
            
            add_meta_box('acfe-clean-meta', 'Single Meta', array($this, 'render_clean_metabox'), "edit-{$taxonomy}", 'side', 'core', array('post_id' => $post_id));
        
        }
        
    }
    
    /*
     * Taxonomy List
     */
    function add_terms_meta_boxes($taxonomy){
    
        // post id
        $post_id = "tax_{$taxonomy}_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
    
        if(acfe_is_single_meta_enabled($post_id) && !acf_is_filter_enabled('acfe/dev/clean_metabox')){
    
            // enable sidebar
            acf_enable_filter('acfe/taxonomy_list/side');
            acf_enable_filter('acfe/taxonomy_list/submitdiv');
    
            add_meta_box('acfe-clean-meta', 'Single Meta', array($this, 'render_clean_metabox'), 'edit', 'side', 'core', array('post_id' => $post_id));
        
        }
        
    }
    
    /*
     * User Meta Boxes
     */
    function add_user_meta_boxes($user){
    
        // post id
        $post_id = "user_{$user->ID}";
    
        // add meta boxes
        $this->add_meta_boxes($post_id, array('profile', 'user-edit'));
    
        if(acfe_is_single_meta_enabled($post_id) && !acf_is_filter_enabled('acfe/dev/clean_metabox')){
    
            add_meta_box('acfe-clean-meta', 'Single Meta', array($this, 'render_clean_metabox'), array('profile', 'user-edit'), 'side', 'core', array('post_id' => $post_id));
        
        }
        
    }
    
    /*
     * Options Page
     */
    function add_option_meta_boxes($page){
    
        // post id
        $post_id = $page['post_id'];
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'acf_options_page');
    
        if(acfe_is_single_meta_enabled($post_id) && !acf_is_filter_enabled('acfe/dev/clean_metabox')){
    
            add_meta_box('acfe-clean-meta', 'Single Meta', array($this, 'render_clean_metabox'), 'acf_options_page', 'side', 'core', array('post_id' => $post_id));
        
        }
        
    }
    
    /*
     * Settings Page
     */
    function add_settings_meta_boxes($page){
    
        // post id
        $post_id = $page;
        
        // add meta boxes
        $this->add_meta_boxes($post_id, $page);
        
    }
    
    /*
     * Attachment List
     */
    function add_attachments_meta_boxes(){
    
        // post id
        $post_id = "attachment_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
        
    }
    
    /*
     * User List
     */
    function add_users_meta_boxes(){
    
        // post id
        $post_id = "user_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
        
    }
    
    function render_clean_metabox($post, $metabox){
        
        $post_id = $metabox['args']['post_id'];
        
        ?>
        <a href="<?php echo add_query_arg(array('acfe_dev_clean' => $post_id, 'acfe_dev_clean_nonce' => wp_create_nonce('acfe_dev_clean'))); ?>" class="button acf-button">
            Clean orphan meta
        </a>
        <?php
        
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
    
            acf_enable_filter('acfe/post_type_list');
            acf_enable_filter('acfe/taxonomy_list');
            acf_enable_filter('acfe/user_list');
            acf_enable_filter('acfe/attachment_list');
            
            if(empty($this->acf_meta)){
                $render_bulk = true;
            }
            
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
    
            acf_enable_filter('acfe/post_type_list');
            acf_enable_filter('acfe/taxonomy_list');
            acf_enable_filter('acfe/user_list');
            acf_enable_filter('acfe/attachment_list');
            
            if(!$render_bulk){
                $render_bulk = true;
            }
            
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
        
        $is_options = $object_type === 'acf_options_page';
        $is_acf = $table_type === 'acf';
        
        $metas = $is_acf ? $this->acf_meta : $this->wp_meta;
        
        ?>
        <table class="wp-list-table widefat fixed striped">
        
            <thead>
                <tr>
                    
                    <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                        <td scope="col" class="check-column"><input type="checkbox" /></td>
                    <?php } ?>
                    
                    <th scope="col" class="col-name">Name</th>
                    <th scope="col" class="col-value">Value</th>
                    
                    <?php if($is_acf){ ?>
                        <th scope="col" class="col-field-type">Field Type</th>
                        <th scope="col" class="col-field-group">Field group</th>
                    <?php } ?>
                    
                    <?php if($is_options){ ?>
                        <th scope="col" class="col-autoload">Autoload</th>
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
                    
                    // ACF
                    $field_type = acf_maybe_get($meta, 'field_type');
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
        
        // Default: String
        $return = '<pre>' . print_r($value, true) . '</pre>';
        
        // Empty
        if(empty($value) && !is_numeric($value)){
    
            $css = 'color:#aaa;';
            $value = '(' . __('empty', 'acf') . ')';
    
            $return = '<pre style="' . $css . '">' . print_r($value, true) . '</pre>';
            
        }
        
        // Serialized
        elseif(is_serialized($value)){
            
            $return = '<pre>' . print_r(maybe_unserialize($value), true) . '</pre>';
            $return .= '<pre class="raw">' . print_r($value, true) . '</pre>';
            
        }
        
        // HTML
        elseif($value != strip_tags($value)){
            
            $return = '<pre>' . print_r(htmlentities($value), true) . '</pre>';
            
        }
        
        // Json
        elseif(acfe_is_json($value)){
            
            $return = '<pre>' . print_r(json_decode($value), true) . '</pre>';
            $return .= '<pre class="raw">' . print_r($value, true) . '</pre>';
            
        }
        
        return $return;
        
    }
    
    function get_meta($post_id = 0){
        
        // Validate post id
        $post_id = acf_get_valid_post_id($post_id);
        
        // Post id empty
        if(empty($post_id)) return;
        
        // Decode post id
        $info = acf_decode_post_id($post_id);
        
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
        
        // No meta found
        if(empty($get_meta)) return;
        
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
            
            // Bail early if no prefix found
            if(!isset($wp_meta["_$key"])) continue;
            
            // Check if key is field_abcde123456?
            if(!acf_is_field_key($wp_meta["_$key"]['value'])) continue;
            
            // Vars
            $field_key = $wp_meta["_$key"]['value'];
            $field_type = '<em>Undefined</em>';
            $field_group_title = '<em>Undefined</em>';
            
            // Get field
            $field = acf_get_field($field_key);
    
            // Check clone in sub field: field_123456abcdef_field_123456abcfed
            if(!$field && substr_count($field_key, 'field_') > 1){
                
                // get field key (last key)
                $_field_key = substr($field_key, strrpos($field_key, 'field_'));
                
                // get field
                $field = acf_get_field($_field_key);
                
            }
            
            // Found field
            if($field){
                
                // Field type
                $field_type = acf_get_field_type($field['type']);
                $field_type = acfe_maybe_get($field_type, 'label', '<em>Undefined</em>');
                
                // Field Group
                $field_group = acfe_get_field_group_from_field($field);
                
                if($field_group){
    
                    $field_group_title = $field_group['title'];
                    
                    // no id setting, try to get raw field group from db
                    if(!$field_group['ID']){
                        $field_group = acf_get_raw_field_group($field_group['key']);
                    }
                    
                    // found db field group
                    if($field_group && $field_group['ID']){
                        
                        $post_status = get_post_status($field_group['ID']);
    
                        if($post_status === 'publish' || $post_status === 'acf-disabled'){
    
                            $field_group_title = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
        
                        }
    
                    }

                }
                
            }
            
            // Assign ACF meta: prefix
            $_meta = $wp_meta["_$key"];
            $_meta['field_type'] = $field_type;
            $_meta['field_group'] = $field_group_title;
            
            $acf_meta[] = $_meta;
            
            // Assign ACF meta: normal
            $_meta = $wp_meta[$key];
            $_meta['field_type'] = $field_type;
            $_meta['field_group'] = $field_group_title;
            
            $acf_meta[] = $_meta;

            // Unset WP Meta
            unset($wp_meta["_$key"]);
            unset($wp_meta[$key]);
            
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
        if(!$id || !$key || !$type){
            wp_die(0);
        }
        
        // Check referer
        check_ajax_referer("acfe_delete_meta_$id");
    
        if(!current_user_can(acf_get_setting('capability'))){
            wp_die(-1);
        }
    
        // Delete option
        if($type === 'option'){
            
            if(delete_option($key)){
                wp_die(1);
            }
            
        // Delete meta
        }else{
            
            if(delete_metadata_by_mid($type, $id)){
                wp_die(1);
            }
            
        }
    
        wp_die(0);
        
    }
    
    function ajax_bulk_delete_meta(){
        
        // Vars
        $ids = acf_maybe_get_POST('ids');
        $type = acf_maybe_get_POST('type');
        
        // Check vars
        if(!$ids || !$type){
            wp_die(0);
        }
        
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
        
        }
    
        // Delete meta
        foreach($ids as $id){
            delete_metadata_by_mid($type, $id);
        }
    
        wp_die(1);
        
    }
    
}

acf_new_instance('acfe_dev');

endif;

function acfe_dev_get_wp_meta(){
    return acf_get_instance('acfe_dev')->wp_meta;
}

function acfe_dev_get_acf_meta(){
    return acf_get_instance('acfe_dev')->acf_meta;
}

function acfe_dev_count_meta(){
    return count(acfe_dev_get_wp_meta()) + count(acfe_dev_get_acf_meta());
}