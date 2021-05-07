<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_dynamic_taxonomies')):

class acfe_dynamic_taxonomies extends acfe_dynamic_module{
    
    /*
     * Initialize
     */
    function initialize(){
    
        $this->active = acf_get_setting('acfe/modules/taxonomies');
        $this->settings = 'modules.taxonomies';
        $this->post_type = 'acfe-dt';
        $this->label = 'Taxonomy Name';
        $this->textdomain = 'ACF Extended: Taxonomies';
        
        $this->tool = 'acfe_dynamic_taxonomies_export';
        $this->tools = array('php', 'json');
        $this->columns = array(
            'acfe-name'         => __('Name', 'acf'),
            'acfe-post-types'   => __('Post Types', 'acf'),
            'acfe-terms'        => __('Terms', 'acf'),
        );
        
    }
    
    /*
     * Actions
     */
    function actions(){
        
        // Features
        add_action('admin_footer-edit-tags.php',            array($this, 'admin_config'));
        add_filter('get_terms_args',                        array($this, 'admin_archive_posts'), 10, 2);
        add_action('pre_get_posts',                         array($this, 'front_archive_posts'));
        add_filter('template_include',                      array($this, 'front_template'), 999);
        
        // Validate
        add_filter('acf/validate_value/name=acfe_dt_name',  array($this, 'validate_name'), 10, 4);
        add_filter('acf/update_value/name=acfe_dt_name',    array($this, 'update_name'), 10, 3);
        
        // Save
        add_filter('acfe/taxonomy/save_args',               array($this, 'save_args'), 10, 3);
        add_action('acfe/taxonomy/save',                    array($this, 'save'), 10, 3);
        
        // Import
        add_action('acfe/taxonomy/import_fields',           array($this, 'import_fields'), 10, 3);
        add_action('acfe/taxonomy/import',                  array($this, 'after_import'), 10, 2);
    
        // Multilang
        add_action('acfe/taxonomy/save',                    array($this, 'l10n_save'), 10, 3);
        add_filter('acfe/taxonomy/register',                array($this, 'l10n_register'), 10, 2);
        
    }
    
    /*
     * Get Name
     */
    function get_name($post_id){
        
        return get_field('acfe_dt_name', $post_id);
        
    }
    
    /*
     * Init
     */
    function init(){
        
        $this->register_post_type();
        $this->register_user_taxonomies();
        
    }
    
