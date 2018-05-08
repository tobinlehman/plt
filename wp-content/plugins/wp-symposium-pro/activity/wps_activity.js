jQuery(document).ready(function() {

	jQuery('#wps_activity_items').show();
	jQuery('#wps_activity_post_div').show();
	jQuery('.wps_activity_settings').show();
	jQuery('#wps_activity_post_button').attr("disabled", false);

	// Get activity on page first load
	if (jQuery("#wps_activity_ajax_div").length) {
        if (!jQuery('#wps_activity_post_private_msg').length) {
		  wps_get_ajax_activity(0,jQuery('#wps_page_size').html(),'replace');
        }
	}
    
    // Get more activity posts
    jQuery('body').on('click', '#wps_activity_load_more', function() {    
        var start = jQuery(this).data('count');
        jQuery('#wps_activity_load_more_div').remove();
        jQuery('#wps_activity_ajax_div').after('<div id="wps_tmp" style="width:100%;text-align:center;"><img style="width:20px;height:20px;margin-top:-3px;" src="'+jQuery('#wps_wait_url').html()+'" /></div>');
        wps_get_ajax_activity(start+1,jQuery('#wps_page_size').html(),'append');
    });
    
	// Activity Settings
    jQuery('body').on('hover', '.wps_activity_content', function() {
        jQuery('.wps_activity_settings').hide();
        jQuery('.wps_comment_settings').hide();
        jQuery('.wps_comment_settings_options').hide();
        jQuery(this).children('.wps_activity_settings').show();
	});
    jQuery('body').on('click', '.wps_activity_settings', function() {
		jQuery('.wps_activity_settings_options').hide();
		jQuery('.wps_comment_settings_options').hide();
		jQuery(this).next('.wps_activity_settings_options').show();
	});

	// Comment Settings
    jQuery('body').on('hover', '.wps_activity_comment', function() {
        jQuery('.wps_comment_settings').hide();
        jQuery(this).children('.wps_comment_settings').show();
	});
    jQuery('body').on('click', '.wps_comment_settings', function() {
        jQuery('.wps_comment_settings').hide();
		jQuery('.wps_activity_settings_options').hide();
		jQuery('.wps_comment_settings_options').hide();
		jQuery(this).next('.wps_comment_settings_options').show();
	});    

	jQuery(document).mouseup(function (e) {
		jQuery('.wps_activity_settings_options').hide();
        jQuery('.wps_comment_settings').hide();
		jQuery('.wps_comment_settings_options').hide();
	});


	// Add activity post
	if (jQuery('#wps_activity_post').length) {

		if (wps_activity_ajax.activity_post_focus)
			jQuery('#wps_activity_post').focus();

		jQuery("#wps_activity_post_button").click(function (event) {

			event.preventDefault();

			if (jQuery('#wps_activity_post').val().length || jQuery('.file-input-name').length) {

                jQuery('#wps_activity_post_button').after('<div id="wps_tmp"><img style="width:20px;height:20px;" src="'+jQuery('#wps_wait_url').html()+'" /></div>');

		        var iframe = jQuery('<iframe name="postiframe" id="postiframe" style="display: none;" />');
		        jQuery("body").append(iframe);

		        var form = jQuery('#theuploadform');
		        form.attr("action", wps_activity_ajax.plugins_url+"/lib_activity.php");
		        form.attr("method", "post");
		        form.attr("enctype", "multipart/form-data");
		        form.attr("encoding", "multipart/form-data");
		        form.attr("target", "postiframe");
		        form.attr("file", jQuery('#wps_activity_image_upload').val());
		        form.submit();

		        jQuery("#postiframe").load(function () {
                    
			    	jQuery("#wps_tmp").remove();
                    var tmp = 'wps_'+jQuery.now();
		            iframeContents = '<div id="'+tmp+'" style="display:none">'+jQuery("#postiframe")[0].contentWindow.document.body.innerHTML+'</div>';
                    jQuery('#wps_activity_post').val('').focus();
                    jQuery("#postiframe").remove();                    
			    	jQuery('#wps_activity_items').prepend(iframeContents);
                    jQuery('#'+tmp).slideDown('fast');
                    
                    if (jQuery('#wps_activity_post_private_msg').length) { jQuery('#wps_activity_post_private_msg').remove(); }
                    jQuery.post( wps_activity_ajax.ajaxurl, { action : 'wps_null' } ); // kick ajaxComplete

		        });

		    } else {
		    	jQuery('#wps_activity_post').css('border', '1px solid red');
		    	jQuery('#wps_activity_post').css('background-color', '#faa');
		    	jQuery('#wps_activity_post').css('color', '#000');
		    }

	        return false;

	    });

	}

	// Add activity comment
    jQuery('body').on('click', '.wps_activity_post_comment_button', function() {

		var id = jQuery(this).attr('rel');		
		var comment = jQuery('#post_comment_'+id).val();
        var t = this;
        
		if (comment.length) {

			jQuery(t).after('<div id="wps_tmp" style="width:20px;height:20px;"><img src="'+jQuery('#wps_wait_url').html()+'" /></div>');
            jQuery(t).hide();
            jQuery('#post_comment_'+id).hide().val('');

            jQuery.post(
			    wps_activity_ajax.ajaxurl,
			    {
			        action : 'wps_activity_comment_add',
			        post_id : id,
			        comment_content: comment,
			        size : jQuery(this).data('size'),
			        link : jQuery(this).data('link')
			    },
			    function(response) {
			    	jQuery('#wps_activity_'+id+'_content').append(response);
			    	jQuery("#wps_tmp").remove();
                    jQuery(t).show();
                    jQuery('#post_comment_'+id).show();
			    }   
			);

		}

	});

	// Make post sticky
    jQuery('body').on('click', '.wps_activity_settings_sticky', function() {

		var id = jQuery(this).attr('rel');
		jQuery(this).hide();
		var height = jQuery('#wps_activity_'+id).height();
		jQuery('#wps_activity_'+id).animate({ height: 1 }, 500, function() {
			jQuery("#wps_activity_items").prepend(jQuery('#wps_activity_'+id));
			jQuery('#wps_activity_'+id).animate({ height: height }, 500);
			
			jQuery.post(
			    wps_activity_ajax.ajaxurl,
			    {
			        action : 'wps_activity_settings_sticky',
			        post_id : id
			    },
			    function(response) {
			    }   
			);

		});

	});

    // Hide post
    jQuery('body').on('click', '.wps_activity_settings_hide', function() {

		var id = jQuery(this).attr('rel');
		jQuery(this).hide();
		var height = jQuery('#wps_activity_'+id).height();
        
        jQuery('#wps_activity_'+id).slideUp();
        //jQuery("#wps_activity_items").prepend(jQuery('#wps_activity_'+id));
        //jQuery('#wps_activity_'+id).animate({ height: height }, 500);

        jQuery.post(
            wps_activity_ajax.ajaxurl,
            {
                action : 'wps_activity_settings_hide',
                post_id : id
            },
            function(response) {
            }   
        );

	});    

	// Make post unsticky
    jQuery('body').on('click', '.wps_activity_settings_unsticky', function() {

		var id = jQuery(this).attr('rel');
		jQuery(this).hide();

		jQuery('#wps_activity_'+id).wps_shake(3, 5, 100);

		jQuery.post(
		    wps_activity_ajax.ajaxurl,
		    {
		        action : 'wps_activity_settings_unsticky',
		        post_id : id
		    },
		    function(response) {
		    }   
		);

	});

	// Delete post from settings
    jQuery('body').on('click', '.wps_activity_settings_delete', function() {

		var id = jQuery(this).attr('rel');
		jQuery('#wps_activity_'+id).fadeOut('slow');

		jQuery.post(
		    wps_activity_ajax.ajaxurl,
		    {
		        action : 'wps_activity_settings_delete',
		        id : id
		    },
		    function(response) {
		    }   
		);

	});

	// Delete comment from settings
    jQuery('body').on('click', '.wps_comment_settings_delete', function() {

        var id = jQuery(this).attr('rel');
		jQuery('#wps_comment_'+id).fadeOut('slow');

		jQuery.post(
		    wps_activity_ajax.ajaxurl,
		    {
		        action : 'wps_comment_settings_delete',
		        id : id
		    },
		    function(response) {
		    }   
		);

	});	

	// Clicked on more... to expand post
    jQuery('body').on('click', '.activity_item_more', function() {
		var id = jQuery(this).attr('rel');
		jQuery('#activity_item_snippet_'+id).hide();
		jQuery('#activity_item_full_'+id).slideDown('slow');
	});

	// Show hidden comments
	jQuery("body").on('click', '.wps_activity_hidden_comments', function () {
		jQuery(this).hide();
		jQuery('.wps_activity_item_'+jQuery(this).attr('rel')).slideDown('slow');
	});
    
    // ------------------------------------------------------------------------------------- ADMIN
    
    // Admin - remove hidden flags
    jQuery("#wps_activity_unhide_all").click(function (event) {

        jQuery.post(
            wps_activity_ajax.ajaxurl,
            {
                action : 'wps_activity_unhide_all',
                post_id : jQuery(this).attr('rel'),
            },
            function(response) {
                alert('OK');
            }   
        ); 

    });
    
	// Admin - new activity
	if (jQuery("#wps_target").length) {

		if (jQuery("#wps_target").val() == '') {
			jQuery("#wps_target").select2({
			    minimumInputLength: 1,
			    query: function (query) {
					jQuery.post(
					    wps_ajax.ajaxurl,
					    {
					        action : 'wps_get_users',
					        term : query.term
					    },
					    function(response) {
					    	var json = jQuery.parseJSON(response);
					    	var data = {results: []}, i, j, s;
							for(var i = 0; i < json.length; i++) {
						    	var obj = json[i];
						    	data.results.push({id: obj.value, text: obj.label});
							}
							query.callback(data);	    	
					    }   
					);
			    }
			});
		}	

	}    

});


