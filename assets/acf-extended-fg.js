(function($){
    
    if(typeof acf === 'undefined')
        return;

    /*
     * ACF Extended: 0.8.4.5
     * Field Flexible Content: Fix duplicated "layout_settings" & "layout_title"
     */
    acf.addAction('ready_field_object', function(field){
        
        // field_acfe_layout_abc123456_settings + field_acfe_layout_abc123456_title
        if(!field.get('key').startsWith('field_acfe_layout_'))
            return;
        
        field.delete();
        
    });

    /*
     * Field: WYSIWYG
     */
    var acfe_repeater_remove_primary_class = function(field){
        
        field.$('.acf-button').removeClass('button-primary');
        
    }
    
    acf.addAction('new_field/name=acfe_wysiwyg_toolbar_1', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_wysiwyg_toolbar_2', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_wysiwyg_toolbar_3', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_wysiwyg_toolbar_4', acfe_repeater_remove_primary_class);
    
    acf.addAction('new_field/name=acfe_meta', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_settings', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_validate', acfe_repeater_remove_primary_class);
    
    function acfe_get_google_map_from_field(field){
        
        return acf.getInstance(field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_preview'));
        
    }
    
    $(function(){

        /*
         * Field: Google Map
         */
        var map_event = false;
        
        acf.addAction('google_map_init', function(map, marker, field){
            
            if(field.get('name') !== 'acfe_google_map_preview')
                return;
            
            google.maps.event.addListener(map, 'zoom_changed', function(){
                
                var zoom = acf.getInstance(field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_zooms > .acf-input > .acf-fields > .acf-field-zoom'));
                
                map_event = true;
                
                zoom.val(map.getZoom());
                
                map_event = false;
                
            });
            
            google.maps.event.addListener(map, 'center_changed', function(){
                
                var $center_lat = field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-center_lat > .acf-input > ul > li:eq(0) input');
                var $center_lng = field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-center_lat > .acf-input > ul > li:eq(1) input');
                
                $center_lat.val(map.getCenter().lat()).change();
                $center_lng.val(map.getCenter().lng()).change();
                
            });
            
            google.maps.event.addListener(map, 'maptypeid_changed', function(){
                
                var map_type = acf.getInstance(field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_type'));
                
                map_type.val(map.getMapTypeId());
                
            });
            
        });
        
        // Height
        acf.addAction('new_field/name=height', function(field){
            
            field.on('input', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                if(isNaN(val))
                    val = 400;
                
                preview.$canvas().height(val);
                
            });
            
        });
        
        // Zoom
        acf.addAction('new_field/name=zoom', function(field){
            
            field.on('change', function(e){
                
                if(map_event)
                    return;
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setZoom(val);
                
            });
            
        });
        
        // Min: Zoom
        acf.addAction('new_field/name=min_zoom', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({minZoom:val});
                
            });
            
        });
        
        // Max: Zoom
        acf.addAction('new_field/name=max_zoom', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({maxZoom:val});
                
            });
            
        });
        
        // Marker Icon
        acf.addAction('new_field/name=acfe_google_map_marker_icon', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = field.val();
                
                if(val){
                    
                    var url = field.$('img').attr('src');
                    
                    var $height = field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_marker_height > .acf-input > ul > li:eq(0) input');
                    var $width = field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_marker_height > .acf-input > ul > li:eq(1) input');
                    
                    var height = parseInt($height.val());
                    var width = parseInt($width.val());
                    
                    preview.map.marker.setIcon({
                        url:        url,
                        size:       new google.maps.Size(width, height),
                        scaledSize: new google.maps.Size(width, height),
                    });
                    
                }else{
                    
                    preview.map.marker.setIcon();
                    
                }
                
            });
            
        });
        
        // Marker: Height
        var acfe_google_map_set_marker_size = function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var icon = preview.map.marker.getIcon();
                
                var $height = field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_marker_height > .acf-input > ul > li:eq(0) input');
                var $width = field.$el.closest('tbody.acf-field-settings').find('> .acf-field-setting-acfe_google_map_marker_height > .acf-input > ul > li:eq(1) input');
                
                var height = parseInt($height.val());
                var width = parseInt($width.val());
                
                preview.map.marker.setIcon({
                    url:        icon.url,
                    size:       new google.maps.Size(width, height),
                    scaledSize: new google.maps.Size(width, height),
                });
                
            });
            
        }
        
        acf.addAction('new_field/name=acfe_google_map_marker_height', acfe_google_map_set_marker_size);
        acf.addAction('new_field/name=acfe_google_map_marker_width', acfe_google_map_set_marker_size);
        
        // Map Type
        acf.addAction('new_field/name=acfe_google_map_type', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = field.val();
                
                preview.map.setOptions({mapTypeId:val});
                
            });
            
        });
        
        // View: Hide UI
        acf.addAction('new_field/name=acfe_google_map_disable_ui', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({disableDefaultUI:val});
                
                var disable_zoom = acf.getFields({
                    sibling: field.$el,
                    name: 'acfe_google_map_disable_zoom_control',
                    suppressFilters: true,
                });
                
                disable_zoom = disable_zoom[0];
                
                var disable_map_type = acf.getFields({
                    sibling: field.$el,
                    name: 'acfe_google_map_disable_map_type',
                    suppressFilters: true,
                });
                
                disable_map_type = disable_map_type[0];
                
                var disable_fullscreen = acf.getFields({
                    sibling: field.$el,
                    name: 'acfe_google_map_disable_fullscreen',
                    suppressFilters: true,
                });
                
                disable_fullscreen = disable_fullscreen[0];
                
                var disable_streeview = acf.getFields({
                    sibling: field.$el,
                    name: 'acfe_google_map_disable_streetview',
                    suppressFilters: true,
                });
                
                disable_streeview = disable_streeview[0];
                
                if(val){
                    
                    disable_zoom.switchOn();
                    disable_zoom.$input().change();
                    
                    disable_map_type.switchOn();
                    disable_map_type.$input().change();
                    
                    disable_fullscreen.switchOn();
                    disable_fullscreen.$input().change();
                    
                    disable_streeview.switchOn();
                    disable_streeview.$input().change();
                
                }else{
                    
                    disable_zoom.switchOff();
                    disable_zoom.$input().change();
                    
                    disable_map_type.switchOff();
                    disable_map_type.$input().change();
                    
                    disable_fullscreen.switchOff();
                    disable_fullscreen.$input().change();
                    
                    disable_streeview.switchOff();
                    disable_streeview.$input().change();
                    
                }
                
            });
            
        });
        
        // View: Hide Zoom Control
        acf.addAction('new_field/name=acfe_google_map_disable_zoom_control', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({zoomControl:!val});
                preview.map.setOptions({scrollwheel:!val});
                
            });
            
        });
        
        // View: Hide Map Selection
        acf.addAction('new_field/name=acfe_google_map_disable_map_type', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({mapTypeControl:!val});
                
            });
            
        });
        
        // View: Hide Fullscreen
        acf.addAction('new_field/name=acfe_google_map_disable_fullscreen', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({fullscreenControl:!val});
                
            });
            
        });
        
        // View: Hide Streetview
        acf.addAction('new_field/name=acfe_google_map_disable_streetview', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = parseInt(field.val());
                
                preview.map.setOptions({streetViewControl:!val});
                
            });
            
        });
        
        // View: Map Style
        acf.addAction('new_field/name=acfe_google_map_style', function(field){
            
            field.on('change', function(e){
                
                var preview = acfe_get_google_map_from_field(field);
                
                var val = field.val();
                
                try{
                    
                    var json = $.parseJSON(val);
                    
                }catch(err){
                    
                    var json = false
                    
                }
                
                if(!val || val.trim().length === 0 || !json){
                    
                    preview.map.setOptions({styles:''});
                    
                    return;
                    
                }
                
                preview.map.setOptions({styles:json});
                
            });
            
        });

        /*
         * Field Setting: Data
         */
        $('.button.edit-field').each(function(k, v){
            
            var tbody = $(this).closest('tbody');
            $(tbody).find('.acfe_modal_open:first').insertAfter($(this));
            $(tbody).find('.acfe-modal:first').appendTo($('body'));
            $(tbody).find('tr.acf-field-setting-acfe_field_data:first').remove();
            
        });
        
        $('.acfe_modal_open').click(function(e){
            
            e.preventDefault();
            
            var key = $(this).attr('data-modal-key');
            
            var $modal = $('.acfe-modal[data-modal-key=' + key + ']');
            
            acfe.modal.open($modal, {
                title: 'Data',
                size: 'medium'
            });
            
        });

        /*
         * Field Group: Advanced Settings
         */
        $('.acf-field[data-name="active"]').after($('.acf-field[data-name="acfe_form"]'));
    
    });
    
})(jQuery);