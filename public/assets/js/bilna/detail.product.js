$(document).ready(function () {
    wwidth = $(window).width();
    wheight = $(window).height();
    $('#product-option .title-form-review').click(function(){
         $('#product-option .wrap-form-comment form').toggle()
    });
     $('#vendor-option .title-form-review').click(function(){
         $('#vendor-option .wrap-form-comment form').toggle()
    });
    
//================== thumb image detail product js ================================
    if (wwidth > 768){
        var width_thumb_area = $("div.thumb_area").width();
        var width_thumb = width_thumb_area/4;
        var counts = $('div.thumb', $(this)).length;
        var width_wrap_thumb = width_thumb * counts;
        var left_thumb_area = - + width_thumb_area;
        var	max_posisi =  Math.floor(width_wrap_thumb / width_thumb_area);
        $(".thumb").css({"width": width_thumb});
        $(".wrap_thumb").css({"width": width_wrap_thumb});
        if ( width_wrap_thumb > width_thumb_area){
            var posisi = 0;
            $(".nav-next").click(function(){
                 if (posisi < (max_posisi - 1)){
                    posisi += 1;
                }else{
                    posisi=posisi;
                };
                var crot = posisi * (-280);
                        $(".wrap_thumb").css({"left" :  crot });
            });
            $(".nav-prev").click(function(){
                if (posisi > 0){
                    posisi -= 1;
                }else{
                    posisi=posisi;
                };
                var crot = posisi * (-280);
                $(".wrap_thumb").css({"left": crot });
            });
        } else {
            $(".nav-next, .nav-prev").css("display","none")
        }
        
        $('#main-image').mouseover(function(){
            $(this).elevateZoom({
                cursor: "crosshair",
                scrollZoom : true,
                zoomWindowPosition: 1,
            });
         });
        $(".thumb img").click(function(){
            //$(".zoomContainer").remove();
            $(".main-image #main-image").attr('src',$(this).attr("data-src"));
            console.log("url(" + $(this).attr("data-src") + ")");
            $(".zoomContainer .zoomWindowContainer div").css({"background-image" : "url(" + $(this).attr("data-src") + ")"});
            $(this).addClass("active-img");
        });
    } else {
    //console.log(counts)
        var counts = $('.main-image img', $(this)).length;
        var current_img = $('.main-image img').attr("data-img");

        $(".main-image img").on("swipeleft",function(){
            current_img = $(this).attr("data-img");
            current_thumb_img = $('.wrap_thumb .thumb').attr("data-img");
            next_img = +current_img + 1;
            next_thumb = +current_thumb_img + 1;
            if (next_img <= counts){
                $(".main-image img, .thumb").removeClass("active-img");
                $("[data-img='" + next_img + "']").addClass("active-img");
            } else {
                return false;
            }
        });
        $(".main-image img").on("swiperight",function(){
            current_img = $(this).attr("data-img");
            prev_img = +current_img - 1;
            prev_thumb = +current_thumb_img - 1;
            if (prev_img >= 1){
                $(".main-image img, .thumb").removeClass("active-img");
                $("[data-img='" + prev_img + "']").addClass("active-img");
            } else {
                return false;
            };
        });    
    };
//================== end thumb image detail product js ================================
    
});
    