var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    	// If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = pair[1];
    	// If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]], pair[1] ];
      query_string[pair[0]] = arr;
    	// If third or later entry with this name
    } else {
      query_string[pair[0]].push(pair[1]);
    }
  } 
    return query_string;
} ();

jQuery.fn.wps_shake = function(intShakes, intDistance, intDuration) {
    this.each(function() {
        jQuery(this).css("position","relative"); 
        for (var x=1; x<=intShakes; x++) {
        	jQuery(this).animate({left:intDistance*-1}, (intDuration/intShakes)/4)
    			.animate({left:intDistance}, (intDuration/intShakes)/2)
    			.animate({left:0}, (intDuration/intShakes)/4);
    	}
  	});
	return this;
};

// Ajax function to return activity
function wps_get_ajax_activity(start, page_size, mode) {

    var arr = jQuery('#wps_activity_array').html();
    var atts = jQuery('#wps_atts_array').html();
    var user_id = jQuery('#wps_user_id').html();
    var nonce = jQuery('#wps_nonce_'+user_id).html();

    jQuery.post(
        wps_activity_ajax.ajaxurl,
        {
            action : 'wps_return_activity_posts',
            this_user : jQuery('#wps_this_user').html(),
            user_id : user_id,
            start: start,
            page: page_size,
            nonce: nonce,
            data: {arr: arr, atts: atts},
        },
        function(response) {
            if (mode == 'replace') {
                if (jQuery("#wps_activity_post_private_msg").length) {
                    jQuery('#wps_activity_post_private_msg').html(response);
                } else {
                    jQuery('#wps_activity_ajax_div').html(response);
                }
            } else {
                jQuery('#wps_tmp').remove();
                jQuery('#wps_activity_ajax_div').append(response);
            }
        }   
    );

}