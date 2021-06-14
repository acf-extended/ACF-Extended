(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Code Editor
     */
    new acf.Model({

        actions: {
            'append_field_object': 'appendCodeEditor'
        },

        // Fix duplicate Code Editor
        appendCodeEditor: function(field) {

            if (field.get('type') !== 'acfe_code_editor')
                return;

            field.$setting('default_value').find('> .acf-input > .acf-input-wrap > .CodeMirror:last').remove();
            field.$setting('placeholder').find('> .acf-input > .acf-input-wrap > .CodeMirror:last').remove();

        },

    });

    /*
     * Field: Column
     */
    new acf.Model({

        actions: {
            'change_field_label/type=acfe_column': 'renderTitle',
            'change_field_type/type=acfe_column': 'renderTitle',
            'render_field_settings/type=acfe_column': 'renderField',
        },

        ucFirst: function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        renderTitle: function($el) {

            var field = acf.getInstance($el);

            var $columns = field.$setting('columns');
            var columns = acf.getInstance($columns).getValue();

            var $endpoint = field.$setting('endpoint');
            var endpoint = acf.getInstance($endpoint).getValue();

            if (endpoint) {

                columns = 'Endpoint';

            }

            field.set('label', '(Column ' + this.ucFirst(columns) + ')');

        },

        renderField: function($el) {

            var field = acf.getFieldObject($el);

            var setLabel = function() {
                field.set('label', true);
            }

            field.on('change', '.acfe-field-columns', setLabel);
            field.on('change', '.acfe-field-columns-endpoint', setLabel);

        }

    });

    /*
     * Field: Taxonomy Terms
     */
    new acf.Model({

        filters: {
            'select2_ajax_data/action=acfe/fields/taxonomy_terms/allow_query': 'taxonomyTermsAjax',
        },

        taxonomyTermsAjax: function(ajaxData, data, $el, field, select) {

            // Taxonomies
            var $taxonomies = $el.closest('.acf-field-settings').find('> .acf-field-setting-taxonomy > .acf-input > select > option:selected');

            var tax = [];

            $taxonomies.each(function() {
                tax.push($(this).val());
            });

            ajaxData.taxonomies = tax;

            // Terms level
            var $level = $el.closest('.acf-field-settings').find('> .acf-field-setting-allow_terms > .acf-input input[type="number"]');

            ajaxData.level = $level.val();

            return ajaxData;

        }

    });

    /*
     * Field: Data
     */
    new acf.Model({

        wait: 'prepare',

        events: {
            'click .acfe_modal_open': 'onClickOpen'
        },

        onClickOpen: function(e, $el) {

            new acfe.Popup($('.acfe-modal[data-modal-key=' + $el.attr('data-modal-key') + ']'), {
                title: 'Data',
                size: 'medium',
                footer: acf.__('Close')
            });

        },

        initialize: function() {

            $('.button.edit-field').each(function() {

                var tbody = $(this).closest('tbody');
                $(tbody).find('.acfe_modal_open:first').insertAfter($(this));
                $(tbody).find('.acfe-modal:first').appendTo($('body'));
                $(tbody).find('tr.acf-field-setting-acfe_field_data:first').remove();

            });

        }

    });

    /*
     * Field Attribute: Before/After
     */
    new acf.Model({

        actions: {
            'new_field': 'onNewField'
        },

        onNewField: function(field) {

            if (field.get('type') === 'tab')
                return;

            var $sibling;

            if (field.has('before')) {

                // vars
                $sibling = field.$el.siblings('[data-name="' + field.get('before') + '"]').first();

                if ($sibling.length)
                    $sibling.before(field.$el);

            } else if (field.has('after')) {

                // vars
                $sibling = field.$el.siblings('[data-name="' + field.get('after') + '"]').first();

                if ($sibling.length)
                    $sibling.after(field.$el);

            }

        }
    });

    /*
     * Tab Attribute: Before/After
     */
    var Tab = acf.models.TabField;

    acf.models.TabField = Tab.extend({

        initialize: function() {

            if (this.has('before')) {

                // vars
                $sibling = this.$el.siblings('[data-name="' + this.get('before') + '"]').first();

                if ($sibling.length)
                    $sibling.before(this.$el);

            } else if (this.has('after')) {

                // vars
                $sibling = this.$el.siblings('[data-name="' + this.get('after') + '"]').first();

                if ($sibling.length)
                    $sibling.after(this.$el);

            }

            // Setup
            Tab.prototype.initialize.apply(this, arguments);

        }

    });

    /*
     * Field Group: Locations - Date/Time Picker
     */
    new acf.Model({

        wait: 'ready',

        actions: {
            'append': 'onAppend',
            'acfe/field_group/rule_refresh': 'refreshFields'
        },

        initialize: function() {
            this.$el = $('#acf-field-group-locations');
        },

        onAppend: function($el) {

            if (!$el.is('.rule-group') && !$el.parent().parent().parent().is('.rule-group'))
                return;

            this.refreshFields();

        },

        refreshFields: function() {

            var fields = acf.getFields({
                parent: this.$('td.value')
            });

            fields.map(function(field) {

                if (!acfe.inArray(field.get('type'), ['date_picker', 'date_time_picker', 'time_picker']))
                    return;

                field.$inputText().removeClass('hasDatepicker').removeAttr('id');

                field.initialize();

            });

        }

    });

    /*
     * Field Group: Meta
     */
    new acf.Model({

        actions: {
            'new_field/name=acfe_meta': 'renderClass',
            'new_field/name=acfe_settings': 'renderClass',
            'new_field/name=acfe_validate': 'renderClass',
        },

        renderClass: function(field) {

            field.$('.acf-button').removeClass('button-primary');

        }

    });

    /*
     * Field Group Custom Slug
     */
    new acf.Model({

        events: {
            'keyup #post_name': 'onInput'
        },

        onInput: function(e, $el) {

            var val = $el.val();

            if (!val.startsWith('group_')) {

                val = 'group_' + val;
                $el.val(val);

            }

            $('[name="acf_field_group[key]"]').val(val);
            $('.misc-pub-acfe-field-group-key code').html(val);

        },

    });

    /*
     * Compatibility
     */
    new acf.Model({

        actions: {
            'ready_field_object': 'flexibleContent'
        },

        // 0.8.4.5 Flexible Content: Fix duplicated "layout_settings" & "layout_title"
        flexibleContent: function(field) {

            // field_acfe_layout_abc123456_settings + field_acfe_layout_abc123456_title
            if (!field.get('key').startsWith('field_acfe_layout_'))
                return;

            field.delete();

        },

    });

})(jQuery);