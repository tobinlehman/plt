jQuery(document).ready(function() {

    // Join site (Multisite only)
	jQuery("#wps_join_site").click(function (event) {
        jQuery.post(
            wps_usermeta.ajaxurl,
            {
                action : 'wps_add_to_site',
            },
            function(response) {
                window.location.href=(response);
            }   
        );       
    });

    // ... click required fields message when one is focussed on
    jQuery(".wps_mandatory_field").click(function (event) {
        jQuery('#wps_required_msg').slideUp('slow');
    });

    // Strength meter (on Edit Profile)
    if(jQuery("#wps_password_strength_result").length > 0) {
        jQuery("#wpspro_password").bind("keyup", function(){
            var pass1 = jQuery("#wpspro_password").val();
            var pass2 = jQuery("#wpspro_password2").val();
            var strength = passwordStrength(pass1, 'admin', pass2);
            wps_updateStrength(strength);
            if (pass1 == '' && pass2 == '') { jQuery("#wps_password_strength_result").hide(); }
        });
        jQuery("#wpspro_password2").bind("keyup", function(){
            var pass1 = jQuery("#wpspro_password").val();
            var pass2 = jQuery("#wpspro_password2").val();
            var strength = passwordStrength(pass1, 'admin', pass2);
            wps_updateStrength(strength);
            if (pass1 == '' && pass2 == '') { jQuery("#wps_password_strength_result").hide(); }
        });
    };
    
    // submit Edit Profile
	jQuery( "#wps_usermeta_change" ).submit(function( event ) {

        // ... first change for mandatory fields
        var all_filled = true;
        // ... but first clear any previous highlights
        // ... and then add if necessary
        jQuery('.wps_mandatory_field').each(function(i, obj) {
            if (jQuery(this).val().trim() == '') {
                if (jQuery('#s2id_'+jQuery(this).attr('id')).length > 0) {
                    jQuery('#s2id_'+jQuery(this).attr('id')).addClass('wps_field_error');
                } else {
                    jQuery(this).addClass('wps_field_error');
                    jQuery(this).val(''); // in case spaces entered, remove them
                }
                all_filled = false;
                // highlight the tab
                var tab_div = jQuery(this).closest('.wps-tab');
                jQuery('#wps-'+jQuery(tab_div).attr('id')).addClass('wps_field_error');
            } else {
                if (jQuery('#s2id_'+jQuery(this).attr('id')).length > 0) {
                    jQuery('#s2id_'+jQuery(this).attr('id')).removeClass('wps_field_error');
                } else {
                    jQuery(this).removeClass('wps_field_error');
                }
                // remove highlight from the tab
                var tab_div = jQuery(this).closest('.wps-tab');
                jQuery('#wps-'+jQuery(tab_div).attr('id')).removeClass('wps_field_error');
            }
        });

        if (all_filled) {

            // ... check passwords match (if entered)
    	  	if (jQuery('#wpspro_password').length) {
    			if (jQuery('#wpspro_password').val() != jQuery('#wpspro_password2').val()) {
    				jQuery('#wpspro_password').addClass('wps_field_error');			
    				jQuery('#wpspro_password2').addClass('wps_field_error');		
                    jQuery('#wps_required_msg').slideDown('fast');
    				event.preventDefault();
                    // highlight the tab
                    var tab_div = jQuery('#wpspro_password').closest('.wps-tab');
                    jQuery('#wps-'+jQuery(tab_div).attr('id')).addClass('wps_field_error');
    			} else {                
    				jQuery('#wpspro_password').removeClass('wps_field_error');			
    				jQuery('#wpspro_password2').removeClass('wps_field_error');		
                    // remove highlight from the tab
                    var tab_div = jQuery('#wpspro_password').closest('.wps-tab');
                    jQuery('#wps-'+jQuery(tab_div).attr('id')).removeClass('wps_field_error');
                }
    		}

        } else {

            jQuery('#wps_required_msg').slideDown('fast');
            event.preventDefault();

        }

	});

	// wps_user_button

	jQuery(".wps_user_button").click(function (event) {

		var url = jQuery(this).attr('rel');		
		event.preventDefault();

		window.location = url;

	});
    
    // wps_close_account
    
    jQuery('#wps_close_account').click(function (event) {
       
        var answer = confirm(jQuery(this).data('sure'));
        if (answer) {
            jQuery.post(
                wps_usermeta.ajaxurl,
                {
                    action : 'wps_deactivate_account',
                    user_id: jQuery(this).data('user'),
                },
                function(response) {
                    alert(jQuery('#wps_close_account').data('logout'));
                    var url = jQuery('#wps_close_account').data('url');
                    if (url) {
                        window.location = url;
                    } else {
                        location.reload();
                    }
                }   
            );
        }
    });

    // Edit Profile Page Tabs
    jQuery('.wps-tabs .wps-tab-links a').on('click', function(e)  {
        var currentAttrValue = jQuery(this).attr('href');
         
        // Show/Hide Tabs
        if (wps_usermeta.animation == 'fade')
            jQuery('.wps-tabs ' + currentAttrValue).fadeIn(800).siblings().hide();
        if (wps_usermeta.animation == 'slide')
            jQuery('.wps-tabs ' + currentAttrValue).slideDown(800).siblings().slideUp(800);
        if (wps_usermeta.animation == 'none')
            jQuery('.wps-tabs ' + currentAttrValue).show().siblings().hide();
 
        // Change/remove current tab to active
        jQuery(this).parent('li').addClass('active').siblings().removeClass('active');
 
        e.preventDefault();
    });

    // Language list
    if (jQuery("#wpspro_lang").length) {
        jQuery("#wpspro_lang").select2({ minimumResultsForSearch: -1 });
    };


});

function wps_updateStrength(strength){
    var status = new Array('wps_score_1', 'wps_score_2', 'wps_score_3', 'wps_score_4', 'wps_score_5');
    var dom = jQuery("#wps_password_strength_result");
    switch(strength){
    case 1:
      dom.removeClass().show().addClass(status[0]).text(wps_usermeta.score1);
      break;
    case 2:
      dom.removeClass().show().addClass(status[1]).text(wps_usermeta.score2);
      break;
    case 3:
      dom.removeClass().show().addClass(status[2]).text(wps_usermeta.score3);
      break;
    case 4:
      dom.removeClass().show().addClass(status[3]).text(wps_usermeta.score4);
      break;
    case 5:
      dom.removeClass().show().addClass(status[4]).text(wps_usermeta.score5);
      break;
    default:
      dom.removeClass().show().addClass(status[0]).text(wps_usermeta.score1);
      break;
    }
}

