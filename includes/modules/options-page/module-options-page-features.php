<?php

if(!defined('ABSPATH')){
    exit;
}


if(!class_exists('acfe_module_options_page_features')):

class acfe_module_options_page_features{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('admin_footer',  array($this, 'admin_footer'));
        //add_action('pre_get_posts', array($this, 'admin_archive_posts'), 15);
    
    }
    
    
    /**
     * admin_footer
     *
     * admin_footer
     */
    function admin_footer(){
    
        if(!acf_current_user_can_admin()){
            return;
        }
    
        global $plugin_page;
        if(!$plugin_page){
            return;
        }
    
        $options_page = acf_get_options_page($plugin_page);
        if(!$options_page){
            return;
        }
    
        $item = acfe_get_module('options_page')->get_raw_item($options_page['menu_slug']);
        if(!$item){
            return;
        }
    
        ?>
        <script type="text/html" id="tmpl-acfe-edit-module">
            <a href="<?php echo admin_url("post.php?post={$item['ID']}&action=edit"); ?>" class="page-title-action acfe-edit-module-button"><span class="dashicons dashicons-admin-generic"></span></a>
        </script>

        <script type="text/javascript">
            (function($){
                $('.wrap > h1').append($('#tmpl-acfe-edit-module').html());
            })(jQuery);
        </script>
        <?php
        
    }
    
    
    /**
     * admin_archive_posts
     *
     * pre_get_posts
     *
     * @param $query
     */
    function admin_archive_posts($query){
        
        // todo: orderby position?
        global $pagenow;
        
        if(!is_admin() || !$query->is_main_query() || $pagenow !== 'edit.php' || $query->get('post_type') !== 'acfe-dop'){
            return;
        }
        
        //$query->set('meta_key', 'position');
        //$query->set('orderby', 'meta_value_num title');
        //$query->set('order', 'ASC');
        
    }
    
}

new acfe_module_options_page_features();

endif;