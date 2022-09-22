<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_screen_post')):

class acfe_screen_post{
    
    // vars
    var $post_type;
    var $post_id;
    
    /**
     * construct
     */
    function __construct(){
    
        /**
         * hooks:
         *
         * acfe/load_post              $post_type, $post_id
         * acfe/add_post_meta_boxes    $post_type, $post
         *
         * acfe/load_posts             $post_type
         * acfe/add_posts_meta_boxes   $post_type
         */
        
        // edit
        add_action('load-post-new.php', array($this, 'post_load'));
        add_action('load-post.php',     array($this, 'post_load'));
        
        // list
        add_action('load-edit.php',     array($this, 'posts_load'));
        
    }
    
    
    /**
     * post_load
     *
     * load-post.php
     */
    function post_load(){
    
        // global
        global $typenow;
        
        // exclude attachment
        if($typenow === 'attachment'){
            return;
        }
    
        // vars
        $post_type = $typenow;
        $post_id = (int) acfe_get_post_id();
        
        // actions
        do_action("acfe/load_post",                        $post_type, $post_id);
        do_action("acfe/load_post/post_type={$post_type}", $post_type, $post_id);
        
        // hooks
        add_action('add_meta_boxes', array($this, 'add_post_meta_boxes'), 10, 2);
        
    }
    
    
    /**
     * add_post_meta_boxes
     *
     * add_meta_boxes
     *
     * @param $post_type
     * @param $post
     */
    function add_post_meta_boxes($post_type, $post){
        
        do_action("acfe/add_post_meta_boxes",                        $post_type, $post);
        do_action("acfe/add_post_meta_boxes/post_type={$post_type}", $post_type, $post);
        
    }
    
    
    /**
     * posts_load
     *
     * load-edit.php
     */
    function posts_load(){
        
        // global
        global $typenow;
        
        // vars
        $post_type = $typenow;
        
        // set vars
        $this->post_type = $post_type;
        
        // actions
        do_action("acfe/load_posts",                        $post_type);
        do_action("acfe/load_posts/post_type={$post_type}", $post_type);
    
        // hooks
        add_action('admin_footer', array($this, 'posts_footer'));
        
    }
    
    
    /**
     * posts_footer
     *
     * admin_footer
     */
    function posts_footer(){
        
        do_action('acfe/add_posts_meta_boxes', $this->post_type);
    
        $this->posts_do_meta_boxes();
        
    }
    
    
    /**
     * posts_do_meta_boxes
     */
    function posts_do_meta_boxes(){
        
        // check filter
        if(!acf_is_filter_enabled('acfe/post_type_list')){
            return;
        }
        
        // enqueue
        acf_enqueue_scripts();
        
        ?>
        <template id="tmpl-acf-after-title">

            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'acf_after_title', $this->post_type); ?>
                </form>
            </div>

        </template>
        
        <template id="tmpl-acf-normal">

            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'normal', $this->post_type); ?>
                </form>
            </div>

        </template>
        
        <template id="tmpl-acf-side">

            <div class="acf-column-2">

                <div id="poststuff" class="acfe-list-postboxes -side">
                    <form method="post">
                        <?php do_meta_boxes('edit', 'side', $this->post_type); ?>
                    </form>
                </div>

            </div>

        </template>
        <script type="text/javascript">
        (function($){
            
            // main form
            var $main = $('#posts-filter');

            $main.wrap('<div class="acf-columns-2" />');
            $main.prepend($('.subsubsub'));
            $main.wrap('<div class="acf-column-1" />');

            // field groups
            var $column_1 = $('.acf-column-1');
            
            $column_1.prepend($('#tmpl-acf-after-title').html());
            $column_1.append($('#tmpl-acf-normal').html());
            $column_1.after($('#tmpl-acf-side').html());
    
            <?php if(!acf_is_filter_enabled('acfe/post_type_list/side')): ?>
                $('.acf-columns-2').removeClass('acf-columns-2');
            <?php endif; ?>

        })(jQuery);
        </script>
        <?php
    }
    
}

new acfe_screen_post();

endif;