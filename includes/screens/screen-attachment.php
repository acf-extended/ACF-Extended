<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_screen_attachment')):

class acfe_screen_attachment{
    
    // vars
    var $post_id;
    
    /**
     * construct
     */
    function __construct(){
    
        /**
         * hooks:
         *
         * acfe/load_attachments
         * acfe/add_attachments_meta_boxes  $post_type
         */
        
        // edit
        add_action('load-post.php',     array($this, 'attachment_load'));
        
        // list
        add_action('load-upload.php', array($this, 'attachments_load'));
        
    }
    
    
    /**
     * attachment_load
     *
     * load-post.php
     */
    function attachment_load(){
    
        // global
        global $typenow;
    
        // exclude attachment
        if($typenow !== 'attachment'){
            return;
        }
    
        // vars
        $post_id = (int) acfe_get_post_id();
    
        // actions
        do_action("acfe/load_attachment", $post_id);
    
        // hooks
        add_action('add_meta_boxes', array($this, 'add_attachment_meta_boxes'), 10, 2);
        
    }
    
    
    /**
     * add_attachment_meta_boxes
     *
     * add_meta_boxes
     *
     * @param $post_type
     * @param $post
     */
    function add_attachment_meta_boxes($post_type, $post){
        
        do_action("acfe/add_attachment_meta_boxes", $post);
        
    }
    
    
    /**
     * attachments_load
     *
     * load-upload.php
     */
    function attachments_load(){
        
        // actions
        do_action("acfe/load_attachments");
    
        // hooks
        add_action('admin_footer', array($this, 'attachments_footer'));
        
    }
    
    
    /**
     * attachments_footer
     *
     * admin_footer
     */
    function attachments_footer(){
        
        do_action('acfe/add_attachments_meta_boxes', 'attachment');
    
        $this->attachments_do_meta_boxes();
        
    }
    
    
    /**
     * attachments_do_meta_boxes
     */
    function attachments_do_meta_boxes(){
        
        // check filter
        if(!acf_is_filter_enabled('acfe/attachment_list')){
            return;
        }
        
        // mode (list/grid)
        // source: wp-admin/upload.php:16
        $mode  = get_user_option('media_library_mode', get_current_user_id()) ? get_user_option('media_library_mode', get_current_user_id()) : 'grid';
        $modes = array('grid', 'list');
    
        if(isset($_GET['mode']) && in_array($_GET['mode'], $modes, true)){
            $mode = $_GET['mode'];
        }
        
        // enqueue
        acf_enqueue_scripts();
        
        ?>
        <template id="tmpl-acf-after-title">

            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'acf_after_title', 'attachment'); ?>
                </form>
            </div>

        </template>
        
        <template id="tmpl-acf-normal">

            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'normal', 'attachment'); ?>
                </form>
            </div>

        </template>
        
        <template id="tmpl-acf-side">

            <div class="acf-column-2">

                <div id="poststuff" class="acfe-list-postboxes -side">
                    <form method="post">
                        <?php do_meta_boxes('edit', 'side', 'attachment'); ?>
                    </form>
                </div>

            </div>

        </template>
        <script type="text/javascript">
        (function($){
            
            <?php if($mode === 'list'): ?>
            
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
        
                <?php if(!acf_is_filter_enabled('acfe/attachment_list/side')): ?>
                    $('.acf-columns-2').removeClass('acf-columns-2');
                <?php endif; ?>
            
            <?php elseif($mode === 'grid'): ?>
            
                // wait for media grid to load
                acf.addAction('load', function(){

                    // media frame
                    var $main = $('.media-frame');

                    $main.wrap('<div class="acf-columns-2" />');
                    $main.wrap('<div class="acf-column-1" />');

                    // field groups
                    var $column_1 = $('.acf-column-1');

                    $column_1.prepend($('#tmpl-acf-after-title').html());
                    $column_1.append($('#tmpl-acf-normal').html());
                    $column_1.after($('#tmpl-acf-side').html());
                    
                    <?php if(!acf_is_filter_enabled('acfe/attachment_list/side')): ?>
                        $('.acf-columns-2').removeClass('acf-columns-2');
                    <?php endif; ?>
                    
                    // fix dev mode bulk actions
                    var $acfWrap = $('#acfe-acf-custom-fields');
                    var $wpWrap = $('#acfe-wp-custom-fields');

                    // move Bulk Button
                    $acfWrap.find('.tablenav.bottom').insertAfter($acfWrap);
                    $wpWrap.find('.tablenav.bottom').insertAfter($wpWrap);
                    
                    // re-initialize postboxes
                    (acf.get('postboxes') || []).map(acf.newPostbox);
                    
                });
            
            <?php endif; ?>

        })(jQuery);
        </script>
        <?php
    }
    
}

new acfe_screen_attachment();

endif;