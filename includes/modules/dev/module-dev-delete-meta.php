<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dev_delete_meta')):

class acfe_dev_delete_meta{
    
    /**
     * construct
     */
    function __construct(){
    
        // check settings
        if((!acfe_is_dev() && !acfe_is_super_dev()) || !acf_current_user_can_admin()){
            return;
        }
        
        // table
        add_filter('acfe/dev/meta/row_actions',           array($this, 'meta_row_actions'), 10, 3);
        add_action('acfe/dev/meta/after_table',           array($this, 'meta_after_table'));
        
        // ajax
        add_action('wp_ajax_acfe/dev/single_delete_meta', array($this, 'ajax_delete_single_meta'));
        add_action('wp_ajax_acfe/dev/bulk_delete_meta',   array($this, 'ajax_delete_bulk_meta'));
        
    }
    
    
    /**
     * meta_row_actions
     *
     * acfe/dev/meta/row_actions
     *
     * @param $row_actions
     * @param $meta
     * @param $args
     *
     * @return mixed
     */
    function meta_row_actions($row_actions, $meta, $args){
        
        // check permission
        if(!current_user_can(acf_get_setting('capability'))){
            return $row_actions;
        }
        
        // delete link
        $delete = array(
            'href'           => '#',
            'class'          => 'acfe-dev-delete-meta',
            'data-meta-id'   => $meta['id'],
            'data-meta-key'  => $meta['key'],
            'data-meta-type' => $meta['type'],
            'data-nonce'     => wp_create_nonce("acfe-dev-delete-meta-{$meta['id']}"),
        );
        
        $row_actions['delete'] = '<a ' . acf_esc_atts($delete). '>' . __('Delete') . '</a>';
        
        // return
        return $row_actions;
        
    }
    
    
    /**
     * meta_after_table
     *
     * acfe/dev/meta/after_table
     *
     * @param $args
     */
    function meta_after_table($args){
        
        // bail early
        if(!current_user_can(acf_get_setting('capability')) || !$args['bulk']){
            return;
        }
        
        ?>
        <div class="acfe-dev-bulk tablenav bottom">

            <div class="alignleft actions bulkactions">

                <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e('Select bulk action'); ?></label>
                <input type="hidden" class="acfe-dev-bulk-meta-type" value="<?php echo $args['type']; ?>" />
                <input type="hidden" class="acfe-dev-bulk-nonce" value="<?php echo wp_create_nonce('acfe-dev-bulk'); ?>" />

                <select class="acfe-dev-bulk-action">
                    <option value="-1"><?php _e('Bulk Actions'); ?></option>
                    <option value="delete"><?php _e('Delete'); ?></option>
                </select>

                <input type="submit" class="button action" value="<?php _e('Apply'); ?>">

            </div>

            <br class="clear">

        </div>
        <?php
    }
    
    
    /**
     * ajax_delete_single_meta
     *
     * wp_ajax_acfe/delete_meta
     */
    function ajax_delete_single_meta(){
        
        // vars
        $id = acf_maybe_get_POST('id');
        $key = acf_maybe_get_POST('key');
        $type = acf_maybe_get_POST('type');
        
        // check vars
        if(!$id || !$key || !$type){
            wp_die(0);
        }
        
        // check referer
        check_ajax_referer("acfe-dev-delete-meta-{$id}");
        
        // check permission
        if(!current_user_can(acf_get_setting('capability'))){
            wp_die(-1);
        }
    
        // delete option
        if($type === 'option'){
    
            global $wpdb;
    
            // retrieve option from option_id
            $row = $wpdb->get_row($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_id = %d LIMIT 1", $id));
            
            if($row){
                
                if(delete_option($row->option_name)){
                    wp_die(1);
                }
                
            }
    
            wp_die(0);
            
        }
    
        // delete by meta type
        if(delete_metadata_by_mid($type, $id)){
            wp_die(1);
        }
    
        wp_die(0);
        
    }
    
    
    /**
     * ajax_delete_bulk_meta
     *
     * wp_ajax_acfe/bulk_delete_meta
     */
    function ajax_delete_bulk_meta(){
        
        // vars
        $ids = acf_maybe_get_POST('ids');
        $type = acf_maybe_get_POST('type');
        
        // check vars
        if(!$ids || !$type){
            wp_die(0);
        }
        
        // check referer
        check_ajax_referer('acfe-dev-bulk');
        
        // check permission
        if(!current_user_can(acf_get_setting('capability'))){
            wp_die(-1);
        }
    
        // delete option
        if($type === 'option'){
            
            global $wpdb;
            
            foreach($ids as $id){
                
                // retrieve option from option_id
                $row = $wpdb->get_row($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_id = %d LIMIT 1", $id));
                
                if($row){
                    delete_option($row->option_name);
                }
                
            }
            
            wp_die(1);
        
        }
    
        // delete by meta type
        foreach($ids as $id){
            delete_metadata_by_mid($type, $id);
        }
    
        wp_die(1);
        
    }
    
}

acf_new_instance('acfe_dev_delete_meta');

endif;