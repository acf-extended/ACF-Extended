<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dynamic_options_pages')):

class acfe_dynamic_options_pages extends acfe_dynamic_module{
    
    /*
     * Initialize
     */
    function initialize(){
    
        $this->name = 'options_page';
        $this->active = acf_get_setting('acfe/modules/options_pages');
        $this->settings = 'modules.options_pages';
        $this->post_type = 'acfe-dop';
        $this->label = 'Options Page Title';
        $this->textdomain = 'ACF Extended: Options Pages';
        
        $this->tool = 'acfe_dynamic_options_pages_export';
        $this->tools = array('php', 'json');
        $this->columns = array(
            'acfe-name'      => __('Menu slug', 'acf'),
            'acfe-post-id'   => __('Post ID', 'acf'),
            'acfe-autoload'  => __('Autoload', 'acf'),
            'acfe-position'  => __('Position', 'acf'),
        );
        
    }
    
    /*
     * Actions
     */
    function actions(){
        
        // Features
        add_action('admin_footer',                                      array($this, 'admin_config'));
        add_action('pre_get_posts',                                     array($this, 'admin_archive_posts'), 15);
        
        // Validate
        add_filter('acf/validate_value/key=field_acfe_dop_menu_slug',   array($this, 'validate_name'), 10, 4);
        add_filter('acf/update_value/key=field_acfe_dop_menu_slug',     array($this, 'update_name'), 10, 3);
        
        // Register
        add_filter('acfe/options_page/prepare_register',                array($this, 'prepare_register'));
        
        // Save
        add_filter('acfe/options_page/save_args',                       array($this, 'save_args'), 10, 3);
        add_action('acfe/options_page/save',                            array($this, 'save'), 10, 3);
        
        // Import
        add_action('acfe/options_page/import_fields',                   array($this, 'import_fields'), 10, 3);
        add_action('acfe/options_page/import',                          array($this, 'after_import'), 10, 2);
    
        // Multilang
        add_action('acfe/options_page/save',                            array($this, 'l10n_save'), 10, 3);
        add_filter('acfe/options_page/register',                        array($this, 'l10n_register'), 10, 2);
    
        $this->register_user_options_pages();
        
    }
    
    /*
     * Get Name
     */
    function get_name($post_id){
        
        return get_field('menu_slug', $post_id);
        
    }
    
    /*
     * Init
     */
    function init(){
    
        $this->register_post_type();
        
    }
    
