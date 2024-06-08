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

    var moduleManager = new acf.Model({
        wait: 'prepare',
        priority: 1,
        initialize: function() {
            if (acfe.get('module') && acfe.get('module.screen') === 'post') {
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
     * module manager
     * @type {acf.Model}
     */
    var moduleManager = new acf.Model({
        wait: 'prepare',
        priority: 1,
        initialize: function() {
            if (acfe.get('module.name') === 'form' && acfe.get('module.screen') === 'post') {
                new module();
            }
        }
    });

    var hideLoadFieldsCheckboxes = function() {

        var loadFields = acf.getFields({
            name: 'load_acf_fields',
        });

        // loop
        loadFields.map(function(field) {

            var values = [];
            var selects = acf.getFields({
                type: 'select',
                sibling: field.$el,
            });

            selects.map(function(select) {
                if (select.has('relatedField') && select.val()) {
                    values.push(select.val());
                }
            });

            // checkbox inputs
            var $inputs = field.$inputs();

            // loop checkboxes
            $inputs.each(function() {

                var $li = $(this).closest('li');

                // show checkbox if not selected
                if (!acfe.inArray($(this).val(), values)) {
                    acf.show($li);
                } else {
                    acf.hide($li);
                }
            });

            field.$control().find('> li').each(function() {

                var $li = $(this);

                // check inputs hidden
                if ($li.find('li').not('.acf-hidden').length === 0) {
                    acf.hide($li);
                } else {
                    acf.show($li);
                }

            });

            // hide field if all checkboxes hidden
            if (field.$control().find('> li').not('.acf-hidden').length === 0) {
                field.hideDisable('acfe_form_field_groups', 'acfe_form_field_groups');
            } else {
                field.showEnable('acfe_form_field_groups', 'acfe_form_field_groups');
            }

        });

    }

    /**
     * module
     */
    var module = acf.Model.extend({

        actions: {
            'new_select2': 'newSelect2',
            'new_field/key=field_post_action_save_append_terms': 'newAppendTerms',
            'new_field/key=field_actions': 'newActions',
            'new_field/key=field_email_action_files': 'newFiles',
            'new_field/key=field_email_action_files_static': 'newFiles',
            'new_field/name=save_acf_fields': 'newCheckboxes',
            'new_field/name=load_acf_fields': 'newCheckboxes',
            'new_field/key=field_field_groups': 'newFieldGroups'
        },

        filters: {
            'select2_args': 'select2Args',
            'select2_ajax_data/action=acfe/form/map_field_ajax': 'mapFieldAjax',
            'select2_ajax_data/action=acfe/form/map_field_groups_ajax': 'mapFieldGroupsAjax',

            'select2_ajax_data/key=field_post_action_save_target_custom': 'customAjaxData',
            'select2_ajax_data/key=field_post_action_load_source_custom': 'customAjaxData',
            'select2_ajax_data/key=field_post_action_save_post_author_custom': 'customAjaxData',
            'select2_ajax_data/key=field_post_action_save_post_parent_custom': 'customAjaxData',

            'select2_ajax_data/key=field_term_action_save_target_custom': 'customAjaxData',
            'select2_ajax_data/key=field_term_action_save_parent_custom': 'customAjaxData',
            'select2_ajax_data/key=field_term_action_load_source_custom': 'customAjaxData',

            'select2_ajax_data/key=field_user_action_save_target_custom': 'customAjaxData',
            'select2_ajax_data/key=field_user_action_load_source_custom': 'customAjaxData',
        },


        /**
         * newSelect2
         *
         * new_select2
         */
        newSelect2: function(select2) {

            // spawn related field model
            if (select2.get('field') && select2.get('field').has('relatedField')) {
                new relatedField(select2);
            }

        },


        /**
         * newAppendTerms
         *
         * new_field/key=field_post_action_save_append_terms
         */
        newAppendTerms: function(field) {

            // get save post terms
            var postTerms = acf.getFields({
                key: 'field_post_action_save_post_terms',
                sibling: field.$el
            }).shift();

            // move append terms
            if (postTerms) {

                field.$inputWrap().addClass('append-terms').appendTo(postTerms.$inputWrap());
                field.$el.remove();

            }

        },


        /**
         * newActions
         *
         * new_field/key=field_actions
         */
        newActions: function(field) {

            field.on('click', '[data-name="add-layout"]', function(e) {
                $('body').find('.acf-fc-popup').addClass('acfe-fc-popup-grey');
            });

        },


        /**
         * newFiles
         *
         * new_field/key=field_email_action_files
         * new_field/key=field_email_action_files_static
         */
        newFiles: function(field) {
            field.$('> .acf-input > .acf-repeater > .acf-actions > .acf-button').removeClass('button-primary');
        },


        /**
         * select2Args
         *
         * select2_args
         */
        select2Args: function(options, $select, fieldData, field, instance) {

            if (field.get('acfeAllowCustom')) {

                var replaceCode = function(state) {

                    // /[{\w:]*(?<full>{(?<name>[\w]+)(?!:})(?::(?<value>\S*?))?})}*/g
                    // /({[\w: +-\\]+}*)/g

                    if (state.text && state.text.indexOf('<code>') === -1) {
                        state.text = state.text.replace(/({[\w: +-\\]+}*)/g, "<code>$1</code>");
                    }

                    // we must escape to let user re-order
                    var $selection = $('<span class="acf-selection"></span>');
                    $selection.html(acf.escHtml(state.text));
                    $selection.data('element', state.element);

                    return $selection;
                };

                options.templateSelection = replaceCode;
                options.templateResult = replaceCode;

            }

            return options;

        },


        /**
         * mapFieldAjax
         *
         * select2_ajax_data/action=acfe/form/map_field_ajax
         *
         * @param ajaxData
         * @param data
         * @param $el
         * @param field
         * @param instance
         * @returns {*}
         */
        mapFieldAjax: function(ajaxData, data, $el, field, instance) {

            ajaxData.field_label = '';
            ajaxData.value = field.val();
            ajaxData.choices = [];
            ajaxData.custom_choices = [];
            ajaxData.field_groups = [];
            ajaxData.is_load = field.has('relatedField') ? 1 : 0;

            // field label
            // get node text to avoid additional HTML (like tooltip)
            var fieldLabel = acfe.getTextNode(field.$labelWrap().find('label'));

            if (!fieldLabel) {
                var parent = acf.getInstance(field.$el.closest('.acf-field-group'));
                if (parent) {
                    fieldLabel = acfe.getTextNode(parent.$labelWrap().find('label'));
                }
            }

            ajaxData.field_label = fieldLabel;

            // choices
            var choices = field.get('choices');
            if (choices) {
                ajaxData.choices = choices;
            }

            // custom choices
            var customChoices = field.get('customChoices');
            if (customChoices) {
                ajaxData.custom_choices = customChoices;
            }

            // field groups
            var fieldGroups = acf.getField('field_field_groups').val();
            if (fieldGroups.length) {
                ajaxData.field_groups = fieldGroups;
            }

            return ajaxData;

        },

        /**
         * mapFieldGroupsAjax
         *
         * select2_ajax_data/action=acfe/form/map_field_groups_ajax
         *
         * @param ajaxData
         * @param data
         * @param $el
         * @param field
         * @param instance
         * @returns {*}
         */
        mapFieldGroupsAjax: function(ajaxData, data, $el, field, instance) {

            // data
            ajaxData.value = field.val();
            ajaxData.choices = [];
            ajaxData.custom_choices = [];

            // choices
            var choices = field.get('choices');
            if (choices) {
                ajaxData.choices = choices;
            }

            // custom choices
            var customChoices = field.get('customChoices');
            if (customChoices) {
                ajaxData.custom_choices = customChoices;
            }

            return ajaxData;

        },


        /**
         * customAjaxData
         *
         * @param ajaxData
         * @param data
         * @param $el
         * @param field
         * @param instance
         * @returns {*}
         */
        customAjaxData: function(ajaxData, data, $el, field, instance) {

            ajaxData.is_form = 1;

            return ajaxData;

        },

        /**
         * newCheckboxes
         *
         * new_field/name=save_acf_fields
         * new_field/name=load_acf_fields
         *
         * @param field
         */
        newCheckboxes: function(field) {

            this.refreshCheckboxChoicesHtml(field);

        },


        /**
         * newFieldGroups
         *
         * new_field/key=field_field_groups
         */
        newFieldGroups: function(field) {

            field.on('change', this.proxy(function(e) {

                this.refreshFieldGroupsMetabox();
                this.refreshCheckboxesChoices();

            }));

        },

        refreshFieldGroupsMetabox: function() {

            // get field groups
            var fieldGroups = acf.getField('field_field_groups').val();
            var metabox = acf.getPostbox('acfe-field-groups');

            if (!metabox) {
                return;
            }

            if (!fieldGroups.length) {
                return metabox.hide();
            }

            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax({
                    action: 'acfe/form/field_groups_metabox',
                    field_groups: fieldGroups
                }),
                type: 'post',
                dataType: 'html',
                context: this,
                success: function(response) {

                    metabox.show();
                    metabox.html(response);

                },
            });

        },

        refreshCheckboxesChoices: function() {

            // get save acf fields
            var saveFields = acf.getFields({
                name: 'save_acf_fields',
            });

            // get load acf fields
            var loadFields = acf.getFields({
                name: 'load_acf_fields',
            });

            // merge fields
            var checkboxes = [].concat(saveFields, loadFields);

            // loop checkboxes fields
            checkboxes.map(function(field) {
                this.refreshCheckboxChoicesHtml(field);
            }, this);

        },

        refreshCheckboxChoicesHtml: function(field) {

            // get field groups
            var fieldGroups = acf.getField('field_field_groups').val();

            if (!fieldGroups.length) {
                return field.hideDisable('acfe_form_field_groups', 'acfe_form_field_groups');
            }

            // send ajax
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax({
                    action: 'acfe/form/map_checkbox_ajax',
                    name: field.getInputName(), // acf[field_actions][row-0][field_post_action_save_acf_fields]
                    _name: field.get('name'),
                    key: field.get('key'),
                    value: field.val(),
                    field_groups: fieldGroups
                }),
                type: 'post',
                dataType: 'html',
                context: this,
                success: function(response) {

                    field.$inputWrap().html(response);

                    if (field.$control().find('> li').length) {

                        var labels = field.$inputWrap().find('> [data-labels]').data('labels');

                        if (labels.length) {

                            labels.map(function(label, i) {
                                field.$control().find('> li:eq(' + i + ')').prepend('<strong>' + label + '</strong>');
                            });

                        }

                        // show enable field
                        field.showEnable('acfe_form_field_groups', 'acfe_form_field_groups');

                    } else {
                        field.hideDisable('acfe_form_field_groups', 'acfe_form_field_groups');
                    }

                    // hide checkboxes based on "load fields"
                    hideLoadFieldsCheckboxes();

                },
            });

        },

    });


    /**
     * relatedField
     *
     * new_select2
     */
    var relatedField = acf.Model.extend({

        field: {},
        select2: {},
        relatedField: false,

        events: {
            'change': 'onChange',
            'hideField': 'onHideField',
            'showField': 'onShowField',
        },

        setup: function(select2) {
            this.select2 = select2;
            this.field = select2.get('field');

            // used for events
            this.$el = this.field.$el;
        },

        initialize: function() {

            // bail early if no related field
            this.relatedField = this.getRelatedField();

            if (!this.relatedField) {
                return;
            }

            this.setupRelatedHTML();
            this.showRelatedMessage(true);

        },

        onChange: function() {
            if (this.field.val()) {
                this.showRelatedMessage();
            } else {
                this.hideRelatedMessage();
            }
        },

        onHideField: function(e, $el, context) {
            if (this.relatedField && context === 'conditional_logic') {
                this.hideRelatedMessage();
            }
        },

        onShowField: function(e, $el, context) {
            if (this.relatedField && context === 'conditional_logic') {
                this.showRelatedMessage();
            }
        },

        setupRelatedHTML: function() {
            if (!this.relatedField.$inputWrap().find('.related-message').length) {
                this.relatedField.$inputWrap().append('<div class="related-message" />');
            }
        },

        showRelatedMessage: function(initialize) {

            initialize = initialize || false;

            // get select value
            var id = this.field.val();
            var value = this.field.val();

            // get select2 option label
            if (this.select2) {

                var options = this.select2.getValue();
                var option = options.shift();

                if (option && option.id) {
                    id = option.id;
                    value = option.text;
                }

            }

            // if value found
            if (value) {

                // display related message
                this.relatedField.$inputWrap().addClass('acfe-display-related-message');
                this.relatedField.$inputWrap().find('.related-message').html('Field: ' + value);

                if (!initialize) {

                    // var $input = this.relatedField.$input();
                    // if(!$input.is('select')){
                    //     console.log($input)
                    //     acf.val($input, '', true); // silent
                    // }

                    if (acf.isset(this.relatedField, 'select2')) {
                        this.relatedField.select2.$el.val(null);
                        this.relatedField.select2.$el.trigger('change');
                    }

                }



                // // set empty value for conditional logic
                // var relatedFieldVal = this.relatedField.val();
                //
                // // delete value silently (to avoid trigger exit prompt)
                // if(acfe.isFieldKey(relatedFieldVal) || (acfe.isArray(relatedFieldVal) && acfe.isFieldKey(relatedFieldVal.shift()))){
                //     acf.val(this.relatedField.$input(), '', true);
                //
                // // delete value normally
                // }else{
                //     this.relatedField.select2.$el.val(null);
                //     this.relatedField.select2.$el.trigger('change');
                // }

                hideLoadFieldsCheckboxes();

            }

        },

        hideRelatedMessage: function() {

            this.relatedField.$inputWrap().removeClass('acfe-display-related-message');
            this.relatedField.$inputWrap().find('.related-message').html('');

            hideLoadFieldsCheckboxes();

        },

        getRelatedField: function() {

            var key = this.field.get('relatedField');
            var $layout = this.field.$el.closest('.layout');

            var relatedField = acf.getFields({
                key: key,
                parent: $layout
            });

            return relatedField.shift();

        }

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