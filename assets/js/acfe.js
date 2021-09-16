(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * ACF Data
     */
    acf.data.acfe = {};

    /*
     * ACFE
     */
    var acfe = {};

    window.acfe = acfe;

    /*
     * Get
     */
    acfe.get = function(name) {
        return acf.data.acfe[name] || null;
    };

    /*
     * Has
     */
    acfe.has = function(name) {
        return this.get(name) !== null;
    };

    /*
     * Set
     */
    acfe.set = function(name, value) {
        acf.data.acfe[name] = value;
        return this;
    };

    /*
     * Parse String
     */
    acfe.parseString = function(val) {
        return val ? '' + val : '';
    };

    /*
     * In Array
     */
    acfe.inArray = function(v1, array) {

        array = array.map(function(v2) {
            return acfe.parseString(v2);
        });

        return (array.indexOf(v1) > -1);

    }

    /*
     * Parse URL
     */
    acfe.parseURL = function(url) {

        url = url || acfe.currentURL();

        var params = {};

        var queryString = url.replace(/^[^\?]+\??/, '');

        if (!queryString)
            return params;

        var Pairs = queryString.split(/[;&]/);

        for (var i = 0; i < Pairs.length; i++) {

            var KeyVal = Pairs[i].split('=');

            if (!KeyVal || KeyVal.length !== 2)
                continue;

            var key = decodeURI(KeyVal[0]);
            var val = decodeURI(KeyVal[1]);

            val = val.replace(/\+/g, ' ');

            params[key] = val;

        }

        return params;

    };

    /*
     * Current URL
     */
    acfe.currentURL = function() {

        return self.location.href;

    };

    /*
     * Current Path
     */
    acfe.currentPath = function() {

        return self.location.pathname;

    };

    /*
     * Current Filename
     */
    acfe.currentFilename = function() {

        return acfe.currentPath().split('/').pop();

    };

    /*
     * Parent Object
     */
    acfe.parentObject = function(obj) {

        return Object.getPrototypeOf(obj);

    }

    /*
     * Get Text Node
     */
    acfe.getTextNode = function($selector) {

        var result;

        $selector.contents().each(function() {
            var text = $.trim($(this).text());

            if (text) {
                result = text;
                return false;
            }

        });

        return result;

    }

    /*
     * Find Submit Wrap
     */
    acfe.findSubmitWrap = function($form) {

        $form = $form || $('form');

        // default post submit div
        var $wrap = $form.find('#submitdiv');
        if ($wrap.length) {
            return $wrap;
        }

        // 3rd party publish box
        var $wrap = $form.find('#submitpost');
        if ($wrap.length) {
            return $wrap;
        }

        // term, user
        var $wrap = $form.find('p.submit').last();
        if ($wrap.length) {
            return $wrap;
        }

        // front end form
        var $wrap = $form.find('.acf-form-submit');
        if ($wrap.length) {
            return $wrap;
        }

        // default
        return $form;

    };

    /*
     * Find Submit
     */
    acfe.findSubmit = function($form) {

        $form = $form || $('form');

        return this.findSubmitWrap($form).find('.button, [type="submit"]');

    }

    /*
     * Find Spinner
     */
    acfe.findSpinner = function($form) {

        $form = $form || $('form');

        return this.findSubmitWrap($form).find('.spinner, .acf-spinner');

    }

    /*
     * Filters
     */
    var filters = [];

    acfe.disableFilters = function() {
        filters = [];
    };

    acfe.getFilters = function() {
        return filters;
    };

    acfe.isFilterEnabled = function(name) {
        return filters.indexOf(name) > -1;
    };

    acfe.enableFilter = function(name) {

        if (filters.indexOf(name) === -1)
            filters.push(name);

    };

    acfe.disableFilter = function(name) {

        for (var i = filters.length; i--;) {

            if (filters[i] !== name)
                continue;

            filters.splice(i, 1);

        }

    };

    /*
     * Field Extend
     */
    acfe.fieldExtend = function(fieldType, props) {

        var field = acf.getFieldType(fieldType);

        props.parent = function() {
            return field.prototype;
        }

        if (!props.initialize) {

            props.initialize = function() {

                field.prototype.initialize.apply(this, arguments);

                if (props.init) {
                    props.init.apply(this, arguments);
                }

                if (props._events) {
                    field.prototype.addEvents.apply(this, [props._events]);
                }

                if (props._actions) {
                    field.prototype.addActions.apply(this, [props._actions]);
                }

                if (props._filters) {
                    field.prototype.addFilters.apply(this, [props._filters]);
                }

            }

        }

        return field.extend(props);

    }

    /*
     * Version Compare
     * https://locutus.io/php/info/version_compare/
     */
    acfe.versionCompare = function(v1, operator, v2) {
        // eslint-disable-line camelcase
        //       discuss at: https://locutus.io/php/version_compare/
        //      original by: Philippe Jausions (https://pear.php.net/user/jausions)
        //      original by: Aidan Lister (https://aidanlister.com/)
        // reimplemented by: Kankrelune (https://www.webfaktory.info/)
        //      improved by: Brett Zamir (https://brett-zamir.me)
        //      improved by: Scott Baker
        //      improved by: Theriault (https://github.com/Theriault)
        //        example 1: version_compare('8.2.5rc', '8.2.5a')
        //        returns 1: 1
        //        example 2: version_compare('8.2.50', '8.2.52', '<')
        //        returns 2: true
        //        example 3: version_compare('5.3.0-dev', '5.3.0')
        //        returns 3: -1
        //        example 4: version_compare('4.1.0.52','4.01.0.51')
        //        returns 4: 1
        // Important: compare must be initialized at 0.
        let i
        let x
        let compare = 0
        // vm maps textual PHP versions to negatives so they're less than 0.
        // PHP currently defines these as CASE-SENSITIVE. It is important to
        // leave these as negatives so that they can come before numerical versions
        // and as if no letters were there to begin with.
        // (1alpha is < 1 and < 1.1 but > 1dev1)
        // If a non-numerical value can't be mapped to this table, it receives
        // -7 as its value.
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
        // This function will be called to prepare each version argument.
        // It replaces every _, -, and + with a dot.
        // It surrounds any nonsequence of numbers/dots with dots.
        // It replaces sequences of dots with a single dot.
        //    version_compare('4..0', '4.0') === 0
        // Important: A string of 0 length needs to be converted into a value
        // even less than an unexisting value in vm (-7), hence [-8].
        // It's also important to not strip spaces because of this.
        //   version_compare('', ' ') === 1
        const _prepVersion = function(v) {
            v = ('' + v).replace(/[_\-+]/g, '.')
            v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
            return (!v.length ? [-8] : v.split('.'))
        }
        // This converts a version component to a number.
        // Empty component becomes 0.
        // Non-numerical component becomes a negative number.
        // Numerical component becomes itself as an integer.
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
        // Important: operator is CASE-SENSITIVE.
        // "No operator" seems to be treated as "<."
        // Any other values seem to make the function return null.
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

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Popup
     */
    var popups = [];

    acfe.Popup = acf.Model.extend({

        data: {
            title: false,
            footer: false,
            size: false,
            destroy: false,
            onOpen: function() {},
            onClose: function() {},
        },

        events: {
            'click .acfe-modal-title>.close': 'onClickClose',
            'click .acfe-modal-footer>button': 'onClickClose',
        },

        setup: function($content, args) {

            $.extend(this.data, args);

            this.$el = $content;
            this.render();

        },

        initialize: function() {

            this.open();

        },

        render: function() {

            // Size
            if (this.get('size')) {
                this.$el.addClass('-' + this.get('size'));
            }

            // Wrapper
            if (!this.$('> .acfe-modal-wrapper').length) {
                this.$el.wrapInner('<div class="acfe-modal-wrapper" />');
            }

            var $wrapper = this.$('> .acfe-modal-wrapper');

            // Content
            if (!$wrapper.find('> .acfe-modal-content').length) {
                $wrapper.wrapInner('<div class="acfe-modal-content" />');
            }

            // Title
            if (this.get('title')) {
                $wrapper.prepend('<div class="acfe-modal-title"><span class="title">' + this.get('title') + '</span><button class="close"></button></div>');
            }

            // Overlay
            $wrapper.prepend('<div class="acfe-modal-wrapper-overlay"></div>');

            // Footer
            if (this.get('footer')) {
                $wrapper.append('<div class="acfe-modal-footer"><button class="button button-primary">' + this.get('footer') + '</button></div>');
            }

            // Hide TinyMCE dropdown when scroll modal (fix position issues)
            if (typeof tinymce !== 'undefined' && acf.isset(tinymce, 'ui', 'FloatPanel')) {

                $wrapper.find('.acfe-modal-content').off('scroll.tinymcePanel').on('scroll.tinymcePanel', function(e) {
                    tinymce.ui.FloatPanel.hideAll();
                });

            }

        },

        open: function() {

            this.$el.addClass('-open');

            popups.push(this);

            acfe.syncPopup();

            // Get sub fields
            var getSubFields = acf.getFields({
                parent: this.$el,
                visible: true,
            });

            // Show sub fields
            getSubFields.map(function(field) {
                acf.doAction('show_field', field, 'group');
            }, this);

            acf.doAction('acfe/modal/open', this.$el, this.data);

            this.get('onOpen').apply(this.$el);

        },

        close: function() {

            this.$('.acfe-modal-wrapper-overlay').remove();
            this.$('.acfe-modal-title').remove();
            this.$('.acfe-modal-footer').remove();

            this.$el.removeAttr('style');
            this.$el.removeClass('-open');

            acfe.syncPopup();

            acf.doAction('acfe/modal/close', this.$el, this.data);

            this.get('onClose').apply(this.$el);

            this.remove();

            if (this.get('destroy')) {
                this.$el.remove();
            }

        },

        remove: function() {

            this.removeEvents();
            this.removeActions();
            this.removeFilters();

        },

        onClickClose: function(e) {

            e.preventDefault();

            if (!popups.length) {
                return false;
            }

            popups.pop().close();

        }

    });

    /*
     * Popup: Close
     */
    acfe.closePopup = function() {

        if (!popups.length) {
            return false;
        }

        popups.pop().close();

    };

    /*
     * Popup: Sync
     */
    acfe.syncPopup = function() {

        var $body = $('body');

        if (popups.length) {

            // Prepare Body
            if (!$body.hasClass('acfe-modal-opened')) {

                $body.addClass('acfe-modal-opened').append($('<div class="acfe-modal-overlay" />'));

                $('.acfe-modal-overlay').on('click', function(e) {

                    e.preventDefault();
                    acfe.closePopup();

                });

            }

            // Prepare Multiple
            popups.map(function(self, i) {

                if (i === popups.length - 1) {
                    return self.$el.removeClass('acfe-modal-sub').css('margin-left', '');
                }

                self.$el.addClass('acfe-modal-sub').css('margin-left', -(500 / (i + 1)));

            });

        } else {

            $('.acfe-modal-overlay').remove();
            $body.removeClass('acfe-modal-opened');

        }

    };

    $(window).on('keydown', function(e) {

        if (e.keyCode !== 27 || !$('body').hasClass('acfe-modal-opened')) {
            return;
        }

        e.preventDefault();
        acfe.closePopup();

    });

    // Compatibility
    acfe.modal = {

        open: function($modal, args) {
            new acfe.Popup($modal, args);
        },

        close: function() {
            acfe.closePopup();
        }

    };

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
            var title = $el.attr('data-acfe-modal-title') || false;
            var footer = $el.attr('data-acfe-modal-footer') || false;

            // find next modal div
            if (!target) {
                target = $el.parent().find('.acfe-modal').first();
            }

            if (target instanceof jQuery) {
                // do nothing
            } else {
                target = $('.acfe-modal[data-acfe-modal=' + target + ']');
            }

            if (!target.length) {
                return;
            }

            var args = {
                size: size
            }

            if (title) {
                args.title = title;
            }

            if (footer) {
                args.footer = footer;
            }

            new acfe.Popup(target, args);

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Tooltip
     */
    new acf.Model({

        tooltip: false,

        events: {
            'click .acfe-field-tooltip': 'showTitle',
        },

        showTitle: function(e, $el) {

            // vars
            var title = $el.attr('title');

            // bail ealry if no title
            if (!title) {
                return;
            }

            // clear title to avoid default browser tooltip
            $el.attr('title', '');

            // create
            if (!this.tooltip) {
                this.tooltip = acf.newTooltip({
                    text: title,
                    target: $el
                });

                // update
            } else {
                this.tooltip.update({
                    text: title,
                    target: $el
                });
            }

        }

    });

})(jQuery);