<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_screen_user')):

class acfe_screen_user{
    
    // vars
    var $user_id;
    
    /*
     * Construct
     */
    function __construct(){
        
        /*
         * acfe/load_user_new
         * acfe/add_user_new_meta_boxes
         *
         * acfe/load_user                   $user_id
         * acfe/add_user_meta_boxes         $user
         *
         * acfe/load_users
         * acfe/add_users_meta_boxes
         */
        
        // add
        add_action('load-user-new.php',     array($this, 'user_new_load'));
        
        // edit
        add_action('load-profile.php',      array($this, 'user_load'));
        add_action('load-user-edit.php',    array($this, 'user_load'));
        
        // list
        add_action('load-users.php',        array($this, 'users_load'));
        
    }
    
    /*
     * User New: Load
     */
    function user_new_load(){
        
        // do not process on multisite
        if(is_multisite()){
            return;
        }
        
        // actions
        do_action("acfe/load_user_new");
        
        // hooks
        add_action('user_new_form', array($this, 'user_new_meta_boxes'));
        
    }
    
    /*
     * User New: Meta Boxes
     */
    function user_new_meta_boxes($user){
    
        // add meta boxes
        do_action('acfe/add_user_new_meta_boxes');
        
        // enhanced ui
        if(acf_get_setting('acfe/modules/ui')){
    
            // do meta boxes
            $screen = get_current_screen();
    
            do_meta_boxes($screen, 'acf_after_title', $user);
            do_meta_boxes($screen, 'normal', $user);
            do_meta_boxes($screen, 'side', $user);
            
        }
        
    }
    
    
    
    /*
     * User: Load
     */
    function user_load(){
        
        // do not process on network screens
        if(acf_is_screen(array('user-edit-network', 'profile-network'))){
            return;
        }
        
        // vars
        $this->user_id = acfe_get_post_id(false);
        
        // actions
        do_action("acfe/load_user", $this->user_id);
    
        // hooks
        add_action('show_user_profile', array($this, 'user_meta_boxes'));
        add_action('edit_user_profile', array($this, 'user_meta_boxes'));
        
    }
    
    /*
     * User: Meta Boxes
     */
    function user_meta_boxes($user){
        
        // add meta boxes
        do_action('acfe/add_user_meta_boxes', $user);
    
        // enhanced ui
        if(acf_get_setting('acfe/modules/ui')){
        
            // do meta boxes
            $screen = get_current_screen();
        
            do_meta_boxes($screen, 'acf_after_title', $user);
            do_meta_boxes($screen, 'normal', $user);
            do_meta_boxes($screen, 'side', $user);
        
        }
        
    }
    
    
    
    /*
     * Users: Load
     */
    function users_load(){
    
        // do not process on network screens
        if(acf_is_screen(array('users-network'))){
            return;
        }
    
        // actions
        do_action("acfe/load_users");
    
        // hooks
        add_action('admin_footer', array($this, 'users_footer'));
        
    }
    
    /*
     * Users: Footer
     */
    function users_footer(){
        
        do_action('acfe/add_users_meta_boxes', 'user');
    
        $this->users_do_meta_boxes();
        
    }
    
    /*
     * Users: Do Meta Boxes
     */
    function users_do_meta_boxes(){
        
        // check filter
        if(!acf_is_filter_enabled('acfe/user_list')){
            return;
        }
        
        // enqueue
        acf_enqueue_scripts();
        
        ?>
        <template id="tmpl-acf-after-title">
            
            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'acf_after_title', 'user'); ?>
                </form>
            </div>
        
        </template>
        
        <template id="tmpl-acf-normal">
            
            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'normal', 'user'); ?>
                </form>
            </div>
        
        </template>
        
        <template id="tmpl-acf-side">
            
            <div class="acf-column-2">
                
                <div id="poststuff" class="acfe-list-postboxes -side">
                    <form method="post">
                        <?php do_meta_boxes('edit', 'side', 'user'); ?>
                    </form>
                </div>
            
            </div>
        
        </template>
        <script type="text/javascript">
        (function($){

            // main form
            var $main = $('.wrap > form');

            $main.wrap('<div class="acf-columns-2" />');
            $main.prepend($('.subsubsub'));
            $main.wrap('<div class="acf-column-1" />');

            // field groups
            var $column_1 = $('.acf-column-1');

            $column_1.prepend($('#tmpl-acf-after-title').html());
            $column_1.append($('#tmpl-acf-normal').html());
            $column_1.after($('#tmpl-acf-side').html());
            
            <?php if(!acf_is_filter_enabled('acfe/user_list/side')): ?>
                $('.acf-columns-2').removeClass('acf-columns-2');
            <?php endif; ?>

        })(jQuery);
        </script>
        <?php
    }
    
}

new acfe_screen_user();

endif;