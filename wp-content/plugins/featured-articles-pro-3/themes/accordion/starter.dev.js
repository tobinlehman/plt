/**
 * @package Featured articles PRO - Wordpress plugin
 * @author CodeFlavors ( codeflavors[at]codeflavors.com )
 * @url http://www.codeflavors.com/featured-articles-pro/
 * @version 2.4
 */
;(function($){
	$(document).ready(function(){
		$('.fa-accordion').CFAccordion({
			beforeInit: function( d ){
				var maxH = 0,
					titles = $(d.slides).find('div.info h2.title').css({'width' : d.width});
				
				$.each(d.slides, function(i, slide){
					var title = $(slide).find('div.info h2.title').css({'height':'auto'}),
						pElem = $(slide).find('div.info div.hide').css({'height':'auto'}),
						titleH = $(title).outerHeight();
					
					if( titleH > maxH ){
						maxH = titleH;
					}
					$(slide).data('title', title);
					$(pElem).data('height', $(pElem).outerHeight());
					$(slide).data('text', pElem);
				})
				$(titles).css({'height':maxH});
				this.maxH = maxH;				
			},//*
			idle: function( d ){
				var self = this;
				$.each( d.slides, function(i, slide){
					var text  = $(slide).data('text'),
						title = $(slide).data('title');
					
					if( text.length > 0 ){
						$(text).stop().animate({
							'height':0, 
							'opacity':0}, {
								queue:false, 
								duration:300, 
								complete: function(){
									$(this).addClass('idle');
									$(title).addClass('idle')
											.css({
												'width' : d.width, 
												'height' : self.maxH
									});
						}});
					}else{
						$(title).stop().addClass('idle').show()
								.animate({
									'width' : d.width, 
									'height' : self.maxH
						}, {queue:false, duration:200});
					}	
				});
			},
			open: function( d ){
				var self = this;
				$.each( d.slides, function(i, slide){
					var text  = $(slide).data('text'),
						title = $(slide).data('title');
					
					if( i == d.index ){
						$(title).removeClass('idle')
								.css({
									'width':'auto', 
									'height':'auto'
						});
						
						if( text.length > 0 ){
							var h = $(text).data('height');
							$(text).removeClass('idle').stop().animate({'height':h, 'opacity':1}, {queue:false, duration:300});
						}
						return;
					}
					
					if( $(slide).hasClass('hasPlayer') ){
						$(title).stop().addClass('idle').show()
						.animate({
							'width' : d.width, 
							'height' : self.maxH
						}, {queue:false, duration:200});
					}
					
					$(text).stop().animate({
						'height':0, 
						'opacity':0 },{
							queue:false, 
							duration:300, 
							complete: function(){
								$(this).addClass('idle');
								$(title).addClass('idle')
										.css({
											'width' : d.width,
											'height' : self.maxH
								});
						}});
				});
			}
			//*/
		});
	});	
})(jQuery);