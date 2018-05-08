(function($){
	var SITE_BASE = "https://coordinators.plt.org/";
	$(document).on('ready', function(){
		$('a[href^="http"]').not('a[href*="coordinators.plt.org"]').attr('target','_blank');
		$('#bbpress-forums').fadeIn();
		$('#buddypress').fadeIn();
		$('.search-btn').addClass('closed');
		$('.search-btn').on('click', function(e){
			e.preventDefault();
			if($(this).hasClass('closed')){
				$(this).addClass('open');
				$(this).removeClass('closed'); 
				$('.header-member .search-form').fadeIn();
				$('.header-member .search-btn img').attr('src', SITE_BASE + 'wp-content/themes/cc/img/white-close.png');
			}else{
				$(this).removeClass('open'); 
				$(this).addClass('closed'); 
				$('.header-member .search-form').fadeOut();
				$('.header-member .search-btn img').attr('src', SITE_BASE + 'wp-content/themes/cc/img/search-white@2x.png');
			}
		});
		$('.content a[href$=".pdf"], .content a[href$=".doc"], .content a[href$=".ppt"]').parents('li').css({
			"listStyle": "none",
			"marginBottom" : "10px"
		});
		// if these exist go up to ul then find external links
		$('.content a[href$=".pdf"], .content a[href$=".doc"], .content a[href$=".ppt"],.content a[href^="http"]').parents('ul').addClass("media-group");
		$('.content a[href^="http"]').not('a[href*="coordinators.plt.org"]').parents('li').css({
			// "listStyle": "none"
		});

		$('a[href$=".pdf"]').attr("target", "_blank");
		$('.media-group a').not('a[href$=".pdf"], a[href$=".doc"], a[href$=".ppt"]').attr("name", "external-link").parents('li').css({
			"marginBottom": "10px",
			"listStyle": "none"
		});
		$('.content a[href$=".pdf"], .content a[href$=".doc"], .content a[href$=".ppt"]').each(function(){
			// console.log($(this);
		});
		$('.content .media-group a[href$=".pdf"], .content .media-group a[href$=".doc"], .content .media-group a[href$=".ppt"]').each(function(){
			console.log("media-group list");
			console.log($(this));
		});
	})
})(jQuery);