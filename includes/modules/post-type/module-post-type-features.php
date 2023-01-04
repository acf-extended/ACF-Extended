<?php

if(!defined('ABSPATH')){
    exit;
}


if(!class_exists('acfe_module_post_type_features')):

class acfe_module_post_type_features{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('admin_footer-edit.php', array($this, 'admin_footer'));
        add_action('pre_get_posts',         array($this, 'admin_archive_posts'), 9); // use 9 to let developers use 10 to override
        add_filter('edit_posts_per_page',   array($this, 'admin_archive_ppp'), 10, 2);
        add_action('pre_get_posts',         array($this, 'front_archive_posts'), 9); // use 9 to let developers use 10 to override
        add_filter('template_include',      array($this, 'front_template'), 999);
        add_filter('post_updated_messages', array($this, 'post_updated_messages'));
        add_filter('enter_title_here',      array($this, 'enter_title_here'), 10, 2);
    
    }
    
    
    /**
     * admin_footer
     *
     * admin_footer-edit.php
     */
    function admin_footer(){
        
        if(!acf_current_user_can_admin()){
            return;
        }
        
        global $typenow;
        if(!$typenow){
            return;
        }
        
        $post_type_object = get_post_type_object($typenow);
        
        // check acfe custom feature
        if(!isset($post_type_object->acfe_archive_ppp)){
            return;
        }
        
        // get raw item
        $item = acfe_get_module('post_type')->get_raw_item($typenow);
        if(!$item){
            return;
        }
        
        ?>
        <script type="text/html" id="tmpl-acfe-edit-module">
            <a href="<?php echo admin_url("post.php?post={$item['ID']}&action=edit"); ?>" class="page-title-action acfe-edit-module-button"><span class="dashicons dashicons-admin-generic"></span></a>
        </script>
        <script type="text/javascript">
            (function($){
                $('.wrap .page-title-action').before($('#tmpl-acfe-edit-module').html());
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
        
        global $pagenow;
        
        if(!is_admin() || !$query->is_main_query() || $pagenow !== 'edit.php'){
            return;
        }
        
        $post_type = $query->get('post_type');
        $object = get_post_type_object($post_type);
        
        $admin_order_by = acfe_maybe_get($object, 'acfe_admin_orderby');
        $admin_order = acfe_maybe_get($object, 'acfe_admin_order');
        $admin_meta_key = acfe_maybe_get($object, 'acfe_admin_meta_key');
        $admin_meta_type = acfe_maybe_get($object, 'acfe_admin_meta_type');
        
        if($admin_order_by && !acfe_maybe_get_REQUEST('orderby')){
            $query->set('orderby', $admin_order_by);
        }
        
        if($admin_order && !acfe_maybe_get_REQUEST('order')){
            $query->set('order', $admin_order);
        }
        
        if($admin_meta_key && !acfe_maybe_get_REQUEST('meta_key')){
            $query->set('meta_key', $admin_meta_key);
        }
        
        if($admin_meta_type && !acfe_maybe_get_REQUEST('meta_type')){
            $query->set('meta_type', $admin_meta_type);
        }
        
    }
    
    
    /**
     * admin_archive_ppp
     *
     * edit_posts_per_page
     *
     * @param $ppp
     * @param $post_type
     *
     * @return mixed
     */
    function admin_archive_ppp($ppp, $post_type){
        
        global $pagenow;
        if($pagenow !== 'edit.php'){
            return $ppp;
        }
        
        $object = get_post_type_object($post_type);
        $admin_ppp = acfe_maybe_get($object, 'acfe_admin_ppp');
        $user_ppp = get_user_option("edit_{$post_type}_per_page");
        
        if(!$admin_ppp || !empty($user_ppp)){
            return $ppp;
        }
        
        return $admin_ppp;
        
    }
    
    
    /**
     * front_archive_posts
     *
     * pre_get_posts:9
     *
     * @param $query
     */
    function front_archive_posts($query){
        
        if(is_admin() || !$query->is_main_query() || !is_post_type_archive()){
            return;
        }
        
        $post_type = $query->get('post_type');
        $object = get_post_type_object($post_type);
        
        $archive_ppp = acfe_maybe_get($object, 'acfe_archive_ppp');
        $archive_orderby = acfe_maybe_get($object, 'acfe_archive_orderby');
        $archive_order = acfe_maybe_get($object, 'acfe_archive_order');
        $archive_meta_key = acfe_maybe_get($object, 'acfe_archive_meta_key');
        $archive_meta_type = acfe_maybe_get($object, 'acfe_archive_meta_type');
        
        if($archive_ppp){
            $query->set('posts_per_page', $archive_ppp);
        }
        
        if($archive_orderby){
            $query->set('orderby', $archive_orderby);
        }
        
        if($archive_order){
            $query->set('order', $archive_order);
        }
        
        if($archive_meta_key){
            $query->set('meta_key', $archive_meta_key);
        }
        
        if($archive_meta_type){
            $query->set('meta_type', $archive_meta_type);
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
        
        if(!is_single() && !is_post_type_archive() && !is_home()){
            return $template;
        }
        
        // Get_query_var
        $query_var = get_query_var('post_type', false);
        
        if(is_array($query_var) && !empty($query_var)){
            $query_var = $query_var[0];
        }
        
        foreach(get_post_types(array(), 'objects') as $post_type){
            
            // Get_query_var check
            $is_query_var = $query_var && $query_var === $post_type->name;
            
            // Get_post_type check
            $get_post_type = get_post_type() === $post_type->name;
            
            // Acfe_archive_template
            $acfe_archive_template = isset($post_type->acfe_archive_template) && !empty($post_type->acfe_archive_template);
            
            // Acfe_archive_template
            $acfe_single_template = isset($post_type->acfe_single_template) && !empty($post_type->acfe_single_template);
            
            // Global check
            if(!$get_post_type || !$is_query_var || (!$acfe_archive_template && !$acfe_single_template)){
                continue;
            }
            
            $rule = array();
            $rule['is_archive'] = is_post_type_archive($post_type->name);
            $rule['has_archive'] = $post_type->has_archive;
            $rule['is_single'] = is_singular($post_type->name);
            
            // Post Exception
            if($post_type->name === 'post'){
                $rule['is_archive'] = is_home();
                $rule['has_archive'] = true;
            }
            
            // Archive
            if($rule['has_archive'] && $rule['is_archive'] && $acfe_archive_template && ($locate = locate_template(array($post_type->acfe_archive_template)))){
                return $locate;
            }
            
            // Single
            elseif($rule['is_single'] && $acfe_single_template && ($locate = locate_template(array($post_type->acfe_single_template)))){
                return $locate;
            }
            
        }
        
        return $template;
        
    }
    
    
    /**
     * post_updated_messages
     *
     * post_updated_messages
     *
     * @param $messages
     *
     * @return array|mixed
     */
    function post_updated_messages($messages){
        
        // globals
        global $post_type, $post_type_object, $post;
        
        // post type not managed by acfe
        if(!isset($post_type_object->acfe_archive_ppp)){
            return $messages;
        }
        
        // vars
        $viewable = is_post_type_viewable($post_type_object);
        $preview_url = get_preview_post_link($post);
        $permalink = get_permalink($post->ID);
        if(!$permalink){
            $permalink = '';
        }
        
        // default links
        $preview_post_link_html   = '';
        $scheduled_post_link_html = '';
        $view_post_link_html      = '';
        
        if($viewable){
            
            $view_item = __('View post');
            $preview_item = __('Preview post');
            
            if(isset($post_type_object->labels->view_item)){
                $view_item = $post_type_object->labels->view_item;
                $preview_item = $post_type_object->labels->view_item;
            }
            
            $preview_post_link_html = sprintf(' <a target="_blank" href="%1$s">%2$s</a>', esc_url($preview_url), $preview_item);
            $scheduled_post_link_html = sprintf(' <a target="_blank" href="%1$s">%2$s</a>', esc_url($permalink), $preview_item);
            $view_post_link_html = sprintf(' <a href="%1$s">%2$s</a>', esc_url($permalink), $view_item);
            
        }
        
        $scheduled_date = sprintf(
            __('%1$s at %2$s'),
            date_i18n(_x('M j, Y', 'publish box date format'), strtotime($post->post_date)),
            date_i18n(_x('H:i', 'publish box time format'), strtotime($post->post_date))
        );
        
        if(isset($post_type_object->labels->item_updated)){
            $messages[ $post_type ][1] = $post_type_object->labels->item_updated . $view_post_link_html;
            $messages[ $post_type ][4] = $post_type_object->labels->item_updated;
            $messages[ $post_type ][7] = $post_type_object->labels->item_updated;
            $messages[ $post_type ][8] = $post_type_object->labels->item_updated . $preview_post_link_html;
            $messages[ $post_type ][10] = $post_type_object->labels->item_updated . $preview_post_link_html;
        }
        
        if(isset($post_type_object->labels->item_published)){
            $messages[ $post_type ][6] = $post_type_object->labels->item_published . $view_post_link_html;
        }
        
        if(isset($post_type_object->labels->item_scheduled)){
            $messages[ $post_type ][9] = $post_type_object->labels->item_scheduled . ' <strong>' . $scheduled_date . '</strong>' . $scheduled_post_link_html;
        }
        
        // return
        return $messages;
        
    }
    
    
    /**
     * enter_title_here
     *
     * enter_title_here
     *
     * @param $placeholder
     * @param $post
     *
     * @return mixed
     */
    function enter_title_here($placeholder, $post){
        
        if(isset($post->post_type)){
    
            $post_type_obj = get_post_type_object($post->post_type);
    
            if(isset($post_type_obj->labels->enter_title)){
                return $post_type_obj->labels->enter_title;
            }
            
        }
        
        return $placeholder;
        
    }
    
}

new acfe_module_post_type_features();

endif;