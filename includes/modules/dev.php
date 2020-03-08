<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acfe_is_dev() && !acfe_is_super_dev())
    return;

if(!class_exists('acfe_dev')):

class acfe_dev{
    
    public $wp_meta = array();
    public $acf_meta = array();
    
	function __construct(){
        
        // Script debug
        if(!defined('SCRIPT_DEBUG'))
            define('SCRIPT_DEBUG', true);
        
        // Post
        add_action('load-post.php',		array($this, 'load_post'));
		add_action('load-post-new.php',	array($this, 'load_post'));
        
        // Term
        add_action('load-term.php',     array($this, 'load_term'));
        
        // User
        add_action('show_user_profile', array($this, 'load_user'));
		add_action('edit_user_profile', array($this, 'load_user'));
        
        // Admin
        add_action('acf/options_page/submitbox_before_major_actions', array($this, 'load_admin'));
        
	}
    
    /*
     * Post
     */
    function load_post(){
        
        global $typenow;
        
        $post_type = $typenow;
        
        // Remove WP post meta box
        remove_meta_box('postcustom', false, 'normal');
        
        if(!acfe_is_super_dev()){
        
            $restricted = array('acf-field-group', 'acfe-dbt', 'acfe-dop', 'acfe-dpt', 'acfe-dt', 'acfe-form', 'acfe-template');
            
            if(in_array($post_type, $restricted))
                return;
        
        }
        
        // actions
        add_action('add_meta_boxes', array($this, 'add_post_meta_boxes'), 10, 2);
        
    }
    
    function add_post_meta_boxes($post_type, $post){
        
        // Add Meta Boxes
        $this->add_meta_boxes(0, $post_type);
        
    }
    
    /*
     * Term
     */
    function load_term(){
        
        $screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
        
        // actions
        add_action("{$taxonomy}_edit_form", array($this, 'edit_term'), 20, 2);
        
    }
    
    function edit_term($term, $taxonomy){
        
        // Get Term ID
        $post_id = acf_get_term_post_id($term->taxonomy, $term->term_id);
        
        // Add Meta Boxes
        $this->add_meta_boxes($post_id, 'edit-term');
        
        // Poststuff
        echo '<div id="poststuff">';
        
            do_meta_boxes('edit-term', 'normal', array());
            
        echo '</div>';
        
    }
    
    /*
     * User
     */
    function load_user(){
        
        // Get User ID
        global $user_id;
        $user_id = (int) $user_id;
        
        if(empty($user_id))
            return;
        
        // Add Meta Boxes
        $this->add_meta_boxes('user_' . $user_id, 'edit-user');
        
        // Poststuff
        echo '<div id="poststuff">';
        
            do_meta_boxes('edit-user', 'normal', array());
            
        echo '</div>';
        
    }
    
    /*
     * Admin
     */
    function load_admin($page){
        
        $this->add_meta_boxes($page['post_id'], 'acf_options_page');
        
    }
    
    /*
     * Add Meta Boxes
     */
    function add_meta_boxes($post_id = 0, $object_type){
        
        // Get Meta
        $this->get_meta($post_id);
        
        // WP Metabox
        if(!empty($this->wp_meta)){
            
            $id = 'acfe-wp-custom-fields';
            $title = 'WP Custom fields <span style="background: #72777c;padding: 1px 5px;border-radius: 4px;color: #fff;margin-left: 3px;font-size: 12px;">' . count($this->wp_meta) . '</span>';
            $context = 'normal';
            $priority = 'low';
            
            add_meta_box($id, $title, array($this, 'wp_render_meta_box'), $object_type, $context, $priority);
            
        }
        
        // ACF Metabox
        if(!empty($this->acf_meta)){
            
            $id = 'acfe-acf-custom-fields';
            $title = 'ACF Custom fields <span style="background: #72777c;padding: 1px 5px;border-radius: 4px;color: #fff;margin-left: 3px;font-size: 12px;">' . count($this->acf_meta) . '</span>';
            $context = 'normal';
            $priority = 'low';
            
            add_meta_box($id, $title, array($this, 'acf_render_meta_box'), $object_type, $context, $priority);
            
        }
        
    }

    function wp_render_meta_box($post, $metabox){
        
        ?>
        <table class="wp-list-table widefat fixed striped" style="border:0;">
        
            <thead>
                <tr>
                    <th scope="col" style="width:30%;">Name</th>
                    <th scope="col" style="width:auto;">Value</th>
                </tr>
            </thead>

            <tbody>
                
                <?php foreach($this->wp_meta as $meta_key => $meta_value){ ?>
                
                    <?php
                    $value_display = $this->render_meta_value($meta_value);
                    ?>
                
                    <tr>
                        <td><strong><?php echo esc_attr($meta_key); ?></strong></td>
                        <td><?php echo $value_display; ?></td>
                    </tr>
                    
                <?php } ?>

            </tbody>

        </table>
        <?php
        
    }
    
