jQuery(document).ready(function(){
    jQuery('.cmon-swingin').on('click',function (e) {
    e.preventDefault();
    var target = this.hash,
    targetx = jQuery(target);
    jQuery('html, body').stop().animate({
    'scrollTop': targetx.offset().top
    }, 900, 'swing', function () {
    window.location.hash = target;
    });
});
}); 


// note: Just add ".cmon-swingin" class on your div 