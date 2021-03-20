(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
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
            'select2_args': 'select2Args'
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

        select2Args: function(options, $select, fieldData, field, instance) {

            if (field.get('acfeAllowCustom')) {

                options.templateSelection = function(state) {

                    if (!state.id) {
                        return state.text;
                    }

                    var text = state.text;

                    var match_field = /{field:(.*)}/g;
                    var match_fields = /{fields}/g;
                    var match_get_field = /{get_field:(.*)}/g;
                    var match_query_var = /{query_var:(.*)}/g;
                    var match_request = /{request:(.*)}/g;
                    var match_current = /{current:(.*)}/g;
                    var match_form = /{(form|form:.*?)}/g;
                    var match_action = /{action:(.*)}/g;

                    text = text.replace(match_field, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{field:$1}</code>");
                    text = text.replace(match_fields, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{fields}</code>");
                    text = text.replace(match_current, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{current:$1}</code>");
                    text = text.replace(match_form, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{$1}</code>");
                    text = text.replace(match_action, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{action:$1}</code>");
                    text = text.replace(match_get_field, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{get_field:$1}</code>");
                    text = text.replace(match_query_var, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{query_var:$1}</code>");
                    text = text.replace(match_request, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{request:$1}</code>");


                    return text;

                };

                options.templateResult = function(state) {

                    if (!state.id) {
                        return state.text;
                    }

                    var text = state.text;

                    var match_field = /{field:(.*?)}/g;
                    var match_fields = /{fields}/g;
                    var match_get_field = /{get_field:(.*?)}/g;
                    var match_query_var = /{query_var:(.*?)}/g;
                    var match_request = /{request:(.*?)}/g;
                    var match_current = /{current:(.*?)}/g;
                    var match_form = /{(form|form:.*?)}/g;
                    var match_action = /{action:(.*?)}/g;

                    text = text.replace(match_field, "<code style='font-size:12px;'>{field:$1}</code>");
                    text = text.replace(match_fields, "<code style='font-size:12px;'>{fields}</code>");
                    text = text.replace(match_get_field, "<code style='font-size:12px;'>{get_field:$1}</code>");
                    text = text.replace(match_query_var, "<code style='font-size:12px;'>{query_var:$1}</code>");
                    text = text.replace(match_request, "<code style='font-size:12px;'>{request:$1}</code>");
                    text = text.replace(match_current, "<code style='font-size:12px;'>{current:$1}</code>");
                    text = text.replace(match_form, "<code style='font-size:12px;'>{$1}</code>");
                    text = text.replace(match_action, "<code style='font-size:12px;'>{action:$1}</code>");

                    return text;

                };

            }

            return options;

        }

    });

    /*
     * Dev Mode
     */
    new acf.Model({

        wait: 'prepare',

        events: {
            'click .acfe_delete_meta': 'onClickSingle',
            'click #acfe_bulk_delete_meta_submit': 'onSubmitBulk',
            'click.postboxes .hide-postbox-tog': 'onClickPostbox',
            'click .acfe-wp-object-modal': 'onClickOpen'
        },

        onClickOpen: function(e, $el) {

            new acfe.Popup($('.acfe-modal[data-modal-key=' + $el.attr('data-modal-key') + ']'), {
                title: $el.attr('data-modal-title'),
                size: 'medium',
                footer: acf.__('Close')
            });

        },

        $acfWrap: function() {
            return $('#acfe-acf-custom-fields');
        },

        $wpWrap: function() {
            return $('#acfe-wp-custom-fields');
        },

        acfCount: function() {
            return $('#acfe-acf-custom-fields tbody tr').length;
        },

        wpCount: function() {
            return $('#acfe-wp-custom-fields tbody tr').length;
        },

        $bulkActions: function() {
            return $('.acfe_dev_bulk_actions');
        },

        initialize: function() {

            var $acfWrap = this.$acfWrap();
            var $wpWrap = this.$wpWrap();
            var $bulkActions = this.$bulkActions();

            // Move Bulk Button
            $acfWrap.find('.tablenav.bottom').insertAfter($acfWrap);
            $wpWrap.find('.tablenav.bottom').insertAfter($wpWrap);

            if (!$acfWrap.is(':visible') && !$wpWrap.is(':visible')) {

                $bulkActions.hide();

            }

        },

        sync: function() {

            var self = this;

            var acfCount = self.acfCount();
            var wpCount = self.wpCount();

            var $acfWrap = self.$acfWrap();
            var $wpWrap = self.$wpWrap();

            var $bulkActions = self.$bulkActions();

            $acfWrap.find('.acfe_dev_meta_count').text(acfCount);
            $wpWrap.find('.acfe_dev_meta_count').text(wpCount);

            if (!acfCount) {
                $acfWrap.remove();
            }

            if (!wpCount) {
                $wpWrap.remove();
            }

            if (!acfCount && !wpCount) {
                $bulkActions.remove();
            }

        },

        onClickSingle: function(e, $el) {

            e.preventDefault();

            var self = this;
            var $tr = $el.closest('tr');

            $.ajax({
                url: acf.get('ajaxurl'),
                type: 'post',
                data: {
                    action: 'acfe/delete_meta',
                    id: $el.attr('data-meta-id'),
                    key: $el.attr('data-meta-key'),
                    type: $el.attr('data-type'),
                    _wpnonce: $el.attr('data-nonce'),
                },
                beforeSend: function() {

                    $tr.css({
                        backgroundColor: '#faafaa'
                    }).fadeOut(350, function() {
                        $tr.remove();
                        self.sync();
                    });

                },
                success: function(response) {

                    if (response !== '1') {
                        $tr.css({
                            backgroundColor: ''
                        });
                        $tr.show();
                    }

                }
            });

        },

        onSubmitBulk: function(e, $el) {

            e.preventDefault();

            var self = this;
            var action = $el.prevAll('.acfe_bulk_delete_meta_action').val();
            var type = $el.prevAll('.acfe_bulk_delete_meta_type').val();
            var nonce = $el.prevAll('.acfe_bulk_delete_meta_nonce').val();

            if (action === 'delete') {

                var ids = [];
                var trs = [];

                $('input.acfe_bulk_delete_meta:checked').each(function() {
                    ids.push($(this).val());
                    trs.push($(this).closest('tr'));
                });

                if (ids.length) {

                    $.ajax({
                        url: acf.get('ajaxurl'),
                        type: 'post',
                        data: {
                            action: 'acfe/bulk_delete_meta',
                            ids: ids,
                            type: type,
                            _wpnonce: nonce,
                        },
                        beforeSend: function() {

                            trs.map(function(tr) {
                                $(tr).css({
                                    backgroundColor: '#faafaa'
                                }).fadeOut(350, function() {
                                    $(tr).remove();
                                    self.sync();
                                });
                            });

                        }
                    });

                }

            }

        },

        onClickPostbox: function(e, $el) {

            var val = $el.val();

            var $acfWrap = this.$acfWrap();
            var $wpWrap = this.$wpWrap();
            var $bulkActions = this.$bulkActions();

            if (!acfe.inArray(val, ['acfe-wp-custom-fields', 'acfe-acf-custom-fields']))
                return;

            if ($el.prop('checked')) {

                if (!$bulkActions.is(':visible'))
                    $bulkActions.show();

            } else if ((val === 'acfe-wp-custom-fields' && !$acfWrap.is(':visible')) || (val === 'acfe-acf-custom-fields' && !$wpWrap.is(':visible'))) {

                $bulkActions.hide();

            }

        }

    });

    /*
     * Module: Author
     */
    new acf.Model({

        actions: {
            'new_field/name=acfe_author': 'newField',
        },

        newField: function(field) {

            field.on('change', function(e) {
                e.stopPropagation();
            });

        }

    });

    /*
     * Postbox: ACFE Class
     */
    acf.addAction('show_postbox', function(postbox) {
        postbox.$el.removeClass('acfe-postbox-left acfe-postbox-top');
    });

    /*
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

        newField: function(field) {

            if (field.get('enableSwitch')) {

                this.enableSwitch(field);

            } else if (field.get('switched') || field.get('switcher')) {

                this.enableSwitcher(field);

            }

        }

    });

})(jQuery);