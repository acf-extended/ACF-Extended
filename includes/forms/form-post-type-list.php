<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_post_type_list')):

class acfe_form_post_type_list{
    
    // vars
    var $post_id;
    var $post_type;
    var $field_groups = array();

    /**
     * construct
     */
    function __construct(){

        add_action('acfe/load_posts', array($this, 'load_posts'));
        
    }


    /**
     * load_posts
     *
     * @param $post_type
     *
     * @return void
     */
    function load_posts($post_type){
        
        // bail early if restricted
        if(acfe_is_post_type_reserved($post_type)){
            return;
        }
        
        // vars
        $this->post_type = $post_type;
        $this->post_id = acf_get_valid_post_id("{$this->post_type}_options");
        
        // submit
        if(acf_verify_nonce('post_type_list')){
            
            // Validate
            if(acf_validate_save_post(true)){
            
                // Autoload
                acf_update_setting('autoload', false);

                // Save
                acf_save_post($this->post_id);
                
                // Redirect
                wp_redirect(add_query_arg(array('message' => 'post_type_list')));
                exit;
            
            }
        
        }
        
        // success message
        if(acf_maybe_get_GET('message') === 'post_type_list'){
            
            $object = get_post_type_object($this->post_type);
            
            acf_add_admin_notice($object->label . ' List Saved.', 'success');
            
        }
    
        // enqueue
        acf_enqueue_scripts(array(
            'uploader'	=> true,
        ));
        
        // get field groups
        $this->field_groups = acf_get_field_groups(array(
            'post_type_list' => $this->post_type
        ));
        
        // validate
        if(empty($this->field_groups)){
            return;
        }
    
        // enable filter
        acf_enable_filter('acfe/post_type_list');
        
        // hooks
        add_action('acfe/add_posts_meta_boxes', array($this, 'add_posts_meta_boxes'));
        
    }


    /**
     * add_posts_meta_boxes
     *
     * @return void
     */
    function add_posts_meta_boxes(){
    
        // Storage for localized postboxes.
        $postboxes = array();
        $field_groups = array();
    
        // merge field groups with their position
        foreach($this->field_groups as $field_group){
            $field_groups[ $field_group['position'] ][] = $field_group;
        }
    
        // loop
        foreach($field_groups as $position => $_field_groups){
        
            $i = 0;
            $total = count($_field_groups) - 1;
    
            // enable sidebar
            if($position === 'side'){
                acf_enable_filter('acfe/post_type_list/side');
            }
        
            foreach($_field_groups as $field_group){
            
                // vars
                $id = "acf-{$field_group['key']}";      // acf-group_123
                $title = $field_group['title'];         // Group 1
                $context = $field_group['position'];    // normal, side, acf_after_title
                $priority = 'high';                     // high, core, default, low
            
                // Reduce priority for sidebar metaboxes for best position.
                if($context == 'side'){
                    $priority = 'core';
                }
            
                $priority = apply_filters('acf/input/meta_box_priority', $priority, $field_group);
            
                // Localize data
                $postboxes[] = array(
                    'id'    => $id,
                    'key'   => $field_group['key'],
                    'style' => $field_group['style'],
                    'label' => $field_group['label_placement'],
                    'edit'  => acf_get_field_group_edit_link($field_group['ID'])
                );
            
                // Add the meta box.
                add_meta_box($id, acf_esc_html($title), array($this, 'render_meta_box'), 'edit', $context, $priority, array('field_group' => $field_group, 'index' => $i, 'total' => $total));
            
                $i++;
            
            }
        
        }
    
        // Localize postboxes.
        acf_localize_data(array(
            'postboxes' => $postboxes
        ));
        
    }


    /**
     * render_meta_box
     *
     * @param $post_type
     * @param $metabox
     *
     * @return void
     */
    function render_meta_box($post_type, $metabox){
    
        // vars
        $id = $metabox['id'];
        $index = $metabox['args']['index'];
        $total = $metabox['args']['total'];
        $field_group = $metabox['args']['field_group'];
        
        // first metabox
        if($index === 0){
            
            // Set form data
            acf_form_data(array(
                'screen'    => 'post_type_list',
                'post_id'   => $this->post_id,
            ));
            
        }
    
        // render fields
        $fields = acf_get_fields($field_group);
        
        acf_render_fields($fields, $this->post_id, 'div', $field_group['instruction_placement']);
        
        // do not show submit if there is already a submitdiv
        if($field_group['position'] === 'side' && acf_is_filter_enabled('acfe/post_type_list/submitdiv')){
            return;
        }
    
        // last metabox
        if($index === $total){
            
            $atts = array(
                'id' => ($field_group['style'] === 'seamless' ? '' : 'major-publishing-actions'),
                'style' => ($field_group['style'] === 'seamless' ? 'padding:0 12px;' : ''),
            );
            
            ?>
            <div <?php echo acf_esc_atts($atts); ?>>
        
                <div id="publishing-action">
            
                    <div class="acf-form-submit">
                        <span class="spinner"></span>
                        <input type="submit" class="button button-primary button-large" value="<?php _e('Update', 'acfe'); ?>" />
                    </div>
        
                </div>
                <div class="clear"></div>
    
            </div>
            <?php
            
        }
        
    }
    
}

acf_new_instance('acfe_form_post_type_list');

endif;