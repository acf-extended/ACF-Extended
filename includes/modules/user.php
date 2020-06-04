<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/users'))
    return;

/**
 * User Edit View
 */
add_action('admin_footer-profile.php', 'acfe_better_user_edit_admin_footer');
add_action('admin_footer-user-edit.php', 'acfe_better_user_edit_admin_footer');
function acfe_better_user_edit_admin_footer(){
    
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
                
                <?php do_meta_boxes(get_current_screen(), 'side', array()); ?>

            </div>
        </div>
    </script>
    <script type="text/javascript">
        (function($){

            // wrap form
            $('#profile-page > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

            // add column side
            $('#profile-page > form .acf-columns-2').append($('#tmpl-acf-column-2').html());
            
            // hide native button
            $('#profile-page > form p.submit').hide();

        })(jQuery);
    </script>
    <?php
    
}

/**
 * User Add View
 */
add_action('admin_footer-user-new.php', 'acfe_better_user_new_admin_footer');
function acfe_better_user_new_admin_footer(){
    
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
                                        <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Add New User'); ?>" />
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
            
            $('.wrap').attr('id', 'profile-page');

            // wrap form
            $('#profile-page > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

            // add column side
            $('#profile-page > form .acf-columns-2').append($('#tmpl-acf-column-2').html());

            // add title
            var title = $('.wrap > h1').text();
            $('#profile-page > form > div > div > table:first').before('<h2>' + title + '</h2>');
            
            // Hide native button
            $('#profile-page > form p.submit').hide();

        })(jQuery);
    </script>
    <?php
    
}