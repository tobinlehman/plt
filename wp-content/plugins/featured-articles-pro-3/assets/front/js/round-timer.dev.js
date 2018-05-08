/**
 * jQuery FA Timer. Functionality for round timer.
 */
;(function($){
	
	$.fn.FA_Timer = function( options ){
		// multiple
		if (this.length > 1){
            this.each(function() { 
				$(this).FA_Timer(options);				
			});
			return this;
        }
		
		var defaults = {
			seconds : 0,
			finish	: function(){}
		};
		
		var self = this,
			options = $.extend({}, defaults, options),
			timer,
			timerCurrent,
			timerSeconds = options.seconds,
			timerFinish,
			slice,
			pie,
			pie_fill;
		
		var init = function(){		
			timerCurrent = 0;
			timerFinish = new Date().getTime() + ( timerSeconds * 1000 );			
			timer = setInterval( stopWatch, 50 );
			
			if( !slice ){
				slice = $('<div />', {
					'class' : 'slice'
				});
				pie = $('<div />', {
					'class' : 'pie'
				});
				$(self).empty().append( slice.append( pie ) );
			}
			
			return self;
		}

		var drawTimer = function( percent ){
			if( 0 == percent ){
				if( pie ){
					slice.removeClass( 'gt50' );
					pie.css({
						rotate:0
					});
					$(pie_fill).hide();
				}
			}
			
			if( percent > 50 ){
				slice.addClass( 'gt50' );
				if( !pie_fill ){
					pie_fill = $('<div />',{
						'class' : 'pie fill'
					}).appendTo( slice );
				}else{
					$(pie_fill).show();
				}	
			}
			
			var deg = 360 / 100 * percent;
			$(pie).css({
				rotate : deg + 'deg'
			});					
		}
		
		var stopWatch = function(){
			var seconds = ( timerFinish-( new Date().getTime() ) ) / 1000;
			if( seconds <= 0 ){
				drawTimer( 100 );
				clearInterval( timer );
				options.finish.call( self );
			}else{
				var percent = 100-(( seconds / timerSeconds ) * 100 );
				drawTimer( percent );
			}
		}
		
		this.reset = function(){
			clearInterval( timer );
			timerFinish = 0;
			drawTimer(0);
		}
		
		this.restart = function( seconds ){
			self.reset();
			if( seconds ){
				timerSeconds = seconds;
			}			
			init();
		}
		
		return init();
	}
	
})(jQuery);