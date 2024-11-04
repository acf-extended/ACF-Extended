<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_performance_ultra_revisions')):

class acfe_performance_ultra_revisions{
    
    /**
     * ultra engine
     * revision meta structure (even with save as individual meta):
     *
     *  acf = array(
     *       text     = my value
     *      _text     = field_6726aa0880d0a,
     *       textarea = my value
     *      _textarea = field_6726aa0b80d0b
     *  )
     *
     * this is because acf copy post meta into revision in acf_copy_postmeta()
     * using acf_update_metadata() which use the logic in ultra_engine->pre_update_metadata()
     * 'save as individual meta' logic is disallowed in ultra_engine->pre_update_metadata()
     *
     */
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('_wp_post_revision_fields', array($this, 'revision_fields'), 10, 2);
        
    }
    
    
    /**
     * revision_fields
     *
     * @param $fields
     * @param $post
     *
     * @return mixed
     */
    function revision_fields($fields, $post = null){
        
        // validate page
        if(acf_is_screen('revision') || acf_is_ajax('get-revision-diffs')){
            
            // bail early if is restoring
            if(acf_maybe_get_GET('action') === 'restore'){
                return $fields;
            }
            
        // allow
        }else{
            
            // bail early (most likely saving a post)
            return $fields;
            
        }
        
        // vars
        $post_id = acf_maybe_get($post, 'ID', false);
        
        // compatibility with WP < 4.5 (test)
        if(!$post_id){
            
            global $post;
            $post_id = $post->ID;
            
        }
        
        // validate engine of parent post_id
        if(acfe_get_object_performance_engine_name($post_id) !== 'ultra'){
            return $fields;
        }
        
        // get all postmeta
        $acf = get_post_meta($post_id, 'acf', true);
        
        // bail early if no meta
        if(!$acf){
            return $fields;
        }
        
        // copy from wp_post_revision_fields()
        // source: /advanced-custom-fields-pro/includes/revisions.php:219
        
        // loop
        foreach($acf as $name => $value){
            
            // attempt to find key value
            $key = acf_maybe_get($acf, '_' . $name);
            
            // bail early if no key
            if(!$key){
                continue;
            }
            
            // Load field.
            $field = acf_get_field($key);
            if(!$field){
                continue;
            }
            
            // get field
            $field_title = $field['label'] . ' (' . $name . ')';
            $field_order = $field['menu_order'];
            $ancestors   = acf_get_field_ancestors( $field );
            
            // ancestors
            if(!empty($ancestors)){
                
                // vars
                $count  = count($ancestors);
                $oldest = acf_get_field($ancestors[ $count - 1 ]);
                
                // update vars
                $field_title = str_repeat( '- ', $count ) . $field_title;
                $field_order = $oldest['menu_order'] . '.1';
            }
            
            // append
            $append[ $name ] = $field_title;
            $order[ $name ]  = $field_order;
            
            // hook into specific revision field filter and return local value
            add_filter( "_wp_post_revision_field_{$name}", array($this, 'wp_post_revision_field' ), 10, 4 );
            
        }
        
        // append
        if(!empty($append)){
            
            // vars
            $prefix = '_';
            
            // add prefix
            $append = acf_add_array_key_prefix( $append, $prefix );
            $order  = acf_add_array_key_prefix( $order, $prefix );
            
            // sort by name (orders sub field values correctly)
            array_multisort( $order, $append );
            
            // remove prefix
            $append = acf_remove_array_key_prefix( $append, $prefix );
            
            // append
            $fields = $fields + $append;
        }
        
        // return
        return $fields;
        
    }
    
    
    /**
     * wp_post_revision_field
     *
     * revision field for acf
     *
     * copy from wp_post_revision_field()
     * source: /advanced-custom-fields-pro/includes/revisions.php:298
     *
     * @param $value
     * @param $field_name
     * @param $post
     * @param $direction
     *
     * @return bool|mixed|string
     */
    function wp_post_revision_field($value, $field_name, $post = null, $direction = false){
        
        $post_id = $post->ID;
        
        // get all postmeta
        $acf = get_post_meta($post_id, 'acf', true);
        
        if(!$acf || !isset($acf[ $field_name ])){
            return '';
        }
        
        $value = $acf[ $field_name ];
        
        // load field.
        $field = acf_maybe_get_field( $field_name, $post_id );
        
        // default formatting.
        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        } elseif ( is_object( $value ) ) {
            $value = serialize( $value );
        }
        
        // image.
        if ( is_array( $field ) && isset( $field['type'] ) && ( $field['type'] === 'image' || $field['type'] === 'file' ) ) {
            $url   = wp_get_attachment_url( $value );
            $value = $value . ' (' . $url . ')';
        }
        
        return $value;
        
    }
    
}

acf_new_instance('acfe_performance_ultra_revisions');

endif;