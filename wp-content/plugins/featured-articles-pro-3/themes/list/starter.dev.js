/**
 * 
 */
;(function($){
	
	$(document).ready(function(){
		$('.fa_slider_list').FeaturedArticles({
			slide_selector 	: '.slide',
			nav_prev 		: '.go-back',
			nav_next		: '.go-forward',
			nav_elem		: '.navigation .nav',
			play_video		: '.play-video.single-link',
			content_container : '.slide-content',
			full_image		: false,
			effect			: false,
			animate_opacity : false,
			begin	: load,
			before	: before,
			after	: after,
			stop	: stop,
			start	: start
			//resize	: resize
		});
		
	});
	
	var load = function(){
		this.nav = $(this).find('.navigation').FA_scroll();
	};
	var before = function( d ){
		this.nav.toIndex( d.next_index );
		
	};
	var after = function(){};
	var stop = function(){};
	var start = function(){};	
	
})(jQuery);

;(function($){
	$.fn.FA_scroll = function(options){
		if( 0 == this.length ){ 
			return false; 
		}
		
		var data = $(this).data(),
			defaults = {
				item	: 'nav'	
			};
		
		
		var options = $.extend( {}, defaults, data, options );
		
		var	items 	= $(this).find( '.' + options.item ),
			size	= $(this).height(),
			self 	= this,
			current = 0;
		
		var initialize = function(){
			
			$(self).scrollTop(0);
			
			var height = 0;			
			$(items).each( function( index ){
				var h 	= $(this).outerHeight(),
					top = height;
				
				$(this).data('size', { 'height' : h, 'top' : top });
				height += h;
				
				$(this).bind( 'click', function(e){
					e.preventDefault();
					//scrollToIndex( index );
				})
				
			});
			
			$(window).resize(function(){
				resize();
			});
			
			return self;
		}	
		
		var resize = function(){
			size = $(self).height();
			var height = 0;			
			$(items).each( function( index ){
				var h 	= $(this).outerHeight(),
					top = height;
				
				$(this).data('size', { 'height' : h, 'top' : top });
				height += h;				
			});
		}
		
		/**
		 * Scroll to a given index
		 */
		var scrollToIndex = function( index ){
			if( index == current ){
				return;
			}
			
			var item = items[ index ],
				top = $(item).data('size').top;
			
			top = top - ( size / 2 - $(item).data('size').height / 2 );
			if( top < 0 ){
				top = 0;
			}
			
			$(self).animate({
				scrollTop : top
			},{ queue:false, duration:300, complete: function(){
				current = index;
			} } );					
						
		}
		
		this.toIndex = function( index ){
			scrollToIndex( index );
		}
		
		return initialize();	
	}		   
})(jQuery);