(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Checkbox & Radio
     */
    acf.registerConditionForFieldType('contains', 'checkbox');
    acf.registerConditionForFieldType('contains', 'radio');

    /**
     * Code Editor
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_code_editor');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_code_editor');
    acf.registerConditionForFieldType('patternMatch', 'acfe_code_editor');
    acf.registerConditionForFieldType('contains', 'acfe_code_editor');
    acf.registerConditionForFieldType('hasValue', 'acfe_code_editor');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_code_editor');

    /**
     * Date Picker
     */
    acf.registerConditionForFieldType('equalTo', 'date_picker');
    acf.registerConditionForFieldType('notEqualTo', 'date_picker');
    acf.registerConditionForFieldType('patternMatch', 'date_picker');
    acf.registerConditionForFieldType('contains', 'date_picker');
    acf.registerConditionForFieldType('greaterThan', 'date_picker');
    acf.registerConditionForFieldType('lessThan', 'date_picker');

    /**
     * Date Time Picker
     */
    acf.registerConditionForFieldType('equalTo', 'date_time_picker');
    acf.registerConditionForFieldType('notEqualTo', 'date_time_picker');
    acf.registerConditionForFieldType('patternMatch', 'date_time_picker');
    acf.registerConditionForFieldType('contains', 'date_time_picker');

    /**
     * Forms
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_forms');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_forms');
    acf.registerConditionForFieldType('patternMatch', 'acfe_forms');
    acf.registerConditionForFieldType('contains', 'acfe_forms');
    acf.registerConditionForFieldType('hasValue', 'acfe_forms');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_forms');

    /**
     * Hidden
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_hidden');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_hidden');
    acf.registerConditionForFieldType('patternMatch', 'acfe_hidden');
    acf.registerConditionForFieldType('contains', 'acfe_hidden');
    acf.registerConditionForFieldType('hasValue', 'acfe_hidden');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_hidden');

    /**
     * Post Status
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_post_statuses');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_post_statuses');
    acf.registerConditionForFieldType('patternMatch', 'acfe_post_statuses');
    acf.registerConditionForFieldType('contains', 'acfe_post_statuses');
    acf.registerConditionForFieldType('hasValue', 'acfe_post_statuses');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_post_statuses');

    /**
     * Post Types
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_post_types');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_post_types');
    acf.registerConditionForFieldType('patternMatch', 'acfe_post_types');
    acf.registerConditionForFieldType('contains', 'acfe_post_types');
    acf.registerConditionForFieldType('hasValue', 'acfe_post_types');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_post_types');

    /**
     * Slug
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_slug');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_slug');
    acf.registerConditionForFieldType('patternMatch', 'acfe_slug');
    acf.registerConditionForFieldType('contains', 'acfe_slug');
    acf.registerConditionForFieldType('hasValue', 'acfe_slug');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_slug');

    /**
     * Taxonomies
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_taxonomies');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_taxonomies');
    acf.registerConditionForFieldType('patternMatch', 'acfe_taxonomies');
    acf.registerConditionForFieldType('contains', 'acfe_taxonomies');
    acf.registerConditionForFieldType('hasValue', 'acfe_taxonomies');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_taxonomies');

    /**
     * Taxonomy
     */
    acf.registerConditionForFieldType('equalTo', 'taxonomy');
    acf.registerConditionForFieldType('notEqualTo', 'taxonomy');
    acf.registerConditionForFieldType('patternMatch', 'taxonomy');
    acf.registerConditionForFieldType('contains', 'taxonomy');
    acf.registerConditionForFieldType('hasValue', 'taxonomy');
    acf.registerConditionForFieldType('hasNoValue', 'taxonomy');

    /**
     * Taxonomy Terms
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('patternMatch', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('contains', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('hasValue', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_taxonomy_terms');

    /**
     * Time Picker
     */
    acf.registerConditionForFieldType('equalTo', 'time_picker');
    acf.registerConditionForFieldType('notEqualTo', 'time_picker');
    acf.registerConditionForFieldType('patternMatch', 'time_picker');
    acf.registerConditionForFieldType('contains', 'time_picker');

    /**
     * User Roles
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_user_roles');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_user_roles');
    acf.registerConditionForFieldType('patternMatch', 'acfe_user_roles');
    acf.registerConditionForFieldType('contains', 'acfe_user_roles');
    acf.registerConditionForFieldType('hasValue', 'acfe_user_roles');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_user_roles');

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    var storage = [];

    acfe.registerEventForFieldType = function(fieldType, events, callback) {

        // force events to array
        if (typeof events === 'string') {
            events = [events];
        }

        // add to storage
        storage.push({
            fieldType: fieldType,
            events: events,
            callback: callback || false
        })

    };

    acfe.getEvents = function(args) {

        // defaults
        args = acf.parseArgs(args, {
            fieldType: '',
        });

        var items = [];

        // loop
        storage.map(function(item) {

            // check args
            if (args.fieldType && item.fieldType.indexOf(args.fieldType) === -1) {
                return;
            }

            // push
            items.push(item);

        });

        // return
        return items;

    };

    var FieldEvent = new acf.Model({

        actions: {
            'new_field': 'newField'
        },

        priority: 20,

        data: {},

        parseEvent: function(event) {
            return event.match(/^(\S+)\s*(.*)$/);
        },

        newField: function(field) {

            // set previous val
            this.set(field.cid, field.val());

            // get items
            var items = acfe.getEvents({
                fieldType: field.get('type')
            });

            // loop items
            items.map(function(item) {

                // loop events
                item.events.map(function(event) {

                    // match event "change input"
                    var match = this.parseEvent(event);

                    // add event listener
                    field.on(match[1], match[2], this.proxy(function(e) {

                        var val = field.val();
                        var prevVal = this.get(field.cid);
                        var $el = $(e.currentTarget);

                        var callback = item.callback || this.proxy(function(val, prevVal, field, e, $el) {

                            // vars
                            var _val = val;
                            var _prevVal = prevVal;

                            // compare object/array values
                            if (typeof _val === 'object') {
                                _val = JSON.stringify(_val);
                            }

                            if (typeof _prevVal === 'object') {
                                _prevVal = JSON.stringify(_prevVal);
                            }

                            // avoid multiple trigger for the same value
                            if (_prevVal !== _val) {

                                this.set(field.cid, val);

                                // actions
                                acf.doAction('acfe/change_field', val, prevVal, field, e, $el);
                                acf.doAction('acfe/change_field/type=' + field.get('type'), val, prevVal, field, e, $el);
                                acf.doAction('acfe/change_field/name=' + field.get('name'), val, prevVal, field, e, $el);
                                acf.doAction('acfe/change_field/key=' + field.get('key'), val, prevVal, field, e, $el);

                            }


                        });

                        callback(val, prevVal, field, e, $el);

                    }));

                }, this);

            }, this);

        }

    });

    // ACF
    acfe.registerEventForFieldType('button_group', 'change');
    acfe.registerEventForFieldType('checkbox', 'change');
    acfe.registerEventForFieldType('color_picker', 'change');
    acfe.registerEventForFieldType('date_picker', 'change');
    acfe.registerEventForFieldType('date_time_picker', 'change');
    acfe.registerEventForFieldType('email', ['input', 'change']);
    acfe.registerEventForFieldType('file', 'change');
    acfe.registerEventForFieldType('flexible_content', 'change');
    acfe.registerEventForFieldType('gallery', 'change');
    acfe.registerEventForFieldType('google_map', 'change');
    acfe.registerEventForFieldType('image', 'change');
    acfe.registerEventForFieldType('link', 'change');
    acfe.registerEventForFieldType('number', ['input', 'change']);
    acfe.registerEventForFieldType('oembed', 'change');
    acfe.registerEventForFieldType('page_link', 'change');
    acfe.registerEventForFieldType('post_object', 'change');
    acfe.registerEventForFieldType('relationship', 'change');
    acfe.registerEventForFieldType('password', ['input', 'change']);
    acfe.registerEventForFieldType('radio', 'change');
    acfe.registerEventForFieldType('range', ['input', 'change']);
    acfe.registerEventForFieldType('repeater', 'change');
    acfe.registerEventForFieldType('select', 'change');
    acfe.registerEventForFieldType('taxonomy', 'change');
    acfe.registerEventForFieldType('text', ['input', 'change']);
    acfe.registerEventForFieldType('textarea', ['input', 'change']);
    acfe.registerEventForFieldType('time_picker', 'change');
    acfe.registerEventForFieldType('true_false', 'change');
    acfe.registerEventForFieldType('url', ['input', 'change']);
    acfe.registerEventForFieldType('user', 'change');
    acfe.registerEventForFieldType('wysiwyg', 'change');

    // ACFE
    acfe.registerEventForFieldType('acfe_advanced_link', 'change');
    acfe.registerEventForFieldType('acfe_block_types', 'change');
    acfe.registerEventForFieldType('acfe_countries', 'change');
    acfe.registerEventForFieldType('acfe_currencies', 'change');
    acfe.registerEventForFieldType('acfe_code_editor', 'change');
    acfe.registerEventForFieldType('acfe_date_range_picker', 'change');
    acfe.registerEventForFieldType('acfe_field_groups', 'change');
    acfe.registerEventForFieldType('acfe_field_types', 'change');
    acfe.registerEventForFieldType('acfe_fields', 'change');
    acfe.registerEventForFieldType('acfe_forms', 'change');
    acfe.registerEventForFieldType('acfe_hidden', 'change');
    acfe.registerEventForFieldType('acfe_image_selector', 'change');
    acfe.registerEventForFieldType('acfe_image_sizes', 'change');
    acfe.registerEventForFieldType('acfe_languages', 'change');
    acfe.registerEventForFieldType('acfe_menu_locations', 'change');
    acfe.registerEventForFieldType('acfe_options_pages', 'change');
    acfe.registerEventForFieldType('acfe_payment', 'change');
    acfe.registerEventForFieldType('acfe_payment_cart', 'change');
    acfe.registerEventForFieldType('acfe_payment_selector', 'change');
    acfe.registerEventForFieldType('acfe_phone_number', 'change');
    acfe.registerEventForFieldType('acfe_post_formats', 'change');
    acfe.registerEventForFieldType('acfe_post_statuses', 'change');
    acfe.registerEventForFieldType('acfe_post_types', 'change');
    acfe.registerEventForFieldType('acfe_recaptcha', 'change');
    acfe.registerEventForFieldType('acfe_taxonomies', 'change');
    acfe.registerEventForFieldType('acfe_taxonomy_terms', 'change');
    acfe.registerEventForFieldType('acfe_templates', 'change');
    acfe.registerEventForFieldType('acfe_user_roles', 'change');
    acfe.registerEventForFieldType('acfe_slug', ['input', 'change']);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.FieldExtender
     *
     * @param protoProps
     * @returns {*}
     * @constructor
     */
    var storage = [];

    acfe.FieldExtender = function(protoProps) {

        // vars
        var id = acfe.extractVar(protoProps, 'id', acf.uniqueId('extender'));

        // validate
        protoProps.type = acfe.getArray(protoProps.type);
        protoProps.dependencies = acfe.getArray(protoProps.dependencies);
        protoProps.extender = id;

        // push to storage
        storage.push(protoProps);

        // return extender
        return id;

    }

    /**
     * acf.Field.setup
     *
     * @type {acf.Field.setup}
     */
    var setup = acf.Field.prototype.setup;

    acf.Field.prototype.setup = function(props) {

        // parent setup
        setup.apply(this, arguments);

        var extenders = getFieldExtenders(this);

        if (!extenders.length) {
            return;
        }

        // vars
        var prototype = Object.getPrototypeOf(this);
        this.extenders = [];

        // loop extenders
        for (var model of extenders) {

            // append extender
            this.extenders.push(model.extender);

            // clone model
            var protoProps = $.extend(true, {}, model);
            var events = acfe.extractVar(protoProps, 'events');

            // cleanup
            acfe.extractVars(protoProps, 'type', 'condition', 'dependencies');

            // apply setup method if any
            if (protoProps.hasOwnProperty('setup')) {
                protoProps.setup.apply(this, arguments);
            }

            // generate child
            var Child = function() {};

            // create proto
            Child.prototype = Object.create(prototype);

            // extend
            $.extend(Child.prototype, protoProps);

            // assign events
            if (events) {
                Child.prototype.events = $.extend(true, {}, Child.prototype.events, events);
            }

            // assign parent
            Child.prototype.__parent__ = prototype;

            // assign prototype for next loop
            prototype = Child.prototype;

        }

        // getParent function
        this.getParent = function(extender) {

            var prototype = Object.getPrototypeOf(this);
            while (prototype) {

                if (prototype.extender === extender) {
                    return prototype.__parent__;
                }

                if (!prototype.__parent__) {
                    return prototype;
                }

                prototype = prototype.__parent__;

            }

            return prototype;

        }

        // assign prototype
        Object.setPrototypeOf(this, prototype);

    }


    /**
     * getFieldExtenders
     *
     * @param field
     * @returns {*[]}
     */
    var getFieldExtenders = function(field) {

        var extenders = [];

        for (var extender of getValidExtenders(field)) {
            extenders.push(getExtender(extender));
        }

        return extenders;

    };


    /**
     * getExtender
     *
     * @param extender
     * @returns {boolean|*}
     */
    var getExtender = function(extender) {

        for (var model of storage) {
            if (model.extender === extender) {
                return model;
            }
        }

        return false;

    };


    /**
     * getValidExtenders
     *
     * @param field
     * @returns {*|*[]}
     */
    var getValidExtenders = function(field) {

        var rules = {};

        for (var model of storage) {

            // validate type
            if (!acfe.inArray(field.get('type'), model.type)) {
                continue;
            }

            // validate condition
            if (model.hasOwnProperty('condition') && !model.condition.apply(field, arguments)) {
                continue;
            }

            // append rule
            rules[model.extender] = model.dependencies;

        }

        // return array
        return sortExtenders(rules);

    };


    /**
     * sortExtenders
     *
     * https://stackoverflow.com/a/54347328
     *
     * @param names
     * @param obj
     * @param start
     * @param depth
     * @returns {*|*[]}
     */
    var sortExtenders = function(names, obj = names, start = [], depth = 0) {

        if (typeof names === 'object' && !Array.isArray(names)) {
            names = Object.keys(names)
        }

        const processed = names.reduce(function(a, b, i) {

            if (obj[b].every(Array.prototype.includes, a)) {
                a.push(b)
            }

            return a;

        }, start);

        const nextNames = names.filter(function(n) {
            return !processed.includes(n)
        });

        const goAgain = nextNames.length && depth <= names.length;

        return goAgain ? sortExtenders(nextNames, obj, processed, depth + 1) : processed;

    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    new acf.Model({
        actions: {
            'new_field': 'newField'
        },
        priority: 1,
        validateField: function(field) {

            // check data correctly set
            if (!field.has('ftype')) {
                return false;
            }

            // check if prototype doesn't already have ftype (acf taxonomy field)
            return !acf.getFieldType(field.get('type')).prototype.get('ftype');

        },
        newField: function(field) {

            // validate
            if (!this.validateField(field)) {
                return;
            }

            // real type (checkbox, radio...)
            field.set('rtype', field.get('type'), true);

            // field type (acfe_post_types, acfe_post_formats...)
            field.set('type', field.get('ftype'), true);

            // assign attribute
            field.$el.attr('data-type', field.get('ftype'));

            // cleanup attribute
            field.$el.removeAttr('data-ftype');

            // cleanup data
            delete field.data['ftype'];

        }
    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Instructions
     */
    new acf.Model({

        field: false,
        placement: false,

        actions: {
            'new_field': 'newField',
        },

        newField: function(field) {

            this.field = field;

            if (field.has('instructionTooltip')) {
                this.setTooltip();
            }

            if (field.has('instructionAboveField')) {
                this.setAboveField();
            }

            if (field.has('instructionPlacement')) {
                this.overridePlacement(field.get('instructionPlacement'));
            }

        },

        setTooltip: function() {

            var icon = acfe.versionCompare(acf.get('wp_version'), '>=', '5.5') ? 'dashicons-info-outline' : 'dashicons-info';

            this.field.$labelWrap().prepend('<span class="acfe-field-tooltip acf-js-tooltip dashicons ' + icon + '" title="' + acf.escHtml(this.field.get('instructionTooltip')) + '"></span>');
            this.field.$labelWrap().find('.description').remove();

        },

        setAboveField: function() {

            this.field.$inputWrap().prepend('<p class="description">' + this.field.get('instructionAboveField') + '</p>');
            this.field.$labelWrap().find('.description').remove();

        },

        overridePlacement: function(target) {

            var current = this.getPlacement();

            // No instruction
            if (!current)
                return;

            // Placement is correct
            if (current === target)
                return;

            this.setPlacement(target);

        },

        getPlacement: function() {

            var placement = false;

            if (this.field.$labelWrap().find('>.description').length)
                placement = 'label';

            else if (this.field.$inputWrap().find('>.description:first-child').length)
                placement = 'above_field';

            else if (this.field.$inputWrap().find('>.description:last-child').length)
                placement = 'field';

            else if (this.field.$labelWrap().find('>.acfe-field-tooltip').length)
                placement = 'tooltip';

            this.placement = placement;

            return this.placement;

        },

        $getInstruction: function() {

            var placement = this.getPlacement();

            if (placement === 'label') {

                return this.field.$labelWrap().find('>.description');

            } else if (placement === 'above_field') {

                return this.field.$inputWrap().find('>.description:first-child');

            } else if (placement === 'field') {

                return this.field.$inputWrap().find('>.description:last-child');

            } else if (placement === 'tooltip') {

                return this.field.$labelWrap().find('>.acfe-field-tooltip');

            }

            return false;

        },

        setPlacement: function(target) {

            var $instruction = this.$getInstruction();

            if (this.placement === 'tooltip') {

                var text = $instruction.attr('title');

                $instruction.remove();
                $instruction = $('<p class="description">' + text + '</p>');

            }

            if (target === 'label') {

                this.field.$labelWrap().append($instruction);

            } else if (target === 'above_field') {

                this.field.$inputWrap().prepend($instruction);

            } else if (target === 'field') {

                this.field.$inputWrap().append($instruction);

            } else if (target === 'tooltip') {

                var icon = acfe.versionCompare(acf.get('wp_version'), '>=', '5.5') ? 'dashicons-info-outline' : 'dashicons-info';

                this.field.$labelWrap().prepend($('<span class="acfe-field-tooltip acf-js-tooltip dashicons ' + icon + '" title="' + acf.escHtml($instruction.html()) + '"></span>'));
                $instruction.remove();

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Labels
     */
    new acf.Model({

        actions: {
            'new_field': 'newField',
        },

        getFieldType: function(field) {
            return field.get('rtype', field.get('type'));
        },

        validateField: function(field) {

            // check setting
            if (!field.has('acfeLabels')) {
                return false;
            }

            // check type & real type
            return this.getFieldType(field) === 'checkbox' || this.getFieldType(field) === 'radio';

        },

        newField: function(field) {

            // bail early
            if (!this.validateField(field)) {
                return;
            }

            // vars
            var label, item;
            var labels = field.get('acfeLabels');

            switch (this.getFieldType(field)) {

                case 'checkbox': {

                    // loop
                    for (label in labels) {
                        item = labels[label];
                        field.$control().find('input[type=checkbox][value="' + item + '"]').closest('ul').before('<strong>' + label + '</strong>');
                    }

                    break;

                }

                case 'radio': {

                    // loop
                    for (label in labels) {
                        item = labels[label];
                        field.$control().find('input[type=radio][value="' + item + '"]').closest('li').addClass('parent').prepend('<strong>' + label + '</strong>');
                    }

                    // horizontal rule
                    if (field.$control().hasClass('acf-hl')) {

                        field.$control().find('li.parent').each(function() {
                            $(this).nextUntil('li.parent').addBack().wrapAll('<li><ul></ul></li>');
                        });

                    }

                    break;

                }

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    acf.Field.prototype.getModal = function(args) {

        var $modal = acfe.findModal('', this.$inputWrap());

        if (!$modal.length) {
            return false;
        }

        return acfe.getModal($modal, args);

    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Select Hooks
     */
    new acf.Model({

        actions: {
            'select2_init': 'init',
        },

        filters: {
            'select2_args': 'args',
            'select2_ajax_data': 'ajaxData',
        },

        init: function($select, options, data, field, instance) {

            // bail early
            if (!field) {
                return;
            }

            // actions
            acf.doAction('select2_init/type=' + field.get('type'), $select, options, data, field, instance);
            acf.doAction('select2_init/name=' + field.get('name'), $select, options, data, field, instance);
            acf.doAction('select2_init/key=' + field.get('key'), $select, options, data, field, instance);

        },

        args: function(options, $select, data, field, instance) {

            // bail early
            if (!field) {
                return options;
            }

            // filters
            options = acf.applyFilters('select2_args/type=' + field.get('type'), options, $select, data, field, instance);
            options = acf.applyFilters('select2_args/name=' + field.get('name'), options, $select, data, field, instance);
            options = acf.applyFilters('select2_args/key=' + field.get('key'), options, $select, data, field, instance);

            // only on pages without woocommerce
            if (!acf.isset(window, 'jQuery', 'fn', 'selectWoo')) {

                options.templateSelection = function(selection) {

                    var text = selection.text;

                    text = acf.applyFilters('select2_template_selection', text, selection, $select, data, field, instance);
                    text = acf.applyFilters('select2_template_selection/type=' + field.get('type'), text, selection, $select, data, field, instance);
                    text = acf.applyFilters('select2_template_selection/name=' + field.get('name'), text, selection, $select, data, field, instance);
                    text = acf.applyFilters('select2_template_selection/key=' + field.get('key'), text, selection, $select, data, field, instance);

                    var $selection = $('<span class="acf-selection"></span>');
                    $selection.html(acf.escHtml(text));
                    $selection.data('element', selection.element);

                    return $selection;

                };

                options.templateResult = function(selection) {

                    var text = selection.text;

                    text = acf.applyFilters('select2_template_result', text, selection, $select, data, field, instance);
                    text = acf.applyFilters('select2_template_result/type=' + field.get('type'), text, selection, $select, data, field, instance);
                    text = acf.applyFilters('select2_template_result/name=' + field.get('name'), text, selection, $select, data, field, instance);
                    text = acf.applyFilters('select2_template_result/key=' + field.get('key'), text, selection, $select, data, field, instance);

                    var $selection = $('<span class="acf-selection"></span>');
                    $selection.html(acf.escHtml(text));
                    $selection.data('element', selection.element);

                    return $selection;

                };

                // fix old ACF 5.9 version which doesn't escape markup
                if (acfe.versionCompare(acf.get('acf_version'), '<', '5.10')) {

                    options.escapeMarkup = function(markup) {
                        if (typeof markup !== 'string') {
                            return markup;
                        }
                        return acf.escHtml(markup);
                    }

                }

            }

            return options;

        },

        ajaxData: function(ajaxData, data, $el, field, instance) {

            // bail early
            if (!field) {
                return ajaxData;
            }

            // filters
            ajaxData = acf.applyFilters('select2_ajax_data/type=' + field.get('type'), ajaxData, data, $el, field, instance);
            ajaxData = acf.applyFilters('select2_ajax_data/name=' + field.get('name'), ajaxData, data, $el, field, instance);
            ajaxData = acf.applyFilters('select2_ajax_data/key=' + field.get('key'), ajaxData, data, $el, field, instance);

            if (ajaxData.action) {
                ajaxData = acf.applyFilters('select2_ajax_data/action=' + ajaxData.action, ajaxData, data, $el, field, instance);
            }

            return ajaxData;

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Advanced Link
     */
    var ACFE_Advanced_Link = acf.Field.extend({

        type: 'acfe_advanced_link',

        events: {
            'click a[data-name="add"]': 'onClickEdit',
            'click a[data-name="edit"]': 'onClickEdit',
            'click a[data-name="remove"]': 'onClickRemove',
        },

        $control: function() {
            return this.$('.acf-link');
        },

        getSubField: function(key) {

            return acf.getFields({
                key: key,
                parent: this.$el
            }).shift();

        },

        getSubFields: function() {

            return acf.getFields({
                parent: this.$el
            });

        },

        getValue: function() {

            // return
            var data = {
                type: this.getSubField('type').val(),
                title: this.getSubField('title').val(),
                value: '',
                name: '',
                target: Boolean(this.getSubField('target').val())
            };

            // assign value
            data.value = this.getSubField(data.type).val();
            data.name = data.value;

            // post / term value
            if (data.type === 'post' || data.type === 'term') {
                data.name = this.getSubField(data.type).$input().find(':selected').text();
            }

            // return
            return data;

        },

        setValue: function(val) {

            // clear value
            if (!val) {
                return this.clearValue();
            }

            // allow val to be a string
            if (acfe.isString(val)) {

                val = {
                    type: 'url',
                    title: '',
                    value: val,
                    target: false
                };

            }

            val = acf.parseArgs(val, {
                type: 'url',
                value: '',
                title: '',
                target: false
            });

            // set sub fields
            this.getSubField('type').val(val.type);
            this.getSubField(val.type).val(val.value); // post / term value
            this.getSubField('title').val(val.title);
            this.getSubField('target').val(val.target);

            // render value
            this.renderValue();

        },

        clearValue: function() {

            // clear subfields values
            this.getSubFields().map(function(field) {
                field.val('');
            });

        },

        renderValue: function() {

            // vars
            var val = this.val();
            var $control = this.$control();

            // remove class
            $control.removeClass('-value -external');

            // add class
            if (val.value || val.title) {
                $control.addClass('-value');
            }

            // target
            if (val.target) {
                $control.addClass('-external');
            }

            // update text
            var url = val.type === 'url' ? val.value : '#';
            this.$('.link-title').html(val.title);
            this.$('.link-url').attr('href', url).html(val.name);

        },

        onClickEdit: function(e, $el) {

            // vars
            this.getModal({
                open: true,
                onClose: this.proxy(function() {
                    this.renderValue();
                })
            });

        },

        onClickRemove: function(e, $el) {

            this.clearValue();
            this.renderValue();

        },

    });

    acf.registerFieldType(ACFE_Advanced_Link);


    /**
     * Field: Advanced Link Ajax Manager
     */
    new acf.Model({

        filters: {
            'select2_ajax_data/action=acfe/fields/advanced_link/post_query': 'ajaxData',
        },

        ajaxData: function(ajaxData, data, $el, field, select) {

            // get advanced link field
            var parentField = field.parent();

            // assign parent field key
            if (parentField) {
                ajaxData.field_key = parentField.get('key');
            }

            return ajaxData;

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Button
     */
    var ACFE_Button = acf.Field.extend({

        type: 'acfe_button',

        events: {
            'click input': 'onClick',
            'click button': 'onClick',
        },

        $input: function() {

            if (this.$('input').length) {

                return this.$('input');

            } else if (this.$('button').length) {

                return this.$('button');

            }

        },

        initialize: function() {

            // vars
            var $button = this.$input();

            // inherit data
            this.inherit($button);

        },

        onClick: function(e, $el) {

            if (!this.get('ajax')) return;

            e.preventDefault();

            // serialize form data
            var data = {
                action: 'acfe/fields/button',
                field_key: this.get('key'),
                acf: acf.serialize(this.$el.closest('form'), 'acf')
            };

            data = acf.applyFilters('acfe/fields/button/data', data, this.$el);
            data = acf.applyFilters('acfe/fields/button/data/name=' + this.get('name'), data, this.$el);
            data = acf.applyFilters('acfe/fields/button/data/key=' + this.get('key'), data, this.$el);

            // Deprecated
            acf.doAction('acfe/fields/button/before_ajax', this.$el, data);

            // Actions
            acf.doAction('acfe/fields/button/before', this.$el, data);
            acf.doAction('acfe/fields/button/before/name=' + this.get('name'), this.$el, data);
            acf.doAction('acfe/fields/button/before/key=' + this.get('key'), this.$el, data);

            // ajax
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax(data),
                type: 'post',
                dataType: 'json',
                context: this,

                // Success
                success: function(response) {

                    // Deprecated
                    acf.doAction('acfe/fields/button/ajax_success', response, this.$el, data);

                    // Actions
                    acf.doAction('acfe/fields/button/success', response, this.$el, data);
                    acf.doAction('acfe/fields/button/success/name=' + this.get('name'), response, this.$el, data);
                    acf.doAction('acfe/fields/button/success/key=' + this.get('key'), response, this.$el, data);

                },

                // Complete
                complete: function(xhr) {

                    var response = xhr.responseText;

                    // Actions
                    acf.doAction('acfe/fields/button/complete', response, this.$el, data);
                    acf.doAction('acfe/fields/button/complete/name=' + this.get('name'), response, this.$el, data);
                    acf.doAction('acfe/fields/button/complete/key=' + this.get('key'), response, this.$el, data);

                }


            });

        }

    });

    acf.registerFieldType(ACFE_Button);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Checkbox
     */
    var Checkbox = acf.models.CheckboxField;

    acf.models.CheckboxField = Checkbox.extend({

        // add setValue method
        // this allows to use field.val('new_value') to assign a new value to the field
        setValue: function(val) {

            // clear value
            if (!val) {
                return this.clearValue();
            }

            // force value as array
            var vals = acfe.getArray(val);

            // map values
            vals.map(function(val) {

                // get option with value
                var $option = this.$(':checkbox[value="' + val + '"]');

                // option exists and is not already selected
                if ($option.length && !$option.is(':checked')) {

                    // select the option
                    $option.prop('checked', true).trigger('change');

                }

            }, this);

        },

        // add clearValue method
        // this allows to set the value to the first radio or if allow_null is enabled, to null
        clearValue: function() {

            var $inputs = this.$inputs();
            var $labels = this.$('label');

            // update "checked" state.
            $inputs.prop('checked', false);

            // remove "selected" class.
            $labels.removeClass('selected');

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Clone
     */
    var Clone = acf.Field.extend({

        wait: false,

        type: 'clone',

        events: {
            'duplicateField': 'onDuplicate'
        },

        initialize: function() {

            if (this.has('acfeCloneModal')) {

                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a href="#" class="acf-button button" data-modal>' + this.get('acfeCloneModalButton') + '</a>');

                this.initializeModal();

            }

        },

        initializeModal: function() {

            // normal title
            var title = this.$labelWrap().find('label').text().trim();

            // inside table
            if (this.$el.is('td')) {

                title = this.get('acfeCloneModalButton');
                var $th = this.$el.closest('table').find(' > thead th[data-key="' + this.get('key') + '"]');

                if ($th.length) {
                    title = acfe.getTextNode($th);
                }

            }

            // fallback to button text
            if (!title.length) {
                title = this.get('acfeCloneModalButton');
            }

            // modal
            this.getModal({
                title: title,
                size: this.has('acfeCloneModalSize') ? this.get('acfeCloneModalSize') : 'large',
                footer: this.has('acfeCloneModalClose') ? acf.__('Close') : false,
                class: 'acfe-modal-edit-' + this.get('name') + ' acfe-modal-edit-' + this.get('key')
            });

        },

        onDuplicate: function(e, $el, $duplicate) {
            $duplicate.find('.acf-input:first > a[data-modal]').remove();
        }

    });

    acf.registerFieldType(Clone);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Code Editor
     */
    var CodeEditor = acf.Field.extend({

        wait: false,

        type: 'acfe_code_editor',

        editor: {},

        events: {
            'showField': 'onShow',
            'duplicateField': 'onDuplicate'
        },

        $control: function() {
            return this.$('> .acf-input > .acf-input-wrap');
        },

        $input: function() {
            return this.$control().find('> textarea');
        },

        initialize: function() {

            // bail early
            if (!acf.isset(wp, 'codeEditor')) {
                return;
            }

            // args
            var args = {
                lineNumbers: this.get('lines'),
                lineWrapping: true,
                styleActiveLine: false,
                continueComments: true,
                indentUnit: this.get('indentUnit'),
                tabSize: 1,
                indentWithTabs: false,
                autoRefresh: true, // needed for gutenberg metabox
                mode: this.get('mode'),
                extraKeys: {
                    'Tab': function(cm) {
                        cm.execCommand('indentMore')
                    },
                    'Shift-Tab': function(cm) {
                        cm.execCommand('indentLess')
                    },
                },
            }

            // filter args
            args = acf.applyFilters('acfe/fields/code_editor/args', args, this);
            args = acf.applyFilters('acfe/fields/code_editor/args/name=' + this.get('name'), args, this);
            args = acf.applyFilters('acfe/fields/code_editor/args/key=' + this.get('key'), args, this);

            // initialize wp editor
            this.editor = wp.codeEditor.initialize(this.$input().get(0), {
                codemirror: $.extend(wp.codeEditor.defaultSettings.codemirror, args)
            });

            if (this.get('rows')) {
                this.editor.codemirror.getScrollerElement().style.minHeight = this.get('rows') * 18.5 + 'px';
            }

            if (this.get('maxRows')) {
                this.editor.codemirror.getScrollerElement().style.maxHeight = this.get('maxRows') * 18.5 + 'px';
            }

            this.editor.codemirror.on('change', this.proxy(this.onEditorChange));

            acf.doAction('acfe/fields/code_editor/init', this.editor, this);
            acf.doAction('acfe/fields/code_editor/init/name=' + this.get('name'), this.editor, this);
            acf.doAction('acfe/fields/code_editor/init/key=' + this.get('key'), this.editor, this);

        },

        onEditorChange: function(e, $el) {

            this.editor.codemirror.save();
            this.$input().change();

        },

        onShow: function() {
            if (this.editor.codemirror) {
                this.editor.codemirror.refresh();
            }
        },

        onDuplicate: function(e, $el, $duplicate) {
            $duplicate.find('.CodeMirror:last').remove();
        },

    });

    acf.registerFieldType(CodeEditor);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Column
     */
    var Column = acf.Field.extend({

        wait: 'new_field',

        type: 'acfe_column',

        $control: function() {
            return this.$('.acf-fields:first');
        },

        initialize: function() {

            if (this.$el.is('td')) {

                this.$el.closest('.acf-table').find('th[data-type="acfe_column"]').remove();
                this.remove();

            }

            if (this.get('endpoint')) {

                this.$el.find('> .acf-label').remove();
                this.$el.find('> .acf-input').remove();

                return;

            }

            var $field = this.$el;
            var $label = this.$el.find('> .acf-label');
            var $input = this.$inputWrap();
            var $wrap = this.$control();

            $label.remove();

            var $parent = $field.parent();
            $parent.addClass('acfe-column-wrapper');
            $wrap.addClass($parent.hasClass('-left') ? '-left' : '');
            $wrap.addClass($parent.hasClass('-clear') ? '-clear' : '');

            $wrap.append($field.nextUntil('.acf-field-acfe-column', '.acf-field'));

        }

    });

    acf.registerFieldType(Column);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Overwrite
     */
    var FlexibleContent = acf.models.FlexibleContentField;

    acf.models.FlexibleContentField = FlexibleContent.extend({

        addSortable: function(self) {

            // bail early if max 1 row
            if (this.get('max') == 1) {
                return;
            }

            // add sortable
            this.$layoutsWrap().sortable({
                items: ' > .layout',
                handle: '> .acf-fc-layout-handle',
                forceHelperSize: false, // changed to false
                forcePlaceholderSize: true,
                revert: 50,
                tolerance: 'pointer', // changed to pointer
                scroll: true,
                stop: function(event, ui) {
                    self.render();
                },
                update: function(event, ui) {
                    self.$input().trigger('change');
                }
            });

        },


        add: function(args) {

            // get element
            var $el = FlexibleContent.prototype.add.apply(this, arguments);

            if ($el.length) {

                // used in append
                $el.data('added', true);
            }

        },

    });

    /**
     * Flexible Content: Validation
     */
    new acf.Model({

        actions: {
            'invalid_field': 'onInvalidField',
            'valid_field': 'onValidField',
        },

        onInvalidField: function(field) {

            field.$el.parents('.layout').addClass('acfe-flexible-modal-edit-error');

        },

        onValidField: function(field) {

            field.$el.parents('.layout').each(function() {

                var $layout = $(this);

                if (!$layout.find('.acf-error').length) {
                    $layout.removeClass('acfe-flexible-modal-edit-error');
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
     * Flexible Content: Append
     */
    var Field = acfe.FieldExtender({

        id: 'fc_append',

        type: 'flexible_content',

        initialize: function() {

            // initialize
            this.getParent(Field).initialize.apply(this, arguments);

            // add events
            this.addEvents({
                'appendLayout': 'acfeAppendLayout',
            });

        },

        acfeAppendLayout: function(e, $el, $layout) {

            // only native acf duplicate
            // .acfe-layout-duplicated is old clone & copy/paste
            if (!$layout.is('.acfe-layout-duplicated')) {

                if (this.has('acfeFlexibleModalEdition')) {
                    this.acfeModalEdit(null, $layout);
                } else {
                    this.openLayout($layout);
                }

            }

            // check if inside modal
            var modal = acfe.getModal($layout.closest('.acfe-modal.-open'));

            // already inside another modal
            if (modal) {

                this.acfeScrollToLayout($layout, modal.$content());

                // old clone + copy/paste
            } else if ($layout.is('.acfe-layout-duplicated')) {

                this.acfeScrollToLayout($layout);

                // added or duplicated
            } else {

                // we must set timeout to let data() being appended
                this.setTimeout(function() {
                    if ($layout.data('added')) {
                        this.acfeScrollToLayout($layout);
                    }
                }, 10);


            }

        },

        acfeScrollToLayout: function($layout, $parent) {

            // vars
            var hasParent = $parent || false;
            $parent = $parent || $('body, html');

            // emulate acf.focusAttention
            if (!acf.isInView($layout)) {

                var scrollTop = hasParent ? $layout.position().top : $layout.offset().top - $(window).height() / 2;

                $parent.animate({
                    scrollTop: scrollTop
                }, 500);

            }

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: One Click
     */
    var Field = acfe.FieldExtender({

        id: 'fc_async',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexibleAjax');
        },

        add: function(args) {

            // defaults
            args = acf.parseArgs(args, {
                layout: '',
                before: false
            });

            // validate
            if (!this.allowAdd()) {
                return false;
            }

            // ajax data
            var ajaxData = {
                action: 'acfe/flexible/models',
                field_key: this.get('key'),
                layout: args.layout,
            };

            // beforeSend callback
            var beforeSend = function() {
                $('body').addClass('-loading');
            }

            // complete callback
            var complete = function() {
                $('body').removeClass('-loading');
            }

            // success callback
            var success = this.proxy(function(html) {

                if (html) {

                    var $layout = $(html);
                    var uniqid = acf.uniqid();

                    var search = 'acf[' + this.get('key') + '][acfcloneindex]';
                    var replace = this.$control().find('> input[type=hidden]').attr('name') + '[' + uniqid + ']';

                    // add row
                    var $el = acf.duplicate({
                        target: $layout,
                        search: search,
                        replace: replace,
                        append: this.proxy(function($el, $el2) {

                            // append
                            if (args.before) {
                                args.before.before($el2);
                            } else {
                                this.$layoutsWrap().append($el2);
                            }

                            // enable
                            acf.enable($el2, this.cid);

                            // render
                            this.render();
                        })
                    });

                    // fix data-id
                    $el.attr('data-id', uniqid);

                    // trigger change for validation errors
                    this.$input().trigger('change');

                    // return
                    return $el;

                }

            });

            // ajax
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax(ajaxData),
                dataType: 'html',
                type: 'post',
                beforeSend: beforeSend,
                success: success,
                complete: complete
            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;

    // Layout: Clone
    model.events['click [data-acfe-flexible-control-clone]'] = 'acfeCloneLayout';
    model.acfeCloneLayout = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout');
        var layout_name = $layout.data('layout');

        // Popup min/max
        var $popup = $(flexible.$popup().html());
        var $layouts = flexible.$layouts();

        var countLayouts = function(name) {
            return $layouts.filter(function() {
                return $(this).data('layout') === name;
            }).length;
        };

        // vars
        var $a = $popup.find('[data-layout="' + layout_name + '"]');
        var min = $a.data('min') || 0;
        var max = $a.data('max') || 0;
        var count = countLayouts(layout_name);

        // max
        if (max && count >= max) {

            $el.addClass('disabled');
            return false;

        } else {

            $el.removeClass('disabled');

        }

        // Fix inputs
        flexible.acfeFixInputs($layout);

        var $_layout = $layout.clone();

        // Clean Layout
        flexible.acfeCleanLayouts($_layout);

        var parent = $el.closest('.acf-flexible-content').find('> input[type=hidden]').attr('name');

        // Clone
        var $layout_added = flexible.acfeDuplicate({
            layout: $_layout,
            before: $layout,
            parent: parent
        });

    }

    // Layout: Copy
    model.events['click [data-acfe-flexible-control-copy]'] = 'acfeCopyLayout';
    model.acfeCopyLayout = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout').clone();
        var source = flexible.$control().find('> input[type=hidden]').attr('name');

        // Fix inputs
        flexible.acfeFixInputs($layout);

        // Clean layout
        flexible.acfeCleanLayouts($layout);

        // Get layout data
        var data = JSON.stringify({
            source: source,
            layouts: $layout[0].outerHTML
        });

        // Append Temp Input
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:none;position:absolute;" value="" />').appendTo($('body'));
        $input.attr('value', data).select();

        // alert
        if (document.execCommand('copy')) {
            alert(acf.__('Layout data has been copied to your clipboard.') + "\n" + acf.__('You can now paste it in the same Flexible Content on another page, using the "Paste" button action.'));

            // prompt
        } else {
            prompt(acf.__('Please copy the following layout(s) data to your clipboard.') + "\n" + acf.__('You can now paste it in the same Flexible Content on another page, using the "Paste" button action.'), data);
        }

        // Remove the temp input
        $input.remove();

    }

    // Flexible: Copy Layouts
    model.acfeCopyLayouts = function() {

        // Get Flexible
        var flexible = this;

        // Get layouts
        var $layouts = flexible.$layoutsWrap().clone();
        var source = flexible.$control().find('> input[type=hidden]').attr('name');

        // Fix inputs
        flexible.acfeFixInputs($layouts);

        // Clean layout
        flexible.acfeCleanLayouts($layouts);

        // Get layouts data
        var data = JSON.stringify({
            source: source,
            layouts: $layouts.html()
        });

        // Append Temp Input
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:none;position:absolute;" value="" />').appendTo(flexible.$el);
        $input.attr('value', data).select();

        // alert
        if (document.execCommand('copy')) {
            alert(acf.__('Layouts data have been copied to your clipboard.') + "\n" + acf.__('You can now paste it in the same Flexible Content on another page, using the "Paste" button action.'));

            // prompt
        } else {
            prompt(acf.__('Please copy the following layout(s) data to your clipboard.') + "\n" + acf.__('You can now paste it in the same Flexible Content on another page, using the "Paste" button action.'), data);
        }


        $input.remove();

    }

    // Flexible: Paste Layouts
    model.acfePasteLayouts = function() {

        // Get Flexible
        var flexible = this;

        var paste = prompt(acf.__('Please paste previously copied layout data in the following field:'));

        // No input
        if (paste == null || paste === '')
            return;

        try {

            // Paste HTML
            var data = JSON.parse(paste);
            var source = data.source;
            var $html = $(data.layouts);

            // Parsed layouts
            var $html_layouts = $html.closest('[data-layout]');

            if (!$html_layouts.length)
                return alert('No layouts data available');

            // Popup min/max
            var $popup = $(flexible.$popup().html());
            var $layouts = flexible.$layouts();

            var countLayouts = function(name) {
                return $layouts.filter(function() {
                    return $(this).data('layout') === name;
                }).length;
            };

            // init
            var validated_layouts = [];

            // Each first level layouts
            $html_layouts.each(function() {

                var $this = $(this);
                var layout_name = $this.data('layout');

                // vars
                var $a = $popup.find('[data-layout="' + layout_name + '"]');
                var min = $a.data('min') || 0;
                var max = $a.data('max') || 0;
                var count = countLayouts(layout_name);

                // max
                if (max && count >= max)
                    return;

                // Validate layout against available layouts
                var get_clone_layout = flexible.$clone($this.attr('data-layout'));

                // Layout is invalid
                if (!get_clone_layout.length)
                    return;

                // Add validated layout
                validated_layouts.push($this);

            });

            // Nothing to add
            if (!validated_layouts.length)
                return alert('No layouts could be pasted');

            // Add layouts
            $.each(validated_layouts, function() {

                var $layout = $(this);
                var search = source + '[' + $layout.attr('data-id') + ']';
                var target = flexible.$control().find('> input[type=hidden]').attr('name');

                flexible.acfeDuplicate({
                    layout: $layout,
                    before: false,
                    search: search,
                    parent: target
                });

            });

        } catch (e) {

            console.log(e);
            alert('Invalid data');

        }

    }

    // Flexible: Dropdown
    model.events['click [data-name="acfe-flexible-control-button"]'] = 'acfeControl';
    model.acfeControl = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $dropdown = $el.next('.tmpl-acfe-flexible-control-popup').html();

        // Init Popup
        var Popup = acf.models.TooltipConfirm.extend({
            render: function() {
                this.html(this.get('text'));
                this.$el.addClass('acf-fc-popup');
            }
        });

        // New Popup
        var popup = new Popup({
            target: $el,
            targetConfirm: false,
            text: $dropdown,
            context: flexible,
            confirm: function(e, $el) {

                if ($el.attr('data-acfe-flexible-control-action') === 'paste')
                    flexible.acfePasteLayouts();

                else if ($el.attr('data-acfe-flexible-control-action') === 'copy')
                    flexible.acfeCopyLayouts();

            }
        });

        popup.on('click', 'a', 'onConfirm');

    }

    // Flexible: Duplicate
    model.acfeDuplicate = function(args) {

        // Arguments
        args = acf.parseArgs(args, {
            layout: '',
            before: false,
            parent: false,
            search: '',
            replace: '',
        });

        // Validate
        if (!this.allowAdd())
            return false;

        var uniqid = acf.uniqid();

        if (args.parent) {

            if (!args.search) {

                args.search = args.parent + '[' + args.layout.attr('data-id') + ']';

            }

            args.replace = args.parent + '[' + uniqid + ']';

        }

        var duplicate_args = {
            target: args.layout,
            search: args.search,
            replace: args.replace,
            append: this.proxy(function($el, $el2) {

                // Add class to duplicated layout
                $el2.addClass('acfe-layout-duplicated');

                // Reset UniqID
                $el2.attr('data-id', uniqid);

                // append before
                if (args.before) {

                    // Fix clone: Use after() instead of native before()
                    args.before.after($el2);

                }

                // append end
                else {

                    this.$layoutsWrap().append($el2);

                }

                // enable
                acf.enable($el2, this.cid);

                // render
                this.render();

            })
        }

        if (acfe.versionCompare(acf.get('acf_version'), '<', '5.9')) {

            // Add row
            var $el = acf.duplicate(duplicate_args);

            // Hotfix for ACF Pro 5.9
        } else {

            // Add row
            var $el = model.acfeNewAcfDuplicate(duplicate_args);

        }

        // trigger change for validation errors
        this.$input().trigger('change');

        // Fix tabs conditionally hidden
        var tabs = acf.getFields({
            type: 'tab',
            parent: $el,
        });

        if (tabs.length) {

            $.each(tabs, function() {

                if (this.$el.hasClass('acf-hidden')) {

                    this.tab.$el.addClass('acf-hidden');

                }

            });

        }


        // return
        return $el;

    }

    /**
     * Based on acf.duplicate (5.9)
     *
     * doAction('duplicate) has been commented out
     * This fix an issue with the WYSIWYG editor field during copy/paste since ACF 5.9
     */
    model.acfeNewAcfDuplicate = function(args) {

        // allow jQuery
        if (args instanceof jQuery) {
            args = {
                target: args
            };
        }

        // defaults
        args = acf.parseArgs(args, {
            target: false,
            search: '',
            replace: '',
            rename: true,
            before: function($el) {},
            after: function($el, $el2) {},
            append: function($el, $el2) {
                $el.after($el2);
            }
        });

        // compatibility
        args.target = args.target || args.$el;

        // vars
        var $el = args.target;

        // search
        args.search = args.search || $el.attr('data-id');
        args.replace = args.replace || acf.uniqid();

        // before
        // - allow acf to modify DOM
        // - fixes bug where select field option is not selected
        args.before($el);
        acf.doAction('before_duplicate', $el);

        // clone
        var $el2 = $el.clone();

        // rename
        if (args.rename) {
            acf.rename({
                target: $el2,
                search: args.search,
                replace: args.replace,
                replacer: (typeof args.rename === 'function' ? args.rename : null)
            });
        }

        // remove classes
        $el2.removeClass('acf-clone');
        $el2.find('.ui-sortable').removeClass('ui-sortable');

        // after
        // - allow acf to modify DOM
        args.after($el, $el2);
        acf.doAction('after_duplicate', $el, $el2);

        // append
        args.append($el, $el2);

        /**
         * Fires after an element has been duplicated and appended to the DOM.
         *
         * @date    30/10/19
         * @since   5.8.7
         *
         * @param   jQuery $el The original element.
         * @param   jQuery $el2 The duplicated element.
         */
        //acf.doAction('duplicate', $el, $el2 );

        // append
        acf.doAction('append', $el2);

        // return
        return $el2;
    };

    // Flexible: Fix Inputs
    model.acfeFixInputs = function($layout) {

        $layout.find('input').each(function() {

            $(this).attr('value', this.value);

        });

        $layout.find('textarea').each(function() {

            $(this).html(this.value);

        });

        $layout.find('input:radio,input:checkbox').each(function() {

            if (this.checked)
                $(this).attr('checked', 'checked');

            else
                $(this).attr('checked', false);

        });

        $layout.find('option').each(function() {

            if (this.selected)
                $(this).attr('selected', 'selected');

            else
                $(this).attr('selected', false);

        });

    }

    // Flexible: Clean Layout
    model.acfeCleanLayouts = function($layout) {

        // Clean WP Editor
        $layout.find('.acf-editor-wrap').each(function() {

            var $input = $(this);

            $input.find('.wp-editor-container div').remove();
            $input.find('.wp-editor-container textarea').css('display', '');

        });

        // Clean Date
        $layout.find('.acf-date-picker').each(function() {

            var $input = $(this);

            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');

        });

        // Clean Time
        $layout.find('.acf-time-picker').each(function() {

            var $input = $(this);

            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');

        });

        // Clean DateTime
        $layout.find('.acf-date-time-picker').each(function() {

            var $input = $(this);

            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');

        });

        // Clean Code Editor
        $layout.find('.acfe-field-code-editor').each(function() {

            var $input = $(this);

            $input.find('.CodeMirror').remove();

        });

        // Clean Color Picker
        $layout.find('.acf-color-picker').each(function() {

            var $input = $(this);

            var $color_picker = $input.find('> input');
            var $color_picker_proxy = $input.find('.wp-picker-container input.wp-color-picker').clone();

            $color_picker.after($color_picker_proxy);

            $input.find('.wp-picker-container').remove();

        });

        // Clean Post Object
        $layout.find('.acf-field-post-object').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();

        });

        // Clean Page Link
        $layout.find('.acf-field-page-link').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();

        });

        // Clean Select2
        $layout.find('.acf-field-select').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();

        });

        // Clean FontAwesome
        $layout.find('.acf-field-font-awesome').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden');

        });


        // Clean Tab
        $layout.find('.acf-tab-wrap').each(function() {

            var $wrap = $(this);

            var $content = $wrap.closest('.acf-fields');

            var tabs = [];
            $.each($wrap.find('li a'), function() {

                tabs.push($(this));

            });

            $content.find('> .acf-field-tab').each(function() {

                $current_tab = $(this);

                $.each(tabs, function() {

                    var $this = $(this);

                    if ($this.attr('data-key') !== $current_tab.attr('data-key'))
                        return;

                    $current_tab.find('> .acf-input').append($this);

                });

            });

            $wrap.remove();

        });

        // Clean Accordion
        $layout.find('.acf-field-accordion').each(function() {

            var $input = $(this);

            $input.find('> .acf-accordion-title > .acf-accordion-icon').remove();

            // Append virtual endpoint after each accordion
            $input.after('<div class="acf-field acf-field-accordion" data-type="accordion"><div class="acf-input"><div class="acf-fields" data-endpoint="1"></div></div></div>');

        });

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Events
     */
    var eventManager = new acf.Model({

        actions: {
            'new_field/type=flexible_content': 'newField',
            'show': 'onShow',
            'hide': 'onHide',
            'append': 'onAppend',
        },

        newField: function(field) {

            // placeholder
            field.addEvents({
                'click .acfe-fc-placeholder': 'onClickCollapse'
            });

            // inline close button
            field.addEvents({
                'click .acfe-flexible-opened-actions > a': 'onClickCollapse'
            });

            if (field.has('acfeFlexibleModalEdition') && (field.has('acfeFlexiblePlaceholder') || field.has('acfeFlexiblePreview'))) {

                // collapse action
                field.removeEvents({
                    'click [data-name="collapse-layout"]': 'onClickCollapse'
                });

                // placeholder collapse Action
                field.removeEvents({
                    'click .acfe-fc-placeholder': 'onClickCollapse'
                });

            }

            // lock
            if (field.has('acfeFlexibleLock')) {
                field.removeEvents({
                    'mouseover': 'onHover'
                });
            }

            field.$layouts().each(function() {
                field.trigger('newLayout', [$(this)]);
            });

        },

        onShow: function($el, context) {

            // validate
            if (context === 'collapse' && $el.is('.layout')) {

                // get field
                var field = acf.getClosestField($el);

                // trigger
                field.trigger('showLayout', [$el]);

            }

        },

        onHide: function($el, context) {

            // validate
            if (context === 'collapse' && $el.is('.layout') && !$el.is('.acf-clone')) {

                // get field
                var field = acf.getClosestField($el);

                // trigger
                field.trigger('hideLayout', [$el]);

            }

        },

        onAppend: function($el) {

            // acf block type "align" attribute pass a react element
            // with acf.doAction('append', this.state.$el))
            // which return a fake jQuery element and break $el.is('...') check
            if (acf.isset($el, 0, '$$typeof')) {
                return;
            }

            // validate
            if ($el.is('.layout')) {

                // get field
                var field = acf.getClosestField($el);

                // trigger
                field.trigger('newLayout', [$el]);
                field.trigger('appendLayout', [$el]);

            }

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Modal Edit
     */
    var Field = acfe.FieldExtender({

        id: 'fc_modal_edit',

        type: 'flexible_content',

        events: {
            'click [data-action="acfe-flexible-modal-edit"]': 'acfeModalEdit',
        },

        acfeModalEdit: function(e, $el) {

            // layout
            var $layout = $el.closest('.layout');

            // modal
            var $modal = $layout.find('> .acfe-modal.-fields');

            // vars
            var $handle = $layout.find('> .acf-fc-layout-handle');
            var order = $handle.find('> .acf-fc-layout-order').outerHTML();
            var title = acfe.getTextNode($handle.find('.acfe-layout-title'));

            var modal = acfe.getModal($modal, {
                open: true,
                title: order + title,
                onOpen: this.proxy(function() {
                    this.openLayout($layout);
                }),
                onClose: this.proxy(function() {
                    this.closeLayout($layout);
                }),
            });
        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Modal Select
     */
    var Field = acfe.FieldExtender({

        id: 'fc_modal_select',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexibleModal') && this.$clones().length > 1;
        },

        onClickAdd: function(e, $el) {

            // get flexible
            var flexible = this;

            // validate
            if (!flexible.validateAdd()) {
                return false;
            }

            // vars
            var layoutsCategories = this.getLayoutsCategories();

            // retrieve original layout (if user clicked on the '+' icon)
            var $before = $el.hasClass('acf-icon') ? $el.closest('.layout') : null;

            // Open Modal
            acfe.newModal({
                title: flexible.get('acfeFlexibleModalTitle', acf.__('Add Row')),
                class: 'acfe-modal-select-' + flexible.get('name') + ' acfe-modal-select-' + flexible.get('key'),
                size: flexible.get('acfeFlexibleModalSize'),
                destroy: true,

                events: {
                    'click .acfe-flexible-categories a': 'onClickCategory',
                    'click a[data-layout]': 'onClickLayout',
                },

                content: function() {
                    return '<div class="acfe-flex-container">' + flexible.getPopupHTML() + '</div>'
                },

                onOpen: function() {

                    // add categories tabs
                    if (layoutsCategories) {
                        this.$content().prepend(layoutsCategories);
                    }

                    // add columns class
                    if (flexible.has('acfeFlexibleModalCol')) {
                        this.$('.acfe-modal-content .acfe-flex-container').addClass('acfe-col-' + flexible.get('acfeFlexibleModalCol'));
                    }

                    // add thumbnails class
                    if (flexible.has('acfeFlexibleThumbnails')) {
                        this.$('.acfe-modal-content .acfe-flex-container').addClass('acfe-flex-thumbnails');
                    }

                    // add icon info class
                    var icon = acfe.versionCompare(acf.get('wp_version'), '>=', '5.5') ? 'dashicons-info-outline' : 'dashicons-info';
                    this.$('li a span.badge').addClass('acf-js-tooltip dashicons ' + icon);

                    // autofocus fix
                    this.$('li:first-of-type a').blur();

                },

                onClickCategory: function(e, $el) {

                    // prevent default
                    e.preventDefault();

                    // vars
                    var $activeCategory = $el;
                    var activeCategory = $activeCategory.data('acfe-flexible-category');

                    // remove all active
                    $activeCategory.closest('.acfe-flexible-categories').find('a').removeClass('nav-tab-active');

                    // set active on current
                    $activeCategory.addClass('nav-tab-active');

                    // show all layouts
                    this.$('a[data-layout] span[data-acfe-flexible-category]').closest('li').show();

                    // active category is 'show all'
                    if (activeCategory === 'acfe-all') {
                        return;
                    }

                    // loop through layouts to check category
                    this.$('a[data-layout] span[data-acfe-flexible-category]').each(function() {

                        // vars
                        var $span = $(this);
                        var categories = $span.data('acfe-flexible-category');

                        // hide layout
                        $span.closest('li').hide();

                        for (var category of categories) {
                            if (acfe.slugify(activeCategory) === acfe.slugify(category)) {

                                // show layout
                                $span.closest('li').show();
                                break;

                            }
                        }

                    });

                },

                onClickLayout: function(e, $el) {

                    e.preventDefault();

                    // close modal
                    this.close();

                    // Add layout
                    flexible.add({
                        layout: $el.data('layout'),
                        before: $before
                    });

                },
            });

        },

        getLayoutsCategories: function() {

            // get layouts selection html
            var $layouts = $(this.getPopupHTML());

            // categories vars
            var html = '';
            var data = [];

            // check if categories exist
            if ($layouts.find('li a span[data-acfe-flexible-category]').exists()) {

                // loop
                $layouts.find('li a span[data-acfe-flexible-category]').each(function() {

                    // get categories from data
                    var categories = $(this).data('acfe-flexible-category');

                    // loop categories
                    categories.map(function(label) {

                        // generate slug
                        var slug = acfe.slugify(label);

                        // search for slug
                        var search = data.filter(function(row) {
                            return row.slug === slug;
                        });

                        // slug not found, append
                        if (acfe.isEmpty(search)) {
                            data.push({
                                slug: slug,
                                label: label
                            });
                        }

                    });
                });

                // prepare html
                if (data.length) {

                    html += '<h2 class="acfe-flexible-categories nav-tab-wrapper">';
                    html += '<a href="#" data-acfe-flexible-category="acfe-all" class="nav-tab nav-tab-active"><span class="dashicons dashicons-menu"></span></a>';

                    data.sort(function(a, b) {
                        return a.slug > b.slug ? 1 : -1;
                    });

                    data.map(function(row) {
                        html += '<a href="#" data-acfe-flexible-category="' + row.label + '" class="nav-tab">' + row.label + '</a>';
                    });

                    html += '</h2>';

                }

            }

            return html;

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Modal Settings
     */
    var Field = acfe.FieldExtender({

        id: 'fc_modal_settings',

        type: 'flexible_content',

        events: {
            'click [data-acfe-flexible-settings]': 'acfeLayoutSettings',
        },

        acfeLayoutSettings: function(e, $el) {

            // layout
            var $layout = $el.closest('.layout');

            // modal
            var $modal = $layout.find('> .acfe-modal.-settings');

            // vars
            var $handle = $layout.find('> .acf-fc-layout-handle');
            var order = $handle.find('> .acf-fc-layout-order').outerHTML();
            var title = acfe.getTextNode($handle.find('.acfe-layout-title'));

            var modal = acfe.getModal($modal, {
                open: true,
                title: order + title,
                onClose: this.proxy(function() {
                    if (this.has('acfeFlexiblePreview')) {
                        this.closeLayout($layout);
                    }
                })
            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: One Click
     */
    var Field = acfe.FieldExtender({

        id: 'fc_one_click',

        type: 'flexible_content',

        condition: function() {
            return this.$clones().length === 1;
        },

        onClickAdd: function(e, $el) {

            // validate
            if (!this.validateAdd()) {
                return false;
            }

            // vars
            var $clones = this.$clones();
            var layout = $($clones[0]).data('layout');

            // within layout
            var $before = null;
            if ($el.hasClass('acf-icon')) {
                $before = $el.closest('.layout');
            }

            // add layout
            this.add({
                layout: layout,
                before: $before
            });

            // hide popup just in case
            var $popup = $('.acf-fc-popup');

            if ($popup.length) {
                $popup.hide();
            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Placeholder
     */
    var Field = acfe.FieldExtender({

        id: 'fc_placeholder',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexiblePlaceholder');
        },

        initialize: function() {

            // initialize
            this.getParent(Field).initialize.apply(this, arguments);

            // add events
            this.addEvents({
                'showLayout': 'acfePlaceholderShowLayout',
                'hideLayout': 'acfePlaceholderHideLayout',
                'newLayout': 'acfePlaceholderNewLayout',
            });

        },

        acfePlaceholderShowLayout: function(e, $el, $layout) {

            // hide placeholder
            if (!this.has('acfeFlexibleModalEdition')) {
                acf.hide($layout.find('> .acfe-fc-placeholder'));
            }

        },

        acfePlaceholderHideLayout: function(e, $el, $layout) {

            // show placeholder
            acf.show($layout.find('> .acfe-fc-placeholder'));

        },

        acfePlaceholderNewLayout: function(e, $el, $layout) {

            // show placeholder
            if (this.isLayoutClosed($layout)) {
                acf.show($layout.find('> .acfe-fc-placeholder'));
            }

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Preview
     */
    var Field = acfe.FieldExtender({

        id: 'fc_preview',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexiblePreview');
        },

        events: {
            'hideLayout': 'acfePreviewHideLayout',
            'appendLayout': 'acfePreviewAppendLayout',
        },

        acfeLayoutPreview: function($layout) {

            // validate
            if (!this.isLayoutClosed($layout) || $layout.find('> .acfe-fc-placeholder').hasClass('-loading')) {
                return;
            }

            // vars
            var key = this.get('key');
            var name = this.get('name');
            var $el = this.$el;
            var layout = $layout.data('layout');
            var index = $layout.index();
            var $placeholder = $layout.find('> .acfe-fc-placeholder');

            $placeholder.addClass('acfe-fc-preview -loading').find('> .acfe-flexible-placeholder').prepend('<span class="spinner"></span>');
            $placeholder.find('> .acfe-fc-overlay').addClass('-hover');

            // vars
            var $input = $layout.children('input');
            var prefix = $input.attr('name').replace('[acf_fc_layout]', '');

            // ajax data
            var ajaxData = {
                action: 'acfe/flexible/layout_preview',
                field_key: key,
                i: index,
                layout: layout,
                value: acf.serialize($layout, prefix)
            };

            acf.doAction('acfe/fields/flexible_content/before_preview', $el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/name=' + name, $el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/key=' + key, $el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/name=' + name + '&layout=' + layout, $el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/key=' + key + '&layout=' + layout, $el, $layout, ajaxData);

            // on success
            var onSuccess = function(response) {

                if (response) {
                    $placeholder.find('> .acfe-flexible-placeholder').html(response);
                } else {
                    $placeholder.removeClass('acfe-fc-preview');
                }

                acf.doAction('acfe/fields/flexible_content/preview', response, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/preview/name=' + name, response, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/preview/key=' + key, response, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/preview/name=' + name + '&layout=' + layout, response, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/preview/key=' + key + '&layout=' + layout, response, $el, $layout, ajaxData);

            };

            // on complete
            var onComplete = function() {

                $placeholder.find('> .acfe-fc-overlay').removeClass('-hover');
                $placeholder.removeClass('-loading').find('> .acfe-flexible-placeholder > .spinner').remove();

            };

            // ajax
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax(ajaxData),
                dataType: 'html',
                type: 'post',
                success: onSuccess,
                complete: onComplete
            });

        },

        acfePreviewHideLayout: function(e, $el, $layout) {

            this.acfeLayoutPreview($layout);

        },

        acfePreviewAppendLayout: function(e, $el, $layout) {

            this.acfeLayoutPreview($layout);

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: State
     */
    var Field = acfe.FieldExtender({

        id: 'fc_state',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexibleOpen');
        },

        addCollapsed: function() {
            // ...
        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Title Ajax
     */
    var Field = acfe.FieldExtender({

        id: 'fc_title_ajax',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexibleRemoveAjaxTitle');
        },

        renderLayout: function() {
            // ...
        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Title Inline
     */
    var Field = acfe.FieldExtender({

        id: 'fc_title_inline',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexibleTitleEdition');
        },

        events: {
            'click .acf-fc-layout-handle': 'acfeEditLayoutTitleToggleHandle',
            'click .acfe-layout-title-text': 'acfeEditLayoutTitle',
            'blur input.acfe-flexible-control-title': 'acfeEditLayoutTitleToggle',
            'click input.acfe-flexible-control-title': 'acfeEditLayoutTitlePropagation',
            'input [data-acfe-flexible-control-title-input]': 'acfeEditLayoutTitleInput',
            'keypress [data-acfe-flexible-control-title-input]': 'acfeEditLayoutTitleInputEnter',
        },

        acfeEditLayoutTitleToggleHandle: function(e, $el) {

            // layout
            var $layout = $el.closest('.layout');

            if ($layout.hasClass('acfe-flexible-title-edition')) {
                $layout.find('> .acf-fc-layout-handle > .acfe-layout-title > input.acfe-flexible-control-title').trigger('blur');
            }

        },

        acfeEditLayoutTitle: function(e, $el) {

            e.stopPropagation();
            this.acfeEditLayoutTitleToggle(e, $el);

        },

        acfeEditLayoutTitleToggle: function(e, $el) {

            // vars
            var $layout = $el.closest('.layout');
            var $handle = $layout.find('> .acf-fc-layout-handle');
            var $title = $handle.find('.acfe-layout-title');

            if ($layout.hasClass('acfe-flexible-title-edition')) {

                var $input = $title.find('> input[data-acfe-flexible-control-title-input]');

                if ($input.val() === '') {
                    $input.val($input.attr('placeholder')).trigger('input');
                }

                $layout.removeClass('acfe-flexible-title-edition');
                $input.insertAfter($handle);

            } else {

                var $input = $layout.find('> input[data-acfe-flexible-control-title-input]');
                var $input = $input.appendTo($title);

                $layout.addClass('acfe-flexible-title-edition');
                $input.focus().attr('size', $input.val().length);

            }

        },

        acfeEditLayoutTitlePropagation: function(e, $el) {
            e.stopPropagation();
        },

        acfeEditLayoutTitleInput: function(e, $el) {

            // vars
            var $layout = $el.closest('.layout');
            var $title = $layout.find('> .acf-fc-layout-handle .acfe-layout-title .acfe-layout-title-text');

            var val = $el.val();

            $el.attr('size', val.length);

            $title.html(val);

        },

        acfeEditLayoutTitleInputEnter: function(e, $el) {

            // 'enter'
            if (e.keyCode === 13) {
                e.preventDefault();
                $el.blur();
            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Flexible Content: Toggle
     */
    var Field = acfe.FieldExtender({

        id: 'fc_toggle',

        type: 'flexible_content',

        condition: function() {
            return this.has('acfeFlexibleToggle');
        },

        events: {
            'click [data-acfe-flexible-control-toggle]': 'acfeLayoutToggle',
        },

        acfeLayoutToggle: function(e, $el) {

            // vars
            var $layout = $el.closest('.layout');
            var $input = $layout.find('> .acfe-flexible-layout-toggle');

            if ($input.length) {

                if ($input.val() === '1') {

                    $layout.removeClass('acfe-flexible-layout-hidden');
                    $input.val('');

                } else {

                    $layout.addClass('acfe-flexible-layout-hidden');
                    $input.val('1');

                }

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Group
     */
    var Group = acf.Field.extend({

        wait: false,

        type: 'group',

        events: {
            'duplicateField': 'onDuplicate'
        },

        initialize: function() {

            if (this.has('acfeGroupModal')) {

                this.$inputWrap().find('> .acf-fields, > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$inputWrap().append('<a href="#" class="acf-button button" data-modal>' + this.get('acfeGroupModalButton') + '</a>');

                this.initializeModal();

            }

        },

        initializeModal: function() {

            // normal title
            var title = this.$labelWrap().find('label').text().trim();

            // inside table
            if (this.$el.is('td')) {

                title = this.get('acfeGroupModalButton');
                var $th = this.$el.closest('table').find(' > thead th[data-key="' + this.get('key') + '"]');

                if ($th.length) {
                    title = acfe.getTextNode($th);
                }

            }

            // fallback to button text
            if (!title.length) {
                title = this.get('acfeGroupModalButton');
            }

            // modal
            this.getModal({
                title: title,
                size: this.has('acfeGroupModalSize') ? this.get('acfeGroupModalSize') : 'large',
                footer: this.has('acfeGroupModalClose') ? acf.__('Close') : false,
                class: 'acfe-modal-edit-' + this.get('name') + ' acfe-modal-edit-' + this.get('key')
            });

        },

        onDuplicate: function(e, $el, $duplicate) {
            $duplicate.find('.acf-input:first > a[data-modal]').remove();
        }

    });

    acf.registerFieldType(Group);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Radio
     */
    var Radio = acf.models.RadioField;

    acf.models.RadioField = Radio.extend({

        // add setValue method
        // this allows to use field.val('new_value') to assign a new value to the field
        setValue: function(val) {

            // clear value
            if (!val) {
                return this.clearValue();
            }

            // get option with value
            var $option = this.$(':radio[value="' + val + '"]');

            // option exists and is not already selected
            if ($option.length && !$option.is(':checked')) {

                // select the option
                $option.prop('checked', true).trigger('change');
                this.onClick(null, $option);

            }

        },

        // add clearValue method
        // this allows to set the value to the first radio or if allow_null is enabled, to null
        clearValue: function() {

            // if allow_null is enabled, get selected option and click it
            if (this.get('allow_null')) {

                if (this.$input().length) {
                    this.onClick(null, this.$input());
                }

                // otherwise use first radio value as default value
            } else {
                this.val(this.$(':radio').first().val());
            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: reCaptcha
     */
    var reCaptcha = acf.Field.extend({

        type: 'acfe_recaptcha',

        wait: 'load',

        actions: {
            'validation_failure': 'validationFailure'
        },

        $control: function() {
            return this.$('.acfe-field-recaptcha');
        },

        $input: function() {
            return this.$('input[type="hidden"]');
        },

        $selector: function() {
            return this.$control().find('> div');
        },

        selector: function() {
            return this.$selector()[0];
        },

        initialize: function() {

            if (this.get('version') === 'v2') {

                this.renderV2(this);

            } else if (this.get('version') === 'v3') {

                this.renderV3();

            }

        },

        renderV2: function(self) {

            // selectors
            var selector = this.selector();
            var $input = this.$input();

            // vars
            var sitekey = this.get('siteKey');
            var theme = this.get('theme');
            var size = this.get('size');

            // request
            this.recaptcha = grecaptcha.render(selector, {
                'sitekey': sitekey,
                'theme': theme,
                'size': size,

                'callback': function(response) {
                    acf.val($input, response, true);
                    self.removeError();
                },

                'error-callback': function() {
                    acf.val($input, '', true);
                    self.showError('An error has occured');
                },

                'expired-callback': function() {
                    acf.val($input, '', true);
                    self.showError('reCaptcha has expired');
                }
            });

        },

        renderV3: function() {

            // vars
            var $input = this.$input();
            var sitekey = this.get('siteKey');

            // request
            grecaptcha.ready(function() {
                grecaptcha.execute(sitekey, {
                    action: 'homepage'
                }).then(function(response) {

                    acf.val($input, response, true);

                });
            });

        },

        validationFailure: function($form) {

            if (this.get('version') === 'v2') {
                grecaptcha.reset(this.recaptcha);
            }

        }

    });

    acf.registerFieldType(reCaptcha);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Init
     */
    var repeater = acf.getFieldType('repeater');
    var model = repeater.prototype;

    // Repeater: Lock Layouts
    model.acfeOnHover = function() {

        var repeater = this;

        // remove event
        repeater.off('mouseover');

    }

    /**
     * Spawn
     */
    acf.addAction('new_field/type=repeater', function(repeater) {

        // ACFE: Lock
        if (repeater.has('acfeRepeaterLock')) {

            repeater.removeEvents({
                'mouseover': 'onHover'
            });

            repeater.addEvents({
                'mouseover': 'acfeOnHover'
            });

        }

        // ACFE: Remove Actions
        if (repeater.has('acfeRepeaterRemoveActions')) {

            repeater.$actions().remove();

            repeater.$el.find('thead:first > tr > th.acf-row-handle:last').remove();
            repeater.$rows().find('> .acf-row-handle:last').remove();

            repeater.$control().find('> .acfe-repeater-stylised-button').remove();


        }

        // ACFE: Stylised button
        if (repeater.has('acfeRepeaterStylisedButton')) {

            repeater.$button().removeClass('button-primary');
            repeater.$actions().wrap('<div class="acfe-repeater-stylised-button" />');

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }


    /**
     * Field: Select
     */
    new acf.Model({

        actions: {
            'new_field/type=select': 'newSelect',
            'select2_init': 'init',
        },

        filters: {
            'select2_args': 'args',
        },

        newSelect: function(field) {

            // inherit properties
            field.inherit(field.$input());

            // remove "- -" characters from placeholder
            // acf already apply this to all select2
            if (!field.get('ui') && field.get('allow_null')) {

                field.$input().find('option').each(function(i, option) {

                    if (!option.value && option.text.startsWith('- ') && option.text.endsWith(' -')) {

                        option.text = option.text.substring(2); // remove starting "- "
                        option.text = option.text.substring(0, option.text.length - 2); // remove ending " -"

                    }

                });

            }

            // prepend / append
            if (field.has('acfePrepend') || field.has('acfeAppend')) {

                if (!field.$input().parent('.acf-input-wrap').length) {

                    field.$input().wrapAll('<div class="acf-input-wrap"></div>');

                    if (field.get('ui')) {
                        field.$('.acf-input-wrap:first').append(field.$('.select2'));
                    }

                    if (field.has('acfePrepend')) {
                        field.$('.acf-input-wrap:first').before('<div class="acf-input-prepend">' + field.get('acfePrepend') + '</div>');
                        field.$input().addClass('acf-is-prepended');
                    }

                    if (field.has('acfeAppend')) {
                        field.$('.acf-input-wrap:first').before('<div class="acf-input-append">' + field.get('acfeAppend') + '</div>');
                        field.$input().addClass('acf-is-appended');
                    }

                }

            }

        },

        init: function($select, options, data, field, instance) {

            // close dropdown on clear
            $select.on('select2:clear', function(e) {
                $(this).on('select2:opening.cancelOpen', function(e) {
                    e.preventDefault();
                    $(this).off("select2:opening.cancelOpen");
                });
            });

            // bail early
            if (!field) {
                return;
            }

            // add css class to dropdown with field name + key for developers <3
            if ($select.data('select2')) {

                $select.data('select2').$dropdown
                    .addClass('select2-dropdown-acf')
                    .addClass('select2-dropdown-acf-field-' + field.get('name'))
                    .addClass('select2-dropdown-acf-field-' + field.get('key'));

            }

            // search placeholder
            // only in single mode
            if (!field.get('multiple') && field.get('acfeSearchPlaceholder')) {

                $select.on('select2:open', function(e) {
                    $('.select2-search.select2-search--dropdown > .select2-search__field').attr('placeholder', field.get('acfeSearchPlaceholder'));
                });

            }

        },

        args: function(options, $select, data, field, instance) {

            // bail early
            if (!field) {
                return options;
            }

            // custom tags disallowed
            if (!field.get('acfeAllowCustom')) {
                return options;
            }

            // allow custom tags
            options.tags = true;

            // create tag
            options.createTag = function(params) {

                var term = $.trim(params.term);

                if (term === '') {
                    return null;
                }

                // vars
                var foundTerm;
                var ajaxResults = acf.isget(this, '_request', 'responseJSON', 'results');

                // ajax results
                if (ajaxResults) {

                    loop: for (var item of ajaxResults) {

                        if (item.children) {
                            for (var child of item.children) {

                                if (typeof child.id === 'string' && child.id.toLowerCase() === term.toLowerCase()) {
                                    foundTerm = true;
                                    break loop;
                                }

                            }
                        }

                    }

                    // normal results
                }
                else {

                    for (var option of this.$element.find('option')) {
                        if (option.value.toLowerCase() === term.toLowerCase()) {
                            foundTerm = true;
                            break;
                        }
                    }

                }

                // found term
                if (foundTerm) {
                    return null;
                }

                // create tag
                return {
                    id: term,
                    text: term
                };

            };

            // insert tag
            options.insertTag = function(results, tag) {

                var found;

                for (var result of results) {
                    if ($.trim(tag.text).toUpperCase() === $.trim(result.text).toUpperCase()) {
                        found = true;
                        break;
                    }
                }

                if (!found) {
                    results.unshift(tag);
                }

            };

            return options;

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Slug
     */
    var ACFE_Slug = acf.Field.extend({

        type: 'acfe_slug',

        events: {
            'input input': 'onInput',
            'focusout input': 'onFocusOut',
        },

        onInput: function(e, $el) {

            $el.val($el.val().toLowerCase()
                .replace(/\s+/g, '-') // Replace spaces with -
                .replace(/[^\w\-]+/g, '') // Remove all non-word chars
                .replace(/\-\-+/g, '-') // Replace multiple - with single -
                .replace(/\_\_+/g, '_') // Replace multiple _ with single _
                .replace(/^-+/, '')); // Trim - from start of text

        },

        onFocusOut: function(e, $el) {

            $el.val($el.val().toLowerCase()
                .replace(/-+$/, '') // Trim - from end of text
                .replace(/_+$/, '')); // Trim _ from end of text

        },

    });

    acf.registerFieldType(ACFE_Slug);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Tab
     */
    new acf.Model({

        actions: {
            'prepare_field/type=tab': 'prepareField',
        },

        prepareField: function(field) {

            if (!field.has('noPreference')) return;

            var $tabs = field.findTabs();
            var tabs = acf.getInstances($tabs);
            var key = field.get('key');

            if (tabs.length) {

                var preference = acf.getPreference('this.tabs');

                if (!preference) return;

                $.each(tabs, function(i, group) {

                    var groupIndex = group.get('index');

                    if (group.data.key !== key)
                        return;

                    preference[groupIndex] = 0;

                });

                // update
                acf.setPreference('this.tabs', preference);

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: Textarea
     */
    var Textarea = acf.Field.extend({

        type: 'textarea',

        events: {
            'keydown textarea': 'onInput',
        },

        onInput: function(e, $el) {

            //check for mode
            if (!this.has('acfeTextareaCode')) return;

            // check for tab input
            if (e.keyCode !== 9) return;

            e.preventDefault();

            var input = this.$el.find('textarea')[0];

            var s = input.selectionStart;

            this.$el.find('textarea').val(function(i, v) {
                return v.substring(0, s) + "    " + v.substring(input.selectionEnd)
            });

            input.selectionEnd = s + 4;

        },

    });

    acf.registerFieldType(Textarea);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: True/False
     */
    var TrueFalse = acf.models.TrueFalseField;

    acf.models.TrueFalseField = TrueFalse.extend({

        // add setValue method
        // this allows to use field.val('new_value') to assign a new value to the field
        setValue: function(val) {

            // clear value
            if (!val) {
                return this.clearValue();
            }

            this.switchOn();
            this.trigger('change'); // trigger change for conditional logic

        },

        // add clearValue method
        // this allows to set the value to the first radio or if allow_null is enabled, to null
        clearValue: function() {

            this.switchOff();
            this.trigger('change'); // trigger change for conditional logic

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Field: WYSIWYG Overwrite
     */
    var Wysiwyg = acf.models.WysiwygField;

    acf.models.WysiwygField = Wysiwyg.extend({

        initialize: function() {

            // initialize Editor if no delay and not already initialized
            if (!this.has('id') && !this.$control().hasClass('delay')) {
                this.initializeEditor();
            }

        }

    });

    /**
     * Field: WYSIWYG
     */
    new acf.Model({

        actions: {
            'show_field/type=wysiwyg': 'showField',
            'ready_field/type=wysiwyg': 'showField',
        },

        showField: function(field) {

            if (!field.has('acfeWysiwygAutoInit') || !field.$el.is(':visible') || field.has('id') || acfe.isFilterEnabled('acfeFlexibleOpen')) {
                return;
            }

            this.initializeEditor(field);

        },

        initializeEditor: function(field) {

            var $wrap = field.$control();

            if ($wrap.hasClass('delay')) {

                $wrap.removeClass('delay');
                $wrap.find('.acf-editor-toolbar').remove();

                // initialize
                field.initializeEditor();

            }

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
            if (!acfe.get('is_admin')) {
                new module();
            }
        }
    });

    var module = acf.Model.extend({

        actions: {
            'new_field/type=date_picker': 'datePicker',
            'new_field/type=date_time_picker': 'datePicker',
            'new_field/type=time_picker': 'datePicker',
            'new_field/type=google_map': 'googleMap',
            'invalid_field': 'invalidField',
            'validation_begin': 'validationBegin',
        },

        events: {
            'click .acf-form .button': 'onClickSubmit',
            'click .acf-form [type="submit"]': 'onClickSubmit',
            'click .acfe-form .button': 'onClickSubmit',
            'click .acfe-form [type="submit"]': 'onClickSubmit',
        },

        $getForm: function(field = false) {

            var $form = $('.acfe-form');

            if (field) {
                $form = field.$el.closest('.acfe-form');
            }

            return $form.length ? $form : false;

        },

        getFormFieldClass: function(field) {

            var $form = this.$getForm(field);

            if (!$form) {
                return false;
            }

            return $form.data('fields-class') || false;

        },

        initialize: function() {

            // fix image/file/gallery media upload
            // avoid acf to use current post to determine if the user can upload a file
            // todo: enhance the logic
            if (acf.isset(window, 'wp', 'media', 'view', 'settings', 'post')) {
                wp.media.view.settings.post = false;
            }

            this.setupUnload();
            this.setupSuccess();

        },

        setupUnload: function() {
            if ($('.acfe-form[data-hide-unload="1"]').length) {
                acf.unload.disable();
            }
        },

        setupSuccess: function() {

            if (!acfe.get('acfe_form_success')) {
                return;
            }

            // Prevent refresh sending post fields again
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            // loop
            acfe.get('acfe_form_success').map(function(form) {

                // hooks
                acf.doAction('acfe/form/success');
                acf.doAction('acfe/form/success/id=' + form.id);
                acf.doAction('acfe/form/success/name=' + form.name);

                // deprecated
                acf.doAction('acfe/form/submit/success');
                acf.doAction('acfe/form/submit/success/id=' + form.id);
                acf.doAction('acfe/form/submit/success/name=' + form.name);

            });

        },

        onClickSubmit: function(e, $el) {

            // prevent submit spam
            if ($el.hasClass('disabled')) {
                e.preventDefault();
            }
        },

        // Datepicker: Add field class
        datePicker: function(field) {

            var fieldClass = this.getFormFieldClass(field);

            if (fieldClass) {
                field.$inputText().addClass(fieldClass);
            }

        },

        // Google Maps: Add field class
        googleMap: function(field) {

            var fieldClass = this.getFormFieldClass(field);

            if (fieldClass) {
                field.$search().addClass(fieldClass);
            }

        },

        // Error: Move error
        invalidField: function(field) {

            var $form = this.$getForm(field);

            if (!$form) {
                return;
            }

            // errors class
            var errorsClass = $form.data('errors-class');

            if (errorsClass) {
                field.$el.find('.acf-notice.-error').addClass(errorsClass);
            }

            // errors position
            var errorsPosition = $form.data('errors-position');

            // position: hide
            if (errorsPosition === 'hide') {
                field.$el.find('.acf-notice.-error').remove();

                // position: below
            } else if (errorsPosition === 'below') {

                if (field.$control().length) {
                    field.$el.find('.acf-notice.-error').insertAfter(field.$control());

                } else if (field.$input().length) {
                    field.$el.find('.acf-notice.-error').insertAfter(field.$input());

                }

                var $selector = false;

                if (field.$control().length) {
                    $selector = field.$control();

                } else if (field.$input().length) {
                    $selector = field.$input();

                }

                if ($selector) {
                    field.$el.find('.acf-notice.-error').insertAfter($selector);
                }

                // position: grouped
            } else if (errorsPosition === 'group') {

                var label = field.$el.find('.acf-label label').text().trim();
                var placeholder = field.$el.find('.acf-input-wrap [placeholder!=""]').attr('placeholder');
                var message = field.$el.find('.acf-notice.-error').text().trim();

                field.$el.find('.acf-notice.-error').remove();

                // try label
                if (label && label.length && label !== '*') {

                    // remove end part " *"
                    label = label.replace(/ \*$/, '');
                    message = label + ': ' + message;

                    // try placeholder
                } else if (placeholder && placeholder.length && placeholder !== '') {
                    message = placeholder + ': ' + message;

                    // try field name
                } else {
                    message = field.get('name') + ': ' + message;

                }

                var $form_error = $form.find('> .acfe-form-error')

                if (!$form_error.length) {
                    $form_error = $('<div class="acf-notice -error acf-error-message acfe-form-error" />').prependTo($form);
                }

                $form_error.append('<p>' + message + '</p>');

            }

        },

        // Ajax Validation
        validationBegin: function($form) {

            if (typeof $form === 'undefined') {
                return;
            }

            $form.find('.acf-error-message').remove();

        }

    });

    // ACF 5.11.4
    var ensureFieldPostBoxIsVisible = function($el) {
        // Find the postbox element containing this field.
        var $postbox = $el.parents('.acf-postbox');

        if ($postbox.length) {
            var acf_postbox = acf.getPostbox($postbox);

            // ACFE: use class check instead of isHiddenByScreenOptions() for older ACF versions
            if (acf_postbox && (acf_postbox.$el.hasClass('hide-if-js') || acf_postbox.$el.css('display') == 'none')) {
                // Rather than using .show() here, we don't want the field to appear next reload.
                // So just temporarily show the field group so validation can complete.
                acf_postbox.$el.removeClass('hide-if-js');
                acf_postbox.$el.css('display', '');
            }
        }
    };

    // ACF 5.11.4
    // added current element as argument
    var ensureInvalidFieldVisibility = function($el) {

        var $inputs = $('.acf-field input');

        // retrieve the current element parents form
        var $form = $el.closest('form');

        // find fields inside the current form only
        if ($form.length) {
            $inputs = $form.find('.acf-field input');
        }

        $inputs.each(function() {
            if (!this.checkValidity()) {
                ensureFieldPostBoxIsVisible($(this));
            }
        });

    };

    // ACF 5.11
    // Fix double front-end form that trigger validation for the other form
    acf.validation.onClickSubmit = function(e, $el) {

        ensureInvalidFieldVisibility($el);

        this.set('originalEvent', e);

    }

    // Rewrite ACF New Condition
    // Allow conditions to work within wrapped div
    acf.newCondition = function(rule, conditions) {

        // currently setting up conditions for fieldX, this field is the 'target'
        var target = conditions.get('field');

        // use the 'target' to find the 'trigger' field.
        // - this field is used to setup the conditional logic events
        var field = target.getField(rule.field);

        // ACF Extended: Check in all form if targeted field not found
        if (target && !field) {
            field = acf.getField(rule.field);
        }

        // bail ealry if no target or no field (possible if field doesn't exist due to HTML error)
        if (!target || !field) {
            return false;
        }

        // vars
        var args = {
            rule: rule,
            target: target,
            conditions: conditions,
            field: field
        };

        // vars
        var fieldType = field.get('type');
        var operator = rule.operator;

        // get avaibale conditions
        var conditionTypes = acf.getConditionTypes({
            fieldType: fieldType,
            operator: operator,
        });

        // instantiate
        var model = conditionTypes[0] || acf.Condition;

        // instantiate
        var condition = new model(args);

        // return
        return condition;

    };

})(jQuery);