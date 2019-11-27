<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if((!acf_get_setting('acfe/dev') && (!defined('ACFE_dev') || !ACFE_dev)) && (!acf_get_setting('acfe/super_dev') && (!defined('ACFE_super_dev') || !ACFE_super_dev)))
    return;

if(!class_exists('acfe_dev')):

class acfe_dev{
    
    public $wp_meta = array();
    public $acf_meta = array();
    
    public $is_super_dev = false;
    
	function __construct(){
        
        // Script debug
        if(!defined('SCRIPT_DEBUG'))
            define('SCRIPT_DEBUG', true);
        
        if(acf_get_setting('acfe/super_dev', false) || (defined('ACFE_super_dev') && ACFE_super_dev))
            $this->is_super_dev = true;
        
        add_action('load-post.php',		array($this, 'load_post'));
		add_action('load-post-new.php',	array($this, 'load_post'));
        
        add_action('load-term.php',     array($this, 'load_term'));
        
	}

    function load_post(){
        
        global $typenow;
        
        $post_type = $typenow;
        
        // Remove WP post meta box
        remove_meta_box('postcustom', false, 'normal');
        
        if(!$this->is_super_dev){
        
            $restricted = array('acfe-dbt', 'acfe-dop', 'acfe-dpt', 'acfe-dt', 'acfe-form');
            
            if(in_array($post_type, $restricted))
                return;
        
        }
        
        // actions
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
        
    }
    
    function load_term(){
        
        $screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
        
        // actions
        add_action("{$taxonomy}_edit_form", array($this, 'edit_term'), 20, 2);
        
    }
    
    function edit_term($term, $taxonomy){
        
        $post_id = acf_get_term_post_id($term->taxonomy, $term->term_id);
        
        $this->get_meta($post_id);
        
        if(!empty($this->wp_meta)){
            
            add_meta_box('acfe-wp-custom-fields', 'WP Custom fields', array($this, 'wp_render_meta_box'), 'edit-term', 'normal', 'low');
            
        }
        
        if(!empty($this->acf_meta)){
            
            add_meta_box('acfe-acf-custom-fields', 'ACF Custom fields', array($this, 'acf_render_meta_box'), 'edit-term', 'normal', 'low');
            
        }
        
        echo '<div id="poststuff">';
        
            do_meta_boxes('edit-term', 'normal', array());
            
        echo '</div>';
        
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
        
        usort($get_meta, function($a, $b){
            return strcmp($a->meta_key, $b->meta_key);
        });
        
        if(empty($get_meta))
            return;
        
        $wp_meta = array();
        
        foreach($get_meta as $meta){
            
            $wp_meta[$meta->meta_key] = $meta->meta_value;
            
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

    function add_meta_boxes($post_type, $post){
        
        $this->get_meta();
        
        if(!empty($this->wp_meta)){
            
            add_meta_box('acfe-wp-custom-fields', 'WP Custom fields <span style="background: #72777c;padding: 1px 5px;border-radius: 4px;color: #fff;margin-left: 3px;font-size: 12px;">'.count($this->wp_meta).'</span>', array($this, 'wp_render_meta_box'), $post_type, 'normal', 'low');
            
        }
        
        if(!empty($this->acf_meta)){
            
            add_meta_box('acfe-acf-custom-fields', 'ACF Custom fields <span style="background: #72777c;padding: 1px 5px;border-radius: 4px;color: #fff;margin-left: 3px;font-size: 12px;">'.count($this->acf_meta).'</span>', array($this, 'acf_render_meta_box'), $post_type, 'normal', 'low');
            
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
                    $meta_key = $meta['key'];
                    $value = $meta['value'];
                    
                    $field = $meta['field'];
                    $field_group = $meta['field_group'];
                    $field_group_display = '<span style="color:#aaa;">' . __('Unknown', 'acf') . '</span>';
                    
                    if($field_group){
                        
                        $field_group_display = $field_group['title'];
                        
                        if(!empty($field_group['ID'])){
                            
                            $post_status = get_post_status($field_group['ID']);
                            
                            if($post_status === 'publish')
                                $field_group_display = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
                            
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
    
}

new acfe_dev();

endif;