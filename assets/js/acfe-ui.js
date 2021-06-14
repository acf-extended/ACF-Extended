(function($) {

    if (typeof acf === 'undefined')
        return;

    acfe.enhancedEditUI = function(props) {
        return new enhancedEditUI(props);
    };

    var enhancedEditUI = acf.Model.extend({

        data: {
            screen: false,
            submit: '> p.submit',
            pageTitle: false
        },

        title: false,
        $form: false,
        $main: false,
        $sidebar: false,

        setup: function(props) {

            // Extend data
            $.extend(this.data, props);

            // $el
            this.$el = $('.wrap');
            this.$el.attr('id', 'poststuff');

            // Init
            this.$('> form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');
            $('#side-sortables').appendTo('.acf-columns-2').wrapAll('<div class="acf-column-2"></div>');

            // Vars
            this.title = this.$('> h1').text();
            this.$form = this.$('> form');
            this.$main = this.$('.acf-column-1');
            this.$sidebar = this.$('.acf-column-2');

            // Fake Page Title
            if (this.get('pageTitle')) {
                this.$main.find('> table:first').before('<h2>' + this.title + '</h2>');
            }

            // Sidebar Title
            $('#submitdiv > .postbox-header > h2.hndle').text(this.title);

            // Submit: Move to Sidebar
            this.$main.find(this.get('submit')).contents().appendTo('#publishing-action');
            this.$main.find(this.get('submit')).remove();

            // Submit: Button
            $('#submitdiv #publishing-action .button').addClass('button-large').after('<span class="spinner"></span>');

        },

        events: {
            'submit form': 'onSubmit'
        },

        initialize: function() {

            this.addActions({
                'acfe/ui/user-edit': 'userEdit',
                'acfe/ui/user-new': 'userNew',
                'acfe/ui/term-edit': 'termEdit',
                'acfe/ui/settings': 'settings',
            });

            acf.doAction('acfe/ui/' + this.get('screen'), this);

        },

        onSubmit: function(e, $el) {

            acf.lockForm($el);

        },

        userEdit: function() {

            // Vars
            var $main = this.$main;

            // Metabox: After Title
            $main.prepend($('#acf_after_title-sortables'));

            // Yoast Settings
            var $yoastSettings = this.$('> form .yoast.yoast-settings');

            if ($yoastSettings.length) {

                $yoastSettings.addClass('postbox');
                $yoastSettings.find('> h2').wrapAll('<div class="postbox-header"></div>');
                $yoastSettings.find('> .postbox-header ~ *').wrapAll('<div class="acf-fields -left"></div>');
                $yoastSettings.find('.acf-fields > label:nth-of-type(1), .acf-fields > input:nth-of-type(1)').wrapAll('<div class="acf-field"></div>');
                $yoastSettings.find('.acf-fields > label:nth-of-type(1), .acf-fields > label:nth-of-type(1) ~ *').wrapAll('<div class="acf-field"></div>');
                $yoastSettings.find('.acf-fields > br').remove();

                $yoastSettings.find('.acf-field').each(function() {

                    var $this = $(this);
                    $this.find('label:nth-of-type(1)').wrapAll('<div class="acf-label"></div>');
                    $this.find('.acf-label ~ *').wrapAll('<div class="acf-input"></div>');

                });

            }

            // RankMath
            var $rankMath = this.$('> form .rank-math-metabox-frame');

            if ($rankMath.length) {

                var rankMathTitle = $rankMath.find('h2').text();

                $rankMath.find('h2').remove();
                $rankMath.wrapAll('<div class="postbox acf-postbox rankmath"><div class="acf-fields -left"><div class="acf-field"><div class="acf-input"></div></div></div></div>');
                $rankMath.closest('.acf-field').prepend('<div class="acf-label"><label>' + rankMathTitle + '</label></div>');
                $rankMath.closest('.postbox').prepend('<div class="postbox-header"><h2>' + rankMathTitle + '</h2></div>');

            }

            // User Role Editor
            var $userRoleEditor = this.$('#ure_select_other_roles');

            if ($userRoleEditor.length) {
                $userRoleEditor.closest('table').find('tr:eq(1) > td > br').remove();
            }

            // Application Passwords
            var $applicationPasswords = $('#application-passwords-section');

            if ($applicationPasswords.length) {

                var title = $applicationPasswords.find('> h2').text();

                $applicationPasswords.addClass('postbox');
                $applicationPasswords.wrapInner('<div class="acf-fields -left"><div class="acf-field"><div class="acf-input"></div></div></div>');
                $applicationPasswords.find('.acf-input > h2').insertBefore($applicationPasswords.find('.acf-fields')).wrapAll('<div class="postbox-header"></div>');
                $applicationPasswords.find('.acf-input').before('<div class="acf-label"><label>' + title + '</label></div>');
                $applicationPasswords.find('.acf-input > p:first').css('margin-top', 15).insertBefore($applicationPasswords.find('p.submit'));

                $applicationPasswords.find('.acf-input > .create-application-password > .form-field').removeClass('form-field');
                $applicationPasswords.find('.acf-input > .create-application-password > div > label').remove();

            }

            // Nickname Field
            var $userNickname = $('input#nickname');

            if ($userNickname.length) {

                $userNickname.wrapAll('<div id="titlediv"><div id="titlewrap"></div></div>');
                $('#titlediv').append($('#edit-slug-box')).prependTo($main);
                $main.find('tr.user-nickname-wrap').remove();

            }

            // Reorder Name to first metabox
            $main.find('> h2:eq(1), > h2:eq(1) + table').insertBefore($main.find('> h2:first'));

        },

        userNew: function() {

            // Vars
            var $main = this.$main;
            this.$('> p:first').insertAfter($main.find('>h2:first'));
            $main.prepend($('#acf_after_title-sortables'));

        },

        termEdit: function() {

            // Vars
            var $main = this.$main;

            // Term Name Field
            var $termName = $('input#name');

            if ($termName.length) {

                $termName.wrapAll('<div id="titlediv"><div id="titlewrap"></div></div>');
                $('#titlediv').append($('.permalink')).prependTo($main);
                $main.find('tr.term-name-wrap').remove();

            }

            // WPML Widget
            var $wpml = $('#icl_tax_menu');

            if ($wpml.length) {

                var widgetTitle = $wpml.find('h3.hndle').text();
                $wpml.find('.inside').addClass('icl-tax-postbox-content').attr('style', '').insertAfter('#submitdiv');
                this.$sidebar.find('.icl-tax-postbox-content').wrapAll('<div id="icl-tax-postbox" class="postbox"></div>').parent().prepend('<div class="postbox-header"><h2 class="hdnle">' + widgetTitle + '</h2></div>');

            }

            // Yoast
            var $yoast = $('.wpseo-taxonomy-metabox-postbox');

            if ($yoast.length) {

                var metaboxTitle = $yoast.find('> h2').text();
                $yoast.find('> .inside').removeClass('inside').wrapAll('<div class="acf-fields -left"><div class="acf-field"><div class="acf-input"></div></div></div>');
                $('<div class="acf-label"><label>' + metaboxTitle + '</label></div>').insertBefore($yoast.find('.acf-input'));

            }

            // RankMath
            var $rankMath = this.$('> form .rank-math-metabox-frame');

            if ($rankMath.length) {

                var rankMathTitle = $rankMath.find('h2').text();

                $rankMath.find('h2').remove();
                $rankMath.wrapAll('<div class="postbox acf-postbox rankmath"><div class="acf-fields -left"><div class="acf-field"><div class="acf-input"></div></div></div></div>');
                $rankMath.closest('.acf-field').prepend('<div class="acf-label"><label>' + rankMathTitle + '</label></div>');
                $rankMath.closest('.postbox').prepend('<div class="postbox-header"><h2>' + rankMathTitle + '</h2></div>');

            }

        },

        settings: function() {

            // Fix potential empty locale causing error
            if (!acf.get('locale'))
                acf.set('locale', 'en_US');

            // Form Data
            $('#acf-form-data').prependTo(this.$form);

            // Metabox: After title
            $('#acf_after_title-sortables').prependTo(this.$main);

            // Metabox: Normal
            $('#normal-sortables').appendTo(this.$main);

            // Writing: Ping
            var $pingSites = this.$('#ping_sites');

            if ($pingSites.length) {
                $pingSites.wrap('<table class="form-table"><tbody><td class="td-full"></td></tbody></table>');
                $pingSites.css('width', '100%');
            }

            // Permlalinks
            var $permalinks = this.$('.permalink-structure');

            if ($permalinks.length) {
                $permalinks.prev().prev('p').insertBefore($permalinks);
            }

        },

    });

    acfe.enhancedListUI = function(props) {
        return new enhancedListUI(props);
    };

    var enhancedListUI = acf.Model.extend({

        setup: function(props) {

            // Extend data
            $.extend(this.data, props);

        },

        initialize: function() {

            // Add button
            $('.wrap .wp-heading-inline').after($('#tmpl-button-add-term').html());

            // Move form
            $('#ajax-response').after($('#col-container #col-left').addClass('acfe-bt'));

            // Hide form
            $('.acfe-bt').hide();

            // Create wrapper
            $('.acfe-bt .form-wrap').append('<div id="poststuff"></div>');

            // Append form inside wrapper
            var $newForm = $('.acfe-bt .form-wrap form');

            $('.acfe-bt #poststuff').append($newForm);
            $newForm.wrapInner('<div class="postbox" id="acfe-bt-form"><div class="inside"></div></div>');

            // Append new title
            var $nativeTitle = $('.acfe-bt .form-wrap > h2');

            $('.acfe-bt .postbox').prepend('<h2 class="hndle">' + $nativeTitle.text() + '</h2>');
            $nativeTitle.remove();

            // ACF class
            $('.acfe-bt .inside .form-field').addClass('acf-field');
            $('.acfe-bt .inside .submit').addClass('form-field');

            $('.acfe-bt .inside .form-field').each(function() {

                var $this = $(this);

                // Polylang Exception
                if ($this.is('#term-translations'))
                    return;

                $this.append('<div class="acf-input"></div>');
                $this.find('.acf-input').append($this.find('> :not("label")'));

                // Add spacing when a meta field has no label
                var $label = $this.find('> label');
                if ($label.length) {

                    $label.wrap('<div class="acf-label"></div>');

                } else {

                    $this.addClass('acfe-bt-no-label');

                }

            });

            // Remove ACF Fields id
            $('#acf-term-fields').contents().unwrap();

            // Button
            var $newButton = $('.acfe-bt-admin-button-add');

            $newButton.click(function(e) {

                e.preventDefault();
                var $wrap = $('.acfe-bt');

                if ($wrap.is(':visible'))
                    $wrap.hide();
                else
                    $wrap.show();

            });

            // Label to left
            if (typeof acf !== 'undefined') {
                acf.postbox.render({
                    'id': 'acfe-bt-form',
                    'label': 'left'
                });
            }

            $('#acfe-bt-form .acf-tab-wrap.-left').removeClass('-left').addClass('-top');

            // WPML Widget
            var $wpml = $('#icl_tax_menu');

            if ($wpml.length) {

                var $wpmlWidget = $wpml.find('.postbox').removeClass('postbox');
                $wpmlWidget.find('.inside').removeClass('inside').css('padding', 0);
                $wpmlWidget.insertBefore('.acfe-bt .inside .submit');

                var wpmlTitle = $wpmlWidget.find('h3.hndle').text();

                $wpmlWidget.find('.hndle').remove();

                $wpmlWidget.wrapAll('<div class="form-field acf-field"><div class="acf-input"></div></div>').parent().parent().prepend('<div class="acf-label"><label>' + wpmlTitle + '</label></div>');


            }

        }

    });

})(jQuery);