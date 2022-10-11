(function($) {

    if (typeof acf === 'undefined') {
        return;
    }

    /**
     * acfe
     *
     * @type {{}}
     */
    window.acfe = {};

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.getArray
     *
     * Forces val as an array, also allows integer 0
     *
     * @param val
     * @returns {*[]}
     */
    acfe.getArray = function(val) {
        val = val === 0 ? '0' : val;
        return [].concat(val || []);
    };


    /**
     * acfe.getEntries
     *
     * Get object entries to use in for(var [x, y] of obj){}
     *
     * @param obj
     * @returns {[string, unknown][]}
     */
    acfe.getEntries = function(obj) {
        return Object.entries(obj);
    }


    /**
     * acfe.inArray
     *
     * @param v1
     * @param array
     * @param strict
     * @returns {boolean}
     */
    acfe.inArray = function(v1, array, strict = false) {

        if (!strict) {

            v1 = acfe.getString(v1);
            array = array.map(function(v2) {
                return acfe.getString(v2);
            });

        }

        for (var i = 0; i < array.length; i++) {
            if (array[i] === v1) {
                return true;
            }
        }

        return false;

    };


    /**
     * acfe.arrayGet
     *
     * Get array/object value using dot notation
     *
     * https://github.com/callmecavs/dotnot/blob/master/src/dotnot.js
     *
     * @param obj
     * @param path
     * @param def
     * @returns {null|*}
     */
    acfe.arrayGet = function(obj, path, def = null) {

        // get path array
        path = acfe.normalizePath(path);

        // length of path array
        var len = path.length;

        // loop through path updating the reference to child objects
        var current = obj;
        var name;

        for (var index = 0; index < len; index++) {

            // current key name
            name = path[index];

            // stop searching if a child object is missing
            if (acfe.isUndefined(current[name])) {
                return def;
            }

            current = current[name];
        }

        return current;

    }


    /**
     * acfe.arrayHas
     *
     * Check array/object has key using dot notation
     * '!!default!!' is workaround allowing 'null' to be considered as set
     *
     * @param obj
     * @param path
     * @returns {*}
     */
    acfe.arrayHas = function(obj, path) {
        return acfe.arrayGet(obj, path, '!!default!!') !== '!!default!!';
    }


    /**
     * acfe.arraySet
     *
     * Set array/object value using dot notation
     *
     * https://github.com/callmecavs/dotnot/blob/master/src/dotnot.js
     *
     * @param obj
     * @param path
     * @param val
     */
    acfe.arraySet = function(obj, path, val) {

        // get path array
        path = acfe.normalizePath(path);

        // length of path array
        var len = path.length;

        // loop through path updating the reference to child objects
        var current = obj;
        var name;

        for (var index = 0; index < len; index++) {

            // current key name
            name = path[index];

            // set value on last key
            if (index === len - 1) {
                current[name] = val;

            } else if (current[name]) {

                if (!acfe.isObject(current[name])) {
                    current[name] = {};
                }

                current = current[name];

            } else {
                current[name] = {};
                current = current[name];

            }

        }

        // re-index array
        obj = acfe.arrayReindex(obj);

        // return
        return obj;

    }


    /**
     * acfe.arrayDelete
     *
     * Delete array/object key via dot notation
     *
     * https://github.com/callmecavs/dotnot/blob/master/src/dotnot.js
     *
     * @param obj
     * @param path
     * @returns {*}
     */
    acfe.arrayDelete = function(obj, path) {

        // get path array
        path = acfe.normalizePath(path);

        // length of path array
        var len = path.length;

        // loop through path updating the reference to child objects
        var current = obj;
        var name;

        for (var index = 0; index < len; index++) {

            // current key name
            name = path[index];

            // set value on last key
            if (index === len - 1) {
                delete current[name];
            } else {
                current = current[name] || {};
            }

        }

        // re-index array
        obj = acfe.arrayReindex(obj);

        // return
        return obj;

    }


    /**
     * acfe.arrayPluck
     *
     * '!!default!!' is workaround allowing 'null' to be considered as set
     *
     * @param obj
     * @param path
     * @param id
     * @returns {*[]}
     */
    acfe.arrayPluck = function(obj, path, id = false) {

        // vars
        var idPath;
        var idObj = {};
        var collect = [];

        // prepare id
        if (id) {

            idPath = path.split('.');
            idPath.pop();
            idPath.push(id);
            idPath = idPath.join('.');

        }

        // loop
        for (var row of obj) {

            var result = acfe.arrayGet(row, path, '!!default!!');

            // bail early
            if (result === '!!default!!') {
                continue;
            }

            // push to collection
            collect.push(result);

            if (id) {
                var key = acfe.arrayGet(row, idPath, '!!default!!');

                if (key !== '!!default!!') {
                    idObj[key] = result;
                }
            }

        }

        // collect id
        if (id) {
            collect = idObj;
        }

        // return
        return collect;

    }


    /**
     * acfe.normalizePath
     *
     * Converts a dot notation path as array
     *
     * @param path
     * @returns {*[]|string[]}
     */
    acfe.normalizePath = function(path) {

        // convert to string
        path = acfe.getString(path);

        // split dot notation
        path = path.split('.');

        // return
        return path;

    }


    /**
     * acfe.arrayReindex
     *
     * Re-index array after delete usage
     *
     * @param obj
     * @returns {*}
     */
    acfe.arrayReindex = function(obj) {

        // filter
        if (acfe.isArray(obj)) {
            obj = obj.filter(function(val) {
                return !acfe.isUndefined(val);
            });
        }

        // return
        return obj;

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acf.data.acfe
     *
     * @type {{}}
     */
    acf.data.acfe = {};


    /**
     * acfe.get
     *
     * @param name
     * @param def
     * @returns {*|null}
     */
    acfe.get = function(name, def = null) {
        return acf.data.acfe[name] || def;
    };


    /**
     * acfe.has
     *
     * @param name
     * @returns {boolean}
     */
    acfe.has = function(name) {
        return this.get(name) != null;
    };


    /**
     * acfe.set
     *
     * @param name
     * @param value
     * @returns {acfe}
     */
    acfe.set = function(name, value) {
        acf.data.acfe[name] = value;
        return this;
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * isFieldKey
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isFieldKey = function(name) {
        return acfe.isString(name) && name.startsWith('field_');
    }


    /**
     * acfe.isFieldName
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isFieldName = function(name) {
        return acfe.isString(name) && !acfe.isFieldKey(name);
    }


    /**
     * isGroupKey
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isGroupKey = function(name) {
        return acfe.isString(name) && name.startsWith('group_');
    }


    /**
     * acfe.getField
     *
     * Allow to query a single field with name, or arguments
     *
     * @param name
     * @returns {[]|*}
     */
    acfe.getField = function(name) {

        // field name
        if (acfe.isFieldName(name)) {

            return acf.getFields({
                name: name,
                limit: 1,
                suppressFilters: true
            }).shift();

            // arguments
        } else if (acfe.isObject(name)) {

            name = acf.parseArgs(name, {
                limit: 1,
                suppressFilters: true
            });

            return acf.getFields(name).shift();

        }

        // default getField
        return acf.getField(name);

    };


    /**
     * find_fields_selector
     *
     * Since ACF 6.0. Allow types as array
     */
    acf.addFilter('find_fields_selector', function(selector, args) {

        // args.types
        // allow types array
        if (args.types && args.types.length && acfe.isArray(args.types)) {

            // vars
            var array = [];

            // loop
            for (var type of args.types) {
                array.push(selector + '[data-type="' + type + '"]');
            }

            selector = array.join(',');

        }

        // return
        return selector;

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * storage
     *
     * @type {*[]}
     */
    var filters = [];


    /**
     * acfe.disableFilters
     */
    acfe.disableFilters = function() {
        filters = [];
    };


    /**
     * acfe.getFilters
     *
     * @returns {*[]}
     */
    acfe.getFilters = function() {
        return filters;
    };


    /**
     * acfe.isFilterEnabled
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isFilterEnabled = function(name) {
        return filters.indexOf(name) > -1;
    };


    /**
     * acfe.enableFilter
     *
     * @param name
     */
    acfe.enableFilter = function(name) {
        if (filters.indexOf(name) === -1) {
            filters.push(name);
        }
    };


    /**
     * acfe.disableFilter
     *
     * @param name
     */
    acfe.disableFilter = function(name) {
        for (var i = filters.length; i--;) {
            if (filters[i] === name) {
                filters.splice(i, 1);
            }
        }
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.findSubmitWrap
     *
     * @param $form
     * @returns {{length}|*|jQuery|HTMLElement}
     */
    acfe.findSubmitWrap = function($form) {

        var $wrap;
        $form = $form || $('form');

        // default post submit div
        $wrap = $form.find('#submitdiv');
        if ($wrap.length) {
            return $wrap;
        }

        // 3rd party publish box
        $wrap = $form.find('#submitpost');
        if ($wrap.length) {
            return $wrap;
        }

        // term, user
        $wrap = $form.find('p.submit').last();
        if ($wrap.length) {
            return $wrap;
        }

        // front end form
        $wrap = $form.find('.acf-form-submit');
        if ($wrap.length) {
            return $wrap;
        }

        // default
        return $form;

    };


    /**
     * acfe.findSubmit
     *
     * @param $form
     * @returns {*|jQuery}
     */
    acfe.findSubmit = function($form) {

        $form = $form || $('form');
        return this.findSubmitWrap($form).find('.button, [type="submit"]');

    }


    /**
     * acfe.findSpinner
     *
     * @param $form
     * @returns {*|jQuery}
     */
    acfe.findSpinner = function($form) {

        $form = $form || $('form');
        return this.findSubmitWrap($form).find('.spinner, .acf-spinner');

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Popup
     */
    acfe.getPopup = function($el) {
        return acf.getInstance($el);
    };

    acfe.getPopups = function() {
        return acf.getInstances($('.acfe-modal'));
    };

    acfe.newPopup = function($el, args) {
        return new acfe.Popup($el, args);
    };

    acfe.Popup = acf.Model.extend({

        data: {
            title: '',
            content: '',
            footer: '',
            class: '',
            size: '',
            autoOpen: true,
            destroy: false,
            events: {},
            onOpen: function() {},
            onClose: function() {},
        },

        events: {
            'click >.acfe-modal-wrapper>.acfe-modal-title>.close': 'onClickClose',
            'click >.acfe-modal-wrapper>.acfe-modal-footer>.close': 'onClickClose',
        },

        setup: function($el, args) {

            // content + args
            if (args) {

                if ($el instanceof jQuery) {
                    // ...
                } else {
                    $el = $($el);
                }

                // set el
                this.$el = $el;

                // only args
            } else {

                // set args
                args = $el;

                // set el
                this.$el = $('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>').appendTo('body');

            }

            // extend data
            $.extend(this.data, args);

            // render
            this.render();

        },

        initialize: function() {

            this.addUnscopedEvents();

            if (this.get('autoOpen')) {
                this.open();
            }

        },

        addUnscopedEvents: function() {

            if (!this.get('events')) {
                return;
            }

            // vars
            var events = this.get('events');
            var delegateEventSplitter = /^(\S+)\s*(.*)$/;

            for (var key in events) {

                // match
                var match = key.match(delegateEventSplitter);

                // vars
                var $el, args, event, selector, callback;

                if (match[2]) {
                    event = match[1];
                    selector = match[2];
                    callback = events[key];
                } else {
                    event = match[1];
                    selector = '';
                    callback = events[key];
                }

                // event
                event = event + '.' + this.cid;

                // callback
                callback = this.proxyEvent(this.get(callback));

                if (selector) {
                    args = [event, selector, callback];
                } else {
                    args = [event, callback];
                }

                $el = this.$el;
                $el.on.apply($el, args);

            }

        },

        $wrapper: function() {
            return this.$('> .acfe-modal-wrapper');
        },

        $title: function(val) {

            // set title
            if (val !== undefined) {
                return this.$wrapper().find('> .acfe-modal-title > .title').html(val);
            }

            return this.$wrapper().find('> .acfe-modal-title');
        },

        $content: function(val) {

            // set content
            if (val !== undefined) {
                return this.$('.acfe-modal-content').html(val);
            }

            return this.$wrapper().find('> .acfe-modal-content');
        },

        $footer: function(val) {

            // set footer
            if (val !== undefined) {
                return this.$wrapper().find('> .acfe-modal-footer').html(val);
            }

            return this.$wrapper().find('> .acfe-modal-footer');
        },

        $overlay: function() {
            return this.$wrapper().find('> .acfe-modal-wrapper-overlay');
        },

        setupClass: function() {

            if (this.get('size')) {
                this.$el.addClass('-' + this.get('size'));
            }

            if (this.get('class')) {
                this.$el.addClass(this.get('class'));
            }

        },

        setupWrapper: function() {
            if (!this.$wrapper().length) {
                this.$el.wrapInner('<div class="acfe-modal-wrapper" />');
            }
        },

        setupTitle: function() {
            if (this.get('title')) {

                if (!this.$title().length) {
                    this.$wrapper().prepend('<div class="acfe-modal-title"><span class="title"></span><button class="close"></button></div>');
                }

                if (typeof this.get('title') === 'function') {
                    this.$title(this.get('title').apply(this));
                } else {
                    this.$title(this.get('title'));
                }
            }
        },

        setupContent: function() {
            if (!this.$content().length) {
                this.$wrapper().wrapInner('<div class="acfe-modal-content" />');
            }

            if (this.get('content')) {

                if (typeof this.get('content') === 'function') {
                    this.$content(this.get('content').apply(this));
                } else {
                    this.$content(this.get('content'));
                }
            }
        },

        setupFooter: function() {

            if (this.get('footer')) {

                if (!this.$footer().length) {
                    this.$wrapper().append('<div class="acfe-modal-footer" />');
                }

                if (typeof this.get('footer') === 'function') {
                    this.$footer(this.get('footer').apply(this));
                } else {
                    this.$footer('<button class="button button-primary close">' + this.get('footer') + '</button>');
                }

            }

        },

        setupOverlay: function() {
            if (!this.$overlay().length) {
                this.$wrapper().prepend('<div class="acfe-modal-wrapper-overlay" />');
            }
        },

        setupTinymce: function() {

            // hide TinyMCE dropdown when scroll modal (fix position issues)
            if (typeof tinymce !== 'undefined' && acf.isset(tinymce, 'ui', 'FloatPanel')) {
                this.$content().off('scroll.tinymcePanel').on('scroll.tinymcePanel', function(e) {
                    tinymce.ui.FloatPanel.hideAll();
                });
            }

        },

        setupFields: function() {

            // get subfields
            var getSubFields = acf.getFields({
                parent: this.$el,
                visible: true,
            });

            // show subfields
            getSubFields.map(function(field) {
                acf.doAction('show_field', field, 'group');
            }, this);

        },

        render: function() {

            // setup
            this.setupClass();
            this.setupWrapper();
            this.setupContent();
            this.setupTitle();
            this.setupFooter();
            this.setupOverlay();
            this.setupTinymce();

        },

        open: function() {

            // add class
            this.$el.addClass('-open');

            // push to popus storage
            modalManager.addPopup(this);

            // action
            acf.doAction('acfe/modal/open', this);

            // function
            this.get('onOpen').apply(this);

            // event
            this.trigger('open');

            // setup fields
            this.setupFields();

        },

        close: function() {

            // remove style & class
            this.$el.removeAttr('style');
            this.$el.removeClass('-open');

            modalManager.removePopup(this);

            // action
            acf.doAction('acfe/modal/close', this);

            // function
            this.get('onClose').apply(this);

            // event
            this.trigger('close');

            // destroy
            if (this.get('destroy')) {
                this.remove();
            }

        },

        onClickClose: function(e, $el) {

            e.preventDefault();
            this.close();

        }

    });

    /**
     * Popup: Close
     */
    acfe.closePopup = function(instance) {

        // close last popup
        if (instance === undefined) {
            return modalManager.closeLastPopup();
        }

        var popup;

        // jQuery element
        if (instance instanceof jQuery) {

            popup = acfe.getPopup(instance);
            if (popup) {
                popup.close();
            }

            // jQuery selector
        } else if (typeof instance === 'string') {

            instance = $(instance);
            popup = acfe.getPopup(instance);
            if (popup) {
                popup.close();
            }

            // ACF instance
        } else {

            if (typeof instance.close === 'function') {
                instance.close();
            }

        }

    };

    var modalManager = new acf.Model({

        popups: [],

        events: {
            'click .acfe-modal-overlay': 'onClick',
            'keydown': 'onKeydown',
        },

        onClick: function(e) {

            e.preventDefault();
            this.closeLastPopup();

        },

        onKeydown: function(e) {
            if (e.keyCode === 27 && $('body').hasClass('acfe-modal-opened')) {
                e.preventDefault();
                this.closeLastPopup();
            }
        },

        addPopup: function(instance) {

            this.popups.push(instance);
            this.syncPopups();

            if (!$('body').hasClass('acfe-modal-opened')) {
                $('body').addClass('acfe-modal-opened').append($('<div class="acfe-modal-overlay" />'));
            }

        },

        removePopup: function(instance) {

            this.popups = this.popups.filter(function(popup) {
                return popup.cid !== instance.cid;
            }, this);

            this.syncPopups();

            if (!this.popups.length) {
                $('.acfe-modal-overlay').remove();
                $('body').removeClass('acfe-modal-opened');
            }

        },

        closeLastPopup: function() {
            if (this.popups.length) {
                this.popups[this.popups.length - 1].close();
            }
        },

        syncPopups: function() {

            // multiple popups: add css margin
            this.popups.map(function(popup, i) {

                // last popup
                if (i === this.popups.length - 1) {
                    return popup.$el.removeClass('acfe-modal-sub').css('margin-left', '');
                }

                // other popups
                popup.$el.addClass('acfe-modal-sub').css('margin-left', -(500 / (i + 1)));

            }, this);

        },

    });

    // Allow open modal in HTML
    new acf.Model({

        events: {
            'click a[data-acfe-modal]': 'onClick',
            'click button[data-acfe-modal]': 'onClick',
            'click input[data-acfe-modal]': 'onClick',
        },

        onClick: function(e, $el) {

            // prevent default
            e.preventDefault();

            // vars
            var target = $el.attr('data-acfe-modal') || false;
            var size = $el.attr('data-acfe-modal-size') || 'medium';
            var title = $el.attr('data-acfe-modal-title') || '';
            var footer = $el.attr('data-acfe-modal-footer') || '';

            // find next modal div
            if (!target) {
                target = $el.parent().find('.acfe-modal').first();
            }

            if (target instanceof jQuery) {
                // do nothing
            } else {
                target = $('.acfe-modal[data-acfe-modal=' + target + ']');
            }

            if (target.length) {

                new acfe.Popup(target, {
                    size: size,
                    title: title,
                    footer: footer,
                });

            }

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acf.Model.get()
     *
     * Extends acf.Model.get() to allow default value
     *
     * @param name
     * @param def
     * @returns {*|null}
     */
    acf.Model.prototype.get = function(name, def = null) {
        return this.data[name] || def;
    };

    /**
     * acf.get()
     *
     * Extends acf.get() to allow default value
     *
     * @param name
     * @param def
     * @returns {*|null}
     */
    acf.get = function(name, def = null) {
        return this.data[name] || def;
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acf helpers
     *
     * acf.strReplace()
     * acf.strCamelCase()
     * acf.strPascalCase()
     * acf.strSlugify()
     * acf.strSanitize()
     * acf.strMatch()
     */


    /**
     * acfe.getString
     *
     * Parse value as string, also allows integer 0
     *
     * @param val
     * @returns {string|string}
     */
    acfe.getString = function(val) {

        if (acfe.isObject(val)) {
            return JSON.stringify(val);
        }

        return !acfe.isEmpty(val) ? '' + val : '';
    };


    /**
     * acfe.getTextNode
     *
     * @param $selector
     * @returns {*}
     */
    acfe.getTextNode = function($selector) {

        if ($selector.exists()) {
            for (row of $selector.contents()) {

                var text = $.trim($(row).text());
                if (text) {
                    return text;
                }

            }
        }

        return '';

    };


    /**
     *
     * @param string
     * @returns {string}
     */
    acfe.ucFirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Tooltip
     */
    var tooltip = new acf.Model({

        tooltips: {},

        events: {
            'click .acfe-field-tooltip': 'clickTooltip',
        },

        clickTooltip: function(e, $el) {

            // title
            var title = $el.attr('title');
            if (!title) {
                return;
            }

            // get field
            var field = acf.getClosestField($el);
            if (!field) {
                return;
            }

            // clear title to avoid default browser tooltip
            $el.attr('title', '');

            // open
            if (!this.tooltips[field.cid]) {

                this.tooltips[field.cid] = acf.newTooltip({
                    text: title,
                    target: $el
                });

                if (acfe.versionCompare(acf.get('wp_version'), '>=', '5.5')) {
                    $el.removeClass('dashicons-info-outline').addClass('dashicons-remove');
                }

                // close
            } else {

                // hide tooltip
                this.tooltips[field.cid].hide();

                // restore title
                $el.attr('title', this.tooltips[field.cid].get('text'));

                this.tooltips[field.cid] = false;

                if (acfe.versionCompare(acf.get('wp_version'), '>=', '5.5')) {
                    $el.removeClass('dashicons-remove').addClass('dashicons-info-outline');
                }

            }

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.isArray
     *
     * Copy acf.isArray
     *
     * @param a
     * @returns {*}
     */
    acfe.isArray = function(a) {
        return Array.isArray(a);
    }


    /**
     * acfe.isObject
     *
     * Copy acf.isObject enhanced
     *
     * @param a
     * @returns {*}
     */
    acfe.isObject = function(a) {
        return typeof a === 'object' && !acfe.isArray(a);
    }


    /**
     * acfe.isNumeric
     *
     * Copy acf.isNumeric
     *
     * @param a
     * @returns {*}
     */
    acfe.isNumeric = function(a) {
        return !isNaN(parseFloat(a)) && isFinite(a);
    }


    /**
     * acfe.isString
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isString = function(a) {
        return typeof a === 'string';
    }


    /**
     * acfe.isUndefined
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isUndefined = function(a) {
        return typeof a === 'undefined';
    }


    /**
     * acfe.isFunction
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isFunction = function(a) {
        return typeof a === 'function';
    }


    /**
     * acfe.isBool
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isBool = function(a) {
        return typeof a === 'boolean';
    }


    /**
     * acfe.isInt
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isInt = function(a) {
        return Number.isInteger(a);
    }


    /**
     * acfe.isJquery
     *
     * @param $a
     * @returns {boolean}
     */
    acfe.isJquery = function($a) {
        return $a instanceof jQuery
    }


    /**
     * acfe.isEmpty
     *
     * Allows integer 0 to be used
     *
     * @param val
     * @returns {boolean}
     */
    acfe.isEmpty = function(val) {

        // array
        if (acfe.isArray(val)) {
            return !val.length;

            // object
        } else if (acfe.isObject(val)) {
            return val && Object.keys(val).length === 0 && Object.getPrototypeOf(val) === Object.prototype

            // string
        } else if (acfe.isString(val)) {
            return !val.length;

        }

        // integer 0
        return !val && !acfe.isNumeric(val);

    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.slugify
     *
     * @param str
     * @returns {string}
     */
    acfe.slugify = function(str) {
        return str.replace(/[\s\./]+/g, '-').replace(/[^\p{L}\p{N}_-]+/gu, '').replace(/-+$/, '').toLowerCase();
    }

    /**
     * acfe.addQueryArgs
     *
     * @returns {string|any|string}
     */
    acfe.addQueryArgs = function() {

        let url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
        let args = arguments.length > 1 ? arguments[1] : undefined;

        // If no arguments are to be appended, return original URL.
        if (!args || !Object.keys(args).length) {
            return url;
        }

        let baseUrl = url; // Determine whether URL already had query arguments.

        const queryStringIndex = url.indexOf('?');

        if (queryStringIndex !== -1) {
            // Merge into existing query arguments.
            args = Object.assign(acfe.getQueryArgs(url), args); // Change working base URL to omit previous query arguments.

            baseUrl = baseUrl.substr(0, queryStringIndex);
        }

        return baseUrl + '?' + buildQueryString(args);

    };


    /**
     * acfe.getQueryArgs
     *
     * @param url
     * @returns {string}
     */
    acfe.getQueryArgs = function(url) {

        return (getQueryString(url) || '' // Normalize space encoding, accounting for PHP URL encoding
            // corresponding to `application/x-www-form-urlencoded`.
            //
            // See: https://tools.ietf.org/html/rfc1866#section-8.2.1
        ).replace(/\+/g, '%20').split('&').reduce((accumulator, keyValue) => {
            const [key, value = ''] = keyValue.split('=') // Filtering avoids decoding as `undefined` for value, where
                // default is restored in destructuring assignment.
                .filter(Boolean).map(decodeURIComponent);

            if (key) {
                const segments = key.replace(/\]/g, '').split('[');
                setPath(accumulator, segments, value);
            }

            return accumulator;
        }, Object.create(null));

    };


    /**
     * acfe.getFragment
     *
     * getFragment('http://localhost:8080/this/is/a/test?query=true#fragment'); // '#fragment'
     * getFragment('https://wordpress.org#another-fragment?query=true');        // '#another-fragment'
     *
     * @param url
     * @returns {string}
     */
    acfe.getFragment = function(url) {

        const matches = /^\S+?(#[^\s\?]*)/.exec(url);

        if (matches) {
            return matches[1];
        }

        return '';

    };


    /**
     * acfe.getCurrentUrl
     *
     * @returns {*}
     */
    acfe.getCurrentUrl = function() {
        return self.location.href;
    };


    /**
     * acfe.getCurrentPath
     *
     * @returns {string|string|string|*}
     */
    acfe.getCurrentPath = function() {
        return self.location.pathname;
    };


    /**
     * acfe.getCurrentFilename
     *
     * @returns {string}
     */
    acfe.getCurrentFilename = function() {
        return acfe.getFilename(acfe.getCurrentPath());
    };


    /**
     * acfe.getFilename
     *
     * @param path
     * @returns {*}
     */
    acfe.getFilename = function(path) {
        return path.split('/').pop();
    };


    /**
     * buildQueryString
     *
     * wp-includes/js/dist/url.js
     *
     * @param data
     * @returns {string}
     */
    var buildQueryString = function(data) {

        let string = '';
        const stack = Object.entries(data);
        let pair;

        while (pair = stack.shift()) {
            let [key, value] = pair; // Support building deeply nested data, from array or object values.

            const hasNestedData = Array.isArray(value) || value && value.constructor === Object;

            if (hasNestedData) {
                // Push array or object values onto the stack as composed of their
                // original key and nested index or key, retaining order by a
                // combination of Array#reverse and Array#unshift onto the stack.
                const valuePairs = Object.entries(value).reverse();

                for (const [member, memberValue] of valuePairs) {
                    stack.unshift([`${key}[${member}]`, memberValue]);
                }
            } else if (value !== undefined) {
                // Null is treated as special case, equivalent to empty string.
                if (value === null) {
                    value = '';
                }

                string += '&' + [key, value].map(encodeURIComponent).join('=');
            }
        } // Loop will concatenate with leading `&`, but it's only expected for all
        // but the first query parameter. This strips the leading `&`, while still
        // accounting for the case that the string may in-fact be empty.


        return string.substr(1);

    };


    /**
     * getQueryString
     *
     * wp-includes/js/dist/url.js
     *
     * @param url
     * @returns {string}
     */
    var getQueryString = function(url) {
        let query;

        try {
            query = new URL(url, 'http://example.com').search.substring(1);
        } catch (error) {}

        if (query) {
            return query;
        }
    };


    /**
     * setPath
     *
     * wp-includes/js/dist/url.js
     *
     * @param object
     * @param path
     * @param value
     */
    var setPath = function(object, path, value) {

        const length = path.length;
        const lastIndex = length - 1;

        for (let i = 0; i < length; i++) {
            let key = path[i];

            if (!key && Array.isArray(object)) {
                // If key is empty string and next value is array, derive key from
                // the current length of the array.
                key = object.length.toString();
            }

            key = ['__proto__', 'constructor', 'prototype'].includes(key) ? key.toUpperCase() : key; // If the next key in the path is numeric (or empty string), it will be
            // created as an array. Otherwise, it will be created as an object.

            const isNextKeyArrayIndex = !isNaN(Number(path[i + 1]));
            object[key] = i === lastIndex ? // If at end of path, assign the intended value.
                value : // Otherwise, advance to the next object in the path, creating
                // it if it does not yet exist.
                object[key] || (isNextKeyArrayIndex ? [] : {});

            if (Array.isArray(object[key]) && !isNextKeyArrayIndex) {
                // If we current key is non-numeric, but the next value is an
                // array, coerce the value to an object.
                object[key] = {
                    ...object[key]
                };
            } // Update working reference object to the next in the path.


            object = object[key];
        }

    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.versionCompare
     *
     * https://locutus.io/php/info/version_compare/
     *
     * @param v1
     * @param operator
     * @param v2
     * @returns {null|boolean|number}
     */
    acfe.versionCompare = function(v1, operator, v2) {
        let i
        let x
        let compare = 0

        const vm = {
            dev: -6,
            alpha: -5,
            a: -5,
            beta: -4,
            b: -4,
            RC: -3,
            rc: -3,
            '#': -2,
            p: 1,
            pl: 1
        }

        const _prepVersion = function(v) {
            v = ('' + v).replace(/[_\-+]/g, '.')
            v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
            return (!v.length ? [-8] : v.split('.'))
        }

        const _numVersion = function(v) {
            return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10))
        }
        v1 = _prepVersion(v1)
        v2 = _prepVersion(v2)
        x = Math.max(v1.length, v2.length)
        for (i = 0; i < x; i++) {
            if (v1[i] === v2[i]) {
                continue
            }
            v1[i] = _numVersion(v1[i])
            v2[i] = _numVersion(v2[i])
            if (v1[i] < v2[i]) {
                compare = -1
                break
            } else if (v1[i] > v2[i]) {
                compare = 1
                break
            }
        }
        if (!operator) {
            return compare
        }

        switch (operator) {
            case '>':
            case 'gt':
                return (compare > 0)
            case '>=':
            case 'ge':
                return (compare >= 0)
            case '<=':
            case 'le':
                return (compare <= 0)
            case '===':
            case '=':
            case 'eq':
                return (compare === 0)
            case '<>':
            case '!==':
            case 'ne':
                return (compare !== 0)
            case '':
            case '<':
            case 'lt':
                return (compare < 0)
            default:
                return null
        }

    };

})(jQuery);