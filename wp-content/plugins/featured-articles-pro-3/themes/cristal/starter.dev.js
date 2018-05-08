;(function($){	
	$(document).ready( function(){
		$('.fa_slider_cristal').FeaturedArticles({
			slide_selector 	: '.fa_slide',
			nav_prev 		: '.go-back',
			nav_next		: '.go-forward',
			nav_elem		: '.fa-nav',
			play_video		: '.play-video.single-link',
			full_image		: true,
			animate_opacity : false,
			distance_in		: 0,
			distance_out	: 0,
			begin	: load,
			before	: before,
			after	: after,
			stop	: stop,
			start	: start
			//resize	: resize
		});		
	})
	
	var timer, // stores the timer reference
		mouseOver; // bool true: mouse is over; false: mouse is out
	
	// slide load event
	var load = function( options ){
		// start the timer
		if( options.auto_slide ){
			timer = $(this).find('.timer');
			timer.FA_Timer({
				seconds : (options.slide_duration - 300) / 1000,
				finish	: function(){
					this.reset();
				}
			});
		}
		
		var self = this;
			options = this.settings();
			
		$(this).find('.fa_carousel').CFCarousel({
			visibleItems 	: 3,// o.carousel_visibile_items,
			opacityIdle 	: 1,//o.carousel_opacity_idle,
			opacityOn		: 1,//o.carousel_opacity_active,
			opacityOver 	: 1,//o.carousel_opacity_over,
			cycle			: options.cycle,//o.carousel_nav_cycle,
			animateNavs		: false,//o.carousel_animate_navs,
			threshold		: 50,
			init: function(){
				self.navCarousel = this;				
			},
			navClick: function(d){
				self.goto_index( d.index );
			}
		});
	}
	
	/**
	 * Slider before animation event
	 */
	var before = function( slides ){	
		// reset the timer if any
		if( timer ){
			timer.reset();
		}
		if( this.navCarousel ){
			this.navCarousel.gotoIndex( slides.next_index );
		}	
	}
	
	/**
	 * After animation slider event
	 */
	var after = function( slides ){
		var options 	= this.settings(),
			duration 	= (options.slide_duration - 250) / 1000;
		
		// restart the timer
		if( !mouseOver && !this.stopped && options.auto_slide ){
			timer.restart(( duration ) );
		}		
	}
	
	/**
	 * Auto slide slider stop event
	 */
	var stop = function(){
		if( timer ){
			timer.reset();
		}
		mouseOver = true;	
	}
	
	/**
	 * Auto slide slider start event
	 */
	var start = function(){
		mouseOver = false;
		if( this.animating() || !timer ){
			return;
		}		
		var options 	= this.settings(),
			duration 	= ( options.slide_duration - 250 ) / 1000;		
		timer.restart( duration );		
	}	
})(jQuery);