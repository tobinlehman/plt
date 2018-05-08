/**
 * @package Featured articles PRO - Wordpress plugin
 * @author CodeFlavors ( codeflavors[at]codeflavors.com )
 * @url http://www.codeflavors.com/featured-articles-pro/
 * @version 2.4
 */

/*
	Author: CodeFlavors ( http://www.codeflavors.com )
	Copyrigh (c) 2011 - author
	License: MIT(http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.opensource.org/licenses/gpl-license.php)
	Package: Wordpress Featured Articles plugin
	Version: 1.1
	jQuery version: 1.6.1 +
	
	Creates a horizontal accordion slideshow
*/
(function($){
	$.fn.CFAccordion = function(options){
		if (this.length > 1){
			this.each(function() { 
				$(this).CFAccordion(options);				
			});
			return this;
		}
		
		var data = $(this).data();
		data.effect_duration *= 1000;
		
		var defaultOptions = {
				elements		: '.slide',
				effect_duration	: 800,
				infoItems		: '.info',
				event			: 'mouseover',
				accMinWidth		: 50,
				
				/* Responsive */
				width 		: 0,
				height 		: 0,
				fullwidth 	: false,
				image_container	: '.fa-image-container', // must be specified when slide image covers full background ( full_image : true )
				_image			: '.main-image', // the image selector (shouldn't have to be specified since it's implemented by the PHP script)
	       		
				/* Selectors */
				play_video		: '.play-video',
				image_container	: '.fa-image-container', // must be specified
				video_container : 'div.fa-video',
				is_mobile		: false,
				// Events
				idle 		: function(){},
				open		: function(){},
				beforeInit	: function(){},
				video_status: function(){}
		};
		
		if( options.accMinWidth < 1 ){
			options.accMinWidth = null;
		}
		
		var options 	= $.extend({}, defaultOptions, data, options),
			self 		= this,
			touch 		= ("ontouchstart" in window) || window.DocumentTouch && document instanceof DocumentTouch,
       		nav_event 	= (touch) ? "touchend" : options.event,
       		slides 		= self.find( options.elements ),
		 	slidesNum 	= slides.length,
		 	// set up some variables for later use
		 	totalWidth,
		 	slideIdle,
		 	slideWidth,
		 	slideMin,
		 	current 	= false,
		 	video_status,
		 	modal_open = false;
		 
		 var settings 	= function(){
			return options; 
		 }
		 
		 var initialize = function(){
			var o = settings(); 
			
			var ratio 	= options.width / options.height,
			width 		= options.fullwidth ? '100%' : options.width;		
			$(self).css({
				'width' : width
			});
			
			// set sizes
			totalWidth 	= $(self).width();
			slideIdle 	= totalWidth/slidesNum;
			slideWidth 	= options.accMinWidth? totalWidth - options.accMinWidth * ( slidesNum - 1 ) : $(slides[0]).width();
			slideMin 	= options.accMinWidth ? options.accMinWidth : (totalWidth - slideWidth) / ( slidesNum - 1 );
			
			$(slides).css({ 
				'width' : slideWidth 
			});
			
			// set the aspect
       		set_aspect();   
			
       		prepare_videos();
       		preload_images();
       		
			o.beforeInit.call(
				self, {
					'slides' : slides, 
					'width' : slideIdle
			}); 
			arrangeSlides('idle');
			
			// container mouseleave event
			$(self).mouseleave(function(){
				if( modal_open ){
					return;
				}
				arrangeSlides('idle');
				current = false;
				$(slides).removeClass('current');
			})
			
			
			//var mouseEvent = options.event == 'click' ? 'click' : 'mouseenter';
			$(slides).bind( nav_event, function(){
				var index = $.inArray(this, slides);				
				$(this).addClass('current');
				arrangeSlides('min', index);
				current = index;	
			})
			if( options.event !== 'click' ){
				$(slides).mouseleave(function(e){
					$(this).removeClass('current');				
				})
			}
			
			$(window).resize(function(){
				resizeAccordion();				
			});
			
			$(self).removeClass('slider-loading').children().show();
			
			return self;
		 }
		 
		 var prepare_videos = function(){
			 /**
			 * Load videos
			 */
			$.each( slides, function( i, slide ){
				// prepare video player
				var player_trigger 	= $(this).find( options.play_video ),
					open_video		= $(player_trigger).data('open_video'),
					player_container = $(this).find( options.video_container );
				
				if( player_container.length < 1 ){
					return;
				}
				
				var	data	= $(player_container).data(),
					width	= data.width,
					height;
				
				//*				
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
				
				// begin dialog *****************************
				if( open_video && 'modal' == open_video ){
					var dialog = $(slide).find( options.video_container ).dialog({
						autoOpen 	: false,
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
							$(slide).data( 'player' ).pause();
							modal_open = false;
						},
						open : function( event, ui ){
							// play video
							if( !$(slide).data('player') ){	
								var player = $(this).FA_VideoPlayer({
									stateChange	: function( status ){
										//check if video is playing
										if( i == current ){
											//
											// Video status change event
											//
											options.video_status.call( self, status );
											if( 1 == status ){
												if( !options.is_mobile ){
													this.play();
												}
											}
										}
										set_video_status( status );
										if( 4 == status ){
											dialog.dialog('close');
										}								
									}
								});
								// store the player on slide
								$(slide).data( 'player', player );
								
							}else{							
								// video is loaded, just play it
								$(slide).data( 'player' ).resizePlayer();
								$(slide).data( 'player' ).play();
							}
							modal_open = true;
						}
					});				
				}
				// end dialog *******************************
				//*/
				$(this).find( options.play_video ).bind( 'click', function(event){
					event.preventDefault();					
					dialog.dialog('open');				
				});// click				
			});// each
			 
		 }
		 
		 var preload_images = function(){			 
			 $.each( slides, function( i, slide ){
				// preload images
				var img_container = $(this).find( options.image_container );
				if( img_container.length > 0 ){
					var data = $(img_container).data(),
						img = $('<img />',{
							'src' 	: data.image,
							'class'	: data.image_class,
							'data-width' : data.width,
							'data-height': data.height
						});
					
					$(img).load(function(){
						var preloader = $(img_container).find('img');
						$(this).insertAfter( $(preloader) );
						$(preloader).remove();
						center_image( $(this) );						
					});						
				}				 
			 });			 
		 }
		 
		 /**
       	 * Store the video status
       	 */
       	var set_video_status = function( status ){
       		video_status = status;
       	}
		 
		 var set_aspect = function(){
			 var ratio 		= options.width / options.height,
				currWidth 	= $(self).width(),
				resizeRatio = ( currWidth / options.width ) * 100;
			
			if( currWidth > options.width ){
				if( !options.height_resize ){
					resizeRatio = 100;
				}
			}	
			
			var	font_size 	= options.font_size * resizeRatio / 100;
			var css = {
				'font-size' : font_size + '%',
				'height'	: ( currWidth / ratio )
			};
			
			if( !options.height_resize ){
				css['max-height'] = options.height;				
			}
			$(self).css(css);
			
			// set images to cover the whole background
			if( slides ){
				$.each( slides, function(){
					var img = $(this).find( options.image_container +' '+ options._image );
					center_image( img );					
				});
			}			
		 }
		 
		/**
		 * Centers images inside their container, both vertically and horizontally
		 */
		var center_image = function( img ){
			var	w 			= img.data('width'),
				h 			= img.data('height'),
				image_prop 	= w/h,
				s_w 		= $(img).parent().width(),
				s_h 		= $(img).parent().height(),
				slider_prop = s_w / s_h;
			
			if( image_prop > slider_prop ){
				$(img).css({
					'width' : 'auto',
					'max-width' : 'none',
					'height' : '100%',
					'max-height' : '100%'							
				});
				
				var img_width = $(img).width();
				
				if( 0 == img_width ){
					$(img).load( function(){
						$(img).css({
							'margin-left' : - ( ( $(img).width()  -  $(self).width() )/2 ),
							'margin-top' : 0
						});
					});
				}else{
					$(img).css({
						'margin-left' : - ( ( img_width  -  s_w )/2 ),
						'margin-top' : 0
					});
				}
				
			}else{
				$(img).css({
					'width' : '100%',
					'max-width' : '100%',
					'height' : 'auto',
					'max-height' : 'none'
				});
				
				var img_height = $(img).height();
				
				if( 0 == img_height ){
					$(img).load( function(){
						$(img).css({
							'margin-top' : - ( ( $(img).height()  -  $(self).height() )/2 ),
							'margin-left' : 0
						});
					});
				}else{
					$(img).css({
						'margin-top' : - ( ( img_height  -  s_h )/2 ),
						'margin-left' : 0
					});
				}
			}			
		}
		 
		 var resizeAccordion = function(){
			 
			 set_aspect();
			 
			 var currentWidth = self.width();
			 if( currentWidth == totalWidth ){
				 return;
			 }
			 $(slides).css({
				 'width' : '90%'
			 });
			 
			 totalWidth = currentWidth;
			 slideIdle 	= totalWidth/slidesNum;
			 slideWidth = options.accMinWidth ? totalWidth - options.accMinWidth * ( slidesNum - 1 ) : $(slides[0]).width();
			 slideMin 	= options.accMinWidth ? options.accMinWidth : (totalWidth - slideWidth) / ( slidesNum - 1 );
			 
			 $(slides).css({
				 'width' : slideWidth
			 });
			 
			 options.beforeInit.call(
				self, {
					'slides' : slides, 
					'width' : slideIdle
			 });

			 arrangeSlides('idle');
		 }
		 
		 var arrangeSlides = function(type, index){
			 var size 		= 'idle' == type ? slideIdle : slideMin,
				 o 			= settings(),
				 current 	= false;
			 // loop slides
			 $.each(slides, function(i, slide){
				 if( i > index ){
					$(slide).animate({
						'left' : (i-1)*size + slideWidth
					},{
						queue: false, 
						duration: o.effect_duration
					});
					return;
				}				
				$(slide).animate({
					'left' : i*size
				},{
					queue:false, 
					duration: o.effect_duration
				});				
			});
			// Fire event
			switch( type ){
				case 'idle':
					o.idle.call(self, {
						'slides' 	: slides, 
						'width' 	: slideIdle
					});
				break;
				case 'min':
					o.open.call(self, {
						'slides' 	: slides, 
						'index' 	: index,
						'width' 	: slideIdle
					});
				break;	
			} 
			 
		 }
		 
		 return initialize();
	}	
})(jQuery);