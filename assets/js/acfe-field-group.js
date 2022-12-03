(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Code Editor
     */
    new acf.Model({

        actions: {
            'append_field_object': 'appendCodeEditor'
        },

        // Fix duplicate Code Editor
        appendCodeEditor: function(field) {

            if (field.get('type') !== 'acfe_code_editor') {
                return;
            }

            var $defaultValue = field.$setting('default_value').find('> .acf-input > .acf-input-wrap > .CodeMirror');
            if ($defaultValue.length > 1) {
                $defaultValue.last().remove();
            }

            var $placeholder = field.$setting('placeholder').find('> .acf-input > .acf-input-wrap > .CodeMirror');
            if ($placeholder.length > 1) {
                $placeholder.last().remove();
            }

        },

    });

    /**
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

    /**
     * Field: Taxonomy Terms
     */
    new acf.Model({

        filters: {
            'select2_ajax_data/action=acfe/fields/taxonomy_terms/allow_query': 'taxonomyTermsAjax',
        },

        taxonomyTermsAjax: function(ajaxData, data, $el, field, select) {

            // Taxonomies
            var $taxonomies = $el.closest('.acf-field-settings').find('.acf-field-setting-taxonomy > .acf-input > select > option:selected');

            var tax = [];

            $taxonomies.each(function() {
                tax.push($(this).val());
            });

            ajaxData.taxonomies = tax;

            // Terms level
            var $level = $el.closest('.acf-field-settings').find('.acf-field-setting-allow_terms > .acf-input input[type="number"]');

            ajaxData.level = $level.val();

            return ajaxData;

        }

    });

    /**
     * Field: Data
     */
    new acf.Model({

        wait: 'prepare',

        initialize: function() {

            $('.button.edit-field').each(function() {

                var $this = $(this);
                var tbody = $this.closest('tbody, .acf-field-settings'); // ACF 6.0 doesn't use tbody anymore
                $(tbody).find('.acfe-data-button:first').insertAfter($this);
                $(tbody).find('.acfe-modal:first').appendTo($('body'));
                $(tbody).find('.acf-field-setting-acfe_field_data:first').remove();

            });

        }

    });

    /**
     * Field Attribute: Before/After
     */
    new acf.Model({

        actions: {
            'new_field': 'onNewField'
        },

        onNewField: function(field) {

            // bail early if no before/after
            if (field.has('before') || field.has('after')) {

                // bail early
                if (field.get('type') === 'tab') {
                    return;
                }

                // vars
                var type = field.has('before') ? 'before' : 'after';
                var $fieldObject = field.$el.closest('.acf-field-object');
                var fieldObject, fieldObjectKey;

                if ($fieldObject.length) {
                    fieldObject = acf.getFieldObject($fieldObject);
                    fieldObjectKey = fieldObject.get('key');
                }

                // get parent from acf-fields div
                var $fields = field.$el.closest('.acf-fields');

                // get parent from acf-table div
                if (!$fields.length) {
                    $fields = field.$el.closest('.acf-table');
                }

                // found parent
                if ($fields.length) {

                    var $sibling;

                    // find within parent field
                    if (fieldObjectKey) {
                        $sibling = $fields.find('[data-name="' + field.get(type) + '"]').not('.acf-input-sub .acf-field-object[data-key!="' + fieldObjectKey + '"] [data-name="' + field.get(type) + '"]').first();

                        // find within parent
                    } else {
                        $sibling = $fields.find('[data-name="' + field.get(type) + '"]').first();
                    }

                    if ($sibling.length) {

                        // apply after/before
                        $sibling[type](field.$el);
                    }

                }

            }

        }
    });

    /**
     * Tab Attribute: Before/After
     */
    var Tab = acf.models.TabField;

    acf.models.TabField = Tab.extend({

        initialize: function() {

            // bail early if no before/after
            if (this.has('before') || this.has('after')) {

                // vars
                var type = this.has('before') ? 'before' : 'after';

                // get parent from acf-fields div
                var $fields = this.$el.closest('.acf-fields');
                var $fieldObject = this.$el.closest('.acf-field-object');
                var fieldObject, fieldObjectKey;

                if ($fieldObject.length) {
                    fieldObject = acf.getFieldObject($fieldObject);
                    fieldObjectKey = fieldObject.get('key');
                }

                // get parent from acf-table div
                if (!$fields.length) {
                    $fields = this.$el.closest('.acf-table');
                }

                // found parent
                if ($fields.length) {

                    var $sibling;

                    // find within parent field
                    if (fieldObjectKey) {
                        $sibling = $fields.find('[data-name="' + this.get(type) + '"]').not('.acf-input-sub .acf-field-object[data-key!="' + fieldObjectKey + '"] [data-name="' + this.get(type) + '"]').first();

                        // find within parent
                    } else {
                        $sibling = $fields.find('[data-name="' + this.get(type) + '"]').first();
                    }

                    if ($sibling.length) {

                        // apply after/before
                        $sibling[type](this.$el);
                    }

                }

            }

            // Setup
            Tab.prototype.initialize.apply(this, arguments);

        }

    });

    /**
     * Field Group: Locations - Date/Time Picker
     */
    new acf.Model({

        wait: 'ready',

        actions: {
            'append': 'onAppend',
            'acfe/field_group/rule_refresh': 'refreshFields'
        },

        initialize: function() {

            // ACF 6.0 changed #acf-field-group-locations to .field-group-locations
            this.$el = $('#acf-field-group-locations, .field-group-locations');
        },

        onAppend: function($el) {

            if (!$el.is('.rule-group') && !$el.parent().parent().parent().is('.rule-group')) {
                return;
            }

            this.refreshFields();

        },

        refreshFields: function() {

            var fields = acf.getFields({
                parent: this.$('td.value')
            });

            fields.map(function(field) {

                if (!acfe.inArray(field.get('type'), ['date_picker', 'date_time_picker', 'time_picker'])) {
                    return;
                }

                field.$inputText().removeClass('hasDatepicker').removeAttr('id');

                field.initialize();

            });

        }

    });

    /**
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

    /**
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

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Repeater
     *
     * Fix ACF 6.0 repeater settings such as Advanced Settings/Validation not working correctly
     */
    new acf.Model({

        actions: {
            'duplicate': 'onAppend',
        },

        onAppend: function($el, $el2) {

            if (acfe.versionCompare(acf.get('acf_version'), '>=', '6.0')) {

                // do not use acf.getClosestField() in order to not instantiate the field
                // otherwise, this would create a bug when duplicating a flexible content layout:
                // new layout sub fields would be moved back to the original layout
                var $field = acf.findClosestField($el2);

                if ($field.is('[data-type="repeater"]')) {

                    // instantiate the field here
                    var field = acf.getField($field);

                    // field.render() should have been in the repeater "add" method, at the end of acf.duplicate()
                    // but it was removed in acf 6.0
                    field.render();

                }

            }

        }


    });

})(jQuery);