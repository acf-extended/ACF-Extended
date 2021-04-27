<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/ui'))
    return;

if(!class_exists('acfe_enhanced_ui')):
    
class acfe_enhanced_ui{
    
    function __construct(){
        
        // Action
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 15);
        
    }
    
    function admin_enqueue_scripts(){
    
        // global
        global $pagenow;
        $enqueue = false;
    
        /*
         * Term
         * source: /advanced-custom-fields-pro/includes/forms/form-taxonomy.php
         */
        if(in_array($pagenow, array('edit-tags.php', 'term.php'))){
            
            // vars
            $screen = get_current_screen();
            $taxonomy = $screen->taxonomy;
            $action = $pagenow === 'edit-tags.php' ? 'term_footer_list' : 'term_footer_edit';
            
            // Remove ACF Render
            acfe_remove_class_action("{$taxonomy}_edit_form", 'acf_form_taxonomy', 'edit_term');
    
            // Add Metaboxes
            add_action("{$taxonomy}_term_edit_form_top",    array($this, 'term_add_metaboxes'), 10, 2);
            add_action("{$taxonomy}_term_edit_form_top",    array($this, 'term_do_metaboxes_top'), 99, 2);
            add_action("{$taxonomy}_edit_form",             array($this, 'term_do_metaboxes'), 99, 2);
    
            // Footer
            add_action('acf/admin_footer',                  array($this, $action));
    
            // Enqueue
            $enqueue = true;
            
        }

        /*
         * User
         * source: /advanced-custom-fields-pro/includes/forms/form-user.php
         */
        elseif(acf_is_screen(array('profile', 'user-edit')) || (acf_is_screen('user') && !is_multisite())){
            
            // vars
            $acf_form_user = acf_get_instance('ACF_Form_User');
            $action = acf_is_screen('user') ? 'user_footer_new' : 'user_footer_edit';
    
            // Remove ACF Render
            remove_action('show_user_profile',              array($acf_form_user, 'render_edit'));
            remove_action('edit_user_profile',              array($acf_form_user, 'render_edit'));
            remove_action('user_new_form',                  array($acf_form_user, 'render_new'));
            
            // Add Metaboxes
            add_action('show_user_profile',                 array($this, 'user_screen_edit'));
            add_action('edit_user_profile',                 array($this, 'user_screen_edit'));
            add_action('user_new_form',                     array($this, 'user_screen_new'));
            
            // Do Metaboxes
            add_action('show_user_profile',                 array($this, 'user_do_metaboxes'), 99);
            add_action('edit_user_profile',                 array($this, 'user_do_metaboxes'), 99);
            add_action('user_new_form',                     array($this, 'user_do_metaboxes'), 99);
    
            // Footer
            add_action('acf/admin_footer',                  array($this, $action));
    
            // Enqueue
            $enqueue = true;
            
        }

        /*
         * Settings
         */
        elseif(acf_is_screen(array('options-general', 'options-writing', 'options-reading', 'options-discussion', 'options-media', 'options-permalink'))){
    
            // Add Metaboxes
            add_action('admin_footer',                      array($this, 'settings_add_metaboxes'));
            add_action('admin_footer',                      array($this, 'settings_do_metaboxes'));
    
            // Settings
            add_action('acf/admin_footer',                  array($this, 'settings_footer'));
    
            // Enqueue
            $enqueue = true;
    
        }
        
        /*
         * Enqueue
         */
        if($enqueue){
    
            // ACF Enqueue
            acf_enqueue_scripts();
    
            // ACF Extended UI
            wp_enqueue_style('acf-extended-ui');
            wp_enqueue_script('acf-extended-ui');
            
        }
        
    }
    
    /*
     * Term: Add Metaboxes
     */
    function term_add_metaboxes($term, $taxonomy){
        
        // post id
        $post_id = 'term_' . $term->term_id;
        
        // screen
        $screen = get_current_screen();
        
        // field groups
        $field_groups = acf_get_field_groups(array(
            'taxonomy' => $taxonomy
        ));
        
        if($field_groups){
    
            // form data
            acf_form_data(array(
                'screen'    => 'taxonomy',
                'post_id'   => $post_id,
            ));
            
            $this->add_metaboxes($field_groups, $post_id, $screen);
            
        }
        
        // Sidebar submit
        add_meta_box('submitdiv', __('Edit'), array($this, 'render_metabox_submit'), $screen, 'side', 'high');
        
    }
    
    /*
     * Term: Do Metaboxes
     */
    function term_do_metaboxes_top($term, $taxonomy){
        
        do_meta_boxes(get_current_screen(), 'acf_after_title', $term);
        
    }
    
    /*
     * Term: Do Metaboxes
     */
    function term_do_metaboxes($term, $taxonomy){
        
        do_meta_boxes(get_current_screen(), 'normal', $term);
        do_meta_boxes(get_current_screen(), 'side', $term);
        
    }
    
    /*
     * Term: Footer List
     */
    function term_footer_list(){
    
        global $tax;
        $can_edit_terms = current_user_can($tax->cap->edit_terms);
    
        ?>
        <script type="text/html" id="tmpl-button-add-term">
            <?php if($can_edit_terms){ ?>
                <a href="#" class="page-title-action acfe-bt-admin-button-add"><?php echo $tax->labels->add_new_item; ?></a>
            <?php } ?>
        </script>

        <script type="text/javascript">
        (function($){

            acfe.enhancedListUI();

            // Polylang + WPML Compatibility New Lang
            <?php if((acf_maybe_get_GET('from_tag') && acf_maybe_get_GET('new_lang')) || acf_maybe_get_GET('trid')){ ?>
            
                var $button = $('.acfe-bt-admin-button-add');
                
                if($button.length){
                    $button.click();
                }
                
            <?php } ?>

        })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Term: Footer Edit
     */
    function term_footer_edit(){
    
        global $tag, $tax;
        
        ?>
        <div class="permalink">
            <?php if(isset($tax->publicly_queryable) && !empty($tax->publicly_queryable)){ ?>
                <div id="edit-slug-box">
                    <strong>Permalink:</strong> <a href="<?php echo get_term_link($tag, $tax); ?>"><?php echo get_term_link($tag, $tax); ?></a>
                </div>
            <?php } ?>
        </div>
        <script type="text/javascript">
        (function($){
            
            acfe.enhancedEditUI({
                screen:     'term-edit',
                submit:     '> .edit-tag-actions',
                pageTitle:  true
            });

        })(jQuery);
        </script>
        <?php
    
    }
    
    /*
     * User: Screen Edit
     */
    function user_screen_edit($user){
    
        // add compatibility with front-end user profile edit forms such as bbPress
        if(!is_admin()){
            acf_enqueue_scripts();
        }
    
        // render
        $this->user_add_metaboxes(array(
            'user_id'   => $user->ID,
            'view'      => 'edit'
        ));
    
    }
    
    /*
     * User: Screen New
     */
    function user_screen_new(){
        
        // Multisite uses a different 'user-new.php' form. Don't render fields here
        if(is_multisite()){
            return;
        }
        
        // render
        $this->user_add_metaboxes(array(
            'user_id'   => 0,
            'view'      => 'add'
        ));
        
    }
    
    /*
     * User: Add Metaboxes
     */
    function user_add_metaboxes($args = array()){
        
        // Native ACF Form user
        $acf_form_user = acf_get_instance('ACF_Form_User');
    
        // Allow $_POST data to persist across form submission attempts.
        if(isset($_POST['acf'])){
            add_filter('acf/pre_load_value', array($acf_form_user, 'filter_pre_load_value'), 10, 3);
        }
    
        // args
        $args = wp_parse_args($args, array(
            'user_id'    => 0,
            'view'        => 'edit'
        ));
        
        // screen
        $screen = 'user'; // new
        
        if($args['view'] == 'edit'){
            $screen = IS_PROFILE_PAGE ? 'profile' : 'user-edit';
        }
    
        // post id
        $post_id = 'user_' . $args['user_id'];
    
        // field groups
        $field_groups = acf_get_field_groups(array(
            'user_id'   => $args['user_id'] ? $args['user_id'] : 'new',
            'user_form' => $args['view']
        ));
        
        if($field_groups){
    
            // form data
            acf_form_data(array(
                'screen'        => 'user',
                'post_id'       => $post_id,
                'validation'    => ($args['view'] == 'register') ? 0 : 1
            ));
    
            $this->add_metaboxes($field_groups, $post_id, $screen);
        
            // actions
            add_action('acf/input/admin_footer', array($acf_form_user, 'admin_footer'), 10, 1);
        
        }
    
        // Sidebar submit
        add_meta_box('submitdiv', __('Edit'), array($this, 'render_metabox_submit'), $screen, 'side', 'high');
        
    }
    
    /*
     * User: Do Metaboxes
     */
    function user_do_metaboxes($user){
        
        do_meta_boxes(get_current_screen(), 'acf_after_title', $user);
        do_meta_boxes(get_current_screen(), 'normal', $user);
        do_meta_boxes(get_current_screen(), 'side', $user);

    }
    
    /*
     * User: Footer New
     */
    function user_footer_new(){
        ?>
        <script type="text/javascript">
        (function($){

            acfe.enhancedEditUI({
                screen:     'user-new',
                pageTitle:  true
            });

        })(jQuery);
        </script>
        <?php
    }
    
    /*
     * User: Footer Edit
     */
    function user_footer_edit(){
        
        global $profileuser;
        
        ?>
        <div id="edit-slug-box">
            <strong>Permalink:</strong> <a href="<?php echo get_author_posts_url($profileuser->ID); ?>"><?php echo get_author_posts_url($profileuser->ID); ?></a>
        </div>
        <script type="text/javascript">
            (function($){

                acfe.enhancedEditUI({
                    screen: 'user-edit'
                });

            })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Settings: Add Metaboxes
     */
    function settings_add_metaboxes(){
        
        $screen = get_current_screen()->id;
    
        // post id
        $post_id = acf_get_valid_post_id($screen);
        
        // field groups
        $field_groups = acf_get_field_groups(array(
            'wp_settings' => $screen
        ));
        
        if($field_groups){
    
            // form data
            acf_form_data(array(
                'screen'    => 'wp_settings',
                'post_id'   => $post_id,
            ));
            
            $this->add_metaboxes($field_groups, $post_id, $screen);
            
        }
    
        // Sidebar submit
        add_meta_box('submitdiv', __('Edit'), array($this, 'render_metabox_submit'), $screen, 'side', 'high');
        
    }
    
    /*
     * Settings: Do Metaboxes
     */
    function settings_do_metaboxes(){
        
        do_meta_boxes(get_current_screen(), 'acf_after_title', array());
        do_meta_boxes(get_current_screen(), 'normal', array());
        do_meta_boxes(get_current_screen(), 'side', array());
        
    }
    
    /*
     * Settings: Footer
     */
    function settings_footer(){
    
        global $pagenow;
    
        ?>
        <script type="text/javascript">
        (function($){

            var pageTitle = false;
        
            <?php if(!in_array($pagenow, array('options-permalink.php', 'options-media.php'))){ ?>
                pageTitle = true;
            <?php } ?>

            acfe.enhancedEditUI({
                screen:     'settings',
                pageTitle:  pageTitle
            });

        })(jQuery);
        </script>
        <?php
    }
    
    /*
     * Add Field Groups Metaboxes
     */
    function add_metaboxes($field_groups, $post_id, $screen){
    
        $postboxes = array();
    
        foreach($field_groups as $field_group){
        
            // vars
            $id = "acf-{$field_group['key']}";      // acf-group_123
            $title = $field_group['title'];         // Group 1
            $context = $field_group['position'];    // normal, side, acf_after_title
            $priority = 'high';                     // high, core, default, low
        
            // Reduce priority for sidebar metaboxes for best position.
            if($context == 'side'){
                $priority = 'core';
            }
        
            $priority = apply_filters('acf/input/meta_box_priority', $priority, $field_group);
        
            // Localize data
            $postboxes[] = array(
                'id'    => $id,
                'key'   => $field_group['key'],
                'style' => $field_group['style'],
                'label' => $field_group['label_placement'],
                'edit'  => acf_get_field_group_edit_link($field_group['ID'])
            );
        
            // Add meta box
            add_meta_box($id, $title, array($this, 'render_metabox'), $screen, $context, $priority, array('post_id' => $post_id, 'field_group' => $field_group));
        
        }
    
        // Localize postboxes.
        acf_localize_data(array(
            'postboxes' => $postboxes
        ));
    
    }
    
    /*
     * Render Metabox
     */
    function render_metabox($post, $metabox){
        
        // vars
        $post_id = $metabox['args']['post_id'];
        $field_group = $metabox['args']['field_group'];
        
        // Render fields.
        $fields = acf_get_fields($field_group);
        acf_render_fields($fields, $post_id, 'div', $field_group['instruction_placement']);
        
    }
    
    /*
     * Render Metabox Submit
     */
    function render_metabox_submit($post, $metabox){
        ?>
        <div class="submitbox">
            <div id="major-publishing-actions">
                <div id="publishing-action"></div>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

}

new acfe_enhanced_ui();

endif;