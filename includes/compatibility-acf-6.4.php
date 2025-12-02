<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_compatibility_acf_64')):

class acfe_compatibility_acf_64{
    
    /**
     * construct
     */
    function __construct(){
        
        // bail early pre-ACF 6.4
        if(!acfe_is_acf_64()){
            return;
        }
        
        // check ACF revisions instance
        if(!property_exists(acf(), 'revisions') || !acf()->revisions instanceof acf_revisions){
            return;
        }
        
        /**
         * Replace ACF revisions hooks
         *
         * Since ACF 6.4+, ACF copy post meta to revisions using a new logic inside acf_copy_postmeta()
         * That new logic uses the new acf_get_meta_instance('post')->update_meta()
         * This bypass the historical acf_update_metadata() which called the hook: acf/pre_update_metadata
         * This hook is important, as it's used by the Performance module to save compressed meta in the revisions
         *
         * The code below that replace ACF hooks is identical to the native ACF 6.4+ code found in:
         * /advanced-custom-fields-pro/includes/revisions.php
         *
         * They have been simply rewritten to call our custom acfe_copy_postmeta() where needed
         * acfe_copy_postmeta() then use the classic logic with acf_update_metadata() > acf/pre_update_metadata
         */
        acfe_replace_action(
            'wp_restore_post_revision',
            array(acf()->revisions, 'wp_restore_post_revision'),
            array($this, 'wp_restore_post_revision'),
            10, 2
        );
        
        /**
         * WP 6.4+
         */
        if(version_compare(get_bloginfo( 'version' ), '6.4', '>=')){
            
            acfe_replace_action(
                '_wp_put_post_revision',
                array(acf()->revisions, 'maybe_save_revision'),
                array($this, 'maybe_save_revision'),
                10, 2
            );
            
            acfe_replace_filter(
                'wp_save_post_revision_post_has_changed',
                array(acf()->revisions, 'check_acf_fields_have_changed'),
                array($this, 'check_acf_fields_have_changed'),
                9, 3
            );
            
        }
        
    }
    
    
    /**
     * check_acf_fields_have_changed
     *
     * Helps WordPress determine if fields have changed, and if in a legacy
     * metabox AJAX request, copies the metadata to the new revision.
     *
     * @param boolean $post_has_changed True if the post has changed, false if not.
     * @param WP_Post $last_revision    The WP_Post object for the latest revision.
     * @param WP_Post $post             The WP_Post object for the parent post.
     *
     * @return bool
     */
    function check_acf_fields_have_changed($post_has_changed, $last_revision, $post){
        
        // no performance mode enabled on this post
        // fallback to ACF native method
        if(!acfe_is_object_performance_enabled($post->ID) && method_exists(acf()->revisions, 'check_acf_fields_have_changed')){
            return acf()->revisions->check_acf_fields_have_changed($post_has_changed, $last_revision, $post);
        }
        
        if(acf_maybe_get_GET('meta-box-loader', false)){
            // We're in a legacy AJAX request, so we copy fields over to the latest revision.
            $this->maybe_save_revision($last_revision->ID, $post->ID);
            
        }elseif(acf_maybe_get_POST('_acf_changed', false)){
            // We're in a classic editor save request, so notify WP that fields have changed.
            $post_has_changed = true;
        }
        
        // Let WordPress decide for REST/block editor requests.
        return $post_has_changed;
        
    }
    
    
    /**
     * maybe_save_revision
     *
     * Copies ACF field data to the latest revision.
     *
     * @param $revision_id (int) The ID of the revision that was just created.
     * @param $post_id     (int) The ID of the post being updated.
     *
     * @return void
     */
    function maybe_save_revision($revision_id, $post_id){
        
        // no performance mode enabled on this post
        // fallback to ACF native method
        if(!acfe_is_object_performance_enabled($post_id) && method_exists(acf()->revisions, 'maybe_save_revision')){
            acf()->revisions->maybe_save_revision($revision_id, $post_id);
            return;
        }
        
        // We don't have anything to copy over yet.
        if(!did_action('acf/save_post')){
            delete_metadata('post', $post_id, '_acf_changed');
            delete_metadata('post', $revision_id, '_acf_changed');
            return;
        }
        
        // Bail if this is an autosave in Classic Editor, it already has the field values.
        if(acf_maybe_get_POST('_acf_changed') && wp_is_post_autosave($revision_id)){
            return;
        }
        
        // Copy the saved meta from the main post to the latest revision.
        acfe_save_post_revision($post_id);
        
    }
    
    
    /**
     * wp_restore_post_revision
     *
     * This action will copy and paste the metadata from a revision to the post
     *
     * @param $post_id     (int) the destination post
     * @param $revision_id (int) the source post
     *
     * @return void
     */
    function wp_restore_post_revision($post_id, $revision_id){
        
        // no performance mode enabled on this post
        // fallback to ACF native method
        if(!acfe_is_object_performance_enabled($post_id) && method_exists(acf()->revisions, 'wp_restore_post_revision')){
            acf()->revisions->wp_restore_post_revision($post_id, $revision_id);
            return;
        }
        
        // copy postmeta from revision to post (restore from revision)
        acfe_copy_postmeta($revision_id, $post_id);
        
        // Make sure the latest revision is also updated to match the new $post data
        // get latest revision
        $revision = acf_get_post_latest_revision($post_id);
        
        // save
        if($revision){
            
            // copy postmeta from revision to latest revision (potentialy may be the same, but most likely are different)
            acfe_copy_postmeta($revision_id, $revision->ID);
        }
    }
    
}

acf_new_instance('acfe_compatibility_acf_64');

endif;


/**
 * acfe_save_post_revision
 *
 * This function will copy meta from a post to it's latest revision
 *
 * @param $post_id
 *
 * @return void
 */
function acfe_save_post_revision($post_id = 0){

	// get latest revision
	$revision = acf_get_post_latest_revision($post_id);

	// save
	if($revision){
		acfe_copy_postmeta($post_id, $revision->ID);
	}
}


/**
 * acfe_copy_postmeta
 *
 * Copies meta from one post to another. Useful for saving and restoring revisions.
 *
 * @param $from_post_id
 * @param $to_post_id
 *
 * @return void
 */
function acfe_copy_postmeta($from_post_id = 0, $to_post_id = 0){

	// Get all postmeta.
	$meta = acf_get_meta($from_post_id);

	// Check meta.
	if($meta){

		// Slash data. WP expects all data to be slashed and will unslash it (fixes '\' character issues).
		$meta = wp_slash($meta);

		// Loop over meta.
		foreach($meta as $name => $value){
			acf_update_metadata($to_post_id, $name, $value);
		}
  
	}
}