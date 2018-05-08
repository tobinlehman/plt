;(function($){
	
	$(document).ready(function(){
		// embed videos
		$('.fa-video-player').fa_video({
			onLoad: function(){
				customizeVideo( this );
			}			
		});
		
		
		videoQuery();
		removeVideo();
		aspectRatio();
		removeSlideImage();
		postLink();
	});
	
	var removeSlideImage = function(){
		$(document).on('click', '#fa-remove-slide-image', function(e){
			e.preventDefault();
			var data = {
				'action' : faEditSlide.remove_image_action,	
				'post_id' : $('#post_ID').val() 	
			};
			data[ faEditSlide.remove_image_nonce.name ] = faEditSlide.remove_image_nonce.nonce;
			$.ajax({
				'url' : ajaxurl,
				'data' : data,
				'dataType' : 'json',
				'type' : 'POST',
				'success' : function( json ){
					if( json.success ){
						$( '#fa-selected-images' ).empty().append( json.data );
						return;
					}					
				},
				'error': function(){
					
				}
			});
		})
		
	}
	
	var postLink = function(){		
		$(document).on( 'click', '#fa-link-post', function(e){
			if( $(this).is(':checked') ){
				$('#fa-custom-link-option').hide();
			}else{
				$('#fa-custom-link-option').show();
			}
		});		
	}
	
	var customizeVideo = function( ref ){
		var update 	= $('#fapro-update-player'),
			data 	= $('.fa-video-player').data();
		
		$(update).click( function(e){
			e.preventDefault();
			
			if( 'vimeo' == data.source ){			
				var title 		= $('#fa-title').is(':checked') ? 1 : 0,
					byline 		= $('#fa-byline').is(':checked') ? 1 : 0,
					portrait 	= $('#fa-portrait').is(':checked') ? 1 : 0,
					color 		= $('#fa-color').val().replace('#', '');
				
				$(ref).data( 'title', title );
				$(ref).data( 'byline', byline );
				$(ref).data( 'portrait', portrait );
				$(ref).data( 'color', color );
				
				$(ref).empty();
				$(ref).fa_video();
			}
			
			if( 'youtube' == data.source ){
				var controls = $('#fa-controls').is(':checked') ? 1 : 0,
					autohide = $('#fa-autohide').is(':checked') ? 1 : 0,
					iv_load_policy = $('#fa-annotations').is(':checked') ? 3 : 1,
					modestbranding = $('#fa-modestbranding').is(':checked') ? 1 : 0;
				
				$(ref).data( 'controls', controls );
				$(ref).data( 'autohide', autohide );
				$(ref).data( 'iv_load_policy', iv_load_policy );
				$(ref).data( 'modestbranding', modestbranding );
				
				$(ref).empty();
				$(ref).fa_video();				
			}
			
		})		
	}// customizeVideo
	
	var aspectRatio = function(){
		$(document).on('change', '.fa_video_aspect_ratio', function(){
			var aspect_ratio_input 	= this,
				parent				= $(this).parents('.fa-player-aspect-options'),
				width_input			= $(parent).find('.fa_video_width'),
				height_output		= $(parent).find('.fa_video_height');		
			
			var val = $(this).val(),
				w 	= Math.round( parseInt($(width_input).val()) ),
				h 	= 0;
			switch( val ){
				case '4x3':
					h = Math.floor((w*3)/4);
				break;
				case '16x9':
					h = Math.floor((w*9)/16);
				break;
				case '2.35x1':
					h = Math.floor(w/2.35);
				break;	
			}
			$(height_output).html(h);						
		});
		
		
		$(document).on( 'keyup', '.fa_video_width', function(){
			var parent				= $(this).parents('.fa-player-aspect-options'),
				aspect_ratio_input	= $(parent).find('.fa_video_aspect_ratio');		
						
			if( '' == $(this).val() ){
				return;				
			}
			var val = Math.round( parseInt( $(this).val() ) );
			$(this).val( val );	
			$(aspect_ratio_input).trigger('change');
		});
		
	}// aspectRatio
	
	/**
	 * Detach a video from a given post
	 */
	var removeVideo = function(){
		
		var messages = faEditSlide.messages,
			querying = false;
				
		$('#fa-remove-video').click(function(e){
			e.preventDefault;
			// a query is already running, bail out
			if( querying ){
				setMessage( messages.querying_video, 'fa-loading' );
				return;
			}
			
			querying = true;			
			setMessage( messages.removing_video, 'fa-loading' );
			
			var data = {
				'action' 		: faEditSlide.remove_video_ajax_action,
				'post_id'		: $('#post_ID').val()				
			};
			data[ faEditSlide.remove_video_nonce.name ] = faEditSlide.remove_video_nonce.nonce;
			$.ajax({
				'url' : ajaxurl,
				'data' : data,
				'dataType' : 'json',
				'type' : 'POST',
				'success' : function( json ){
					querying = false;
					resetMessages();					
					if( !json.success ){
						setMessage( json.data, 'fa-error' );
						return;
					}
					
					$('#fa-video-settings').empty();
					$('#fa-video-query-container').empty().html( json.data.video_query );
					videoQuery();
					
				},
				'error': function(){
					querying = false;
					setMessage( messages.query_error, 'fa-error' );
				}
			});			
		})		
	}// removeVideo
	
	// video query functionality
	var videoQuery = function(){
		
		var pref		= faEditSlide.id_prefix,
			parent 		= $('#' + pref + '-slide-video-query'),
			submitBtn 	= $('#fa-video-query-btn'),
			id			= $(parent).find('input[name=fa_video_id]'),
			messages	= faEditSlide.messages,
			querying 	= false;
		
		$(submitBtn).click(function(e){
			e.preventDefault();
			// a query is already running, bail out
			if( querying ){
				setMessage( messages.querying_video, 'fa-loading' );
				return;
			}
			
			var s = $('#' + pref + '-slide-video-query input[name=fa_video_source]:checked').val() || $('#fa_video_source').val(),
				i = $(id).val();
			// reset messages on form submit
			resetMessages();
			// error message, fields empty
			if( '' == s || '' == i ){
				setMessage( messages.empty_video_query, 'fa-error' );
				return;
			}
			
			querying = true;			
			setMessage( messages.loading_video, 'fa-loading' );
			
			var data = {
				'action' 		: faEditSlide.ajax_action,
				'video_source' 	: s,
				'video_id'		: i,
				'post_id'		: $('#post_ID').val(),
				'set_thumbnail' : $('#fa_set_image').is(':checked') ? 1 : 0,
				'set_slide_img' : $('#fa_set_slide_image').is(':checked') ? 1 : 0,		
				'set_video'		: $('#fa_set_video').is(':checked') ? 1 : 0,
			};
			data[ faEditSlide.wp_nonce.name ] = faEditSlide.wp_nonce.nonce;
			
			$.ajax({
				'url' : ajaxurl,
				'data' : data,
				'dataType' : 'json',
				'type' : 'POST',
				'success' : function( json ){
					querying = false;
					resetMessages();					
					if( !json.success ){
						setMessage( json.data, 'fa-error' );
						return;
					}
					
					// set title
					if( $('#fa_set_title').is(':checked') ){
						// posts other than slide post type have a custom title field set. Check if it exists and populate it
						var ct = $('#fa-custom-title');
						if( ct.length > 0 ){
							$(ct).val( json.data.video.title );
						}else{						
							// update the title
							$('#title-prompt-text').addClass('screen-reader-text');
							$('#title[type=text]').val( json.data.video.title );
						}
					}
					// set content
					if( $('#fa_set_content').is(':checked') ){
						var cc = $('#fa-custom-content-post');
						if( cc.length > 0 ){
							// clear editor contents
							fa_clear_editor( 'fa-custom-content-post' );
							// set contents to video description
							if( json.data.video.description ){
								send_to_fa_custom_editor( json.data.video.description );
							}							
						}else{						
							// clear editor contents
							fa_clear_editor();
							// set contents to video description
							if( json.data.video.description ){
								window.send_to_editor( json.data.video.description );
							}
						}	
					}
					// set the featured image
					if( $('#fa_set_image').is(':checked') ){
						if( json.data.thumbnail ){
							WPSetThumbnailHTML( json.data.thumbnail );							
						}						
					}
					
					if( $('#fa_set_slide_image').is(':checked') ){
						if( json.data.slide_img ){
							$( '#fa-selected-images' ).empty().append( json.data.slide_img );
						}
					}
					
					// display the video settings if successfull
					if( $('#fa_set_video').is(':checked') ){
						if( json.data.video_settings ){
							$('#fa-video-settings').empty().html( json.data.video_settings );							
							$('.fa-video-player').fa_video({
								onLoad : function( state ){
									customizeVideo( this );									
								}			
							});							
						}						
						if( json.data.video_query ){
							$('#fa-video-query-container').empty().html( json.data.video_query );
						}
						removeVideo();
					}
					
				},
				'error': function(){
					querying = false;
					setMessage( messages.query_error, 'fa-error' );
				}
			});			
		})				
	};	
	
	/**
	 * Set a message and add a CSS class to messages div
	 */
	var setMessage = function( message, addClass ){
		$('#fa-video-query-messages')
			.empty()
			.attr({'class' : addClass + ' has-message'})
			.html( message );
	}
	
	/**
	 * Reset the messages box
	 */
	var resetMessages = function(){
		$('#fa-video-query-messages')
			.empty()
			.removeAttr('class');			
	}
	
})(jQuery);

