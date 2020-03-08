(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;
    
    /*
     * Actions
     */
    /*
     * Actions
     */
    model.events['click .acf-fc-layout-handle'] = 'acfeEditLayoutTitleToggleHandle';
    model.acfeEditLayoutTitleToggleHandle = function(e, $el){
        
        var flexible = this;
        
        // Title Edition
        if(!flexible.has('acfeFlexibleTitleEdition'))
            return;
        
        // Vars
        var $layout = $el.closest('.layout');
        
        if($layout.hasClass('acfe-flexible-title-edition')){
            
            $layout.find('> .acf-fc-layout-handle > .acfe-layout-title > input.acfe-flexible-control-title').trigger('blur');
            
        }
        
    }
    
    model.events['click .acfe-layout-title-text'] = 'acfeEditLayoutTitle';
    model.acfeEditLayoutTitle = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Title Edition
        if(!flexible.has('acfeFlexibleTitleEdition'))
            return;
        
        // Stop propagation
        e.stopPropagation();
        
        // Toggle
        flexible.acfeEditLayoutTitleToggle(e, $el);
        
    }
    
    model.events['blur input.acfe-flexible-control-title'] = 'acfeEditLayoutTitleToggle';
    model.acfeEditLayoutTitleToggle = function(e, $el){
        
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        var $title = $handle.find('.acfe-layout-title');
        
        if($layout.hasClass('acfe-flexible-title-edition')){
            
            var $input = $title.find('> input[data-acfe-flexible-control-title-input]');
            
            if($input.val() === '')
                $input.val($input.attr('placeholder')).trigger('input');
            
            $layout.removeClass('acfe-flexible-title-edition');
            
            $input.insertAfter($handle);
            
        }
        
        else{
            
            var $input = $layout.find('> input[data-acfe-flexible-control-title-input]');
            
            var $input = $input.appendTo($title);
            
            $layout.addClass('acfe-flexible-title-edition');
            $input.focus().attr('size', $input.val().length);
            
        }
        
    }
    
    // Layout: Edit Title
    model.events['click input.acfe-flexible-control-title'] = 'acfeEditLayoutTitlePropagation';
    model.acfeEditLayoutTitlePropagation = function(e, $el){
        
        e.stopPropagation();
        
    }
    
    // Layout: Edit Title Input
    model.events['input [data-acfe-flexible-control-title-input]'] = 'acfeEditLayoutTitleInput';
    model.acfeEditLayoutTitleInput = function(e, $el){
        
        // Vars
        var $layout = $el.closest('.layout');
        var $title = $layout.find('> .acf-fc-layout-handle .acfe-layout-title .acfe-layout-title-text');
        
        var val = $el.val();
        
        $el.attr('size', val.length);
        
        $title.html(val);
        
    }
    
    // Layout: Edit Title Input Enter
    model.events['keypress [data-acfe-flexible-control-title-input]'] = 'acfeEditLayoutTitleInputEnter';
    model.acfeEditLayoutTitleInputEnter = function(e, $el){
        
        // Enter Key
        if(e.keyCode !== 13)
            return;
        
        e.preventDefault();
        $el.blur();
        
    }
    
    // Layout: Settings
    model.events['click [data-acfe-flexible-settings]'] = 'acfeLayoutSettings';
    model.acfeLayoutSettings = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout');
        
        // Modal data
        var $modal = $layout.find('> .acfe-modal.-settings');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        
        var $layout_order = $handle.find('> .acf-fc-layout-order').outerHTML();
        var $layout_title = $handle.find('.acfe-layout-title-text').text();
        
        // Open modal
        acfe.modal.open($modal, {
            title: $layout_order + ' ' + $layout_title,
            footer: acf.__('Close'),
            onOpen: function(){
                
                flexible.acfeEditorsInit($layout);
                
            },
            onClose: function(){
                
                if(flexible.has('acfeFlexiblePreview')){
                    
                    flexible.closeLayout($layout);
                    
                }
                
            }
        });
        
    }
    
    // Layout: Clone
    model.events['click [data-acfe-flexible-control-clone]'] = 'acfeCloneLayout';
    model.acfeCloneLayout = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout');
        var layout_name = $layout.data('layout');
        
        // Popup min/max
        var $popup = $(flexible.$popup().html());
        var $layouts = flexible.$layouts();

        var countLayouts = function(name){
            return $layouts.filter(function(){
                return $(this).data('layout') === name;
            }).length;
        };
        
         // vars
        var $a = $popup.find('[data-layout="' + layout_name + '"]');
        var min = $a.data('min') || 0;
        var max = $a.data('max') || 0;
        var count = countLayouts(layout_name);
        
        // max
        if(max && count >= max){
            
            $el.addClass('disabled');
            return false;
            
        }else{
            
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
    model.acfeCopyLayout = function(e, $el){
        
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
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:rect(0,0,0,0);position:absolute;" value="" />').appendTo($el);
        $input.attr('value', data).select();
        
        // Command: Copy
        if(document.execCommand('copy'))
            alert('Layout has been transferred to your clipboard');
            
        // Prompt
        else
            prompt('Copy the following layout data to your clipboard', data);
        
        // Remove the temp input
        $input.remove();
        
    }
    
    // Flexible: Copy Layouts
    model.acfeCopyLayouts = function(){
        
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
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:rect(0,0,0,0);position:absolute;" value="" />').appendTo(flexible.$el);
        $input.attr('value', data).select();
        
        // Command: Copy
        if(document.execCommand('copy'))
            alert('Layouts have been transferred to your clipboard');
            
        // Prompt
        else
            prompt('Copy the following layouts data to your clipboard', data);
        
        $input.remove();
        
    }
    
    // Flexible: Paste Layouts
    model.acfePasteLayouts = function(){
        
        // Get Flexible
        var flexible = this;
        
        var paste = prompt('Paste layouts data in the following field');
        
        // No input
        if(paste == null || paste === '')
            return;
        
        try{
            
            // Paste HTML
            var data = JSON.parse(paste);
            var source = data.source;
            var $html = $(data.layouts);
            
            // Parsed layouts
            var $html_layouts = $html.closest('[data-layout]');
            
            if(!$html_layouts.length)
                return alert('No layouts data available');
            
            // Popup min/max
            var $popup = $(flexible.$popup().html());
            var $layouts = flexible.$layouts();
            
            var countLayouts = function(name){
                return $layouts.filter(function(){
                    return $(this).data('layout') === name;
                }).length;
            };
            
            // init
            var validated_layouts = [];
            
            // Each first level layouts
            $html_layouts.each(function(){
                
                var $this = $(this);
                var layout_name = $this.data('layout');
                
                // vars
                var $a = $popup.find('[data-layout="' + layout_name + '"]');
                var min = $a.data('min') || 0;
                var max = $a.data('max') || 0;
                var count = countLayouts(layout_name);
                
                // max
                if(max && count >= max)
                    return;
                
                // Validate layout against available layouts
                var get_clone_layout = flexible.$clone($this.attr('data-layout'));
                
                // Layout is invalid
                if(!get_clone_layout.length)
                    return;
                
                // Add validated layout
                validated_layouts.push($this);
                
            });
            
            // Nothing to add
            if(!validated_layouts.length)
                return alert('No layouts could be pasted');
            
            // Add layouts
            $.each(validated_layouts, function(){
                
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
            
        }catch(e){
            
            console.log(e);
            alert('Invalid data');
            
        }
        
    }
    
    // Flexible: Dropdown
    model.events['click [data-name="acfe-flexible-control-button"]'] = 'acfeControl';
    model.acfeControl = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $dropdown = $el.next('.tmpl-acfe-flexible-control-popup').html();
        
        // Init Popup
        var Popup = acf.models.TooltipConfirm.extend({
            render: function(){
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
            confirm: function(e, $el){
                
                if($el.attr('data-acfe-flexible-control-action') === 'paste')
                    flexible.acfePasteLayouts();
                
                else if($el.attr('data-acfe-flexible-control-action') === 'copy')
                    flexible.acfeCopyLayouts();
                
            }
        });
        
        popup.on('click', 'a', 'onConfirm');
        
    }
    
    // Flexible: Duplicate
    model.acfeDuplicate = function(args){
        
        // Arguments
        args = acf.parseArgs(args, {
            layout: '',
            before: false,
            parent: false,
            search: '',
            replace: '',
        });
        
        // Validate
        if(!this.allowAdd())
            return false;
        
        var uniqid = acf.uniqid();
        
        if(args.parent){
            
            if(!args.search){
                
                args.search = args.parent + '[' + args.layout.attr('data-id') + ']';
                
            }
            
            args.replace = args.parent + '[' + uniqid + ']';
            
        }
        
        // Add row
        var $el = acf.duplicate({
            target: args.layout,
            search: args.search,
            replace: args.replace,
            append: this.proxy(function($el, $el2){
                
                // Add class to duplicated layout
                $el2.addClass('acfe-layout-duplicated');
                
                // Reset UniqID
                $el2.attr('data-id', uniqid);
                
                // append before
                if(args.before){
                    
                    // Fix clone: Use after() instead of native before()
                    args.before.after($el2);
                    
                }
                
                // append end
                else{
                    
                    this.$layoutsWrap().append($el2);
                    
                }
                
                // enable 
                acf.enable($el2, this.cid);
                
                // render
                this.render();
                
            })
        });
        
        // trigger change for validation errors
        this.$input().trigger('change');

        // Fix tabs conditionally hidden
        var tabs = acf.getFields({
            type: 'tab',
            parent: $el,
        });

        if(tabs.length){

            $.each(tabs, function(){

                if(this.$el.hasClass('acf-hidden')){

                    this.tab.$el.addClass('acf-hidden');

                }

            });

        }

        
        // return
        return $el;
        
    }
    
    // Flexible: Fix Inputs
    model.acfeFixInputs = function($layout){
        
        $layout.find('input').each(function(){
            
            $(this).attr('value', this.value);
            
        });
        
        $layout.find('textarea').each(function(){
            
            $(this).html(this.value);
            
        });
        
        $layout.find('input:radio,input:checkbox').each(function() {
            
            if(this.checked)
                $(this).attr('checked', 'checked');
            
            else
                $(this).attr('checked', false);
            
        });
        
        $layout.find('option').each(function(){
            
            if(this.selected)
                $(this).attr('selected', 'selected');
                
            else
                $(this).attr('selected', false);
            
        });
        
    }
    
    // Flexible: Clean Layout
    model.acfeCleanLayouts = function($layout){
        
        // Clean WP Editor
        $layout.find('.acf-editor-wrap').each(function(){
            
            var $input = $(this);
            
            $input.find('.wp-editor-container div').remove();
            $input.find('.wp-editor-container textarea').css('display', '');
            
        });
        
        // Clean Date
        $layout.find('.acf-date-picker').each(function(){
            
            var $input = $(this);
            
            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
            
        });
        
        // Clean Time
        $layout.find('.acf-time-picker').each(function(){
            
            var $input = $(this);
            
            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
            
        });
        
        // Clean DateTime
        $layout.find('.acf-date-time-picker').each(function(){
            
            var $input = $(this);
            
            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
            
        });
        
        // Clean Color Picker
        $layout.find('.acf-color-picker').each(function(){
            
            var $input = $(this);
            
            var $color_picker = $input.find('> input');
            var $color_picker_proxy = $input.find('.wp-picker-container input.wp-color-picker').clone();
            
            $color_picker.after($color_picker_proxy);
            
            $input.find('.wp-picker-container').remove();
            
        });
        
        // Clean Post Object
        $layout.find('.acf-field-post-object').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
            
        });
        
        // Clean Page Link
        $layout.find('.acf-field-page-link').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
            
        });
        
        // Clean Select2
        $layout.find('.acf-field-select').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
            
        });
        
        // Clean FontAwesome
        $layout.find('.acf-field-font-awesome').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden');
            
        });


        // Clean Tab
        $layout.find('.acf-tab-wrap').each(function(){
            
            var $wrap = $(this);
            
            var $content = $wrap.closest('.acf-fields');
            
            var tabs = [];
            $.each($wrap.find('li a'), function(){
                
                tabs.push($(this));
                
            });
            
            $content.find('> .acf-field-tab').each(function(){
                
                $current_tab = $(this);
                
                $.each(tabs, function(){
                    
                    var $this = $(this);
                    
                    if($this.attr('data-key') !== $current_tab.attr('data-key'))
                        return;
                    
                    $current_tab.find('> .acf-input').append($this);
                    
                });
                
            });
            
            $wrap.remove();
            
        });
        
        // Clean Accordion
        $layout.find('.acf-field-accordion').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-accordion-title > .acf-accordion-icon').remove();
            
            // Append virtual endpoint after each accordion
            $input.after('<div class="acf-field acf-field-accordion" data-type="accordion"><div class="acf-input"><div class="acf-fields" data-endpoint="1"></div></div></div>');
            
        });
        
    }
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        // ACFE: Lock
        if(flexible.has('acfeFlexibleLock')){
            
            flexible.removeEvents({'mouseover': 'onHover'});
            
        }
        
    });
    
})(jQuery);