<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dev')):

class acfe_dev{
    
    var $wp_meta  = array(),
        $acf_meta = array(),
        $type     = '';
    
    function __construct(){
    
        // check settings
        if((!acfe_is_dev() && !acfe_is_super_dev()) || !acf_current_user_can_admin()){
            return;
        }
        
        // enqueue
        add_action('admin_enqueue_scripts',             array($this, 'admin_enqueue_scripts'));
        
        // load
        add_action('acfe/load_post',                    array($this, 'load_post'));
        
        // add meta boxes
        add_action('acfe/add_post_meta_boxes',          array($this, 'add_post_meta_boxes'), 10, 2);
        add_action('acfe/add_posts_meta_boxes',         array($this, 'add_posts_meta_boxes'));
        add_action('acfe/add_term_meta_boxes',          array($this, 'add_term_meta_boxes'), 10, 2);
        add_action('acfe/add_terms_meta_boxes',         array($this, 'add_terms_meta_boxes'));
        add_action('acfe/add_user_meta_boxes',          array($this, 'add_user_meta_boxes'));
        add_action('acfe/add_option_meta_boxes',        array($this, 'add_option_meta_boxes'));
        
        add_action('acfe/add_settings_meta_boxes',      array($this, 'add_settings_meta_boxes'));
        add_action('acfe/add_attachment_meta_boxes',    array($this, 'add_attachment_meta_boxes'));
        add_action('acfe/add_attachments_meta_boxes',   array($this, 'add_attachments_meta_boxes'));
        add_action('acfe/add_users_meta_boxes',         array($this, 'add_users_meta_boxes'));
        
        // table render
        add_filter('acfe/dev/meta/columns',             array($this, 'meta_columns'), 10, 3);
        add_action('acfe/dev/meta/render_column',       array($this, 'meta_render_column'), 10, 4);
        
    }
    
    
    /**
     * admin_enqueue_scripts
     */
    function admin_enqueue_scripts(){
        
        // enqueue acf on network screen
        if(acf_is_screen(array('profile-network', 'user-edit-network', 'user-network'))){
            acf_enqueue_scripts();
        }
        
    }
    
    
    /**
     * load_post
     *
     * acfe/load_post
     */
    function load_post(){
        
        // force remove wp post meta metabox
        remove_meta_box('postcustom', false, 'normal');
        
    }
    
    
    /**
     * add_post_meta_boxes
     *
     * acfe/add_post_meta_boxes
     *
     * @param $post_type
     * @param $post
     */
    function add_post_meta_boxes($post_type, $post){
        
        // check restricted post types
        if(acfe_is_post_type_reserved_dev($post_type)){
            return;
        }
        
        // post id
        $post_id = $post->ID;
        
        // add meta boxes
        $this->add_meta_boxes($post_id, $post_type);
    
        // action
        do_action('acfe/dev/add_meta_boxes', $post_id, $post_type, 'post');
        
    }
    
    
    /**
     * add_posts_meta_boxes
     *
     * acfe/add_posts_meta_boxes
     *
     * @param $post_type
     */
    function add_posts_meta_boxes($post_type){
        
        // post id
        $post_id = "{$post_type}_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
    
        // action
        do_action('acfe/dev/add_meta_boxes', $post_id, 'edit', 'posts');
        
    }
    
    
    /**
     * add_term_meta_boxes
     *
     * acfe/add_term_meta_boxes
     *
     * @param $taxonomy
     * @param $term
     */
    function add_term_meta_boxes($taxonomy, $term){
    
        // post id
        $post_id = "term_{$term->term_id}";
    
        // add meta boxes
        $this->add_meta_boxes($post_id, "edit-{$taxonomy}");
    
        // action
        do_action('acfe/dev/add_meta_boxes', $post_id, "edit-{$taxonomy}", 'term');
        
    }
    
    
    /**
     * add_terms_meta_boxes
     *
     * acfe/add_terms_meta_boxes
     *
     * @param $taxonomy
     */
    function add_terms_meta_boxes($taxonomy){
    
        // post id
        $post_id = "tax_{$taxonomy}_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
    
        // action
        do_action('acfe/dev/add_meta_boxes', $post_id, 'edit', 'terms');
        
    }
    
    
    /**
     * add_user_meta_boxes
     *
     * acfe/add_user_meta_boxes
     *
     * @param $user
     */
    function add_user_meta_boxes($user){
    
        // post id
        $post_id = "user_{$user->ID}";
    
        // add meta boxes
        $this->add_meta_boxes($post_id, array('profile', 'user-edit'));
    
        // action
        do_action('acfe/dev/add_meta_boxes', $post_id, array('profile', 'user-edit'), 'user');
        
    }
    
    
    /**
     * add_option_meta_boxes
     *
     * acfe/add_option_meta_boxes
     *
     * @param $page
     */
    function add_option_meta_boxes($page){
    
        // post id
        $post_id = $page['post_id'];
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'acf_options_page');
        
