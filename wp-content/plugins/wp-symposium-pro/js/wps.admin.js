jQuery(document).ready(function() {

    jQuery('.wps_admin_fav').click(function(e) {
        if(  e.offsetX <= 15 ) {
            var classes_str = jQuery(this).attr('class');
            var classes = classes_str.split(" ");
            var classes_length = classes.length;
            var classes_item = classes[2];
            if (classes_length > 3) {
                classes_item = classes[3];
            } else {
            }
            var item = classes_item.replace("wps_fav_", "");
            jQuery.post(
                wps_ajax.ajaxurl,
                {
                    item : item,
                    action : 'wps_toggle_main_menu',
                },
                function(response) {
                    location.reload();
                }   
            );              
        }        
    });

    jQuery('#wps_hide_welcome_header').click(function() {
        if(jQuery('#wps_welcome').css('display') == 'block') {
            jQuery('#wps_welcome').slideUp();
        } else {
            jQuery('#wps_welcome').slideDown();
        }
        jQuery.post(
            wps_ajax.ajaxurl,
            {
                action : 'wps_hide_welcome_header_toggle',
            },
            function(response) {}   
        );         
    });

    jQuery('#wps_hide_admin_links').click(function() {
        jQuery.post(
            wps_ajax.ajaxurl,
            {
                action : 'wps_hide_admin_links_toggle',
            },
            function(response) {
                location.reload();
            }   
        );         
    });
    jQuery('#wps_hide_admin_links_show').click(function() {
        jQuery.post(
            wps_ajax.ajaxurl,
            {
                action : 'wps_hide_admin_links_toggle',
            },
            function(response) {
                location.reload();
            }   
        );         
    });

    jQuery('#wps_forum_featured_upload_image_button').click(function() {
        formfield = jQuery('#wps_forum_featured_upload_image').attr('name');
        tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        return false;
    });

    if (jQuery("#wps_forum_featured_upload_image_button").length) {
        window.send_to_editor = function(html) {
            var imgurl = jQuery('img', html).attr('src');
            if (typeof imgurl === "undefined") imgurl = jQuery(html).attr('src');
            jQuery('#wps_forum_featured_upload_image').val(imgurl);
            tb_remove();
        }
    }

    // Un-hide shortcode options once page is ready
    if (jQuery('#wps_admin_getting_started_options_left_and_middle').length) {
        jQuery('#wps_admin_getting_started_options_please_wait').hide();
        jQuery('#wps_admin_getting_started_options_left_and_middle').show();
        jQuery('#wps_admin_getting_started_options_right').show();
    }

    // Save shortcode options
    jQuery('#wps_shortcode_options_save_submit').click(function () {

        // Find shortcode that's showing (to save)
        var wps_expand_shortcode = '';
        jQuery('.wps_admin_getting_started_option_shortcode').each(function(i, obj) {
            if (jQuery(this).hasClass('wps_admin_getting_started_active')) {
                wps_expand_shortcode = jQuery(this).attr('rel');
            }
        });
        
        if (wps_expand_shortcode != '') {   

            jQuery('#'+wps_expand_shortcode)
                .css('opacity', '0.2')
                .css('color', '#9f9f9f')
                .css('background', '#efefef');

            var this_obj = this;
            jQuery(this_obj).addClass('admin_button_please_wait');
            jQuery('.spinner').addClass('is-active');
            jQuery(this_obj).hide();
            
            var arr = [];
            var c = 0;
            jQuery('#'+wps_expand_shortcode+' input').each(function(i, obj) {
                var name = jQuery(this).attr('name');
                var type = jQuery(this).attr('type');
                if (type != 'checkbox') {
                    var val = jQuery(this).val();
                } else {
                    if (jQuery(this).is(":checked")) {
                        var val = 'on';
                    } else {
                        var val = 'off';
                    }
                }
                arr.push([name,type,val]);
            });
            jQuery('#'+wps_expand_shortcode+' select').each(function(i, obj) {
                var name = jQuery(this).attr('name');
                var type = 'select';
                var val = jQuery(this).val();
                arr.push([name,type,val]);
            });          

            jQuery.post(
                wps_ajax.ajaxurl,
                {
                    action : 'wps_shortcode_options_save',
                    data: {arr: arr},
                },
                function(response) {
                    if (response != '') { alert(response) };
                    
                    jQuery('#'+wps_expand_shortcode)
                        .css('opacity', '1.0')
                        .css('color', '#000')                    
                        .css('background', '#fff');

                    jQuery(this_obj).removeClass('admin_button_please_wait');
                    jQuery('.spinner').removeClass('is-active');
                    jQuery(this_obj).show();
                    
                }   
            ); 
            

        } else {
                                                                       
            alert('Error saving shortcode options.');
            
        }
        
        
    });
                                         
    
	// Remember which admin section to show after saving
    jQuery('#wps_setup').submit(function () {
        
        // Sections
    	var wps_expand = '';
		jQuery('.wps_admin_getting_started_content').each(function(i, obj) {
		    if (jQuery(this).css('display') != 'none') {
		    	wps_expand = jQuery(this).attr('id');
		    }
		});

		var input = jQuery("<input>")
		               .attr("type", "hidden")
		               .attr("name", "wps_expand").val(wps_expand);

		jQuery('#wps_setup').append(jQuery(input));
            
    });
    
    // Show default settings tab
    jQuery('.wps_admin_getting_started_option').click(function() {
        jQuery('.wps_admin_getting_started_option_shortcode').hide();
        jQuery('.wps_admin_getting_started_option_value').hide();
        jQuery('#wps_admin_getting_started_options_right').hide();
        jQuery('.wps_setup_submit_options').hide();
        //jQuery('#wps_admin_getting_started_options_help').slideUp('slow');
        jQuery('.wps_admin_getting_started_option').removeClass('wps_admin_getting_started_active');
        jQuery('.wps_admin_getting_started_option_shortcode').removeClass('wps_admin_getting_started_active');
        jQuery(this).addClass('wps_admin_getting_started_active');
        var tab = jQuery(this).data('shortcode');
        jQuery('.wps_'+tab).show();
    });
    jQuery('.wps_admin_getting_started_option_shortcode').click(function() {
        jQuery('.wps_admin_getting_started_option_value').hide();
        jQuery('#wps_admin_getting_started_options_right').show();   
        jQuery('.wps_setup_submit_options').show();        
        jQuery('.wps_admin_getting_started_option_shortcode').removeClass('wps_admin_getting_started_active');
        jQuery(this).addClass('wps_admin_getting_started_active');
        var tab = jQuery(this).data('tab');
        jQuery('#'+tab).show();
    });

    // Show/Hide shortcode help
    jQuery('#wps_show_shortcodes_show').click(function() {
        jQuery('table.wps_shortcode_value_row tr td:nth-child(3)').show();
        jQuery('#wps_show_shortcodes_hide').fadeIn('fast');
        jQuery(this).hide();
    });
    jQuery('#wps_show_shortcodes_hide').click(function() {
        jQuery('table.wps_shortcode_value_row tr td:nth-child(3)').hide();
        jQuery('#wps_show_shortcodes_show').fadeIn('fast');
        jQuery(this).hide();
    });
        
    // Scroll to previous section after save
    if (jQuery('#wps_expand').length) {
        var e = '#'+jQuery('#wps_expand').val();
        jQuery('html, body').animate({
                scrollTop: jQuery(e).offset().top-100
            }, 0); // Increase 0 to scroll, higher = slower
    }

	// Show content on menu click
	jQuery(".wps_admin_getting_started_menu_item").click(function (event) {
		// Tidy up
        jQuery('.wps_admin_getting_started_menu_item').removeClass('wps_admin_getting_started_menu_item_remove_icon');
		var t = jQuery(this);
		if (jQuery('#'+t.attr('rel')).css('display') == 'none') {
			jQuery(".wps_admin_getting_started_content").css('opacity', '0.2').slideUp('slow');		
			jQuery('#'+t.attr('rel')).css('opacity', '1.0').slideDown('slow');
            t.addClass('wps_admin_getting_started_menu_item_remove_icon');
		} else {
			jQuery('#'+t.attr('rel')).css('opacity', '0.2').css('border-left', '1px solid #000').css('border-right', '1px solid #000').css('border-bottom', '1px solid #000');
			jQuery('#'+t.attr('rel')).slideUp('slow');
		}
	});
    
    // Color Picker
    if (jQuery('.wps-color-picker').length) {
        jQuery('.wps-color-picker').wpColorPicker();
    }    

    // Show shortcode tip
    if (jQuery(".wps_shortcode_tip_available").length) {
        jQuery(".wps_shortcode_tip_available").click(function (event) {
            jQuery(this).parent().parent().nextAll('#wps_shortcode_tip').first().fadeIn('slow').fadeOut('fast').fadeIn('slow');
        });
    }
    
});
