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
        
        // Get Categories
        $(layouts).find('li a span').each(function(){
            
            var $link = $(this);

            if(!$link.data('acfe-flexible-category'))
                return true;
            
            var category = $link.data('acfe-flexible-category');
            
            if(categories.array.indexOf(category) != -1)
                return true;
            
            categories.array.push(category);
            
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
        
        // Modal: Click Categories
        $modal.find('.acfe-flexible-categories a').click(function(e){
            
            e.preventDefault();
            
            var $link = $(this);
            
            $link.closest('.acfe-flexible-categories').find('a').removeClass('nav-tab-active');
            $link.addClass('nav-tab-active');
            
            var selected_category = $link.data('acfe-flexible-category');
            
            $modal.find('a[data-layout] span').each(function(){
                
                var $span = $(this);
                
                var current_category = $span.data('acfe-flexible-category');
                
                $span.closest('li').show();
                
                if(selected_category != 'acfe-all' && current_category != selected_category){
                    
                    $span.closest('li').hide();
                    
                }
                
            });
            
        });
        
        // Modal: Click Add Layout
        $modal.on('click', 'a[data-layout]', function(e){
            
            e.preventDefault();
            
            // Add layout
            var $layout_added = flexible.add({
                layout: $(this).data('layout'),
                before: $layout_source
            });
            
            // Close modal
            acfe.modal.close(true);
            
            if(!$layout_added)
                return;
            
            // Modal Edition: Open
            if(flexible.has('acfeFlexibleModalEdition')){
                
                $layout_added.find('> [data-action="acfe-flexible-modal-edit"]').trigger('click');
                
            }
            
            // Normal Edition: Open
            else{
                
                flexible.openLayout($layout_added);
                
            }
            
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