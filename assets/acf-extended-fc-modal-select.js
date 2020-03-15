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
    model.acfeModalSelect = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Validate
        if(!flexible.validateAdd())
            return false;
        
        // Layout
        var $layout_source = null;
        
        if($el.hasClass('acf-icon'))
            $layout_source = $el.closest('.layout');
        
        // Get Available Layouts
        var layouts = flexible.getPopupHTML();
        
        // Init Categories
        var categories = {
            array: [],
            html: ''
        };

        function SearchArray(element, array){

            var len = array.length,
                str = element.toString().toLowerCase();

            for(var i = 0; i < len; i++){
                if(array[i].toLowerCase() === str){
                    return i;
                }
            }

            return -1;

        }
        
        // Get Categories
        $(layouts).find('li a span').each(function(){
            
            var $link = $(this);

            if(!$link.data('acfe-flexible-category'))
                return true;

            var category = $link.data('acfe-flexible-category');

            $.each(category, function(i, c){

                if(SearchArray(c, categories.array) !== -1)
                    return true;

                categories.array.push(c);

            });
            
        });
        
        // Categories HTML
        if(categories.array.length){
        
            categories.array.sort();
                
            categories.html += '<h2 class="acfe-flexible-categories nav-tab-wrapper">';
            
            categories.html += '<a href="#" data-acfe-flexible-category="acfe-all" class="nav-tab nav-tab-active"><span class="dashicons dashicons-menu"></span></a>';
            
            $(categories.array).each(function(k, category){
                
                categories.html += '<a href="#" data-acfe-flexible-category="' + category + '" class="nav-tab">' + category + '</a>';
                
            });
            
            categories.html += '</h2>';
        
        }
        
        // Modal Title
        var $modal_title = 'Add Row';
        
        if(flexible.has('acfeFlexibleModalTitle'))
            $modal_title = flexible.get('acfeFlexibleModalTitle');
        
        // Create Modal
        var $modal = $('' + 
            '<div class="acfe-modal">' + 
            
                categories.html + 
                '<div class="acfe-flex-container">' + 
                    layouts +        
                '</div>' +
                
            '</div>'
        
        ).appendTo('body');
        
        // Open Modal
        var $modal = acfe.modal.open($modal, {
            title: $modal_title,
            size: 'full',
            destroy: true
        });
        
        // Modal: Columns
        if(flexible.has('acfeFlexibleModalCol'))
            $modal.find('.acfe-modal-content .acfe-flex-container').addClass('acfe-col-' + flexible.get('acfeFlexibleModalCol'));
        
        // Modal: ACF autofocus fix
        $modal.find('li:first-of-type a').blur();
        
        // count layouts
        var $layouts = flexible.$layouts();
        var countLayouts = function(name){
            
            return $layouts.filter(function(){
                return $(this).data('layout') === name;
            }).length;
            
        };
        
        $modal.find('a[data-layout]').each(function(){
            
            // vars
            var $a = $(this);
            var min = $a.data('min') || 0;
            var max = $a.data('max') || 0;
            var name = $a.data('layout') || '';
            var count = countLayouts( name );
            
            // max
            if(max && count >= max){
                $a.addClass('disabled');
                return;
            }
            
            // min
            if(min && count < min){
                
                // vars
                var required = min - count;
                var title = acf.__('{required} {label} {identifier} required (min {min})');
                var identifier = acf._n('layout', 'layouts', required);
                                    
                // translate
                title = title.replace('{required}', required);
                title = title.replace('{label}', name); // 5.5.0
                title = title.replace('{identifier}', identifier);
                title = title.replace('{min}', min);
                
                // badge
                $a.append('<span class="badge" title="' + title + '">' + required + '</span>');
                
            }
        
        });
        
        // Modal: Click Categories
        $modal.find('.acfe-flexible-categories a').click(function(e){
            
            e.preventDefault();
            
            var $link = $(this);
            
            $link.closest('.acfe-flexible-categories').find('a').removeClass('nav-tab-active');
            $link.addClass('nav-tab-active');
            
            var selected_category = $link.data('acfe-flexible-category');
            
            $modal.find('a[data-layout] span').each(function(){
                
                // Get span
                var $span = $(this);
                
                // Show All
                $span.closest('li').show();
                
                var category = $span.data('acfe-flexible-category');
                
                // Specific category
                if(selected_category !== 'acfe-all'){
                    
                    // Hide All
                    $span.closest('li').hide();

                    $.each(category, function(i, c){

                        if(selected_category.toLowerCase() === c.toLowerCase()){

                            $span.closest('li').show();

                            return false;

                        }

                    });
                    
                }
                
            });
            
        });
        
        // Modal: Click Add Layout
        $modal.on('click', 'a[data-layout]', function(e){
            
            e.preventDefault();
            
            // Close modal
            acfe.modal.close(true);
            
            // Add layout
            var $layout_added = flexible.add({
                layout: $(this).data('layout'),
                before: $layout_source
            });
            
        });
        
    }
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        if(!flexible.has('acfeFlexibleModal'))
            return;
        
        // Vars
        var $clones = flexible.$clones();
        
        if($clones.length <= 1)
            return;
        
        // Remove native ACF Tooltip action
        flexible.removeEvents({'click [data-name="add-layout"]': 'onClickAdd'});
        
        // Add ACF Extended Modal action
        flexible.addEvents({'click [data-name="add-layout"]': 'acfeModalSelect'});

    });
    
})(jQuery);