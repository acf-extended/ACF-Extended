<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/taxonomies'))
    return;

/**
 * Terms List View
 */
add_action('admin_footer-edit-tags.php', 'acfe_better_taxonomy_admin_footer');
function acfe_better_taxonomy_admin_footer(){
    
    global $tax;
    $can_edit_terms = current_user_can($tax->cap->edit_terms);
    
    ?>
    <script type="text/html" id="tmpl-acfe-bt-admin-button-add">
        <?php if($can_edit_terms){ ?>
            <a href="#" class="page-title-action acfe-bt-admin-button-add"><?php echo $tax->labels->add_new_item; ?></a>
        <?php } ?>
    </script>
    
    <script type="text/html" id="tmpl-acfe-bt-wrapper">
        <div id="poststuff"></div>
    </script>
    
    <script type="text/javascript">
    (function($){
        // Add button
        $('.wrap .wp-heading-inline').after($('#tmpl-acfe-bt-admin-button-add').html());
        
        // Move form
        $('#ajax-response').after($('#col-container #col-left').addClass('acfe-bt'));
        
        // Hide form
        $('.acfe-bt').hide();
        
        // Create wrapper
        $('.acfe-bt .form-wrap').append($('#tmpl-acfe-bt-wrapper').html());
        
        // Append form inside wrapper
        $('.acfe-bt #poststuff').append($('.acfe-bt .form-wrap form'));
        
        $('.acfe-bt .form-wrap form').wrapInner('<div class="postbox" id="acfe-bt-form"><div class="inside"></div></div>');
        
        // Append new title
        $('.acfe-bt .postbox h2 span').append($('.acfe-bt .form-wrap > h2').text());
        $('.acfe-bt .form-wrap > h2').remove();
        
        // Acf class
        $('.acfe-bt .inside .form-field, .acfe-bt .inside .submit').addClass('acf-field');
        
        $('.acfe-bt .inside .form-field, .acfe-bt .inside .submit').each(function(){
            
            $(this).append('<div class="acf-input"></div>');
            $(this).find('.acf-input').append($(this).find('> :not("label")'));
            
            // Add spacing when a meta field has no label
            var $label = $(this).find('> label');
            if($label.length){
                
                $label.wrap('<div class="acf-label"></div>');
                
            }else{
                
                $(this).addClass('acfe-bt-no-label');
                
            }
            
        });
        
        // Remove ACF Fields id
        $('#acf-term-fields').contents().unwrap();
        
        $('.acfe-bt-admin-button-add').click(function(e){
            
            e.preventDefault();
            
            if($('.acfe-bt').is(':visible'))
                $('.acfe-bt').hide();
            else
                $('.acfe-bt').show();
            
        });
        
        // Label to left
        if(typeof acf !== 'undefined'){
            acf.postbox.render({
                'id':       'acfe-bt-form',
                'label':    'left'
            });	
        }
        
        $('#acfe-bt-form .acf-tab-wrap.-left').removeClass('-left').addClass('-top');
        
        // Polylang Compatibility Fix
        <?php if(isset($_GET['from_tag']) && !empty($_GET['from_tag']) && isset($_GET['new_lang']) && !empty($_GET['new_lang'])){ ?>
        
            $('.acfe-bt-admin-button-add').click();

        <?php } ?>
        
    })(jQuery);
    </script>
    <?php
    
}

/**
 * Term Edit View
 */
add_action('admin_footer-term.php', 'acfe_better_taxonomy_edit_admin_footer');
function acfe_better_taxonomy_edit_admin_footer(){
    
    ?>
    <script type="text/html" id="tmpl-acf-column-2">
        <div class="acf-column-2">
            
            <div id="poststuff" class="acfe-acfe-bt-admin-column">
                
                    <div class="postbox">
                    
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Edit', 'acfe'); ?></span></h2>
                        
                        <div class="inside">
                            <div class="submitbox">
                                
                                <div id="major-publishing-actions">
                                
                                    <div id="publishing-action">
                                    
                                        <div class="acfe-form-submit">
                                            <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Update', 'acfe'); ?>" />
                                            <span class="acf-spinner"></span>
                                        </div>
                                        
                                    </div>
                                    <div class="clear"></div>
                                    
                                </div>
                                
                            </div>

                        </div>
                        
                    </div>
                
            </div>
        </div>
    </script>
    <script type="text/javascript">
    (function($){
        
        // wrap form
        $('#edittag').wrapInner('<div class="acf-columns-2"><div style="float:left; width:100%;"></div></div>');
        
        // add column side
        $('#edittag .acf-columns-2').append($('#tmpl-acf-column-2').html());
        
        // Add acf-input
        //$('#edittag .form-table td:not(".acf-input")').wrapInner('<div class="acf-input"></div>');
        
        $('#edittag .edit-tag-actions').hide();
        
    })(jQuery);
    </script>
    <?php
    
}