jQuery(function ($) {
    $('body').on('click', '.wpdm-download-locked.pop-over' ,function () {

        var $dc = $($(this).attr('href'));
        if ($(this).attr('data-ready') == undefined) {

            $(this).popover({
                placement: 'bottom',
                html: true,
                content: function () {

                    return $dc.html();


                }
            });
            $(this).attr('data-ready', 'hide');
        }

        if ($(this).attr('data-ready') == 'hide'){
            $(this).popover('show');
            $(this).attr('data-ready', 'show');
        } else if ($(this).attr('data-ready') == 'show'){
            $(this).popover('hide');
            $(this).attr('data-ready', 'hide');
        }


    return false;
    });

    $('body').on('click', '.po-close' ,function () {

        $('.wpdm-download-link').popover('hide');
        $('.wpdm-download-link').attr('data-ready','hide');

    });
});