$(document).ready(function(){
    $('.list-brand a[href^="#"], .list-vendor a[href^="#"]').on('click',function (e) {
        e.preventDefault();
        var target = this.hash,
            targetx = jQuery(target);
        jQuery('html, body').stop().animate({
            'scrollTop': targetx.offset().top
        }, 500, 'swing', function () {
            window.location.hash = target;
        });
    });
});