    function acf_render_meta_box($post, $metabox){
        
        ?>
        <table class="wp-list-table widefat fixed striped" style="border:0;">
        
            <thead>
                <tr>
                    <th scope="col" style="width:30%;">Name</th>
                    <th scope="col" style="width:auto;">Value</th>
                    <th scope="col" style="width:120px;">Field group</a></th>
                </tr>
            </thead>

            <tbody>
                
                <?php foreach($this->acf_meta as $meta){ ?>
                
                    <?php
                    
                    // Field
                    $field = $meta['field'];
                    $meta_key = $meta['key'];
                    $value = $meta['value'];
                    
                    // Field Group
                    $field_group_display = __('Local', 'acf');
                    $field_group = $meta['field_group'];
                    
                    if($field_group){
                        
                        $field_group_display = $field_group['title'];
                        
                        if(!empty($field_group['ID'])){
                            
                            $post_status = get_post_status($field_group['ID']);
                            
                            if($post_status === 'publish' || $post_status === 'acf-disabled'){
                                
                                $field_group_display = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
                                
                            }
                            
                        }
                        
                    }
                    
                    $value_display = $this->render_meta_value($meta['value']);
                    
                    ?>
                
                    <tr>
                        <td><strong><?php echo esc_attr($meta_key); ?></strong></td>
                        <td><?php echo $value_display; ?></td>
                        <td><?php echo $field_group_display; ?></td>
                    </tr>
                    
                <?php } ?>

            </tbody>

        </table>
        <?php
        
    }
    
    function render_meta_value($value){
        
        $return = '';
        
        // Serialized
        if(is_serialized($value)){
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r(maybe_unserialize($value), true) . '</pre>';
            $return .= '<pre style="max-height:200px; overflow:auto; white-space: pre; margin-top:10px;">' . print_r($value, true) . '</pre>';
            
        }
        
        // HTML
        elseif($value != strip_tags($value)){
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r(htmlentities($value), true) . '</pre>';
            
        }
        
        // Json
        elseif(acfe_is_json($value)){
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre;">' . print_r(json_decode($value), true) . '</pre>';
            $return .= '<pre style="max-height:200px; overflow:auto; white-space: pre; margin-top:10px;">' . print_r($value, true) . '</pre>';
            
        }
        
        // String
        else{
            
            $css = '';
            
            if(empty($value)){
                
                $css = 'color:#aaa;';
                $value = '(' . __('empty', 'acf') . ')';
                
            }
            
            $return = '<pre style="max-height:200px; overflow:auto; white-space: pre; ' . $css . '">' . print_r($value, true) . '</pre>';
            
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
            
            $search = str_replace('_', '\_', $search);
            $_search = str_replace('_', '\_', $_search);
            
            $get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s", $search, $_search));
            
        }
        
        if(empty($get_meta))
            return;
        
        $wp_meta = array();
        
        // Post / Term / User
        if($info['type'] !== 'option'){
            
            usort($get_meta, function($a, $b){
                return strcmp($a->meta_key, $b->meta_key);
            });
            
            foreach($get_meta as $meta){
                
                $wp_meta[$meta->meta_key] = $meta->meta_value;
                
            }
        
        // Option
        }else{
            
            usort($get_meta, function($a, $b){
                return strcmp($a->option_name, $b->option_name);
            });
            
            foreach($get_meta as $meta){
                
                $wp_meta[$meta->option_name] = $meta->option_value;
                
            }
            
        }
        
        $acf_meta = array();
        
        foreach($wp_meta as $key => $value){
            
            // ACF Meta
            if(isset($wp_meta["_$key"])){
                
                $field = false;
                $field_group = false;
                
                if(acf_is_field_key($wp_meta["_$key"])){
                    
                    $field = acf_get_field($wp_meta["_$key"]);
                    $field_group = acfe_get_field_group_from_field($field);
                    
                }
                
                $acf_meta[] = array(
                    'key'           => "_$key",
                    'value'         => $wp_meta["_$key"],
                    'field'         => $field,
                    'field_group'   => $field_group,
                );
                
                $acf_meta[] = array(
                    'key'           => $key,
                    'value'         => $wp_meta[$key],
                    'field'         => $field,
                    'field_group'   => $field_group,
                );
                
                unset($wp_meta["_$key"]);
                unset($wp_meta[$key]);
                
            }
            
        }
        
        $this->wp_meta = $wp_meta;
        $this->acf_meta = $acf_meta;
        
    }
    
}

new acfe_dev();

endif;