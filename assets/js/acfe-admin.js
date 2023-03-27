(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Module: Author
     */
    acf.addAction('new_field/name=acfe_author', function(field) {
        field.on('change', function(e) {
            e.stopPropagation();
        });
    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    new acf.Model({

        wait: 'prepare',

        actions: {
            'refresh_post_screen': 'onRefreshScreen',
        },

        events: {
            'click .acfe-dev-delete-meta': 'onDeleteSingle',
            'click .acfe-dev-bulk [type="submit"]': 'onDeleteBulk',
            'change #acfe-wp-custom-fields-hide': 'onCheckPostbox',
            'change #acfe-acf-custom-fields-hide': 'onCheckPostbox',
        },

        $acf: function() {
            return $('#acfe-acf-custom-fields');
        },

        $wp: function() {
            return $('#acfe-wp-custom-fields');
        },

        $bulk: function() {
            return $('.acfe-dev-bulk');
        },

        count: function(metabox) {
            return this['$' + metabox]().find('tbody tr').length;
        },

        hideBulk: function() {
            this.$bulk().hide();
        },

        showBulk: function() {
            this.$bulk().show();
        },

        initialize: function() {

            this.$bulk().insertAfter(this.$bulk().closest('.postbox'));

            if (!this.$acf().is(':visible') && !this.$wp().is(':visible')) {
                this.hideBulk();
            }

            $('.metabox-prefs .acfe-dev-meta-count').remove();

        },

        syncMetaboxes: function() {

            this.$acf().find('.acfe-dev-meta-count').text(this.count('acf'));
            this.$wp().find('.acfe-dev-meta-count').text(this.count('wp'));

            if (!this.count('acf')) {
                this.$acf().remove();
            }

            if (!this.count('wp')) {
                this.$wp().remove();
            }

            if ((!this.count('acf') && !this.count('wp')) || (!this.$acf().is(':visible') && !this.$wp().is(':visible'))) {
                this.hideBulk();
            }

        },

        onDeleteSingle: function(e, $el) {

            e.preventDefault();

            var self = this;
            var $tr = $el.closest('tr');

            $.ajax({
                url: acf.get('ajaxurl'),
                type: 'post',
                data: {
                    action: 'acfe/dev/single_delete_meta',
                    id: $el.attr('data-meta-id'),
                    key: $el.attr('data-meta-key'),
                    type: $el.attr('data-meta-type'),
                    _wpnonce: $el.attr('data-nonce'),
                },
                beforeSend: function() {

                    $tr.addClass('deleted').delay(200).fadeOut(250, function() {
                        $tr.remove();
                        self.syncMetaboxes();
                    });

                },
                success: function(response) {

                    if (response !== '1') {
                        $tr.removeClass('deleted');
                        $tr.show();
                    }

                }
            });

        },

        onDeleteBulk: function(e, $el) {

            e.preventDefault();

            var self = this;
            var action = $el.prevAll('.acfe-dev-bulk-action').val();
            var type = $el.prevAll('.acfe-dev-bulk-meta-type').val();
            var nonce = $el.prevAll('.acfe-dev-bulk-nonce').val();

            if (action !== 'delete') {
                return;
            }

            var ids = [];
            var trs = [];

            $('input.acfe-dev-bulk-checkbox:checked').each(function() {
                ids.push($(this).val());
                trs.push($(this).closest('tr'));
            });

            if (!ids.length) {
                return;
            }

            $.ajax({
                url: acf.get('ajaxurl'),
                type: 'post',
                data: {
                    action: 'acfe/dev/bulk_delete_meta',
                    ids: ids,
                    type: type,
                    _wpnonce: nonce,
                },
                beforeSend: function() {

                    trs.map(function(tr) {
                        $(tr).addClass('deleted').delay(200).fadeOut(250, function() {
                            $(tr).remove();
                            self.syncMetaboxes();
                        });
                    });

                }
            });

        },

        onCheckPostbox: function(e, $el) {

            var val = $el.val();

            if ($el.prop('checked')) {
                this.showBulk();

            } else if ((val === 'acfe-wp-custom-fields' && !this.$acf().is(':visible')) || (val === 'acfe-acf-custom-fields' && !this.$wp().is(':visible'))) {
                this.hideBulk();

            }

        },

        onRefreshScreen: function(data) {

            // fix dev mode postbox being hidden
            // on page attributes template change
            data.hidden.map(function(id) {
                if (id === 'acfe-wp-custom-fields' || id === 'acfe-acf-custom-fields' || id === 'acfe-performance') {
                    acf.getPostbox(id).showEnable();
                }
            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Enable Switch
     */
    new acf.Model({

        actions: {
            'new_field': 'newField',
        },

        isRepeater: function(field) {
            return field.get('type') === 'repeater' || field.get('type') === 'flexible_content';
        },

        getCondition: function(target) {
            return this.isRepeater(target) ? target.val() === 0 : !target.val().length;
        },

        newField: function(field) {

            if (field.get('enableSwitch')) {
                this.enableSwitch(field);

            } else if (field.get('switched') || field.get('switcher')) {
                this.enableSwitcher(field);

            }

        },

        enableSwitcher: function(field) {

            var self = this;
            var switcher, target;

            if (field.get('switched')) {

                switcher = acf.getField(field.$el.prev());
                target = field;

            } else if (field.get('switcher')) {

                switcher = field;
                target = acf.getField(field.$el.next());

            }

            if (self.getCondition(target)) {

                switcher.switchOff();
                switcher.show('switcher');
                target.hide('switcher');

            } else {

                switcher.hide('switcher');
                target.show('switcher');

            }

            if (field.get('switcher')) {

                // Switch Action
                switcher.on('change', function() {

                    if (switcher.$input().prop('checked')) {

                        switcher.hide('switcher');

                        target.show('switcher');

                        if (self.isRepeater(target)) {
                            target.add();
                        }

                    }

                });

                // Field Action
                target.on('change', function(e, $el) {

                    if (self.getCondition(target)) {

                        switcher.switchOff();
                        switcher.show('switcher');
                        target.hide('switcher');

                    }

                });

            }

        },

        enableSwitch: function(field) {

            // Clone
            var $row = field.$el.clone();

            // Params
            $row.removeAttr('data-enable-switch');
            $row.attr('data-switcher', true);
            $row.attr('data-name', field.get('name') + '_acfe_switch');
            $row.attr('data-key', field.get('name') + '_acfe_switch');
            $row.attr('data-type', 'true_false');

            // HTML
            $row.find('>.acf-input').html('<div class="acf-true-false">\n' +
                '<input type="hidden" value="0">' +
                '<label>\n' +
                '<input type="checkbox" value="1" class="acf-switch-input" autocomplete="off">\n' +
                '<div class="acf-switch"><span class="acf-switch-on" style="min-width: 18px;">' + acf.__('Yes') + '</span>' +
                '<span class="acf-switch-off" style="min-width: 18px;">' + acf.__('No') + '</span><div class="acf-switch-slider"></div></div>' +
                '</label>\n' +
                '</div>');

            // Insert
            $row = $row.insertBefore(field.$el);

            // New Switch
            acf.getField($row);

            // Remove Attribute
            field.$el.removeAttr('data-enable-switch');
            field.set('enableSwitch', false);

            field.$el.attr('data-switched', true);
            field.set('switched', true);

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * ACFE Form
     */
    new acf.Model({

        actions: {

            // Buttons
            'new_field/name=acfe_form_actions': 'actionsButton',
            'new_field/name=acfe_form_email_files': 'filesButton',
            'new_field/name=acfe_form_email_files_static': 'filesButton',

            // Post
            'new_field/name=acfe_form_post_map_target': 'mapFields',
            'new_field/name=acfe_form_post_map_post_type': 'mapFields',
            'new_field/name=acfe_form_post_map_post_status': 'mapFields',
            'new_field/name=acfe_form_post_map_post_title': 'mapFields',
            'new_field/name=acfe_form_post_map_post_name': 'mapFields',
            'new_field/name=acfe_form_post_map_post_content': 'mapFields',
            'new_field/name=acfe_form_post_map_post_excerpt': 'mapFields',
            'new_field/name=acfe_form_post_map_post_author': 'mapFields',
            'new_field/name=acfe_form_post_map_post_parent': 'mapFields',
            'new_field/name=acfe_form_post_map_post_terms': 'mapFields',

            // User
            'new_field/name=acfe_form_user_map_email': 'mapFields',
            'new_field/name=acfe_form_user_map_username': 'mapFields',
            'new_field/name=acfe_form_user_map_password': 'mapFields',
            'new_field/name=acfe_form_user_map_first_name': 'mapFields',
            'new_field/name=acfe_form_user_map_last_name': 'mapFields',
            'new_field/name=acfe_form_user_map_nickname': 'mapFields',
            'new_field/name=acfe_form_user_map_display_name': 'mapFields',
            'new_field/name=acfe_form_user_map_website': 'mapFields',
            'new_field/name=acfe_form_user_map_description': 'mapFields',
            'new_field/name=acfe_form_user_map_role': 'mapFields',

            // Term
            'new_field/name=acfe_form_term_map_name': 'mapFields',
            'new_field/name=acfe_form_term_map_slug': 'mapFields',
            'new_field/name=acfe_form_term_map_taxonomy': 'mapFields',
            'new_field/name=acfe_form_term_map_parent': 'mapFields',
            'new_field/name=acfe_form_term_map_description': 'mapFields',
        },

        filters: {
            'select2_template_selection': 'select2TemplateSelection',
            'select2_template_result': 'select2TemplateSelection',
        },

        actionsButton: function(field) {

            field.on('click', '[data-name="add-layout"]', function(e) {

                $('body').find('.acf-fc-popup').addClass('acfe-fc-popup-grey');

            });

        },

        filesButton: function(field) {

            field.$('> .acf-input > .acf-repeater > .acf-actions > .acf-button').removeClass('button-primary');

        },

        mapFields: function(field) {

            var $layout = field.$el.closest('.layout');
            var $message = $layout.find('> .acf-fields > .acf-field[data-name="' + field.get('name') + '_message"] > .acf-input');

            var selected = field.$input().find('option:selected').text();

            if (selected.length) {
                $message.html(selected);
            }

            field.$input().on('change', function() {

                // Message
                var text = $(this).find('option:selected').text();

                $message.html(text);

            });

        },

        select2TemplateSelection: function(text, selection, $select, fieldData, field, instance) {

            if (field.get('acfeAllowCustom')) {
                return this.replaceCode(text);
            }

            return text;

        },

        replaceCode: function(text) {

            text = text.replace(/{field:(.*?)}/g, "<code>{field:$1}</code>");
            text = text.replace(/{fields}/g, "<code>{fields}</code>");
            text = text.replace(/{get_field:(.*?)}/g, "<code>{get_field:$1}</code>");
            text = text.replace(/{query_var:(.*?)}/g, "<code>{query_var:$1}</code>");
            text = text.replace(/{request:(.*?)}/g, "<code>{request:$1}</code>");
            text = text.replace(/{current:(.*?)}/g, "<code>{current:$1}</code>");
            text = text.replace(/{(form|form:.*?)}/g, "<code>{$1}</code>");
            text = text.replace(/{action:(.*?)}/g, "<code>{action:$1}</code>");

            return text;

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    var moduleManager = new acf.Model({
        wait: 'prepare',
        priority: 1,
        initialize: function() {
            if (acfe.get('module') && acfe.get('module').screen === 'post') {
                new module(acfe.get('module'));
            }
        }
    });

    var module = acf.Model.extend({

        setup: function(props) {
            this.inherit(props);
        },

        filters: {
            'validation_complete': 'onValidationComplete',
        },

        onValidationComplete: function(data, $el, instance) {

            // title
            var $title = $('#titlewrap #title');

            // validate post title
            if (!$title.val()) {

                // data
                data.valid = 0;
                data.errors = data.errors || [];

                // push error
                data.errors.push({
                    input: '',
                    message: this.get('messages.label')
                });

                $title.focus();

            }

            return data;

        },

        initialize: function() {

            // update status
            $('#post-status-display').html(this.get('messages.status'));

            // move export links
            $('.acfe-misc-export').insertAfter('.misc-pub-post-status');

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Postboxes: ACFE Class
     */
    acf.addAction('show_postbox', function(postbox) {
        postbox.$el.removeClass('acfe-postbox-left acfe-postbox-top');
    });

})(jQuery);