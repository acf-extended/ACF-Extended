<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_location_taxonomy_list')):

class acfe_location_taxonomy_list{
    
    // vars
    var $post_id;
    var $taxonomy;
    var $field_groups = array();
    
    function __construct(){
        
        // load
        add_action('acfe/load_terms',                           array($this, 'load_terms'));
        
        // locations
        add_filter('acf/location/rule_types',                   array($this, 'rule_types'));
        add_filter('acf/location/rule_values/taxonomy_list',    array($this, 'rule_values'));
        add_filter('acf/location/rule_match/taxonomy_list',     array($this, 'rule_match'), 10, 3);
        
    }
    
    function load_terms($taxonomy){
        
        // bail early if restricted
        if(acfe_is_taxonomy_reserved($taxonomy)){
            return;
        }
        
        // vars
        $this->taxonomy = $taxonomy;
        $this->post_id = acf_get_valid_post_id("tax_{$this->taxonomy}_options");
        
        // Submit
        if(acf_verify_nonce('taxonomy_list')){
            
            // Validate
            if(acf_validate_save_post(true)){
            
                // Autoload
                acf_update_setting('autoload', false);

                // Save
                acf_save_post($this->post_id);
                
                // Redirect
                wp_redirect(add_query_arg(array('message' => 'taxonomy_list')));
                exit;
            
            }
        
        }
        
        // Success message
        if(isset($_GET['message']) && $_GET['message'] === 'taxonomy_list'){
            
            $object = get_taxonomy($this->taxonomy);
            
            acf_add_admin_notice($object->label . ' List Saved.', 'success');
            
        }
    
        // enqueue
        acf_enqueue_scripts(array(
            'uploader'	=> true,
        ));
    
        // get field groups
        $this->field_groups = acf_get_field_groups(array(
            'taxonomy_list' => $this->taxonomy
        ));
        
        // validate
        if(empty($this->field_groups)){
            return;
        }
    
        // enable filter
        acf_enable_filter('acfe/taxonomy_list');
    
        // hooks
        add_action('acfe/add_terms_meta_boxes', array($this, 'add_terms_meta_boxes'));
        
    }
    
    function add_terms_meta_boxes(){
    
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
                acf_enable_filter('acfe/taxonomy_list/side');
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
                'screen'    => 'taxonomy_list',
                'post_id'   => $this->post_id,
            ));
            
        }
        
        // render fields
        $fields = acf_get_fields($field_group);
        
        acf_render_fields($fields, $this->post_id, 'div', $field_group['instruction_placement']);
    
        // do not show submit if there is already a submitdiv
        if($field_group['position'] === 'side' && acf_is_filter_enabled('acfe/taxonomy_list/submitdiv')){
            return;
        }
        
        // last metabox
        if($index === $total){
            
            $atts = array(
                'id' => ($field_group['style'] === 'seamless' ? '' : 'major-publishing-actions'),
                'style' => ($field_group['style'] === 'seamless' ? 'padding:0 12px;' : ''),
            );
            
            ?>
            <div <?php echo acf_esc_attrs($atts); ?>>

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
    
    function rule_types($choices){
        
        $name = __('Forms', 'acf');
        
        $choices[$name] = acfe_array_insert_after('taxonomy', $choices[$name], 'taxonomy_list', __('Taxonomy List'));

        return $choices;
        
    }
    
    function rule_values($choices){
        
        $choices = array('all' => __('All', 'acf'));
        $choices = array_merge($choices, acf_get_taxonomy_labels());
        
        return $choices;
        
    }
    
    function rule_match($match, $rule, $screen){
        
        if(!acf_maybe_get($screen, 'taxonomy_list') || !acf_maybe_get($rule, 'value'))
            return $match;
        
        $match = ($screen['taxonomy_list'] === $rule['value']);
        
        if($rule['value'] === 'all')
            $match = true;
        
        if($rule['operator'] === '!=')
            $match = !$match;
        
        return $match;

    }
    
}

new acfe_location_taxonomy_list();

endif;