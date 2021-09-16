<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/ui'))
    return;

if(!class_exists('acfe_enhanced_ui_term')):
    
class acfe_enhanced_ui_term extends acfe_enhanced_ui{
    
    var $taxonomy;
    var $term_id;
    
    function initialize(){
        
        // load
        add_action('acfe/load_term',            array($this, 'load_term'));
        add_action('acfe/load_terms',           array($this, 'load_terms'));
        
        // meta boxes
        add_action('acfe/add_term_meta_boxes',  array($this, 'add_term_meta_boxes'), 10, 2);
        
    }
    
    /*
     * Term: Load
     */
    function load_term($taxonomy){
    
        // enqueue
        $this->enqueue_scripts();
        
        // var
        $this->taxonomy = $taxonomy;
        
        // hooks
        add_action('admin_enqueue_scripts', array($this, 'term_enqueue_scripts'), 15);
        add_action('admin_footer',          array($this, 'term_footer'));
        
    }
    
    /*
     * Terms: Load
     */
    function load_terms($taxonomy){
    
        // enqueue
        $this->enqueue_scripts();
        
        // hooks
        add_action('admin_footer', array($this, 'terms_footer'));
        
    }
    
    /*
     * Term: Add Metaboxes
     */
    function add_term_meta_boxes($taxonomy, $term){
        
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
        add_meta_box('submitdiv', __('Edit'), array($this, 'render_metabox_submit'), $screen, 'side', 'high', 'term');
        
    }
    
    /*
     * Term: Enqueue Scripts
     */
    function term_enqueue_scripts(){
        
        // remove acf render (must be priority 15 to remove it correctly)
        // advanced-custom-fields-pro/includes/forms/form-taxonomy.php
        acfe_remove_class_action("{$this->taxonomy}_edit_form", 'acf_form_taxonomy', 'edit_term');
        
    }
    
    /*
     * Term: Footer
     */
    function term_footer(){
        
        global $tag, $tax;
        
        ?>
        <div class="permalink">
            <?php if(acfe_maybe_get($tax, 'publicly_queryable')){ ?>
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
     * Terms: Footer
     */
    function terms_footer(){
    
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

            acfe.enhancedListUI({
                taxonomy: '<?php echo $tax->name; ?>'
            });

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

}

new acfe_enhanced_ui_term();

endif;