$(document).ready(function () {
    // datepicker
    $("#birthday").datepicker({
        format: 'dd/mm/yyyy',
    });
    
    $('select#change_language').change(function() {
        location.href = baseUri + 'index/setlang/' + $(this).val();
    });

    
    wwidth = $(window).width();
    wheight = $(window).height();
    
    $( "#tabs-desc-desktop" ).tabs();
    
    $( ".flash-message span" ).click(function(){
        $(".flash-message").hide()
    });
    
    //======================== header js ===========================
    $("#back-top").css({"bottom":$("#footer").height()+150+"px"});

    $("#back-top").hide();
    $(window).scroll(function () {
            if ($(this).scrollTop() > 600) {
                $('#back-top').fadeIn();
            } else {
                $('#back-top').fadeOut();
            };
			
			//h_height = $(".wrap-header").height();
        if(wwidth > 768) {
			if ($(this).scrollTop() > 90) {
                $(".wrap-header").addClass("sticky-mode");
				//$(".sticky-mode").css({"position":"fixed", "width":"100%","top":"0"});	
                $(".handling-sticky").css({"height" : 100});
				//$(".handling-sticky").css({"height" : + h_height});		
			}
			else {
				//$(".nav-container").css(	{"position":"relative"});	
				$(".handling-sticky").css({"height" :"0"});
                $(".wrap-header").removeClass("sticky-mode");
			};
            
            //======================== popup js ===========================
             var popup = function(Id){
                 if( $("#"+Id).length ){
                    $("#"+Id).css({"display":"block"});
                    popupwidth = $(".box-popup").width();
                    popupheight = $(".box-popup").height();
                    margin_horizontal =  (wwidth/2) - (popupwidth/2);
                    margin_vertical =  (wheight/2) - (popupheight/2);
                    $(".box-popup").css({"left": margin_horizontal, "position" : "fixed", "top":margin_vertical});
                 } else {alert("id ga ada ");}
            };

            var close_popup = function(Id){
                 $("#"+Id).css({"display":"none"});
            }

                //copas disini
                    var wrapPopupId = 'config-prod';
                $('.conf-button').click(function(){
                        popup(wrapPopupId);
                });
                 $('.close-popup').click(function(){
                        close_popup(wrapPopupId);
                });
                //end copas smpe sini

            //======================== end popup js ===========================

        }else{
          
        }
    });
    
	if(wwidth > 768) {
    
    } else {
        //================== select featured homepage =======================
        
            $(".content-tab .featured-1").show();
            $(".content-tab .featured-2").hide();
            $(".content-tab .featured-3").hide();
            $( ".select-featured-mobile" ).change(function () {
                var value = "";
                $( ".select-featured-mobile option:selected" ).each(function() {
                  value = $( this ).attr("value");
                     console.log(value);
                if( value == "select-value-1"){
                        $(".content-tab .featured-1").show();
                        $(".content-tab .featured-2").hide();
                        $(".content-tab .featured-3").hide();
                } else if( value == "select-value-2"){
                        $(".content-tab .featured-1").hide();
                        $(".content-tab .featured-2").show();
                        $(".content-tab .featured-3").hide();
                }else if( value == "select-value-3") {
                        $(".content-tab .featured-1").hide();
                        $(".content-tab .featured-2").hide();
                        $(".content-tab .featured-3").show();
                };
                });

            });
    };
    
    
    $('#back-top a').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 400);
            return false;
    });
     //======================== end header js ===========================
    
    //======================== cart js ===========================
    $('.button-cart').click(function () {
            $('.cart-list-product').slideToggle();
    });
    //=================== end cart js ===========================
    
    
    //================= megamenu js ==============================
     $('.mobile-left-menu .wrap-menu-cat > ul > li').click(function () {
         $(".mobile-left-menu .wrap-menu-cat > ul > li").removeClass("menu-active");
         $(this).addClass("menu-active");
     });
    $('.button-left-menu').click(
        function () {
                $('.mobile-left-menu').css({
                    "margin-left":"0",
                    "right":"50px"
                    });
                $('.wrap-body').css({
                    "margin-right":" -100%",
                    "position": "fixed",
                    "right": "50px",
                    "width": "100%"
                    });
                 $('.mobile-left-menu .close-menu').css({
                    "left": "0",
                    "right": "50px"
                    });
                $('.close-menu-second').css({
                    "display": "block"
                    });
            
        });
     
    $('.close-menu, .close-menu-second').click(
        function () {
                $('.mobile-left-menu').css({
                    "margin-left":"-200%",
                    "right":"auto"
                    });
                $('.wrap-body').css({
                    "margin-right":" 0",
                    "position": "relative",
                    "right": "0",
                    "width": "auto"
                });
                $('.mobile-left-menu .close-menu').css({
                    "left": "auto",
                    "right": "auto"
                });
        });
    $('.wrap-megamenu').click(function (){
           $('.megamenu').slideToggle();
    });
    //=======================end megamenu js=============================

    //===================== filter left js =============================
    $( "#slider-range" ).slider({
      range: true,
      min: 10000,
      max: 1000000,
      values: [ 10000, 1000000 ],
      slide: function( event, ui ) {
        $( "#amount" ).val( "Rp. " + ui.values[ 0 ] + " - Rp. " + ui.values[ 1 ] );
      }
    });
    $( "#amount" ).val( "Rp." + $( "#slider-range" ).slider( "values", 0 ) +
      " - Rp." + $( "#slider-range" ).slider( "values", 1 ) );
    $(".box-filter .wrap-list").niceScroll({touchbehavior:false,cursorminheight:20,cursorcolor:"#aaaaaa",cursoropacitymax:1,cursorwidth:10,autohidemode:false,background:"#d2d2d2"});
    //===================== end filter left js =============================

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
                         if (posisi < max_posisi){
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
				$(".thumb img").click(function(){
					$(".main-image img").attr('src',$(this).attr("data-src"));
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
    
    
    //================== accordion ================================
    $('.desc_area').hide();
    $('.title_desc').click( function(){
        $('.desc_area').slideUp("slow");
        var id_desc= $(this).attr("data-acc");
        $( '[data-acc='+ id_desc+ ']' ).slideDown("slow");
    });
    
    //=================================== edit customer js =====================


    $(".company").hide();
    $(".building_name").hide();
    $(".floor").hide();
    $(".block").hide();

   
    $(".type-address").change(function(){
        var value_address = $(".type-address").val();
        if( value_address == "residence"){
                $(".company").hide();
                $(".building_name").hide();
                $(".floor").hide();
                $(".block").hide();
        } else if( value_address == "apartment"){
                $(".building_name").show();
                $(".floor").show();
                $(".block").show();
                $(".company").hide();
        }else if(value_address == 'office'){
            $(".company").show();
            $(".building_name").show();
            $(".floor").show();
            $(".block").show();

        }
    });
    
    if($(".type-address:selected")){
        value_type_address = $(".type-address").val();
        if( value_type_address == "residence"){
                    $(".company").hide();
                    $(".building_name").hide();
                    $(".floor").hide();
                    $(".block").hide();
            } else if( value_type_address == "apartment"){
                    $(".building_name").show();
                    $(".floor").show();
                    $(".block").show();
                    $(".company").hide();
            }else if(value_type_address == 'office'){
                $(".company").show();
                $(".building_name").show();
                $(".floor").show();
                $(".block").show();
                    
            };
    }
    //================== megamenu js ================================

    
//========================= js slider category ======================
    
    
//========================= end js slider testimonial ======================
//========================= js slider testimonial ======================
    

    
//========================= end js slider testimonial ======================
    
});