var fa_clear_editor = function( edId ) {
	var editor,
		hasTinymce = typeof tinymce !== 'undefined',
		hasQuicktags = typeof QTags !== 'undefined';

	if ( ! wpActiveEditor ) {
		if ( hasTinymce && tinymce.activeEditor ) {
			editor = tinymce.activeEditor;
			wpActiveEditor = editor.id;
		} else if ( ! hasQuicktags ) {
			return false;
		}
	} else if ( hasTinymce ) {
		editor = tinymce.get( edId || wpActiveEditor );
	}

	if ( editor && ! editor.isHidden() ) {
		editor.execCommand( 'mceSetContent', false, '' );
	} else {
		document.getElementById( edId || wpActiveEditor ).value = '';
	}
};

var fa_custom_editor, send_to_fa_custom_editor;
send_to_fa_custom_editor = function( html ) {
	var editor,
		hasTinymce = typeof tinymce !== 'undefined',
		hasQuicktags = typeof QTags !== 'undefined';

	if ( ! fa_custom_editor ) {
		if ( hasTinymce && tinymce.activeEditor ) {
			fa_custom_editor = 'fa-custom-content-post';
			editor = tinymce.get( fa_custom_editor );
		} else if ( ! hasQuicktags ) {
			return false;
		}
	} else if ( hasTinymce ) {
		editor = tinymce.get( wpActiveEditor );
	}

	if ( editor && ! editor.isHidden() ) {
		editor.execCommand( 'mceInsertContent', false, html );
	} else if ( hasQuicktags ) {
		QTags.insertContent( html );
	} else {
		document.getElementById( fa_custom_editor ).value = html;
	}
};
