<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_taxonomy_features')):

class acfe_module_taxonomy_features{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acfe/module/register_item/module=taxonomy', array($this, 'register_taxonomy'));
        add_action('admin_footer-edit-tags.php',                array($this, 'admin_footer'));
        add_filter('get_terms_args',                            array($this, 'admin_archive_posts'), 9, 2); // use 9 to let developers use 10 to override
        add_action('pre_get_posts',                             array($this, 'front_archive_posts'), 9); // use 9 to let developers use 10 to override
        add_filter('template_include',                          array($this, 'front_template'), 999);
        
    }
    
    
    /**
     * register_taxonomy
     *
     * acfe/module/register_item/module=taxonomy
     *
     * @param $item
     */
    function register_taxonomy($item){
    
        // admin terms per page
        add_filter("edit_{$item['name']}_per_page", array($this, 'admin_archive_ppp'));
        
    }
    
    
    /**
     * admin_archive_ppp
     *
     * @param $ppp
     *
     * @return mixed
     */
    function admin_archive_ppp($ppp){
    
        global $pagenow;
        if($pagenow !== 'edit-tags.php'){
            return $ppp;
        }
    
        $taxonomy = acf_maybe_get_GET('taxonomy');
        if(empty($taxonomy)){
            return $ppp;
        }
    
        $object = get_taxonomy($taxonomy);
        $acfe_admin_ppp = acfe_maybe_get($object, 'acfe_admin_ppp');
    
        // setting not set
        if(!$acfe_admin_ppp){
            return $ppp;
        }
    
        // check if user has a screen option
        if(!empty(get_user_option("edit_{$taxonomy}_per_page"))){
            return $ppp;
        }
    
        return $acfe_admin_ppp;
        
    }
    
    
    /**
     * admin_footer
     *
     * admin_footer-edit-tags.php
     */
    function admin_footer(){
        
        if(!acf_current_user_can_admin()){
            return;
        }
        
        global $taxnow;
        if(!$taxnow){
            return;
        }
        
        $item = acfe_get_module('taxonomy')->get_raw_item($taxnow);
        if(!$item){
            return;
        }
        
        ?>
        <script type="text/html" id="tmpl-acfe-edit-module">
            <a href="<?php echo admin_url("post.php?post={$item['ID']}&action=edit"); ?>" class="page-title-action acfe-edit-module-button"><span class="dashicons dashicons-admin-generic"></span></a>
        </script>
        <script type="text/javascript">
            (function($){
                $('.wrap .wp-heading-inline').after($('#tmpl-acfe-edit-module').html());
            })(jQuery);
        </script>
        <?php
        
    }
    
    
    /**
     * admin_archive_posts
     *
     * get_terms_args
     *
     * @param $args
     * @param $taxonomies
     *
     * @return mixed
     */
    function admin_archive_posts($args, $taxonomies){
        
        global $pagenow;
        
        if(!is_admin() || empty($taxonomies) || $pagenow !== 'edit-tags.php'){
            return $args;
        }
        
        $taxonomy = array_shift($taxonomies);
        $object = get_taxonomy($taxonomy);
        
        $acfe_admin_orderby = acfe_maybe_get($object, 'acfe_admin_orderby');
        $acfe_admin_order = acfe_maybe_get($object, 'acfe_admin_order');
        $acfe_admin_meta_key = acfe_maybe_get($object, 'acfe_admin_meta_key');
        $acfe_admin_meta_type = acfe_maybe_get($object, 'acfe_admin_meta_type');
        
        if($acfe_admin_orderby && !acfe_maybe_get_REQUEST('orderby')){
            $args['orderby'] = $acfe_admin_orderby;
        }
        
        if($acfe_admin_order && !acfe_maybe_get_REQUEST('order')){
            $args['order'] = $acfe_admin_order;
        }
        
        if($acfe_admin_meta_key && !acfe_maybe_get_REQUEST('meta_key')){
            $args['meta_key'] = $acfe_admin_meta_key;
        }
        
        if($acfe_admin_meta_type && !acfe_maybe_get_REQUEST('meta_type')){
            $args['meta_type'] = $acfe_admin_meta_type;
        }
        
        return $args;
        
    }
    
    
    /**
     * front_archive_posts
     *
     * pre_get_posts:9
     *
     * @param $query
     */
    function front_archive_posts($query){
        
        if(is_admin() || !$query->is_main_query() || !is_tax()){
            return;
        }
        
        $term = $query->get_queried_object();
        
        if(!is_a($term, 'WP_Term')){
            return;
        }
        
        $object = get_taxonomy($term->taxonomy);
        
        $acfe_single_ppp = acfe_maybe_get($object, 'acfe_single_ppp');
        $acfe_single_orderby = acfe_maybe_get($object, 'acfe_single_orderby');
        $acfe_single_order = acfe_maybe_get($object, 'acfe_single_order');
        $acfe_single_meta_key = acfe_maybe_get($object, 'acfe_single_meta_key');
        $acfe_single_meta_type = acfe_maybe_get($object, 'acfe_single_meta_type');
        
        if($acfe_single_ppp){
            $query->set('posts_per_page', $acfe_single_ppp);
        }
        
        if($acfe_single_orderby){
            $query->set('orderby', $acfe_single_orderby);
        }
        
        if($acfe_single_order){
            $query->set('order', $acfe_single_order);
        }
        
        if($acfe_single_meta_key){
            $query->set('meta_key', $acfe_single_meta_key);
        }
        
        if($acfe_single_meta_type){
            $query->set('meta_type', $acfe_single_meta_type);
        }
        
    }
    
    
    /**
     * front_template
     *
     * template_include
     *
     * @param $template
     *
     * @return mixed|string
     */
    function front_template($template){
        
        if(!is_tax() && !is_category() && !is_tag()){
            return $template;
        }
        
        if(!isset(get_queried_object()->taxonomy)){
            return $template;
        }
        
        $object = get_queried_object()->taxonomy;
        
        foreach(get_taxonomies(array('public' => true), 'objects') as $taxonomy){
            
            if($object !== $taxonomy->name || !isset($taxonomy->acfe_single_template)){
                continue;
            }
            
            if($locate = locate_template(array($taxonomy->acfe_single_template))){
                return $locate;
            }
            
        }
        
        return $template;
        
    }
    
}

new acfe_module_taxonomy_features();

endif;