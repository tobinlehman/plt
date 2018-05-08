/* Fixed bar JQuery by Gabriele Venturi */


jQuery(function( $ ){

var breakpoint = 1024;
	var sidebarClass = "sidebar-primary";
	var wrapClass = "wrap"; //Must be a parent of the sidebar object
	var animation_speed = 0;
	 
	//DO NOT MODIFY FROM HERE
	$(document).ready(function(){
		//Set the sidebar and the container objects
		var $window = $(window);
		var $document = $(document);
		var $sidebar = $('.'+sidebarClass);
		var $wrap = $sidebar.closest($('.'+wrapClass));
		var window_min = 0;
		var window_max = 0;
		var threshold_offset = 550;
		var adjust_bar = 0;
		var last_margin = 0;
		var last_scroll = 0;
		
		//Set the new sidebar position if is a desktop version
		if($document.outerWidth() >= 700){
			//$sidebar.css("top", $sidebar.offset().top);
			$sidebar.css("position", "absolute");
		} else {
			$height = $(window).height() + 'px';
			$sidebar.css("position", "fixed");
			$sidebar.css("top", "0px");
			$sidebar.css("z-index", "99999");
			$sidebar.css("left", "-50%");
			$sidebar.css("height", $height);
			$sidebar.css("max-height", $height);
			return;
		}
		
		//Set max and min positon the sidebar can reach
		function set_limits(){
			//max and min container movements
			var max_move = $wrap.offset().top + $wrap.height() - $sidebar.height();
			var min_move = $wrap.offset().top;
			//save them
			$sidebar.attr("data-min", min_move).attr("data-max",max_move);
			//window thresholds so the movement isn't called when its not needed!
			//you may wish to adjust the freshhold offset
			window_min = min_move - threshold_offset;
			window_max = max_move + threshold_offset;
		}
		set_limits();
		
		function window_scroll(){
			//if the window is within the threshold, begin movements
			if($document.outerWidth() < breakpoint){
				return;
			}
			if($window.scrollTop() >= window_min && $window.scrollTop() < window_max){
				//reset the limits (optional)
				set_limits();
				//move the sidebar
				move_sidebar();
			}
		}
		$window.bind("scroll", window_scroll);
		
		function move_sidebar(){
			var wst = $window.scrollTop();
			//if the window scroll is within the min and max (the container will be "sticky";
			if(wst >= $sidebar.attr("data-min") && wst < $sidebar.attr("data-max")){

				$sidebar.css({
					"margin-top": "0px",
					"position": "fixed",
    				"top": "0px",
    				"overflow-y": "auto",
    				"height": "auto",
    				"max-height": $(window).height() +"px"
				});
			}else if(wst < $sidebar.attr("data-min")){//If the window scroll is below the minimum 
				console.log("2");
				var margin_top = 0;
				adjust_bar = 1*$sidebar.attr("data-min");
				$sidebar.finish().animate({"margin-top": "-="+last_margin}, animation_speed);
				$sidebar.css({
					"margin-top": "0px",
					"position": "absolute",
    				"top": "215px",
    				"overflow-y": "inherit",
    				"height": $(window).height() +"px",
    				"max-height": "auto",
    				"bottom" : '0px'
				});
				// $sidebar.css({
				// 	"width":"30%"
				// });
				$sidebar.animate({
					"width": "300px"
				}, 200);
			}else if(wst >= $sidebar.attr("data-max")){//If the window scroll is above the maximum 
				console.log("3");
				$sidebar.css({
					"bottom": "70px",
					"top": "inherit"
				});
				var margin_top = $sidebar.attr("data-max")-$sidebar.attr("data-min")-$wrap.offset().top;
				adjust_bar = adjust_bar = -1*($sidebar.height() + $wrap.offset().top - $window.height());;
				$sidebar.finish().animate({"margin-top": "+="+(margin_top-last_margin)}, animation_speed);
			}
			
			last_margin = margin_top;
			last_scroll = $window.scrollTop();
		}
		//do one container move on load
		move_sidebar();
	});	
		
});