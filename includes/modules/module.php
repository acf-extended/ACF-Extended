<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_dynamic_module')):

class acfe_dynamic_module{
    
    // vars
    public  $active = false,
            $settings = '',
            $post_type = '',
            $label = '',
            $textdomain = '',
            $tool = '',
            $columns = array(),
            $tools = array();
    
    /*
     * Construct
     */
    function __construct(){
    
        $this->initialize();
    
        if(!$this->active) return;
        
        $this->actions();
        $this->add_local_field_group();
    
        add_action('init',                      array($this, 'init'));
        add_action('current_screen',            array($this, '_current_screen'));
        
        add_action('acf/save_post',             array($this, '_save_post'), 20);
        add_action('trashed_post',              array($this, '_trashed_post'));
        add_action('untrashed_post',            array($this, '_untrashed_post'));
        add_filter('acf/get_post_types',        array($this, 'get_post_types'), 10, 2);
        
    }
    
    /*
     * Initialize
     */
    function initialize(){
        // ...
    }
    
    /*
     * Init
     */
    function actions(){
        // ...
    }
    
    /*
     * Get Name
     */
    function get_name($post_id){
        return false;
    }
    
    /*
     * Init
     */
    function init(){
        // ...
    }
    
    /*
     * Current Screen
     */
    function _current_screen(){
    
        $this->current_screen();
        
        // Single
        if(acf_is_screen($this->post_type)){
            
            remove_meta_box('slugdiv', $this->post_type, 'normal');
    
            add_action('admin_enqueue_scripts',                         array($this, '_post_head'));
            add_action('post_submitbox_misc_actions',                   array($this, '_post_submitbox_misc_actions'));
            add_filter('enter_title_here',                              array($this, 'post_enter_title_here'), 10, 2);
            add_action('admin_footer',                                  array($this, '_post_footer'));
            add_action('load-post.php',                                 array($this, 'post_load'));
            add_action('load-post-new.php',                             array($this, 'post_new_load'));
            add_filter('submenu_file',                                  array($this, 'submenu_file'));
            
            $this->post_screen();
            
        // List
        }elseif(acf_is_screen("edit-{$this->post_type}")){
    
            global $wp_post_statuses;
            $wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'acf' );
    
            add_filter("manage_edit-{$this->post_type}_columns",        array($this, '_edit_columns'));
            add_action("manage_{$this->post_type}_posts_custom_column", array($this, 'edit_columns_html'), 10, 2);
            add_filter('display_post_states',                           array($this, 'display_post_states'), 10, 2);
            add_filter('post_row_actions',                              array($this, '_edit_row_actions'), 10, 2);
            add_filter('page_row_actions',                              array($this, '_edit_row_actions'), 10, 2);
            add_action('admin_footer',                                  array($this, 'edit_footer'));
            add_action('load-edit.php',                                 array($this, 'edit_load'));
            add_filter("bulk_actions-edit-{$this->post_type}",          array($this, 'bulk_actions'));
            add_filter("handle_bulk_actions-edit-{$this->post_type}",   array($this, 'handle_bulk_actions'), 10, 3);
    
            $this->edit_screen();
            
        }
        
    }
    
    function current_screen(){
        // ...
    }
    
    function post_screen(){
        // ...
    }
    
    function post_load(){
        // ...
    }
    
    function post_new_load(){
        // ...
    }
    
    function submenu_file($submenu_file){
        return "edit.php?post_type={$this->post_type}";
    }
    
    function edit_screen(){
        // ...
    }
    
    function edit_load(){
        // ...
    }
    
    function bulk_actions($actions){
        
        acfe_unset($actions, 'edit');
    
        foreach($this->tools as $action){
            
            $action_name = $action === 'php' ? 'PHP' : 'Json';
            $actions["export_{$action}"] = __('Export ', 'acf') . $action_name;
            
        }
        
        return $actions;
        
    }
    
    function handle_bulk_actions($redirect, $action, $post_ids){
        
        $post_ids = acfe_maybe_get_REQUEST('post');
        
        if(!$post_ids){
            return $redirect;
        }
    
        foreach($this->tools as $tool_action){
            
            if($action !== "export_{$tool_action}") continue;
            
            $keys = array();
            foreach($post_ids as $post_id){
                
                $name = $this->get_name($post_id);
                
                if(!$name) continue;
                
                $keys[] = $name;
                
            }
            
            $keys = implode('+', $keys);
            $url = admin_url("edit.php?post_type=acf-field-group&page=acf-tools&tool={$this->tool}&action={$tool_action}&keys={$keys}");
            
            wp_redirect($url);
            exit;
        
        }
        
        return $redirect;
        
    }
    
    /*
     * Post Head
     */
    function _post_head(){
    
        // no autosave
        wp_dequeue_script('autosave');
    
        $this->post_head();
        
    }
    
    function post_head(){
        // ...
    }
    
    /*
     * Post Submit Box
     */
    function _post_submitbox_misc_actions($post){
        
        $status = $post->post_status === 'publish' || $post->post_status === 'auto-draft' ? __("Active",'acf') : __("Inactive",'acf');
        $name = $this->get_name($post->ID);
        
        $tools = array();
        
        foreach($this->tools as $action){
            
            $action_name = $action === 'php' ? 'PHP' : 'Json';
            $tools[] = '<a href="' . admin_url("edit.php?post_type=acf-field-group&page=acf-tools&tool={$this->tool}&action={$action}&keys={$name}") . '">' . $action_name . '</a>';
            
        }
        
        if($tools){ ?>
        <div class="misc-pub-section acfe-misc-export">
            <span class="dashicons dashicons-editor-code"></span>
            Export: <?php echo implode(' ', $tools); ?>
        </div>
        <?php } ?>
        <script type="text/javascript">
            (function($) {
                $('#post-status-display').html('<?php echo $status; ?>');
                <?php if($tools){ ?>$('.acfe-misc-export').insertAfter('.misc-pub-post-status');<?php } ?>
            })(jQuery);
        </script>
        <?php
        
        $this->post_submitbox_misc_actions($post);
        
    }
    
    function post_submitbox_misc_actions($post){
        // ...
    }
    
    /*
     * Post Enter Title Here
     */
    function post_enter_title_here($placeholder, $post){
        return $this->label;
    }
    
    /*
     * Post Footer
     */
    function _post_footer(){
        
        ?>
        <script type="text/javascript">
        (function($){

            $('#post').submit(function(e){

                // vars
                var $title = $('#titlewrap #title');

                // empty
                if($title.val()) return;
                
                e.preventDefault();
                
                alert('<?php echo $this->label; ?> is required.');
                
                $title.focus();
            });

        })(jQuery);
        </script>
        <?php
    
        $this->post_footer();
        
    }
    
    function post_footer(){
        // ...
    }
    
    /*
     * Edit Columns
     */
    function _edit_columns($columns){
        
        if(empty($this->columns)){
            return $columns;
        }
    
        $columns = array_merge(array('cb' => $columns['cb'], 'title' => $columns['title']), $this->columns);
        
        return $this->edit_columns($columns);
        
    }
    
    function edit_columns($columns){
        return $columns;
    }
    
    /*
     * Edit Columns HTML
     */
    function edit_columns_html($column, $post_id){
        // ...
    }
    
    /*
     * Display Post States
     */
    function display_post_states($post_states, $post){
        
        if($post->post_status === 'acf-disabled'){
            $post_states['acf-disabled'] = '<span class="dashicons dashicons-hidden acf-js-tooltip" title="' . _x('Disabled', 'post status', 'acf') . '"></span>';
        }
        
        return $post_states;
        
    }
    
    /*
     * Edit Row Actions
     */
    function _edit_row_actions($actions, $post){
    
        if(!in_array($post->post_status, array('publish', 'acf-disabled'))){
            return $actions;
        }
    
        $post_id = $post->ID;
        $name = $this->get_name($post_id);
        
        acfe_unset($actions, 'inline hide-if-no-js');
        
        // View
        $view = $this->edit_row_actions_view($post, $name);
        
        if($view){
            $actions['view'] = $view;
        }
        
        // Tools
        foreach($this->tools as $action){
            
            $action_name = $action === 'php' ? 'PHP' : 'Json';
            $actions[$action] = '<a href="' . admin_url("edit.php?post_type=acf-field-group&page=acf-tools&tool={$this->tool}&action={$action}&keys={$name}") . '">' . $action_name . '</a>';
            
        }
        
        return $this->edit_row_actions($actions, $post);
        
    }
    
    function edit_row_actions($actions, $post){
        return $actions;
    }
    
    function edit_row_actions_view($post, $name){
        return false;
    }
    
    /*
     * Edit Foot
     */
    function edit_footer(){
        // ...
    }
    
    /*
     * ACF Save post
     */
    function _save_post($post_id){
    
        if(!is_numeric($post_id) || get_post_type($post_id) !== $this->post_type){
            return;
        }
        
        $this->save_post($post_id);
        
    }
    
    function save_post($post_id){
        // ...
    }
    
    /*
     * Trashed Post Type
     */
    function _trashed_post($post_id){
        
        if(get_post_type($post_id) !== $this->post_type){
            return;
        }
    
        $this->trashed_post($post_id);
        
    }
    
    function trashed_post($post_id){
        // ...
    }
    
    /*
     * Untrashed Post Type
     */
    function _untrashed_post($post_id){
        
        if(get_post_type($post_id) !== $this->post_type){
            return;
        }
        
        $this->_save_post($post_id);
        $this->untrashed_post($post_id);
        
    }
    
    function untrashed_post($post_id){
        // ...
    }
    
    /*
     * Import
     */
    function import($name, $args){
        // ...
    }
    
    /*
     * Export
     */
    function export_choices(){
        return array();
    }
    
    function export_data($name){
        // ...
    }
    
    function export_php($data){
        return false;
    }
    
    /*
     * Reset
     */
    function reset(){
        // ...
    }
    
    /*
     * Exclude Post Type
     */
    function get_post_types($post_types, $args){
        
        foreach($post_types as $k => $post_type){
            
            if($post_type !== $this->post_type) continue;
            
            unset($post_types[$k]);
            
        }
        
        return $post_types;
        
    }
    
    /*
     * Get Field Labels Recursive
     */
    function get_fields_labels_recursive(&$array, $field){
        
        $label = '';
        
        $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
        $label = str_repeat('- ', $ancestors) . $label;
        
        $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
        $label .= $field['required'] ? ' <span class="acf-required">*</span>' : '';
        
        $array[$field['key']] = $label;
        
        if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
            
            foreach($field['sub_fields'] as $s_field){
                
                $this->get_fields_labels_recursive($array, $s_field);
                
            }
            
        }
        
    }
    
    /*
     * Add Local Field Group
     */
    function add_local_field_group(){
        // ...
    }
    
}

endif;