jQuery(function( $ ){
	$sidebar = $('aside.sidebar.sidebar-primary.widget-area');
	$('.mobile .fa').on('click', function(){
		if($(this).hasClass('closed')){
			$(this).addClass('open');
			$(this).removeClass('closed');
			$($sidebar).animate({
				left: '-1%',
				"-webkit-filter": "blur(2px)",
				"filter": "blur(2px)"
			}, 500);
			return;
		}
		if($(this).hasClass('open')){
			$(this).addClass('closed');
			$(this).removeClass('open');
			$($sidebar).animate({
				left: '-52%',
				"-webkit-filter": "blur(0)",
				"filter": "blur(0)"
			}, 500);
			return;
		}
	});
	console.log(location.pathname.split("/")[2]);
	$('.sidebar.sidebar-primary.widget-area li a[href*="/' + location.pathname.split("/")[2] + '"]').addClass('active');
	console.log($('.sidebar.sidebar-primary.widget-area li a[href*="/' + location.pathname.split("/")[2] + '"]'));
	$(".sidebar.widget-area ul li a").each(function() {
        // console.log($(this).attr('href'));
        var linkpath = $(this).attr('href').split("/")[4];
        var pathname = location.pathname.split("/")[2];
        console.log(linkpath);
        console.log(pathname);
        // console.log(location.pathname.split("/")[2])
    });
});