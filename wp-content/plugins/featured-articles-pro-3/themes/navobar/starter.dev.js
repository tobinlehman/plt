/**
 * @package Featured articles PRO - Wordpress plugin
 * @author CodeFlavors ( codeflavors[at]codeflavors.com )
 * @url http://www.codeflavors.com/featured-articles-pro/
 * @version 2.4
 */
(function($){
	
	$(window).load(function(){
		$('.fa-slidenav').FeaturedArticles({
			slide_selector 	: '.slide',
			nav_prev 		: '.slide-back',
			nav_next		: '.slide-forward',
			nav_elem 		: '.navigation-items li',	
			play_video		: '.play-video.single-link',
			full_image		: true,
			animate_opacity : false,
			distance_in		: 0,
			distance_out	: 0,
			
			load	 : navobar_load,
			before	 : navobar_before,
			after	 : navobar_after,
			change	 : navobar_change,
			start	 : navobar_start,
			stop	 : navobar_stop
		});
	});
	
	var navobar_load = function( o ){
		var self 	= this,
			options = this.settings();
		this.slide = $(this).find('.navigation-inside').CFSlidingNav({
			'is_mobile' : options.is_mobile,
			'cycle' 	: true,
			'sidenavs'  : false,
			navChange: function(data){
				//self.goto_index(data.index);
			}
		});		
		
		var panels = $(this).find('.content');	
		$.each(panels, function(){
			var h = $(this).height();
			$(this).data('height', h);
			$(this).css('top', -h);
		})
		
		$(panels[this.get_current_index()]).css({'top': 0});
		
		if( options.is_mobile ){
			return;
		}
		
		var lis = $(this).find('.navigation-items li');
		$(lis).mouseenter(function(){
			$(this).addClass('hover');
		}).mouseleave(function(){
			$(this).removeClass('hover');
		})
		
		$(lis).mouseenter(function(){
			var th = $(this).data('thumb');
			
			if( typeof th !== 'undefined'  ){
				var thumb = th;
			}else{
				var thumb = $(this).find('.nav-thumb').detach().appendTo("body");
				$(this).data('thumb', thumb);
			}
			
			var o = $(this).offset();
			o.top-=110;
			$(thumb).css(o).show();
		}).mouseleave(function(){
			$($(this).data('thumb')).hide();
		})
		
	};
	
	var navobar_before = function(d){
		// slide out current panel and bring in next one
		var cPanel = $(d.current).find('.content'),
			nPanel = $(d.next).find('.content'),
			cH = $(cPanel).data('height'),
			nH = $(nPanel).data('height');
			
		$(cPanel).animate({'top':-cH}, {duration:400, queue:false});
		$(nPanel).animate({'top':0}, {duration:400, queue:false});
		
		if( this.tElem )
			this.tElem.detach();
		if( this.timer )
			this.timer.stop();		
	};

	var navobar_after = function(d){
		var navs = this.get_navs();
		$( navs[ this.get_current_index() ] ).append( this.tElem );
		if( this.interval && !this.stopped )
			this.timer.restart();		
	}
	
	var navobar_change = function(d){
		var lis 	= this.get_navs(),
			cIndex 	= d.current_index,
			nIndex 	= d.next_index;	
		this.slide.gotoIndex( nIndex );
	}
	
	var navobar_start = function(){
		var o = this.settings();
		
		if( o.auto_slide && !this.timer ){
			var delay = o.slide_duration;		
			this.tElem = $('<div></div>').attr({'class':'timer'}).css({'width':'0%'});
			
			var navs = this.get_navs();
			
			$( navs[ this.get_current_index() ] ).append( this.tElem );		
			this.timer = $(this.tElem).SlidemixTimer({'delay':delay});	
		}
		
		this.timer.restart();
	}
	
	var navobar_stop = function(){
		if( this.timer )
			this.timer.stopAnimation();
	}			
	
})(jQuery)

/**
 * SlidemixTimer - jQuery plugin
 * Copyright (c) 2011 - http://www.codeflavors.com	
 */

;(function($){
	
	if( typeof SlidemixTimer == 'function' ) return;
	
	// Extending the jQuery core:
	$.fn.SlidemixTimer = function(options){
		
		// run it on multiple elements
		if (this.length > 1){
            return this.each(function() { 
				$(this).SlidemixTimer(options);				
			});			
        }
		
		// set the default values
		var defaults = {
			delay: 1000	
		};
		// extend defaults with options
		var options = $.extend({}, defaults, options),
			self = this;
		
		this.stopped = false;
		
		// initialize the script
		var initialize = function(){
			
			self.css({'width':0}).animate({
				'width':'100%'
			}, {
				queue		: false,
				duration	: options.delay,
				easing		: 'linear',
				complete	: function(){
					self.css({'width':0});
				}
			});
			return self;
		}					
		
		var startAnimation = function(){
			self.stopped = false;
			self.css({'width':0}).animate({'width':'100%'}, {
				queue:false,						  
				duration: options.delay,
				easing:'linear',
				complete: function(){
					self.css({'width':0});
				}
			})
		}	
		
		var pauseAnimation = function(){
			self.stop();
			self.stopped = true;
		}
		
		var stopAnimation = function(){
			pauseAnimation();
			self.css({'width':'0%'});
		}
		
		var restartAnimation = function(){
			stopAnimation();
			startAnimation();
		}
		
		/* public methods */
		self.pause = function(){
			pauseAnimation();
		}
		self.resume = function(){
			if( self.stopped ){
				startAnimation();
			}
		}
		self.stopAnimation = function(){
			stopAnimation();
		}
		self.restart = function(){
			restartAnimation();
		}
		return initialize();
	}	
})(jQuery)

