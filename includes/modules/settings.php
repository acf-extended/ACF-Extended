<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/ui'))
    return;

/**
 * General Options
 */
add_action('admin_footer-options-general.php', 'acfe_better_options_general_admin_footer');
add_action('admin_footer-options-writing.php', 'acfe_better_options_general_admin_footer');
add_action('admin_footer-options-reading.php', 'acfe_better_options_general_admin_footer');
add_action('admin_footer-options-discussion.php', 'acfe_better_options_general_admin_footer');
add_action('admin_footer-options-media.php', 'acfe_better_options_general_admin_footer');
add_action('admin_footer-options-permalink.php', 'acfe_better_options_general_admin_footer');
function acfe_better_options_general_admin_footer(){
    
    global $pagenow;
    
    ?>
    <script type="text/html" id="tmpl-acf-column-2">
        <div class="acf-column-2">

            <div id="poststuff" class="acfe-acfe-bt-admin-column">

                <div class="postbox">

                    <h2 class="hndle ui-sortable-handle"><span><?php _e('Settings'); ?></span></h2>

                    <div class="inside">
                        <div class="submitbox">

                            <div id="major-publishing-actions">

                                <div id="publishing-action">

                                    <div class="acfe-form-submit">
                                        <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Save Changes'); ?>" />
                                        <span class="acf-spinner"></span>
                                    </div>

                                </div>
                                <div class="clear"></div>

                            </div>

                        </div>

                    </div>

                </div>
                
                <?php do_meta_boxes(get_current_screen(), 'side', array()); ?>

            </div>
        </div>
    </script>
    <script type="text/javascript">
        (function($){

            // ACF Extended UI
            $('.wrap').addClass('acfe-ui');

            // wrap form
            $('.acfe-ui > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

            // add column side
            $('.acfe-ui > form .acf-columns-2').append($('#tmpl-acf-column-2').html());
            
            <?php if(!in_array($pagenow, array('options-permalink.php', 'options-media.php'))){ ?>
            
                // add title
                var title = $('.wrap > h1').text();
                $('.acfe-ui > form > div > div > table:first').before('<h2>' + title + '</h2>');
                
            <?php } ?>

            $('.acfe-ui > h1').css('margin-bottom', '13px');
            
            if($('#ping_sites').length){

                $('#ping_sites').wrap('<table class="form-table"><tbody><td class="td-full"></td></tbody></table>');
                $('#ping_sites').css('width', '100%');
                
            }
            
            // Hide native button
            $('.acfe-ui > form p.submit').hide();

        })(jQuery);
    </script>
    <?php
    
}