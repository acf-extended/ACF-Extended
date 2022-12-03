<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dev_clean_meta')):

class acfe_dev_clean_meta{
    
    function __construct(){
    
        // check settings
        if((!acfe_is_dev() && !acfe_is_super_dev()) || !acf_current_user_can_admin()){
            return;
        }
        
        // load
        add_action('acfe/load_post',         array($this, 'load_page'));
        add_action('acfe/load_posts',        array($this, 'load_page'));
        add_action('acfe/load_term',         array($this, 'load_page'));
        add_action('acfe/load_terms',        array($this, 'load_page'));
        add_action('acfe/load_user',         array($this, 'load_page'));
        add_action('acfe/load_users',        array($this, 'load_page'));
        add_action('acfe/load_settings',     array($this, 'load_page'));
        add_action('acfe/load_option',       array($this, 'load_page'));
        add_action('acfe/load_attachment',   array($this, 'load_page'));
        add_action('acfe/load_attachments',  array($this, 'load_page'));
        
        // metabox
        add_action('acfe/dev/clean_metabox', array($this, 'load_metabox'), 10, 3);
        
    }
    
    
    /**
     * load_page
     *
     * acfe/load_post
     * acfe/load_posts
     * acfe/load_term
     * acfe/load_terms
     * acfe/load_user
     * acfe/load_users
     * acfe/load_settings
     * acfe/load_option'
     * acfe/load_attachment
     * acfe/load_attachments
     */
    function load_page(){
        
        // vars
        $post_id = acfe_maybe_get_REQUEST('acfe_dev_clean');
        $nonce = acfe_maybe_get_REQUEST('acfe_dev_clean_nonce');
        
        // check none
        if($post_id && wp_verify_nonce($nonce, 'acfe_dev_clean')){
            
            // get deleted meta
            $deleted = acfe_delete_orphan_meta($post_id);
            
            // set transient
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
            
            // vars
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
                
                $link = ' <a href="#" data-modal="clean-meta-debug">' . __('View', 'acfe') . '</a>';
                
                add_action('admin_footer', function() use($deleted){
                    ?>
                    <div class="acfe-modal" data-modal="clean-meta-debug" data-title="<?php _e('Deleted meta', 'acfe'); ?>" data-footer="<?php _e('Close', 'acfe'); ?>">
                        <div class="acfe-modal-spacer">
                            <pre><?php print_r($deleted); ?></pre>
                        </div>
                    </div>
                    <?php
                });
                
                acf_add_admin_notice("{$count} meta cleaned.{$link}", 'success');
                
            }
            
            // cleanup transient
            delete_transient('acfe_dev_clean');
            
        }
        
    }
    
    
    /**
     * load_metabox
     *
     * @param $post_id
     * @param $screen
     * @param $type
     */
    function load_metabox($post_id, $screen, $type){
        
        // bail early
        if(!acfe_is_single_meta_enabled($post_id) || acf_is_filter_enabled('acfe/dev/clean_metabox')){
            return;
        }
        
        // enable filters to force sidebar on list screen
        switch($type){
            
            case 'posts': {
                
                acf_set_filters(array(
                    'acfe/post_type_list/side'      => true,
                    'acfe/post_type_list/submitdiv' => true,
                ));
                
                break;
            }
            
            case 'terms': {
                
                acf_set_filters(array(
                    'acfe/taxonomy_list/side'      => true,
                    'acfe/taxonomy_list/submitdiv' => true,
                ));
                
                break;
                
            }
            
        }
        
        // add meta box
        $this->add_meta_box($post_id, $screen);
        
    }
    
    
    /**
     * add_meta_box
     *
     * @param $post_id
     * @param $screen
     */
    function add_meta_box($post_id, $screen){
    
        add_meta_box('acfe-clean-meta', __('Single Meta', 'acfe'), array($this, 'render_metabox'), $screen, 'side', 'core', array('post_id' => $post_id));
        
    }
    
    
    /**
     * render_metabox
     *
     * @param $post
     * @param $metabox
     */
    function render_metabox($post, $metabox){
        
        // post id
        $post_id = $metabox['args']['post_id'];
        
        // url
        $url = add_query_arg(array(
            'acfe_dev_clean'       => $post_id,
            'acfe_dev_clean_nonce' => wp_create_nonce('acfe_dev_clean'))
        );
        
        ?>
        <a href="<?php echo esc_url($url); ?>" class="button acf-button">
            <?php _e('Clean orphan meta', 'acfe'); ?>
        </a>
        <?php
        
    }
    
}

acf_new_instance('acfe_dev_clean_meta');

endif;