;(function($){
	$.fn.CFSlidingNav = function(options){
		if (this.length > 1){
			this.each(function() { 
				$(this).CFSlidingNav(options);				
			});
			return this;
		}
		// defaults
		var defaultOptions = {
			items 			: '.navigation-items li',
			itemsContainer 	: '.navigation-items',
			cycle 			: false,
			is_mobile		: false,
			sidenavs		: true,
			// events
			change 		: function(){},
			itemChange	: function(){},
			navChange	: function(){}
		};
			
		// merge user options with defaults
		var options 	= $.extend({}, defaultOptions, options),
			self		= this,
			current		= 0,
			endingItem	= 0,
			container 	= $(this).find(options.itemsContainer),
			totalWidth	= $(this).width(),
			itemsWidth	= 0,
			items 		= $(this).find(options.items);
		
		var initialize = function(){
			// get nav items width
			storeItemSizes();
			startSideNavs();
			
			$(items).click(function( e ){
				var index = $.inArray(this, items);
				gotoItem( this, index );
				options.itemChange.call(self,{'index':index});
			})
				
			$(window).resize(function(){
				storeItemSizes();
				totalWidth	= $(self).width();
				gotoItem( items[current], current, true );				
			})
			return self;
		}
		
		var storeItemSizes = function(){
			var left = 0;
			$.each(items, function(i, item){
				var width = $(this).outerWidth();
				$(this).data('width', width);
				$(this).data('left', left);
				
				left += width;
				itemsWidth += width;				
			})
			
			var endWidth = 0,
				endItem = -1;
			for( var t = items.length-1; t >= 0; t-- ){
				var itemWidth = $(items[t]).data('width');
				endWidth += itemWidth;
				if( endWidth  < totalWidth  ){
					endItem = t;
				}
			}
	
			endingItem = endItem;
			
			for( var t = items.length-1; t >= 0; t-- ){
				var itemLeft 	= $(items[t]).data('left'),
					itemWidth	= $(items[t]).data('width');
				
				if( itemLeft + itemWidth < totalWidth ){
					$(items[t]).data('goto', 0);
				}else if( t >= endItem ){
					$(items[t]).data('goto', endItem);
				}else{				
					$(items[t]).data('goto', t);
				}
			}			
		}
		
		var startSideNavs = function(){
			if( !options.sidenavs ){
				return;
			}
			
			var backNav = $(self).parent().find('.slide-back'),
				nextNav = $(self).parent().find('.slide-forward');
			
			$(backNav).click(function(e){
				e.preventDefault();
				var index = current - 1 < 0 ? ( options.cycle ? items.length-1 : 0 ) : current - 1;
				gotoItem( items[index], index );
				options.navChange.call(self, {'index':index, 'direction':-1});
			})
			
			$(nextNav).click(function(e){
				e.preventDefault();
				var index = current + 1 >= items.length ? ( options.cycle ? 0 : items.length-1 ) : current + 1;
				gotoItem(items[index], index);
				options.navChange.call(self, {'index':index, 'direction':1});
			})
			
		}
		
		var gotoItem = function( item, index, force ){
			if( index == current && !force ){
				return;
			}
			
			if( index < current ){
				if( index > endingItem ){
					var left = $(items[endingItem]).data('left');
				}else{
					var gotoIndex = $(item).data('goto'),
						left = $(items[gotoIndex]).data('left');
				}			
			}else if( index > current || index == current ){
				var gotoIndex	= $(item).data('goto'),
					left		= $(items[gotoIndex]).data('left');
			}else{
				var ind 	= index - 1 < 0 ? 0 : index - 1,
					left 	= $(items[ind]).data('left');
			}
			
			
			
			//if( current <= endingItem || index == endingItem || index == 0 ){
				$(container).stop().animate({'margin-left': 0 == left ? 0 : -left}, {queue:false, duration:300});
			//}
			
			$(items[current]).removeClass('active');
			$(item).addClass('active');
			
			options.change.call(self, {'index' : index});			
			current = index;
		}
		
		this.gotoIndex = function(index){
			gotoItem( items[index], index );
		}
		
		return initialize();
	}
})(jQuery);