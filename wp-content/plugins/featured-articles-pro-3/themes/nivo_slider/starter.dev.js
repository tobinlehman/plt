;(function($){
	
	$(window).load( function(){
		var sliders = $('.fa-nivo-slider-wrapper');
		$.each( sliders, function(){
			// nivo slider defaults
			var options = {
				effect: 'random',               // Specify sets like: 'fold,fade,sliceDown'
			    slices: 15,                     // For slice animations
			    boxCols: 8,                     // For box animations
			    boxRows: 4,                     // For box animations
			    animSpeed: 500,                 // Slide transition speed
			    pauseTime: 3000,                 // How long each slide will show
			    startSlide: 0,                     // Set starting Slide (0 index)
			    directionNav: true,             // Next & Prev navigation
			    controlNav: true,                 // 1,2,3... navigation
			    controlNavThumbs: false,         // Use thumbnails for Control Nav
			    pauseOnHover: true,             // Stop animation while hovering
			    manualAdvance: true,             // Force manual transitions
			    prevText: '&laquo;',                 // Prev directionNav text
			    nextText: '&raquo;',                 // Next directionNav text
			    randomStart: false,             // Start on a random slide
			    // events
			    beforeChange: function(){},     // Triggers before a slide transition
			    afterChange: function(){},         // Triggers after a slide transition
			    slideshowEnd: function(){},     // Triggers after all slides have been shown
			    lastSlide: function(){},         // Triggers when last slide is shown
			    afterLoad: function(){}         // Triggers when slider has loaded					
			};
			
			// slider data			
			var data = $(this).data();
			// set the slider options into the defaults
			options.animSpeed = data.effect_duration * 1000,
			options.pauseTime = data.slide_duration * 1000 + options.animSpeed;
			options.manualAdvance = !data.auto_slide;
			
			// start nivo slider
			var slider = $(this).find('.fa-nivo-slider').nivoSlider( options );
			slider.parent().removeClass('slider-loading').css({'height':'auto'});
			
			
			// videos
			$(document).on( 'click', '.fa-nivo-slider .nivo-caption a.play-video', slider,  function( e ){
				
				e.preventDefault();
				
				var slider = e.data,				
					player_container = $(slider).find('.nivo-caption .fa-video');
				
				if( player_container.length < 1 ){
					return;
				}
				
				var	data	= $(player_container).data(),
					width	= data.width,
					height;
				
				// calculate the video width based on height
				switch( data.aspect ){
					case '16x9':
					default:
						height = (width *  9) / 16;
					break;
					case '4x3':
						height = (width *  3) / 4;
					break;
				}
				
				// dialog
				var dialog = $(player_container).dialog({
					autoOpen 	: true,
					width 		: width,
					height		: height,
					maxWidht	: width,
					maxHeight	: height,
					draggable	: false,
					closeOnEscape	: true,
					resizable	: false,
					modal		: true,
					dialogClass	: 'fa-video-modal',
					close		: function(){
						// pause video when closing modal
						$(player_container).data( 'player' ).pause();
						modal_open = false;
						// restart slider when closing modal
						slider.data('nivoslider').start();
						
						//set_timer();
					},
					open : function( event, ui ){
						// play video
						if( !$(player_container).data('player') ){														
							var player = $(this).fa_video({
								onLoad	: function(){
									//if( !options.is_mobile ){
										this.play();
									//}																			
								},
								onFinish: function(){
									dialog.dialog('close');
								}
							});
							// store the player on slide
							$(player_container).data( 'player', player );
							
						}else{							
							// video is loaded, just play it
							$(player_container).data( 'player' ).resize();
							$(player_container).data( 'player' ).play();
						}
						modal_open = true;
						// stop slider when modal is open
						slider.data('nivoslider').stop();
					}
				});
				// end dialog
				
			});// end video click
			
		});
	});
})(jQuery);