jQuery(document).ready(function() {
    
	/* Admin - add forum */

	jQuery("#wps_admin_forum_add_button").click(function (event) {
		if (jQuery('#wps_admin_forum_add_details').css('display') != 'none') {

			if ( jQuery('#wps_admin_forum_add_name').val().length != 0) {
				jQuery('#wps_admin_forum_add_name').css('background-color', '#fff').css('color', '#000');
				if (jQuery('#wps_admin_forum_add_description').val().length == 0) {
					event.preventDefault();
					jQuery('#wps_admin_forum_add_description').css('background-color', '#faa').css('color', '#000');
				}
			} else {
				event.preventDefault();
				jQuery('#wps_admin_forum_add_name').css('background-color', '#faa').css('color', '#000');				
			}
		}
	});

	jQuery("#wps_admin_forum_add").click(function (event) {
		event.preventDefault();
		if (jQuery('#wps_admin_forum_add_details').css('display') == 'none') {
			jQuery('#wps_admin_forum_add_details').slideDown('fast');
		}
	});

	/* Quick jump */

	if (jQuery("#wps_forums_go_to").length) {

		jQuery("#wps_forums_go_to").change(function() {
			var url = jQuery(this).val();
			if (url != '') { 
				jQuery("body").addClass("wps_wait_loading");
				window.location = url;
			}
		});

	}
	
	/* Add Post */

	if (jQuery("#wps_forum_post_button").length) {

		jQuery('#wps_forum_post_button').prop("disabled", false);
		jQuery('#wps_forum_post_title').val('');
		jQuery('#wps_forum_post_textarea').val('');
		jQuery('.wps_forum_extension_text').val(''); // Possible forum extensions
		jQuery('.wps_forum_extension_textarea').val(''); // Possible forum extensions
		
		jQuery("#wps_forum_post_button").click(function (event) {
            
			event.preventDefault();
            jQuery('.wps_field_error').removeClass('wps_field_error'); // remove all highlighed fields before re-checking

            if (jQuery('#wps_forum_post_form').css('position') == 'fixed') {
                
                // for all
                if (jQuery("#closed_switch").length) {
                    jQuery("#closed_switch").parent().hide();
                }
                // for classic...
                if (jQuery(".wps_forum_posts_classic").length) {
                    jQuery(".wps_forum_posts_classic").slideUp();
                }
                // for table...
                if (jQuery(".wps_forum_posts").length) {
                    jQuery(".wps_forum_posts").slideUp();
                }
                if (jQuery(".wps_forum_posts_header").length) {
                    jQuery(".wps_forum_posts_header").slideUp();
                }
                if (jQuery(".wps_pagination_numbers").length) {
                    jQuery(".wps_pagination_numbers").hide();
                }
                
				jQuery('#wps_forum_post_form').css('position', 'relative').css('left', '0px').css('top', '0px');
				document.getElementById('wps_forum_post_title').focus();

			} else {

				if (jQuery('#wps_forum_post_title').val().length) {
                    if (typeof tinyMCE !== 'undefined') {
                        /* check if Visual or Text mode */
                        if (tinyMCE.activeEditor != null) {
                            var editor = tinyMCE.get('wps_forum_post_textarea');
                            var content = editor.getContent();
                        } else {
                            var content = jQuery('#wps_forum_post_textarea').val();
                        }
                    } else {
                        var content = jQuery('#wps_forum_post_textarea').val();
                    }
					if (content.length) {

						// Check for mandatory fields
						var all_filled = true;
						jQuery('.wps_mandatory_field').each(function(i, obj) {
                            var value = jQuery(this).val();
                            if (value.trim() == '') {
								jQuery(this).addClass('wps_field_error');
								all_filled = false;
							}
						});
                        
						if (all_filled) {
                            
                            /* First add the post */
							jQuery(this).attr("disabled", true);
							jQuery("body").addClass("wps_wait_loading");
					        var iframe = jQuery('<iframe name="wps_forum_postiframe" id="wps_forum_postiframe" style="display:none;" />');
					        jQuery("body").append(iframe);

					        var form = jQuery('#wps_forum_post_theuploadform');
					        form.attr("action", jQuery('#wps_forum_plugins_url').val()+"/lib_forum.php");
					        form.attr("method", "post");
					        form.attr("enctype", "multipart/form-data");
					        form.attr("encoding", "multipart/form-data");
					        form.attr("target", "wps_forum_postiframe");
					        form.attr("file", jQuery('#wps_forum_image_upload').val());
					        form.submit();

					        jQuery("#wps_forum_postiframe").load(function () {
                                iframeContents = jQuery("#wps_forum_postiframe")[0].contentWindow.document.body.innerHTML;
                                if (iframeContents.indexOf("*") == 0) {
                                    alert(jQuery('#valid_exts_msg').val());
                                    iframeContents = iframeContents.replace('*', '');
                                }
                                iframeContents = iframeContents.split("|");
                                var reload_loc = document.location; // reload current page
                                var post_id = iframeContents[0];
								var wps_forum_moderate = iframeContents[2];
								if (iframeContents[1] != 'reload') {
                                    reload_loc = iframeContents[1].replace('&amp;','&'); // to go straight to new post or url to redirect to	
                                }
                                // now call AJAX to do hook, like subscribers, so can skip any delay
                                jQuery.post(
                                    wps_forum_ajax.ajaxurl,
                                    {
                                        action : 'wps_forum_post_add_ajax_hook',
                                        post_id : post_id
                                    },
                                    function(response) {
                                        //alert(response); // Will show debugging info
                                    }                                     
                                );                                
                                
                                // and reload page whilst the hook carrys on (wait a little to ensure above is fired)
                                setTimeout(function(){ document.location = reload_loc }, 2000);

					        });
                            
					    }

					} else {

						jQuery('#wps_forum_post_content_label').addClass('wps_field_error');

					}

				} else {

					jQuery('#wps_forum_post_title').addClass('wps_field_error');

				}

			}

		});

	}

	/* Add Reply */
	
	if (jQuery("#wps_forum_comment_button").length) {
                
		jQuery('#wps_forum_comment_button').prop("disabled", false);
		jQuery('#wps_forum_comment').val('');
		
		jQuery("#wps_forum_comment_button").click(function (event) {

			event.preventDefault();

			if(jQuery('#wps_forum_comment_form').css('display') == 'none') {

				jQuery('#wps_forum_comment_form').show();
				document.getElementById('wps_forum_comment').focus();

			} else {

                if (typeof tinyMCE !== 'undefined') {
                    /* check if Visual or Text mode */
                    if (tinyMCE.activeEditor != null) {
                        var editor = tinyMCE.get('wps_forum_comment');
                        var content = editor.getContent();
                    } else {
                        var content = jQuery('#wps_forum_comment').val();
                    }
                } else {
                    var content = jQuery('#wps_forum_comment').val();
                }                

                if (content.length || wps_forum_ajax.is_admin) {

                    // Check for mandatory fields
                    var all_filled = true;
                    jQuery('.wps_mandatory_field').each(function(i, obj) {
                        if (jQuery(this).val() == '') {
                            jQuery(this).addClass('wps_field_error');
                            all_filled = false;
                        }
                    });
                    
                    if (all_filled) {
                        
                        jQuery(this).attr("disabled", true);

                        jQuery("body").addClass("wps_wait_loading");

                        var iframe = jQuery('<iframe name="wps_forum_commentiframe" id="wps_forum_commentiframe" style="display: none;" />');
                        jQuery("body").append(iframe);

                        var form = jQuery('#wps_forum_comment_theuploadform');
                        form.attr("action", jQuery('#wps_forum_plugins_url').val()+"/lib_forum.php");
                        form.attr("method", "post");
                        form.attr("enctype", "multipart/form-data");
                        form.attr("encoding", "multipart/form-data");
                        form.attr("target", "wps_forum_commentiframe");
                        form.submit();

                        jQuery("#wps_forum_commentiframe").load(function () {
                            iframeContents = jQuery("#wps_forum_commentiframe")[0].contentWindow.document.body.innerHTML;
                            if (iframeContents.indexOf("*") == 0) {
                                alert(jQuery('#valid_exts_msg').val());
                                iframeContents = iframeContents.replace('*', '');
                            }                            
                            if (iframeContents == 'reload') {
                            	var url = document.location.toString();
                            	url = url.replace(/[?,&]gotoend=1/g,'');
                            	if(url.indexOf('?') > 0) {
                            		url += '&gotoend=1';
                            	} else {
                            		url += '?gotoend=1';
                            	}
                                window.location = url;
                            } else {
                                window.location = iframeContents;
                            }
                        });
                        
                    } else {

                        jQuery('#wps_forum_comment').addClass('wps_field_error');

                    }                        

				} else {

					jQuery('#wps_forum_comment').addClass('wps_field_error');

				}

			}

		});

	}

	// Add comment (comment on reply)

	if (jQuery(".wps_forum_post_comment_form_submit").length) {

		jQuery('.wps_forum_post_comment_form_submit').prop("disabled", false);
		jQuery('.wps_forum_post_comment_form').val('');
		
		jQuery(".wps_forum_post_comment_form_submit").click(function (event) {

            /*
            alert('start');
            jQuery.post(
                wps_forum_ajax.ajaxurl,
                {
                    action : 'wps_forum_add_subcomment'
                },
                function(response) {
                    alert('done');
                }   
            );
            */
            
			event.preventDefault();
			var id = jQuery(this).attr('rel');

			if(jQuery('#sub_comment_div_'+id).css('display') == 'none') {

				jQuery('#sub_comment_div_'+id).slideDown('fast');
				document.getElementById('sub_comment_'+id).focus();

			} else {

                var the_button = this;
				var the_textarea = jQuery('#sub_comment_'+id);
				jQuery(this).parent().append('<div id="wps_tmp" style="width:20px;height:20px;margin-bottom:20px"><img src="'+jQuery('#wps_wait_url').html()+'" /></div>');
				jQuery(the_button).hide();
				jQuery(the_textarea).hide();

				if (jQuery('#sub_comment_'+id).val().length) {

					var comment = jQuery('#sub_comment_'+id).val();
					jQuery('#sub_comment_'+id).val('');

					jQuery.post(
					    wps_forum_ajax.ajaxurl,
					    {
					        action : 'wps_forum_add_subcomment',
					        post_id : jQuery(this).data('post-id'),
					        comment_id : id,
					        comment : comment,
					        size : jQuery(this).data('size'),
					        wps_forum_moderate : 1,
					    },
					    function(response) {
					    	if (jQuery('#sub_comment_div_'+id).prev('.wps_forum_post_subcomments').length) {
								jQuery('#sub_comment_div_'+id).prev('.wps_forum_post_subcomments').append(response);
							} else {
								jQuery('#sub_comment_div_'+id).prepend(response);
							}
							jQuery('.wps_forum_post_subcomment').slideDown('fast');
							jQuery("body").removeClass("wps_wait_loading");
							document.getElementById('sub_comment_'+id).focus();							
                            
                            // Show any content marked for after page has loaded from returned content
                            if (jQuery('.wps_show_after_page_load').length) {
                                jQuery('.wps_show_after_page_load').show();
                            }
                            
                            jQuery('#sub_comment_'+id).removeClass('wps_field_error');
                			jQuery("#wps_tmp").remove();
                			jQuery(the_button).show();
                			jQuery(the_textarea).show();
                            
					    }   
					);                  

				} else {
                    
                    jQuery('#sub_comment_'+id).addClass('wps_field_error');
                	jQuery("#wps_tmp").remove();
                	jQuery(the_button).show();
                	jQuery(the_textarea).show();

				}

			}

		});

	}

	// Reopen post
	
	if (jQuery("#wps_forum_comment_reopen_button").length) {

		jQuery('#wps_forum_comment_reopen_button').prop("disabled", false);
		
		jQuery("#wps_forum_comment_reopen_button").click(function (event) {

			event.preventDefault();
			jQuery(this).attr("disabled", true);
			jQuery("body").addClass("wps_wait_loading");

			var post_id = jQuery('#reopen_post_id').val();

			jQuery.post(
			    wps_forum_ajax.ajaxurl,
			    {
			        action : 'wps_forum_comment_reopen',
			        post_id : post_id
			    },
			    function(response) {
			    	location.reload();
			    }   
			);

		});

	}

	/* Choose forum */
	if (jQuery("#wps_forum_post_choose").length) {
		jQuery("#wps_forum_post_choose").select2();
	}

	/* Edit Post */

	if (jQuery("#wps_post_forum_slug").length) {
		jQuery("#wps_post_forum_slug").select2();
	}
    
	/* Forum Settings */
	
	jQuery(".wps_forum_settings").mouseover(function (event) {
		jQuery('.wps_forum_settings_options').hide();
		jQuery('.wps_comment_settings_options').hide();
		jQuery(this).next('.wps_forum_settings_options').show();
	});	

	jQuery(".wps_forum_comment_settings").mouseover(function (event) {
		jQuery('.wps_forum_settings_options').hide();
		jQuery('.wps_comment_settings_options').hide();
		jQuery(this).next('.wps_forum_comment_settings_options').show();
	});	

	jQuery(document).mouseup(function (e) {
		jQuery('.wps_forum_settings_options').hide();
		jQuery('.wps_comment_settings_options').hide();
	});

	/* Closed switch */

	jQuery("#closed_switch").click(function (event) {
		var state = 'off';
		if (jQuery(this).is(":checked")) {
			jQuery('.wps_forum_post_closed').slideDown('fast');
			state = 'on';
		} else {
			jQuery('.wps_forum_post_closed').slideUp('fast');
		}
		jQuery.post(
		    wps_forum_ajax.ajaxurl,
		    {
		        action : 'wps_forum_closed_switch',
		        state : state
		    },
		    function(response) {
		    }   
		);
	});	
	

});

    
/* Edit Reply */

function wps_validate_forum_reply_edit() {

    var r = true;
	
    if (jQuery('#wps_forum_comment_edit_textarea').val().length) {

        // Check for mandatory fields
        var all_filled = true;
        jQuery('.wps_mandatory_field').each(function(i, obj) {
            if (jQuery(this).val() == '') {
                jQuery(this).addClass('wps_field_error');
                all_filled = false;
                r = false;
            }
        });

        if (!all_filled)
            jQuery('#wps_forum_comment').addClass('wps_field_error');

    } else {
        jQuery('#wps_forum_comment_edit_textarea').addClass('wps_field_error');
        r = false;
    } 

    return r;

}