    /*
     * Register Post Type
     */
    function register_post_type(){
    
        $capability = acf_get_setting('capability');
        
        if(!acf_get_setting('show_admin'))
            $capability = false;
    
        register_post_type($this->post_type, array(
            'label'                 => 'Taxonomies',
            'description'           => 'Taxonomies',
            'labels'                => array(
                'name'          => 'Taxonomies',
                'singular_name' => 'Taxonomy',
                'menu_name'     => 'Taxonomies',
                'edit_item'     => 'Edit Taxonomy',
                'add_new_item'  => 'New Taxonomy',
            ),
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'tools.php',
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
     * Register User Taxonomies
     */
    function register_user_taxonomies(){
        
        $settings = apply_filters('acfe/taxonomy/prepare_register', acfe_get_settings($this->settings));
    
        if(empty($settings))
            return;
    
        foreach($settings as $name => $args){
            
            // Bail early
            if(empty($name) || taxonomy_exists($name))
                continue;
            
            // Filters
            $args = apply_filters("acfe/taxonomy/register",                 $args, $name);
            $args = apply_filters("acfe/taxonomy/register/name={$name}",    $args, $name);
            
            if($args === false)
                continue;
            
            // Extract Post Types
            $post_types = acf_extract_var($args, 'post_types', array());
            
            // Register
            register_taxonomy($name, $post_types, $args);
            
            // Filter Admin: Posts Per Page
            add_filter("edit_{$name}_per_page", array($this, 'admin_archive_ppp'));
            
        }
        
    }
    
    /*
     * Post Screen
     */
    function post_screen(){
        
        flush_rewrite_rules();
        
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
            
            // Post Types
            case 'acfe-post-types':
                
                $pt = '—';
                
                $get_post_types = acf_get_array(get_field('post_types', $post_id));
                
                if(!empty($get_post_types)){
                    
                    $post_types = array();
                    
                    foreach($get_post_types as $post_type){
                        
                        if(!post_type_exists($post_type))
                            continue;
    
                        $post_types[] = $post_type;
                        
                    }
                    
                    if(!empty($post_types)){
                        
                        $post_types_labels = acf_get_pretty_post_types($post_types);
                        
                        if(!empty($post_types_labels)){
                            $pt = implode(', ', $post_types_labels);
                        }
                        
                    }
                    
                }
                
                echo $pt;
                break;
            
            // Terms
            case 'acfe-terms':
                
                // vars
                $c = '—';
                $name = $this->get_name($post_id);
                
                if(taxonomy_exists($name)){
    
                    $count = wp_count_terms($name, array(
                        'hide_empty' => false
                    ));
    
                    if(!is_wp_error($count) && !empty($count)){
        
                        $c = '<a href="' . admin_url('edit-tags.php?taxonomy=' . $name) . '">' . $count . '</a>';
        
                    }
                    
                }
                
                echo $c;
                break;
            
        }
        
    }
    
    /*
     * Edit Row Actions View
     */
    function edit_row_actions_view($post, $name){
    
        $view = "edit-tags.php?taxonomy={$name}";
    
        $post_types = acf_get_array(get_field('post_types', $post->ID));
        if(isset($post_types[0]))
            $view .= "&post_type={$post_types[0]}";
    
        return '<a href="' . admin_url($view) . '">' . __('View') . '</a>';
        
    }
    
    /*
     * Admin Config Button
     */
    function admin_config(){
    
        if(!acf_current_user_can_admin())
            return;
    
        // Get taxonomy
        global $taxnow;
    
        // Check taxonomy
        $taxonomy = $taxnow;
        if(empty($taxonomy))
            return;
    
        // Taxonomy object
        $taxonomy_obj = get_taxonomy($taxonomy);
        if(!isset($taxonomy_obj->acfe_admin_ppp))
            return;
    
        // Get Dynamic Post Type Post
        $acfe_dt_post_type = get_page_by_path($taxonomy, 'OBJECT', $this->post_type);
    
        if(empty($acfe_dt_post_type))
            return;
    
        ?>
        <script type="text/html" id="tmpl-acfe-dt-title-config">
            &nbsp;<a href="<?php echo admin_url('post.php?post=' . $acfe_dt_post_type->ID . '&action=edit'); ?>" class="page-title-action acfe-dt-admin-config"><span class="dashicons dashicons-admin-generic"></span></a>
        </script>

        <script type="text/javascript">
            (function($){

                // Add button
                $('.wrap .wp-heading-inline').after($('#tmpl-acfe-dt-title-config').html());

            })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Admin: Archive Posts
     */
    function admin_archive_posts($args, $taxonomies){
        
        if(!is_admin())
            return $args;
        
        global $pagenow;
        
        if($pagenow !== 'edit-tags.php')
            return $args;
        
        if(empty($taxonomies))
            return $args;
        
        $taxonomy = array_shift($taxonomies);
        $taxonomy_obj = get_taxonomy($taxonomy);
        
        $acfe_admin_orderby = (isset($taxonomy_obj->acfe_admin_orderby) && !empty($taxonomy_obj->acfe_admin_orderby));
        $acfe_admin_order = (isset($taxonomy_obj->acfe_admin_order) && !empty($taxonomy_obj->acfe_admin_order));
        
        if($acfe_admin_orderby && (!isset($_REQUEST['orderby']) || empty($_REQUEST['orderby'])))
            $args['orderby'] = $taxonomy_obj->acfe_admin_orderby;
        
        if($acfe_admin_order && (!isset($_REQUEST['order']) || empty($_REQUEST['order'])))
            $args['order'] = $taxonomy_obj->acfe_admin_order;
        
        return $args;
        
    }
    
    /*
     * Admin: Archive Posts Per Page
     */
    function admin_archive_ppp($ppp){
        
        global $pagenow;
        
        if($pagenow !== 'edit-tags.php')
            return $ppp;
        
        $taxonomy = $_GET['taxonomy'];
        if(empty($taxonomy))
            return $ppp;
        
        $taxonomy_obj = get_taxonomy($taxonomy);
        if(!isset($taxonomy_obj->acfe_admin_ppp) || empty($taxonomy_obj->acfe_admin_ppp))
            return $ppp;
        
        // Check if user has a screen option
        if(!empty(get_user_option("edit_{$taxonomy}_per_page")))
            return $ppp;
        
        return $taxonomy_obj->acfe_admin_ppp;
        
    }
    
    /*
     * Front: Archive Posts
     */
    function front_archive_posts($query){
        
        if(is_admin() || !$query->is_main_query() || !is_tax())
            return;
        
        $term_obj = $query->get_queried_object();
        
        if(!is_a($term_obj, 'WP_Term'))
            return;
        
        $taxonomy = $term_obj->taxonomy;
        $taxonomy_obj = get_taxonomy($taxonomy);
        
        $acfe_single_ppp = (isset($taxonomy_obj->acfe_single_ppp) && !empty($taxonomy_obj->acfe_single_ppp));
        $acfe_single_orderby = (isset($taxonomy_obj->acfe_single_orderby) && !empty($taxonomy_obj->acfe_single_orderby));
        $acfe_single_order = (isset($taxonomy_obj->acfe_single_order) && !empty($taxonomy_obj->acfe_single_order));
        
        if($acfe_single_ppp)
            $query->set('posts_per_page', $taxonomy_obj->acfe_single_ppp);
        
        if($acfe_single_orderby)
            $query->set('orderby', $taxonomy_obj->acfe_single_orderby);
        
        if($acfe_single_order)
            $query->set('order', $taxonomy_obj->acfe_single_order);
        
    }
    
    /*
     * Front: Template
     */
    function front_template($template){
        
        if(!is_tax() && !is_category() && !is_tag())
            return $template;
        
        if(!isset(get_queried_object()->taxonomy))
            return $template;
        
        $taxonomy_obj = get_queried_object()->taxonomy;
        
        foreach(get_taxonomies(array('public' => true), 'objects') as $taxonomy){
            if($taxonomy_obj !== $taxonomy->name || !isset($taxonomy->acfe_single_template))
                continue;
            
            if($locate = locate_template(array($taxonomy->acfe_single_template)))
                return $locate;
        }
        
        return $template;
        
    }
    
    /*
     * Validate Name
     */
    function validate_name($valid, $value, $field, $input){
    
        if(!$valid)
            return $valid;
    
        // Reserved WP Taxonomies
        // https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
        $exclude = array(
            'attachment',
            'attachment_id',
            'author',
            'author_name',
            'calendar',
            'cat',
            'category',
            'category__and',
            'category__in',
            'category__not_in',
            'category_name',
            'comments_per_page',
            'comments_popup',
            'customize_messenger_channel',
            'customized',
            'cpage',
            'day',
            'debug',
            'error',
            'exact',
            'feed',
            'fields',
            'hour',
            'link_category',
            'm',
            'minute',
            'monthnum',
            'more',
            'name',
            'nav_menu',
            'nonce',
            'nopaging',
            'offset',
            'order',
            'orderby',
            'p',
            'page',
            'page_id',
            'paged',
            'pagename',
            'pb',
            'perm',
            'post',
            'post__in',
            'post__not_in',
            'post_format',
            'post_mime_type',
            'post_status',
            'post_tag',
            'post_type',
            'posts',
            'posts_per_archive_page',
            'posts_per_page',
            'preview',
            'robots',
            's',
            'search',
            'second',
            'sentence',
            'showposts',
            'static',
            'subpost',
            'subpost_id',
            'tag',
            'tag__and',
            'tag__in',
            'tag__not_in',
            'tag_id',
            'tag_slug__and',
            'tag_slug__in',
            'taxonomy',
            'tb',
            'term',
            'theme',
            'type',
            'w',
            'withcomments',
            'withoutcomments',
            'year',
        );
    
        $exclude = array_merge($exclude, acfe_get_setting('reserved_taxonomies', array()));
    
        // Reserved Names
        if(in_array($value, $exclude))
            return __('This taxonomy name is reserved');
    
        // Editing Current Dynamic Taxonomy
        $current_post_id = acf_maybe_get_POST('post_ID');
    
        if(!empty($current_post_id)){
        
            $current_name = get_field($field['name'], $current_post_id);
        
            if($value === $current_name)
                return $valid;
        
        }
    
        // Check existing WP Taxonomies
        global $wp_taxonomies;
    
        if(!empty($wp_taxonomies)){
        
            foreach($wp_taxonomies as $taxonomy){
            
                if($value !== $taxonomy->name)
                    continue;
            
                $valid = __('This taxonomy name already exists');
            
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
     * ACF Save post
     */
    function save_post($post_id){
        
        // vars
        $args = array();
        $name = $this->get_name($post_id);
        
        // Filters
        $args = apply_filters("acfe/taxonomy/save_args",               $args, $name, $post_id);
        $args = apply_filters("acfe/taxonomy/save_args/name={$name}",  $args, $name, $post_id);
        $args = apply_filters("acfe/taxonomy/save_args/id={$post_id}", $args, $name, $post_id);
        
        if($args === false)
            return;
        
        // Actions
        do_action("acfe/taxonomy/save",                 $name, $args, $post_id);
        do_action("acfe/taxonomy/save/name={$name}",    $name, $args, $post_id);
        do_action("acfe/taxonomy/save/id={$post_id}",   $name, $args, $post_id);
        
    }
    
    /*
     * Save Args
     */
    function save_args($args, $name, $post_id){
        
        $label = get_post_field('post_title', $post_id);
        $name = get_field('acfe_dt_name', $post_id);
        $description = get_field('description', $post_id);
        $hierarchical = get_field('hierarchical', $post_id);
        $post_types = get_field('post_types', $post_id);
        $public = get_field('public', $post_id);
        $publicly_queryable = get_field('publicly_queryable', $post_id);
        $update_count_callback = get_field('update_count_callback', $post_id);
        $sort = get_field('sort', $post_id);
        
        // Labels
        $labels = get_field('labels', $post_id);
        $labels_args = array();
        foreach($labels as $k => $l){
            if(empty($l))
                continue;
            
            $labels_args[$k] = $l;
        }
        
        // Menu
        $show_ui = get_field('show_ui', $post_id);
        $show_in_menu = get_field('show_in_menu', $post_id);
        $show_in_nav_menus = get_field('show_in_nav_menus', $post_id);
        $show_tagcloud = get_field('show_tagcloud', $post_id);
        $meta_box_cb = get_field('meta_box_cb', $post_id);
        $meta_box_cb_custom = get_field('meta_box_cb_custom', $post_id);
        $show_in_quick_edit = get_field('show_in_quick_edit', $post_id);
        $show_admin_column = get_field('show_admin_column', $post_id);
        
        // Capability
        $capabilities = acf_decode_choices(get_field('capabilities', $post_id));
        
        // Single
        $single_template = get_field('acfe_dt_single_template', $post_id);
        $single_posts_per_page = (int) get_field('acfe_dt_single_posts_per_page', $post_id);
        $single_orderby = get_field('acfe_dt_single_orderby', $post_id);
        $single_order = get_field('acfe_dt_single_order', $post_id);
        $rewrite = get_field('rewrite', $post_id);
        $rewrite_args_select = get_field('rewrite_args_select', $post_id);
        $rewrite_args = get_field('rewrite_args', $post_id);
        
        // Admin
        $admin_posts_per_page = (int) get_field('acfe_dt_admin_terms_per_page', $post_id);
        $admin_orderby = get_field('acfe_dt_admin_orderby', $post_id);
        $admin_order = get_field('acfe_dt_admin_order', $post_id);
        
        // REST
        $show_in_rest = get_field('show_in_rest', $post_id);
        $rest_base = get_field('rest_base', $post_id);
        $rest_controller_class = get_field('rest_controller_class', $post_id);
        
        // Register: Args
        $args = array(
            'label'                 => $label,
            'description'           => $description,
            'hierarchical'          => $hierarchical,
            'post_types'            => $post_types,
            'public'                => $public,
            'publicly_queryable'    => $publicly_queryable,
            'update_count_callback' => $update_count_callback,
            'sort'                  => $sort,
            
            // Labels
            'labels'                => $labels_args,
            
            // Menu
            'show_ui'               => $show_ui,
            'show_in_menu'          => $show_in_menu,
            'show_in_nav_menus'     => $show_in_nav_menus,
            'show_tagcloud'         => $show_tagcloud,
            'show_in_quick_edit'    => $show_in_quick_edit,
            'show_admin_column'     => $show_admin_column,
            
            // Single
            'rewrite'               => $rewrite,
            
            // REST
            'show_in_rest'          => $show_in_rest,
            'rest_base'             => $rest_base,
            'rest_controller_class' => $rest_controller_class,
            
            // ACFE: Single
            'acfe_single_template'  => $single_template,
            'acfe_single_ppp'       => $single_posts_per_page,
            'acfe_single_orderby'   => $single_orderby,
            'acfe_single_order'     => $single_order,
            
            // ACFE: Admin
            'acfe_admin_ppp'        => $admin_posts_per_page,
            'acfe_admin_orderby'    => $admin_orderby,
            'acfe_admin_order'      => $admin_order,
        );
        
        // Rewrite: override
        if($rewrite && $rewrite_args_select){
            
            $args['rewrite'] = array(
                'slug'          => $rewrite_args['acfe_dt_rewrite_slug'],
                'with_front'    => $rewrite_args['acfe_dt_rewrite_with_front'],
                'hierarchical'  => $rewrite_args['hierarchical']
            );
            
        }
        
        // Capabilities
        $args['capabilities'] = $capabilities;
        
        // Metabox CB
        $args['meta_box_cb'] = null;
        
        if($meta_box_cb === 'false')
            $args['meta_box_cb'] = false;

        elseif($meta_box_cb === 'custom')
            $args['meta_box_cb'] = $meta_box_cb_custom;
        
        return $args;
        
    }
    
    /*
     * Save
     */
    function save($name, $args, $post_id){
        
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
        
        // Flush permalinks
        flush_rewrite_rules();
        
    }
    
    /*
     * Import
     */
    function import($name, $args){
    
        // Vars
        $settings = acfe_get_settings($this->settings);
        $title = $args['label'];
    
        // Already exists
        if(isset($settings[$name])){
            return new WP_Error('acfe_dt_import_already_exists', __("Taxonomy \"{$title}\" already exists. Import aborted."));
        }
    
        // Import Post
        $post_id = false;
        
        $post = array(
            'post_title'    => $title,
            'post_name'     => $name,
            'post_type'     => $this->post_type,
            'post_status'   => 'publish'
        );
    
        $post = apply_filters("acfe/taxonomy/import_post",                 $post, $name);
        $post = apply_filters("acfe/taxonomy/import_post/name={$name}",    $post, $name);
        
        if($post !== false){
            $post_id = wp_insert_post($post);
        }
        
        if(!$post_id || is_wp_error($post_id)){
            return new WP_Error('acfe_dt_import_error', __("Something went wrong with the taxonomy \"{$title}\". Import aborted."));
        }
    
        // Import Args
        $args = apply_filters("acfe/taxonomy/import_args",                 $args, $name, $post_id);
        $args = apply_filters("acfe/taxonomy/import_args/name={$name}",    $args, $name, $post_id);
        $args = apply_filters("acfe/taxonomy/import_args/name={$post_id}", $args, $name, $post_id);
    
        if($args === false)
            return $post_id;
    
        // Import Fields
        acf_enable_filter('local');
        
        do_action("acfe/taxonomy/import_fields",                  $name, $args, $post_id);
        do_action("acfe/taxonomy/import_fields/name={$name}",     $name, $args, $post_id);
        do_action("acfe/taxonomy/import_fields/id={$post_id}",    $name, $args, $post_id);
    
        acf_disable_filter('local');
    
        // Save
        $this->save_post($post_id);
        
        return $post_id;
        
    }
    
    function import_fields($name, $args, $post_id){
    
        // Register Args
        update_field('acfe_dt_name', $name, $post_id);
        update_field('description', $args['description'], $post_id);
        update_field('hierarchical', $args['hierarchical'], $post_id);
        update_field('post_types', $args['post_types'], $post_id);
        update_field('public', $args['public'], $post_id);
        update_field('publicly_queryable', $args['publicly_queryable'], $post_id);
        update_field('update_count_callback', $args['update_count_callback'], $post_id);
        update_field('sort', $args['sort'], $post_id);
    
        // Meta box callback
        if(!isset($args['meta_box_cb']) || $args['meta_box_cb'] === null){
        
            update_field('meta_box_cb', 'null', $post_id);
            update_field('meta_box_cb_custom', '', $post_id);
        
        }

        elseif($args['meta_box_cb'] === false){
        
            update_field('meta_box_cb', 'false', $post_id);
            update_field('meta_box_cb_custom', '', $post_id);
        
        }

        elseif(empty($args['meta_box_cb']) || is_string($args['meta_box_cb'])){
        
            update_field('meta_box_cb', 'custom', $post_id);
            update_field('meta_box_cb_custom', $args['meta_box_cb'], $post_id);
        
        }
    
        // Labels
        if(!empty($args['labels'])){
        
            foreach($args['labels'] as $label_key => $label_value){
                update_field('labels_' . $label_key, $label_value, $post_id);
            }
        
        }
    
        // Menu
        update_field('show_ui', $args['show_ui'], $post_id);
        update_field('show_in_menu', $args['show_in_menu'], $post_id);
        update_field('show_in_nav_menus', $args['show_in_nav_menus'], $post_id);
        update_field('show_tagcloud', $args['show_tagcloud'], $post_id);
        update_field('show_in_quick_edit', $args['show_in_quick_edit'], $post_id);
        update_field('show_admin_column', $args['show_admin_column'], $post_id);
    
        // Capability
        if(isset($args['capabilities']))
            update_field('capabilities', acf_encode_choices($args['capabilities'], false), $post_id);
    
        // Single
        update_field('acfe_dt_single_template', $args['acfe_single_template'], $post_id);
        update_field('acfe_dt_single_posts_per_page', $args['acfe_single_ppp'], $post_id);
        update_field('acfe_dt_single_orderby', $args['acfe_single_orderby'], $post_id);
        update_field('acfe_dt_single_order', $args['acfe_single_order'], $post_id);
        update_field('rewrite', $args['rewrite'], $post_id);
    
        // Admin
        update_field('acfe_dt_admin_terms_per_page', $args['acfe_admin_ppp'], $post_id);
        update_field('acfe_dt_admin_orderby', $args['acfe_admin_orderby'], $post_id);
        update_field('acfe_dt_admin_order', $args['acfe_admin_order'], $post_id);
    
        // REST
        update_field('show_in_rest', $args['show_in_rest'], $post_id);
        update_field('rest_base', $args['rest_base'], $post_id);
        update_field('rest_controller_class', $args['rest_controller_class'], $post_id);
    
        // Rewrite: override
        if($args['rewrite'] && is_array($args['rewrite'])){
        
            update_field('rewrite', true, $post_id);
        
            update_field('rewrite_args_select', true, $post_id);
        
            update_field('rewrite_args_acfe_dt_rewrite_slug', $args['rewrite']['slug'], $post_id);
            update_field('rewrite_args_acfe_dt_rewrite_with_front', $args['rewrite']['with_front'], $post_id);
            update_field('rewrite_args_hierarchical', $args['rewrite']['hierarchical'], $post_id);
        
        }
        
    }
    
    /*
     * After Import
     */
    function after_import($ids, $data){
        
        flush_rewrite_rules();
        
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
            
            $choices[$name] = esc_html($args['label']);
            
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
        $args = apply_filters("acfe/taxonomy/export_args",                 $args, $name);
        $args = apply_filters("acfe/taxonomy/export_args/name={$name}",    $args, $name);
        
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
        
        foreach($data as $taxonomy => $args){
            
            // Translate settings if textdomain is set.
            if($l10n && $l10n_textdomain){
                
                $args['label'] = acf_translate($args['label']);
                $args['description'] = acf_translate($args['description']);
                
                if(!empty($args['labels'])){
                    
                    foreach($args['labels'] as $key => &$label){
                        
                        $args['labels'][$key] = acf_translate($label);
                        
                    }
                    
                }
                
            }
            
            $post_types = array();
            if(acf_maybe_get($args, 'post_types')){
                
                $post_types = $args['post_types'];
                
            }
            
            $post_types = var_export($post_types, true);
            $post_types = str_replace( array_keys($str_replace), array_values($str_replace), $post_types );
            $post_types = preg_replace( array_keys($preg_replace), array_values($preg_replace), $post_types );
            
            // code
            $code = var_export($args, true);
            
            
            // change double spaces to tabs
            $code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
            
            
            // correctly formats "=> array("
            $code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
            
            
            // esc_textarea
            $code = esc_textarea( $code );
            
            // echo
            echo "register_taxonomy('{$taxonomy}', {$post_types}, {$code});" . "\r\n" . "\r\n";
            
        }
        
    }
    
    /*
     * Reset
     */
    function reset(){
        
        $args = apply_filters("acfe/taxonomy/reset_args", array(
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
        acf_log('[ACF Extended] Reset: Taxonomies');
        
        return true;
        
    }
    
    /*
     * Multilang Save
     */
    function l10n_save($name, $args, $post_id){
        
        // Bail early
        if(!acfe_is_wpml())
            return;
        
        // Translate: Label
        if(isset($args['label'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Label', $args['label']);
        }
        
        // Translate: Description
        if(isset($args['description'])){
            do_action('wpml_register_single_string', $this->textdomain, 'Description', $args['description']);
        }
        
        // Translate: Labels
        if(isset($args['labels'])){
            
            foreach($args['labels'] as $label_name => &$label_text){
                do_action('wpml_register_single_string', $this->textdomain, ucfirst($label_name), $label_text);
            }
            
        }
        
    }
    
    /*
     * Multilang Register
     */
    function l10n_register($args, $name){
        
        // Translate: Label
        if(isset($args['label'])){
            $args['label'] = acfe_translate($args['label'], 'Label', $this->textdomain);
        }
        
        // Translate: Description
        if(isset($args['description'])){
            $args['description'] = acfe_translate($args['description'], 'Description', $this->textdomain);
        }
        
        // Translate: Labels
        if(isset($args['labels'])){
            
            foreach($args['labels'] as $label_name => &$label_text){
                $label_text = acfe_translate($label_text, ucfirst($label_name), $this->textdomain);
            }
            
        }
        
        return $args;
        
    }
    
    /*
     * Add Local Field Group
     */
    function add_local_field_group(){
    
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_taxonomy',
            'title' => __('Dynamic Taxonomy', 'acfe'),
        
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
                    'key' => 'field_acfe_dt_tab_general',
                    'label' => 'General',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_name',
                    'label' => 'Name',
                    'name' => 'acfe_dt_name',
                    'type' => 'acfe_slug',
                    'instructions' => 'The name of the taxonomy. Name should only contain lowercase letters and the underscore character, and not be more than 32 characters long (database structure restriction)',
                    'required' => 1,
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
                    'maxlength' => 32,
                ),
                array(
                    'key' => 'field_acfe_dt_description',
                    'label' => 'Description',
                    'name' => 'description',
                    'type' => 'text',
                    'instructions' => 'Include a description of the taxonomy',
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
                    'key' => 'field_acfe_dt_hierarchical',
                    'label' => 'Hierarchical',
                    'name' => 'hierarchical',
                    'type' => 'true_false',
                    'instructions' => 'Is this taxonomy hierarchical (have descendants) like categories or not hierarchical like tags',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_post_types',
                    'label' => 'Post types',
                    'name' => 'post_types',
                    'type' => 'acfe_post_types',
                    'instructions' => '',
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
                    'field_type' => 'checkbox',
                    'return_format' => 'name',
                ),
                array(
                    'key' => 'field_acfe_dt_public',
                    'label' => 'Public',
                    'name' => 'public',
                    'type' => 'true_false',
                    'instructions' => 'Whether a taxonomy is intended for use publicly either via the admin interface or by front-end users',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_publicly_queryable',
                    'label' => 'Publicly queryable',
                    'name' => 'publicly_queryable',
                    'type' => 'true_false',
                    'instructions' => 'Whether the taxonomy is publicly queryable',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_update_count_callback',
                    'label' => 'Update count callback',
                    'name' => 'update_count_callback',
                    'type' => 'text',
                    'instructions' => 'A function name that will be called when the count of an associated $object_type, such as post, is updated',
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
                    'key' => 'field_acfe_dt_meta_box_cb',
                    'label' => 'Meta box callback',
                    'name' => 'meta_box_cb',
                    'type' => 'select',
                    'instructions' => 'Provide a callback function name for the meta box display.<br /><br/>Defaults to the categories meta box for hierarchical taxonomies and the tags meta box for non-hierarchical taxonomies. No meta box is shown if set to false.',
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
                    'choices' => array(
                        'null' => 'Null (default)',
                        'false' => 'False',
                        'custom' => 'Custom',
                    ),
                    'default_value' => array(
                        0 => 'null',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_meta_box_cb_custom',
                    'label' => 'Meta box callback',
                    'name' => 'meta_box_cb_custom',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
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
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dt_meta_box_cb',
                                'operator' => '==',
                                'value' => 'custom',
                            )
                        )
                    )
                ),
                array(
                    'key' => 'field_acfe_dt_sort',
                    'label' => 'Sort',
                    'name' => 'sort',
                    'type' => 'true_false',
                    'instructions' => 'Whether this taxonomy should remember the order in which terms are added to objects',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_tab_menu',
                    'label' => 'Menu',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_show_ui',
                    'label' => 'Show UI',
                    'name' => 'show_ui',
                    'type' => 'true_false',
                    'instructions' => 'Whether to generate a default UI for managing this post type in the admin',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_show_in_menu',
                    'label' => 'Show in menu',
                    'name' => 'show_in_menu',
                    'type' => 'true_false',
                    'instructions' => 'Where to show the taxonomy in the admin menu. show_ui must be true',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_show_in_nav_menus',
                    'label' => 'Show in nav menus',
                    'name' => 'show_in_nav_menus',
                    'type' => 'true_false',
                    'instructions' => 'true makes this taxonomy available for selection in navigation menus',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_show_tagcloud',
                    'label' => 'Show tagcloud',
                    'name' => 'show_tagcloud',
                    'type' => 'true_false',
                    'instructions' => 'Whether to allow the Tag Cloud widget to use this taxonomy',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_show_in_quick_edit',
                    'label' => 'Show in quick edit',
                    'name' => 'show_in_quick_edit',
                    'type' => 'true_false',
                    'instructions' => 'Whether to show the taxonomy in the quick/bulk edit panel',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_show_admin_column',
                    'label' => 'Show admin column',
                    'name' => 'show_admin_column',
                    'type' => 'true_false',
                    'instructions' => 'Whether to allow automatic creation of taxonomy columns on associated post-types table',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_tab_single',
                    'label' => 'Single',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_single_template',
                    'label' => 'Template',
                    'name' => 'acfe_dt_single_template',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Which template file to load for the term query. More informations on <a href="https://developer.wordpress.org/themes/basics/template-hierarchy/">Template hierarchy</a>',
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
                    'placeholder' => 'my-template.php',
                    'prepend' => trailingslashit(acfe_get_setting('theme_folder')),
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_single_posts_per_page',
                    'label' => 'Posts per page',
                    'name' => 'acfe_dt_single_posts_per_page',
                    'type' => 'number',
                    'instructions' => 'ACF Extended: Number of posts to display on the admin list screen',
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
                    'default_value' => 10,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => -1,
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_single_orderby',
                    'label' => 'Order by',
                    'name' => 'acfe_dt_single_orderby',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Sort retrieved posts by parameter in the admin list screen. Defaults to \'date (post_date)\'.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => array(
                        '5c9479dec93c4' => array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'date',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_single_order',
                    'label' => 'Order',
                    'name' => 'acfe_dt_single_order',
                    'type' => 'select',
                    'instructions' => 'ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the admin list screen. Defaults to \'DESC\'.',
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
                    'choices' => array(
                        'ASC' => 'ASC',
                        'DESC' => 'DESC',
                    ),
                    'default_value' => array(
                        0 => 'DESC',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_rewrite',
                    'label' => 'Rewrite',
                    'name' => 'rewrite',
                    'type' => 'true_false',
                    'instructions' => 'Set to false to prevent automatic URL rewriting a.k.a. "pretty permalinks". Pass an argument array to override default URL settings for permalinks',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_rewrite_args_select',
                    'label' => 'Rewrite Arguments',
                    'name' => 'rewrite_args_select',
                    'type' => 'true_false',
                    'instructions' => 'Use additional rewrite arguments',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dt_rewrite',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_rewrite_args',
                    'label' => 'Rewrite Arguments',
                    'name' => 'rewrite_args',
                    'type' => 'group',
                    'instructions' => 'Additional arguments',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_dt_rewrite',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_dt_rewrite_args_select',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'row',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_dt_rewrite_slug',
                            'label' => 'Slug',
                            'name' => 'acfe_dt_rewrite_slug',
                            'type' => 'text',
                            'instructions' => 'Used as pretty permalink text (i.e. /tag/) - defaults to $taxonomy (taxonomy\'s name slug)',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dt_rewrite_args_select',
                                        'operator' => '==',
                                        'value' => '1',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_validate' => '',
                            'acfe_update' => '',
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => 'Default',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dt_rewrite_with_front',
                            'label' => 'With front',
                            'name' => 'acfe_dt_rewrite_with_front',
                            'type' => 'true_false',
                            'instructions' => 'Allowing permalinks to be prepended with front base',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dt_rewrite_args_select',
                                        'operator' => '==',
                                        'value' => '1',
                                    ),
                                ),
                            ),
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
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                        array(
                            'key' => 'field_acfe_dt_rewrite_hierarchical',
                            'label' => 'Hierarchical',
                            'name' => 'hierarchical',
                            'type' => 'true_false',
                            'instructions' => 'True or false allow hierarchical urls',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_dt_rewrite_args_select',
                                        'operator' => '==',
                                        'value' => '1',
                                    ),
                                ),
                            ),
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
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_dt_tab_admin',
                    'label' => 'Admin',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_admin_terms_per_page',
                    'label' => 'Terms per page',
                    'name' => 'acfe_dt_admin_terms_per_page',
                    'type' => 'number',
                    'instructions' => 'ACF Extended: Number of terms to display on the admin list screen',
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
                    'default_value' => 10,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => -1,
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_admin_orderby',
                    'label' => 'Order by',
                    'name' => 'acfe_dt_admin_orderby',
                    'type' => 'text',
                    'instructions' => 'ACF Extended: Sort retrieved terms by parameter in the admin list screen. Accepts term fields \'name\', \'slug\', \'term_group\', \'term_id\', \'id\', \'description\', \'parent\', \'count\' (for term taxonomy count), or \'none\' to omit the ORDER BY clause',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_validate' => '',
                    'acfe_update' => array(
                        '5c9479dec93c4' => array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'name',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_admin_order',
                    'label' => 'Order',
                    'name' => 'acfe_dt_admin_order',
                    'type' => 'select',
                    'instructions' => 'ACF Extended: Designates the ascending or descending order of the \'orderby\' parameter in the admin list screen. Defaults to \'ASC\'.',
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
                    'choices' => array(
                        'ASC' => 'ASC',
                        'DESC' => 'DESC',
                    ),
                    'default_value' => array(
                        0 => 'ASC',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_tab_labels',
                    'label' => 'Labels',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_labels',
                    'label' => 'Labels',
                    'name' => 'labels',
                    'type' => 'group',
                    'instructions' => 'An array of labels for this taxonomy. By default tag labels are used for non-hierarchical types and category labels for hierarchical ones.<br /><br />
Default: if empty, name is set to label value, and singular_name is set to name value.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'row',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_dt_singular_name',
                            'label' => 'Singular name',
                            'name' => 'singular_name',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_menu_name',
                            'label' => 'Menu name',
                            'name' => 'menu_name',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_all_items',
                            'label' => 'All items',
                            'name' => 'all_items',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_edit_item',
                            'label' => 'Edit item',
                            'name' => 'edit_item',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_view_item',
                            'label' => 'View item',
                            'name' => 'view_item',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_update_item',
                            'label' => 'Update item',
                            'name' => 'update_item',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_add_new_item',
                            'label' => 'Add new item',
                            'name' => 'add_new_item',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_new_item_name',
                            'label' => 'New item name',
                            'name' => 'new_item_name',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_parent_item',
                            'label' => 'Parent item',
                            'name' => 'parent_item',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_parent_item_colon',
                            'label' => 'Parent item colon',
                            'name' => 'parent_item_colon',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_search_items',
                            'label' => 'Search items',
                            'name' => 'search_items',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_popular_items',
                            'label' => 'Popular items',
                            'name' => 'popular_items',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_separate_items_with_commas',
                            'label' => 'Separate items with commas',
                            'name' => 'separate_items_with_commas',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_add_or_remove_items',
                            'label' => 'Add or remove items',
                            'name' => 'add_or_remove_items',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_choose_from_most_used',
                            'label' => 'Choose from most used',
                            'name' => 'choose_from_most_used',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_not_found',
                            'label' => 'Not found',
                            'name' => 'not_found',
                            'type' => 'text',
                            'instructions' => '',
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
                            'key' => 'field_acfe_dt_back_to_items',
                            'label' => 'Back to items',
                            'name' => 'back_to_items',
                            'type' => 'text',
                            'instructions' => '',
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
                    ),
                ),
                array(
                    'key' => 'field_acfe_dt_tab_capability',
                    'label' => 'Capability',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_capabilities',
                    'label' => 'Capabilities',
                    'name' => 'capabilities',
                    'type' => 'textarea',
                    'instructions' => 'An array of the capabilities for this taxonomy:<br /><br />
manage_terms : edit_posts<br />
edit_terms : edit_posts<br />
delete_terms : edit_posts<br />
assign_terms : edit_posts',
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
                    'maxlength' => '',
                    'rows' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_tab_rest',
                    'label' => 'REST',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_dt_show_in_rest',
                    'label' => 'Show in rest',
                    'name' => 'show_in_rest',
                    'type' => 'true_false',
                    'instructions' => 'Whether to include the taxonomy in the REST API. Set this to true for the taxonomy to be available in the block editor',
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
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_dt_rest_base',
                    'label' => 'Rest base',
                    'name' => 'rest_base',
                    'type' => 'text',
                    'instructions' => 'To change the base url of REST API route',
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
                    'key' => 'field_acfe_dt_rest_controller_class',
                    'label' => 'Rest controller class',
                    'name' => 'rest_controller_class',
                    'type' => 'text',
                    'instructions' => 'REST API Controller class name',
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
                    'default_value' => 'WP_REST_Terms_Controller',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ));
    
    }
    
}

acf_new_instance('acfe_dynamic_taxonomies');

endif;