        // action
        do_action('acfe/dev/add_meta_boxes', $post_id, 'acf_options_page', 'option');
        
    }
    
    
    /**
     * add_settings_meta_boxes
     *
     * acfe/add_settings_meta_boxes
     *
     * @param $page
     */
    function add_settings_meta_boxes($page){
    
        // post id
        $post_id = $page;
        
        // add meta boxes
        $this->add_meta_boxes($post_id, $page);
    
        do_action('acfe/dev/add_meta_boxes', $post_id, $page, 'settings');
        
    }
    
    
    /**
     * add_attachment_meta_boxes
     *
     * acfe/add_attachment_meta_boxes
     *
     * @param $post
     */
    function add_attachment_meta_boxes($post){
        
        // vars
        $post_id = $post->ID;
        $post_type = $post->post_type;
        
        // add meta boxes
        $this->add_meta_boxes($post_id, $post_type);
    
        do_action('acfe/dev/add_meta_boxes', $post_id, $post_type, 'attachment');
        
    }
    
    
    /**
     * add_attachments_meta_boxes
     *
     * acfe/add_attachments_meta_boxes
     */
    function add_attachments_meta_boxes(){
        
        // post id
        $post_id = "attachment_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
    
        do_action('acfe/dev/add_meta_boxes', $post_id, 'edit', 'attachments');
        
    }
    
    
    /**
     * add_users_meta_boxes
     *
     * acfe/add_users_meta_boxes
     */
    function add_users_meta_boxes(){
    
        // post id
        $post_id = "user_options";
        
        // add meta boxes
        $this->add_meta_boxes($post_id, 'edit');
    
        do_action('acfe/dev/add_meta_boxes', $post_id, 'edit', 'users');
        
    }
    
    
    /**
     * add_meta_boxes
     *
     * @param $post_id
     * @param $screen
     */
    function add_meta_boxes($post_id, $screen){
        
        // setup meta
        $this->setup_meta($post_id);
        
        // do action
        // do_action('acfe/dev/add_meta_boxes', $post_id, $screen);
        
        // vars
        $bulk = false;
        $context = 'normal';
        $priority = 'low';
        
        // wp meta
        if(!empty($this->wp_meta)){
            
            // display metabox on object lists screen
            acf_set_filters(array(
                'acfe/post_type_list'  => true,
                'acfe/taxonomy_list'   => true,
                'acfe/user_list'       => true,
                'acfe/attachment_list' => true,
            ));
            
            if(empty($this->acf_meta)){
                $bulk = true;
            }
            
            // vars
            $id = 'acfe-wp-custom-fields';
            $title = $this->type === 'option' ? __('WP Options Meta', 'acfe') : __('WP Custom Fields', 'acfe');
            $title .= '<span class="acfe-dev-meta-count">' . count($this->wp_meta) . '</span>';
            
            add_meta_box($id, $title, array($this, 'render_meta_box'), $screen, $context, $priority, array('table' => 'wp_meta', 'type' => $this->type, 'bulk' => $bulk));
            
        }
        
        // acf meta
        if(!empty($this->acf_meta)){
    
            // display metabox on object lists screen
            acf_set_filters(array(
                'acfe/post_type_list'  => true,
                'acfe/taxonomy_list'   => true,
                'acfe/user_list'       => true,
                'acfe/attachment_list' => true,
            ));
            
            if(!$bulk){
                $bulk = true;
            }
            
            $id = 'acfe-acf-custom-fields';
            $title = $this->type === 'option' ? __('ACF Options Meta', 'acfe') : __('ACF Custom Fields', 'acfe');
            $title .= '<span class="acfe-dev-meta-count">' . count($this->acf_meta) . '</span>';
            
            add_meta_box($id, $title, array($this, 'render_meta_box'), $screen, $context, $priority, array('table' => 'acf_meta', 'type' => $this->type, 'bulk' => $bulk));
            
        }
        
    }
    
    
    /**
     * render_meta_box
     *
     * @param $post
     * @param $metabox
     */
    function render_meta_box($post, $metabox){
        
        //vars
        $args = $metabox['args'];
        $columns = apply_filters('acfe/dev/meta/columns', array(), $args);
        
        ?>
        <table class="wp-list-table widefat fixed striped">
        
            <thead>
                <tr>
                    
                    <?php foreach($columns as $column_name => $column_label): ?>
                    
                        <?php
                        $el = $column_name === 'checkbox' ? 'td' : 'th';
                        $class = $column_name === 'checkbox' ? 'check-column' : "col-{$column_name}";
                        
                        echo "<{$el} scope='col' class='{$class}'>{$column_label}</{$el}>";
                        ?>
                    
                    <?php endforeach; ?>
                    
                </tr>
            </thead>

            <tbody>
                
                <?php foreach($this->{$args['table']} as $meta): ?>
                
                    <tr>
    
                        <?php foreach($columns as $column_name => $column_label): ?>
                            
                            <?php
                            $el = $column_name === 'checkbox' ? 'th' : 'td';
                            $attrs = $column_name === 'checkbox' ? array('scope' => 'row', 'class' => 'check-column') : array('class' => "col-{$column_name}");
        
                            echo "<{$el} " . acf_esc_atts($attrs) . ">";
                                do_action('acfe/dev/meta/render_column', $column_name, $meta, $args);
                            echo "</{$el}>";
                            ?>
                        
                        <?php endforeach; ?>
                        
                    </tr>
                    
                <?php endforeach; ?>

            </tbody>

        </table>

        <?php do_action('acfe/dev/meta/after_table', $args); ?>
        <script type="text/javascript">
        if(typeof acf !== 'undefined'){
            acf.newPostbox(<?php echo json_encode(array('id' => $metabox['id'])); ?>);
        }
        </script>
        <?php
        
    }
    
    
    /**
     * meta_columns
     *
     * acfe/dev/meta/columns
     *
     * @param $columns
     * @param $args
     *
     * @return array|string[]
     */
    function meta_columns($columns, $args){
        
        $columns = array(
            'name' => __('Name', 'acfe'),
            'value' => __('Value', 'acfe'),
        );
        
        if(current_user_can(acf_get_setting('capability'))){
            $columns = array_merge(array('checkbox' => '<input type="checkbox" />'), $columns);
        }
        
        if($args['table'] === 'acf_meta'){
            $columns['field-type'] = __('Field Type', 'acf');
            $columns['field-group'] = __('Field Group', 'acf');
        }
        
        if($args['type'] === 'option'){
            $columns['autoload'] = __('Autoload', 'acfe');
        }
        
        return $columns;
        
    }
    
    
    /**
     * meta_render_column
     *
     * acfe/dev/meta/render_column
     *
     * @param $column_name
     * @param $meta
     * @param $args
     */
    function meta_render_column($column_name, $meta, $args){
        
        switch($column_name){
            
            case 'checkbox': {
                
                ?>
                <input type="checkbox" class="acfe-dev-bulk-checkbox" value="<?php echo $meta['id']; ?>" />
                <?php
                break;
                
            }
            
            case 'name': {
                
                ?>
                <strong><?php echo esc_attr($meta['key']); ?></strong>
                <?php
        
                $row_actions = apply_filters('acfe/dev/meta/row_actions', array(), $meta, $args);
        
                if($row_actions){
            
                    echo '<div class="row-actions">';
            
                    echo implode(' | ', array_map(function($action_name, $action){
                        return "<span class='{$action_name}'>{$action}</span>";
                    }, array_keys($row_actions), $row_actions));
            
                    echo '</div>';
            
                }
                
                break;
            
            }
        
            case 'value': {
                
                echo $this->render_meta_value($meta['value']);
                break;
            
            }
        
            case 'field-type': {
        
                echo acf_maybe_get($meta, 'field_type');
                break;
            
            }
        
            case 'field-group': {
        
                echo acf_maybe_get($meta, 'field_group');
                break;
            
            }
        
            case 'autoload': {
        
                echo $meta['autoload'];
                break;
            
            }
            
        }
        
    }
    
    
    /**
     * render_meta_value
     *
     * @param $value
     *
     * @return string
     */
    function render_meta_value($value){
        
        // raw
        $raw = map_deep($value, '_wp_specialchars');
        
        // string (default)
        $return = '<pre>' . print_r($raw, true) . '</pre>';
        
        // empty value
        if(acf_is_empty($value)){
    
            $return = '<pre style="color:#aaa;">(' . __('empty', 'acf') . ')</pre>';
            
        // serialized value
        }elseif(is_serialized($value)){
            
            $value = maybe_unserialize($value);
            
            if(is_object($value) && is_a($value, '__PHP_Incomplete_Class')){
                // do nothing
            }else{
                $value = @map_deep($value, '_wp_specialchars');
            }
            
            $return = '<pre>' . print_r($value, true) . '</pre>';
            $return .= '<pre class="raw">' . print_r($raw, true) . '</pre>';
            
        // html value
        }elseif(acfe_is_html($value)){
            
            $return = '<pre>' . print_r($raw, true) . '</pre>';
            
        // json value
        }elseif(acfe_is_json($value)){
            
            $value = json_decode($value);
            $value = @map_deep($value, '_wp_specialchars');
            
            $return = '<pre>' . print_r($value, true) . '</pre>';
            $return .= '<pre class="raw">' . print_r($raw, true) . '</pre>';
            
        }
        
        // return
        return $return;
        
    }
    
    
    /**
     * setup_meta
     *
     * @param $post_id
     */
    function setup_meta($post_id = 0){
        
        // validate
        $post_id = acf_get_valid_post_id($post_id);
        
        // bail early
        if(empty($post_id)){
            return;
        }
        
        // extract decoded post_id
        // $id
        // $type
        extract(acf_decode_post_id($post_id));
        
        // set global type
        $this->type = $type;
        
        // get meta
        $all_meta = $this->get_meta($id, $type);
        $wp_meta = $this->sort_meta($all_meta, $type);
        
        // bail early
        if(empty($wp_meta)){
            return;
        }
    
        // vars
        $acf_meta = array();
        
        // loop to prepare acf_meta
        foreach($wp_meta as $key => $meta){
            
            $ref = false;
            $ref_found = false;
            
            // no prefix, so not acf meta
            if(isset($wp_meta["_$key"])){
                $ref = $wp_meta["_$key"];
                $ref_found = true;
            }
            
            // filters
            $ref = apply_filters('acfe/dev/meta_ref', $ref, $wp_meta, $type, $key, $id, $post_id);
            
            if(!$ref){
                continue;
            }
            
            // check if key is field_abcde123456
            if(!acf_is_field_key($ref['value'])){
                continue;
            }
            
            // vars
            $field_key = $ref['value'];
            $field_type = '<em>' . __('Undefined', 'acfe') . '</em>';
            $field_group_title = '<em>' . __('Undefined', 'acfe') . '</em>';
            
            // get field
            $field = acf_get_field($field_key);
    
            // check clone in sub field: field_123456abcdef_field_123456abcfed
            if(!$field && substr_count($field_key, 'field_') > 1){
                
                // get field key (last key)
                $_field_key = substr($field_key, strrpos($field_key, 'field_'));
                
                // get field
                $field = acf_get_field($_field_key);
                
            }
            
            // found field
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
            
            // assign acf meta: prefix
            if($ref_found){
    
                unset($wp_meta["_$key"]);
    
                $_meta = $ref;
                $_meta['field_type'] = $field_type;
                $_meta['field_group'] = $field_group_title;
    
                $acf_meta[] = $_meta;
                
            }
            
            // assign acf meta: normal
            $_meta = $wp_meta[ $key ];
            $_meta['field_type'] = $field_type;
            $_meta['field_group'] = $field_group_title;
            
            $acf_meta[] = $_meta;

            // unset wp meta
            unset($wp_meta[ $key ]);
            
        }
        
        // assign global
        $this->wp_meta = $wp_meta;
        $this->acf_meta = $acf_meta;
        
    }
    
    
    /**
     * get_meta
     *
     * @param $id
     * @param $type
     *
     * @return array|object|stdClass|null
     */
    function get_meta($id, $type){
    
        global $wpdb;
        
        $all_meta = null;
    
        switch($type){
        
            case 'post': {
            
                $all_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE `post_id` = %d ", $id));
                break;
            
            }
        
            case 'term': {
            
                $all_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->termmeta} WHERE `term_id` = %d ", $id));
                break;
            
            }
        
            case 'user': {
            
                $all_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->usermeta} WHERE `user_id` = %d ", $id));
                break;
            
            }
        
            case 'option': {
            
                $search_ = $wpdb->esc_like("{$id}_") . '%';
                $_search_ = $wpdb->esc_like("_{$id}_") . '%';
            
                $all_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE `option_name` LIKE %s OR `option_name` LIKE %s OR `option_name` = %s", $search_, $_search_, $id));
                break;
            
            }
        
        }
        
        return $all_meta;
        
    }
    
    
    /**
     * sort_meta
     *
     * @param $all_meta
     * @param $type
     *
     * @return array|false
     */
    function sort_meta($all_meta, $type){
        
        if(empty($all_meta)){
            return false;
        }
    
        $wp_meta = array();
    
        // re-order
        switch($type){
        
            case 'post':
            case 'term': {
            
                usort($all_meta, function($a, $b){
                    return strcmp($a->meta_key, $b->meta_key);
                });
            
                foreach($all_meta as $meta){
                
                    $wp_meta[ $meta->meta_key ] = array(
                        'id'    => $meta->meta_id,
                        'key'   => $meta->meta_key,
                        'value' => $meta->meta_value,
                        'type'  => $type,
                    );
                
                }
            
                break;
            
            }
        
            case 'user': {
            
                usort($all_meta, function($a, $b){
                    return strcmp($a->meta_key, $b->meta_key);
                });
            
                foreach($all_meta as $meta){
                
                    $wp_meta[ $meta->meta_key ] = array(
                        'id'    => $meta->umeta_id,
                        'key'   => $meta->meta_key,
                        'value' => $meta->meta_value,
                        'type'  => $type,
                    );
                
                }
            
                break;
            
            }
        
            case 'option': {
            
                usort($all_meta, function($a, $b){
                    return strcmp($a->option_name, $b->option_name);
                });
            
                foreach($all_meta as $meta){
                
                    $wp_meta[$meta->option_name] = array(
                        'id'        => $meta->option_id,
                        'key'       => $meta->option_name,
                        'value'     => $meta->option_value,
                        'autoload'  => $meta->autoload,
                        'type'      => $type,
                    );
                
                }
            
                break;
            
            }
        
        }
        
        return $wp_meta;
        
    }
    
}

acf_new_instance('acfe_dev');

endif;

/**
 * acfe_dev_get_wp_meta
 *
 * @return mixed
 */
function acfe_dev_get_wp_meta(){
    return acf_get_instance('acfe_dev')->wp_meta;
}


/**
 * acfe_dev_get_acf_meta
 *
 * @return mixed
 */
function acfe_dev_get_acf_meta(){
    return acf_get_instance('acfe_dev')->acf_meta;
}


/**
 * acfe_dev_count_meta
 *
 * @return int
 */
function acfe_dev_count_meta(){
    return count(acfe_dev_get_wp_meta()) + count(acfe_dev_get_acf_meta());
}