    /*
     * Register Post Type
     */
    function register_post_type(){
    
        $capability = acf_get_setting('capability');
        
        if(!acf_get_setting('show_admin'))
            $capability = false;
        
        register_post_type($this->post_type, array(
            'label'                 => 'Options Page',
            'description'           => 'Options Page',
            'labels'                => array(
                'name'          => 'Options Pages',
                'singular_name' => 'Options Page',
                'menu_name'     => 'Options Pages',
                'edit_item'     => 'Edit Options Page',
                'add_new_item'  => 'New Options Page',
            ),
            'supports'              => array('title'),
            'hierarchical'          => true,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=acf-field-group',
            'menu_icon'             => 'dashicons-layout',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'rewrite'               => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capabilities'          => array(
                'publish_posts'         => $capability,
                'edit_posts'            => $capability,
                'edit_others_posts'     => $capability,
                'delete_posts'          => $capability,
                'delete_others_posts'   => $capability,
                'read_private_posts'    => $capability,
                'edit_post'             => $capability,
                'delete_post'           => $capability,
                'read_post'             => $capability,
            ),
            'acfe_admin_orderby'    => 'title',
            'acfe_admin_order'      => 'ASC',
            'acfe_admin_ppp'        => 999,
        ));
        
    }
    
    /*
     * Register User Options Pages
     */
    function register_user_options_pages(){
        
        $settings = apply_filters('acfe/options_page/prepare_register', acfe_get_settings($this->settings));
    
        if(empty($settings))
            return;
    
        foreach($settings as $name => $args){
            
            // Bail early
            if(empty($name) || acf_get_options_page($name))
                continue;
    
            // Filters
            $args = apply_filters("acfe/options_page/register",                 $args, $name);
            $args = apply_filters("acfe/options_page/register/name={$name}",    $args, $name);
            
            if($args === false)
                continue;
    
            // Register
            acf_add_options_page($args);
        
        }
        
    }
    
    /*
     * Post Head
     */
    function post_head(){
        
        global $post;
    
        $post_id = $post->ID;
        $name = $this->get_name($post_id);
    
        $field_groups = acf_get_field_groups(array(
            'options_page' => $name
        ));
    
        if($field_groups){
    
            add_meta_box( 'acfe-dop-field-groups', __('Field Groups', 'acf'), array($this, 'metabox_render'), $this->post_type, 'normal', 'default', $field_groups);
            
        }
        
    }
    
    /*
     * Metabox Render
     */
    function metabox_render($array, $data){
        
        $data = $data['args'];
        
        foreach($data as $field_group){ ?>

            <div class="acf-field">

                <div class="acf-label">
                    <label for="acf-_post_title"><a href="<?php echo admin_url('post.php?post=' . $field_group['ID'] . '&action=edit'); ?>"><?php echo $field_group['title']; ?></a></label>
                    <p class="description"><?php echo $field_group['key']; ?></p>
                </div>

                <div class="acf-input">
                    <?php $fields = acf_get_fields($field_group); ?>
                    
                    <?php if(!empty($fields)){ ?>

                        <table class="acf-table">
                            <thead>
                            <th class="acf-th" width="25%"><strong>Label</strong></th>
                            <th class="acf-th" width="25%"><strong>Name</strong></th>
                            <th class="acf-th" width="25%"><strong>Key</strong></th>
                            <th class="acf-th" width="25%"><strong>Type</strong></th>
                            </thead>

                            <tbody>
                            <?php
                            
                            $array = array();
                            foreach($fields as $field){
                                
                                $this->get_fields_labels_recursive($array, $field);
                                
                            }
                            
                            foreach($array as $field_key => $field_label){
                                
                                $field = acf_get_field($field_key);
                                $type = acf_get_field_type($field['type']);
                                $type_label = '-';
                                if(isset($type->label))
                                    $type_label = $type->label;
                                ?>

                                <tr class="acf-row">
                                    <td width="25%"><?php echo $field_label; ?></td>
                                    <td width="25%"><?php echo $field['name']; ?></td>
                                    <td width="25%"><code><?php echo $field_key; ?></code></td>
                                    <td width="25%"><?php echo $type_label; ?></td>
                                </tr>
                            
                            <?php } ?>
                            </tbody>
                        </table>
                    
                    <?php } ?>
                </div>

            </div>
        
        <?php } ?>

        <script type="text/javascript">
        (function($){

            if(typeof acf === 'undefined')
                return;

            acf.newPostbox(<?php echo wp_json_encode(array(
                'id'    => 'acfe-dop-field-groups',
                'key'   => '',
                'style' => 'default',
                'label' => 'left',
                'edit'  => false
            )); ?>);

        })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Edit Columns HTML
     */
    function edit_columns_html($column, $post_id){
        
        switch($column){
            
            // Name
            case 'acfe-name':
                
                echo '<code style="font-size: 12px;">' . $this->get_name($post_id) . '</code>';
                break;
            
            // Post ID
            case 'acfe-post-id':
    
                $p_id = get_field('post_id', $post_id);
    
                if(empty($p_id))
                    $p_id = 'options';
    
                echo '<code style="font-size: 12px;">' . $p_id. '</code>';
                break;
            
            // Autoload
            case 'acfe-autoload':
                
                $autoload = get_field('autoload', $post_id);
                echo $autoload ? __('Yes') : __('No');
                break;
                
            // Position
            case 'acfe-position':
                
                $position = get_field('position', $post_id);
                echo !acf_is_empty($position) ? $position : '—';
                break;
            
        }
        
    }
    
    /*
     * Edit Row Actions View
     */
    function edit_row_actions_view($post, $name){
        
        return '<a href="' . admin_url("admin.php?page={$name}") . '">' . __('View') . '</a>';
        
    }
    
    /*
     * Admin Config Button
     */
    function admin_config(){
    
        if(!acf_current_user_can_admin())
            return;
    
        global $plugin_page;
    
        if(!$plugin_page)
            return;
    
        $page = acf_get_options_page($plugin_page);
    
        if(!acf_maybe_get($page, 'menu_slug'))
            return;
    
        // Get Dynamic Options Page
        $acfe_dop_options_page = get_posts(array(
            'post_type'         => $this->post_type,
            'posts_per_page'    => 1,
            'name'              => $page['menu_slug']
        ));
    
        if(empty($acfe_dop_options_page))
            return;
    
        $acfe_dop_options_page = $acfe_dop_options_page[0];
    
        ?>
        <script type="text/html" id="tmpl-acfe-dop-title-config">
            <a href="<?php echo admin_url('post.php?post=' . $acfe_dop_options_page->ID . '&action=edit'); ?>" class="page-title-action acfe-edit-module-button"><span class="dashicons dashicons-admin-generic"></span></a>
        </script>

        <script type="text/javascript">
        (function($){

            // Add button
            $('.wrap > h1').append($('#tmpl-acfe-dop-title-config').html());

        })(jQuery);
        </script>
        <?php
    
    }
    
    /*
     * Admin: Archive Posts
     */
    function admin_archive_posts($query){
        
        global $pagenow;
        
        if (!is_admin() || !$query->is_main_query() || $pagenow !== 'edit.php' || $query->get('post_type') !== $this->post_type)
            return;
        
        $query->set('meta_key', 'position');
        $query->set('orderby', 'meta_value_num title');
        $query->set('order', 'ASC');
        
    }
    
    /*
     * Validate Name
     */
    function validate_name($valid, $value, $field, $input){
    
        if(!$valid)
            return $valid;
    
        // Editing Current Block Type
        $current_post_id = acf_maybe_get_POST('post_ID');
    
        if(!empty($current_post_id)){
        
            $current_name = get_field($field['name'], $current_post_id);
        
            if($value === $current_name)
                return $valid;
        
        }
    
        // Check existing ACF Options Pages
        $pages = acf_get_options_pages();
    
        if(!empty($pages)){
        
            foreach($pages as $slug => $page){
            
                if($slug !== $value)
                    continue;
            
                $valid = __('This options page slug already exists');
            
            }
        
        }
    
        return $valid;
    
    }
    
    /*
     * Update Name
     */
    function update_name($value, $post_id, $field){
    
        // Previous value
        $_value = get_field($field['name'], $post_id);
    
        // Value Changed. Delete option
        if($_value !== $value){
            acfe_delete_settings("{$this->settings}.{$_value}");
        }
    
        return $value;
    
    }
    
    /*
     * Prepare Register
     */
    function prepare_register($settings){
        
        if(empty($settings))
            return $settings;
        
        // Init re-order
        $top_pages = $sub_pages = array();
    
        foreach($settings as $name => $args){
        
            // Sub pages
            if(acf_maybe_get($args, 'parent_slug')){
                
                // force int position
                $args['position'] = (int) $args['position'];
                
                // save sub page
                $sub_pages[$name] = $args;
                
                continue;
                
            }
        
            // Top pages
            $top_pages[$name] = $args;
        
        }
    
        // Re-order sub pages
        if(!empty($sub_pages)){
            
            uasort($sub_pages, function($a, $b){
                return (int) $a['position'] - (int) $b['position'];
            });
            
        }
    
        // Merge
        $settings = array_merge($top_pages, $sub_pages);
        
        return $settings;
        
    }
    
    /*
     * ACF Save post
     */
    function save_post($post_id){
        
        // vars
        $args = array();
        $name = $this->get_name($post_id);
        
        // Filters
        $args = apply_filters("acfe/options_page/save_args",                $args, $name, $post_id);
        $args = apply_filters("acfe/options_page/save_args/name={$name}",   $args, $name, $post_id);
        $args = apply_filters("acfe/options_page/save_args/id={$post_id}",  $args, $name, $post_id);
        
        if($args === false)
            return;
        
        // Actions
        do_action("acfe/options_page/save",                 $name, $args, $post_id);
        do_action("acfe/options_page/save/name={$name}",    $name, $args, $post_id);
        do_action("acfe/options_page/save/id={$post_id}",   $name, $args, $post_id);
        
    }
    
    /*
     * Save Args
     */
    function save_args($args, $name, $post_id){
        
        $page_title = get_post_field('post_title', $post_id);
        $name = get_field('menu_slug', $post_id);
        
        // Menu Title
        $menu_title = get_field('menu_title', $post_id);
        if(empty($menu_title))
            $menu_title = $page_title;
        
        // Register Args
        $parent_slug = get_field('parent_slug', $post_id);
        $capability = get_field('capability', $post_id);
        $position = get_field('position', $post_id);
        $icon_url = get_field('icon_url', $post_id);
        $redirect = get_field('redirect', $post_id);
        $p_id = get_field('post_id', $post_id);
        $autoload = get_field('autoload', $post_id);
        $update_button = get_field('update_button', $post_id);
        $updated_message = get_field('updated_message', $post_id);
        
        // Register: Args
        $args = array(
            'page_title'        => $page_title,
            'menu_slug'         => $name,
            'menu_title'        => $menu_title,
            'capability'        => $capability,
            'position'          => $position,
            'parent_slug'       => $parent_slug,
            'icon_url'          => $icon_url,
            'redirect'          => $redirect,
            'post_id'           => $p_id,
            'autoload'          => $autoload,
            'update_button'     => $update_button,
            'updated_message'   => $updated_message,
        );
        
        // Redirect
        $args['redirect'] = true;
        if(empty($redirect))
            $args['redirect'] = false;
        
        // Autoload
        $args['autoload'] = true;
        if(empty($autoload))
            $args['autoload'] = false;
        
        // Post ID
        if(empty($p_id))
            $args['post_id'] = 'options';
        
        return $args;
        
    }
    
    /*
     * Save
     */
    function save($name, $args, $post_id){
        
        // Parent
        $parent = 0;
        $parent_slug = $args['parent_slug'];
        
        if(!empty($parent_slug)){
            
            $get_dop_parent = get_posts(array(
                'post_type'         => $this->post_type,
                'posts_per_page'    => 1,
                'fields'            => 'ids',
                'meta_query'        => array(
                    array(
                        'key'   => 'menu_slug',
                        'value' => $parent_slug
                    )
                )
            ));
            
            if(!empty($get_dop_parent)){
                $parent = $get_dop_parent[0];
            }
            
        }
        
        // Get ACFE option
        $settings = acfe_get_settings($this->settings);
        
        // Create ACFE option
        $settings[$name] = $args;
        
        // Sort keys ASC
        ksort($settings);
        
        // Update ACFE option
        acfe_update_settings($this->settings, $settings);
        
        // Update post
        wp_update_post(array(
            'ID'            => $post_id,
            'post_name'     => $name,
            'post_parent'   => $parent,
            'post_status'   => 'publish',
        ));
        
    }
    
    /*
     * Trashed Post Type
     */
    function trashed_post($post_id){
        
        $name = $this->get_name($post_id);
        
        // Get ACFE option
        $settings = acfe_get_settings($this->settings);
        
        // Unset ACFE option
        acfe_unset($settings, $name);
        
        // Update ACFE option
        acfe_update_settings($this->settings, $settings);
        
    }
    
    /*
     * Import
     */
    function import($name, $args){
    
        // Vars
        $settings = acfe_get_settings($this->settings);
        $title = $args['page_title'];
    
        // Already exists
        if(isset($settings[$name])){
            return new WP_Error('acfe_dop_import_already_exists', __("Options page \"{$title}\" already exists. Import aborted."));
        }
    
        // Import Post
        $post_id = false;
    
        $post = array(
            'post_title'    => $title,
            'post_name'     => $name,
            'post_type'     => $this->post_type,
            'post_status'   => 'publish'
        );
    
        $post = apply_filters("acfe/options_page/import_post",                 $post, $name);
        $post = apply_filters("acfe/options_page/import_post/name={$name}",    $post, $name);
    
        if($post !== false){
            $post_id = wp_insert_post($post);
        }
    
        if(!$post_id || is_wp_error($post_id)){
            return new WP_Error('acfe_dop_import_error', __("Something went wrong with the options page \"{$title}\". Import aborted."));
        }
    
        // Import Args
        $args = apply_filters("acfe/options_page/import_args",                 $args, $name, $post_id);
        $args = apply_filters("acfe/options_page/import_args/name={$name}",    $args, $name, $post_id);
        $args = apply_filters("acfe/options_page/import_args/name={$post_id}", $args, $name, $post_id);
    
        if($args === false)
            return $post_id;
    
        // Import Fields
        acf_enable_filter('local');
        
        do_action("acfe/options_page/import_fields",                  $name, $args, $post_id);
        do_action("acfe/options_page/import_fields/name={$name}",     $name, $args, $post_id);
        do_action("acfe/options_page/import_fields/id={$post_id}",    $name, $args, $post_id);
    
        acf_disable_filter('local');
        
        // Save
        $this->save_post($post_id);
        
        return $post_id;
        
    }
    
    function import_fields($name, $args, $post_id){
        
        update_field('menu_title', $args['menu_title'], $post_id);
        update_field('menu_slug', $args['menu_slug'], $post_id);
        update_field('capability', $args['capability'], $post_id);
        update_field('position', $args['position'], $post_id);
        update_field('parent_slug', $args['parent_slug'], $post_id);
        update_field('icon_url', $args['icon_url'], $post_id);
        update_field('redirect', $args['redirect'], $post_id);
        update_field('post_id', $args['post_id'], $post_id);
        update_field('autoload', $args['autoload'], $post_id);
        update_field('update_button', $args['update_button'], $post_id);
        update_field('updated_message', $args['updated_message'], $post_id);
        
    }
    
    /*
     * After Import
     */
    function after_import($ids, $data){
    
        $sub_pages = array();
    
        // Loop over json
        foreach($ids as $post_id){
            
            $parent_slug = get_field('parent_slug', $post_id);
            
            if(empty($parent_slug))
                continue;
    
            $sub_pages[$post_id] = $parent_slug;
        
        }
        
        // Update Options Sub Pages
        if(!empty($sub_pages)){
        
            foreach($sub_pages as $post_id => $parent_slug){
            
                // Get possible parent options pages
                $get_parent = get_posts(array(
                    'post_type'         => $this->post_type,
                    'posts_per_page'    => 1,
                    'fields'            => 'ids',
                    'meta_query'        => array(
                        array(
                            'key'   => 'menu_slug',
                            'value' => $parent_slug
                        )
                    )
                ));
            
                if(empty($get_parent))
                    continue;
            
                // Update sub page post
                wp_update_post(array(
                    'ID'            => $post_id,
                    'post_parent'   => $get_parent[0],
                ));
            
            }
        
        }
    
    }
    
    /*
     * Export: Choices
     */
    function export_choices(){
        
        $choices = array();
        $settings = acfe_get_settings($this->settings);
        
        if(!$settings)
            return $choices;
        
        foreach($settings as $name => $args){
            
            $choices[$name] = esc_html($args['page_title']);
            
        }
        
        return $choices;
        
    }
    
    /*
     * Export: Data
     */
    function export_data($name){
        
        // Settings
        $settings = acfe_get_settings($this->settings);
        
        // Doesn't exist
        if(!isset($settings[$name]))
            return false;
        
        $args = $settings[$name];
        
        // Filters
        $args = apply_filters("acfe/options_page/export_args",                 $args, $name);
        $args = apply_filters("acfe/options_page/export_args/name={$name}",    $args, $name);
        
        // Return
        return $args;
        
    }
    
    /*
     * Export: PHP
     */
    function export_php($data){
        
        // prevent default translation and fake __() within string
        acf_update_setting('l10n_var_export', true);
        
        $str_replace = array(
            "  "            => "\t",
            "'!!__(!!\'"    => "__('",
            "!!\', !!\'"    => "', '",
            "!!\')!!'"      => "')",
            "array ("       => "array("
        );
        
        $preg_replace = array(
            '/([\t\r\n]+?)array/'   => 'array',
            '/[0-9]+ => array/'     => 'array'
        );
        
        // Get settings.
        $l10n = acf_get_setting('l10n');
        $l10n_textdomain = acf_get_setting('l10n_textdomain');
        
        echo "if( function_exists('acf_add_options_page') ):" . "\r\n" . "\r\n";
        
        foreach($data as $args){
            
            // Translate settings if textdomain is set.
            if($l10n && $l10n_textdomain){
                
                $args['page_title'] = acf_translate($args['page_title']);
                $args['menu_title'] = acf_translate($args['menu_title']);
                $args['update_button'] = acf_translate($args['update_button']);
                $args['updated_message'] = acf_translate($args['updated_message']);
                
            }
            
            // code
            $code = var_export($args, true);
            
            // change double spaces to tabs
            $code = str_replace(array_keys($str_replace), array_values($str_replace), $code);
            
            // correctly formats "=> array("
            $code = preg_replace(array_keys($preg_replace), array_values($preg_replace), $code);
            
            // esc_textarea
            $code = esc_textarea($code);
            
            // echo
            echo "acf_add_options_page({$code});" . "\r\n" . "\r\n";
            
        }
        
        echo "endif;";
        
    }
    
    /*
     * Reset
     */
    function reset(){
        
        $args = apply_filters("acfe/options_page/reset_args", array(
            'post_type'         => $this->post_type,
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'post_status'       => array('publish', 'acf-disabled'),
        ));
        
        $posts = get_posts($args);
        
        if(empty($posts))
            return false;
        
        foreach($posts as $post_id){
            $this->save_post($post_id);
        }
        
        // Log
        acf_log('[ACF Extended] Reset: Options Pages');
        
        return true;
        
    }
    
    /*
     * Multilang Save
     */
    function l10n_save($name, $args, $post_id){
        
        // Bail early
        if(!acfe_is_wpml())
            return;
    
        // Translate: Page Title
        if(isset($args['page_title'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Page_title', $args['page_title']);
        }
    
        // Translate: Menu Title
        if(isset($args['menu_title'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Menu_title', $args['menu_title']);
        }
    
        // Translate: Update button
        if(isset($args['update_button'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Update_button', $args['update_button']);
        }
    
        // Translate: Updated message
        if(isset($args['updated_message'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Updated_message', $args['updated_message']);
        }
        
    }
    
    /*
     * Multilang Register
     */
    function l10n_register($args, $name){
        
        // Translate: Page Title
        if(isset($args['page_title'])){
            $args['page_title'] = acfe_translate($args['page_title'], 'Page_title', $this->textdomain);
        }
        
        // Translate: Menu Title
        if(isset($args['menu_title'])){
            $args['menu_title'] = acfe_translate($args['menu_title'], 'Menu_title', $this->textdomain);
        }
        
        // Translate: Update button
        if(isset($args['update_button'])){
            $args['update_button'] = acfe_translate($args['update_button'], 'Update_button', $this->textdomain);
        }
        
        // Translate: Updated message
        if(isset($args['updated_message'])){
            $args['updated_message'] = acfe_translate($args['updated_message'], 'Updated_message', $this->textdomain);
        }
        
        return $args;
        
    }
    
    /*
     * Add Local Field Group
     */
    function add_local_field_group(){
    
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_options_page',
            'title' => __('Options Page', 'acfe'),
        
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $this->post_type,
                    ),
                ),
            ),
        
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        
            'fields' => array(
                array(
                    'key' => 'field_acfe_dop_menu_slug',
                    'label' => 'Menu slug',
                    'name' => 'menu_slug',
                    'type' => 'acfe_slug',
                    'instructions' => '(string) The URL slug used to uniquely identify this options page. Defaults to a url friendly version of Menu Title',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => array(
                        '5cd2a4d60fbf2' => array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_menu_title',
                    'label' => 'Menu title',
                    'name' => 'menu_title',
                    'type' => 'text',
                    'instructions' => '(string) The title displayed in the wp-admin sidebar. Defaults to Page Title',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_capability',
                    'label' => 'Capability',
                    'name' => 'capability',
                    'type' => 'text',
                    'instructions' => '(string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.<br /><br />

Read more about capability here: <a href="https://wordpress.org/support/article/roles-and-capabilities/">https://wordpress.org/support/article/roles-and-capabilities/</a>',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => 'edit_posts',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_position',
                    'label' => 'Position',
                    'name' => 'position',
                    'type' => 'text',
                    'instructions' => '(int|string) The position in the menu order this menu should appear. Defaults to bottom of utility menu items.<br /><br />

WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!<br />
Risk of conflict can be reduced by using decimal instead of integer values, e.g. \'63.3\' instead of 63 (must use quotes).',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_parent_slug',
                    'label' => 'Parent slug',
                    'name' => 'parent_slug',
                    'type' => 'text',
                    'instructions' => '(string) The slug of another WP admin page. if set, this will become a child page.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_icon_url',
                    'label' => 'Icon url',
                    'name' => 'icon_url',
                    'type' => 'text',
                    'instructions' => '(string) The icon class for this menu. Defaults to default WordPress gear.<br /><br />
Read more about dashicons here: <a href="https://developer.wordpress.org/resource/dashicons/">https://developer.wordpress.org/resource/dashicons/</a>',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_redirect',
                    'label' => 'Redirect',
                    'name' => 'redirect',
                    'type' => 'true_false',
                    'instructions' => '(boolean) If set to true, this options page will redirect to the first child page (if a child page exists).
If set to false, this parent page will appear alongside any child pages. Defaults to true',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
                array(
                    'key' => 'field_acfe_dop_post_id',
                    'label' => 'Post ID',
                    'name' => 'post_id',
                    'type' => 'text',
                    'instructions' => '(int|string) The \'$post_id\' to save/load data to/from. Can be set to a numeric post ID (123), or a string (\'user_2\').
Defaults to \'options\'.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => 'options',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_autoload',
                    'label' => 'Autoload',
                    'name' => 'autoload',
                    'type' => 'true_false',
                    'instructions' => '(boolean) Whether to load the option (values saved from this options page) when WordPress starts up.
Defaults to false.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
                array(
                    'key' => 'field_acfe_dop_update_button',
                    'label' => 'Update button',
                    'name' => 'update_button',
                    'type' => 'text',
                    'instructions' => '(string) The update button text.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => 'Update',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dop_updated_message',
                    'label' => 'Updated Message',
                    'name' => 'updated_message',
                    'type' => 'text',
                    'instructions' => '(string) The message shown above the form on submit.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => '',
                    'acfe_permissions' => '',
                    'default_value' => 'Options Updated',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ));
    
    }
    
}

acf_new_instance('acfe_dynamic_options_pages');

endif;