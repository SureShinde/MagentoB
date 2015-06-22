$(document).ready(function () {
    $(function() {
        //$("img.lazy").lazyload({
        //    threshold : 200
        //});
    });
    
    // datepicker
    $("#birthday").datepicker({
        dateFormat : "dd/mm/yy",
        changeMonth : true,
        changeYear : true,
        yearRange : '1900:' + new Date().getFullYear()
    });
    
    //======================= languange ============================
    $('select#change_language').change(function() {
        location.href = baseUri + 'index/setlang/' + $(this).val();
    });
    
    var lang_name = $('select#change_language').val();
    $('.language').addClass(lang_name);
    //======================= end languange =========================
    
    wwidth = $(window).width();
    wheight = $(window).height();
    
    $( "#tabs-desc-desktop" ).tabs();
    
    $( ".flash-message span" ).click(function(){
        $(".flash-message").hide()
    });
    
    
    var val_select_category =  $('select.category').val();
    $(".placeholder_select_category").html(val_select_category);
    $( 'select.category' ).change(function(){
        var val_select_category =  $(this).val();
        $(".placeholder_select_category").html(val_select_category);
    });
    //======================== header js ===========================
    
    $('.backtotop').click(function(event){
		event.preventDefault();
		$('body,html').animate({
			scrollTop: 0 ,
		 	});
        return false;
	});
    $('.onlinechat').click(function(){
        if ($('.wrap-online-chat').hasClass('in_animation')){
        $('.wrap-online-chat').removeClass('in_animation');
        $('.wrap-online-chat').addClass('out_animation');
        } else {
        $('.wrap-online-chat').removeClass('out_animation');
        $('.wrap-online-chat').addClass('in_animation');
        }
    });
    
    $('.backtotop').hide();
    
    
    $(window).scroll(function () {
        if(wwidth > 468) {
            if ($(this).scrollTop() > 100) {
                $('.backtotop').fadeIn();
            } else {
                $('.backtotop').fadeOut();
            };
        } else if(wwidth < 467){
            
            if ($(this).scrollTop() > 100) {
                $('.backtotop').fadeIn();
                $('.floating-right').fadeIn();
            } else {
                $('.floating-right').fadeOut();
            };
        }

        if(wwidth > 768) {
            if ($(this).scrollTop() > 100) {
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
                 if( $("#"+Id).length){
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
                 //console.log(value);
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
    
    
//======================== end header js ===========================
    
//======================== cart js ===========================
    $('.button-cart').click(function () {
            $('.cart-list-product').slideToggle();
    });
//=================== end cart js ===========================
    
//================= megamenu js ==============================
    
    $(".first-level > ul > li:first-of-type").addClass("active-cat");
    $(".first-level > ul > li").hover(function() {
        $('.first-level > ul > li.active-cat .second-level').hide();
        $('.first-level > ul > li.active-cat div').css("color","inherit");
        $('.first-level > ul > li').removeClass("hover-cat");
        $(this).addClass("hover-cat");
        $(".first-level > ul > li.hover-cat img.lazy").lazyload({
            threshold : 200,
        });
    });
    $(".first-level > ul").mouseleave(function() {
        $('.first-level > ul > li').removeClass("hover-cat");
        $('.first-level > ul > li.active-cat .second-level').show();
        $('.first-level > ul > li.active-cat div').css("color","#F05A2A");
    });
    $('.first-level-url').click(function() {
        if($(this).parent().hasClass("hover-cat") || $(this).parent().hasClass("active-cat")){
            window.location.href = $(this).attr("data-href");
        }else{
            $('.first-level > ul > li.active-cat .second-level').hide();
            $('.first-level > ul > li').removeClass("hover-cat");
            $(this).parent().addClass("hover-cat");
        }
    });
    
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
    $('.wrap-megamenu, .bg-dark-megamenu').click(function() {
        $('.bg-dark-megamenu').toggle();
        $('.megamenu').toggleClass("megamenu-active");
    });
//=======================end megamenu js=============================
    
//================== accordion ================================
    $('.desc_area').hide();
    $('.title_desc').click( function(){
        $('.desc_area').slideUp("slow");
        var id_desc= $(this).attr("data-acc");
        $( '[data-acc='+ id_desc+ ']' ).slideDown("slow");
    });
    $('.content-left .second-level-wrap .wrap-secondlevel-category .second-level, .content-right .second-level-wrap .wrap-secondlevel-category .second-level').click( function(){
        $(this).parents( ".wrap-secondlevel-category" ).toggleClass("active");
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
        var value_type_address = $(".type-address").val();
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

//========================= js customer connect sosial media ===================
    $('.connect-sosmed-area .sosmed-button').click(function() {
        if ($(this).hasClass('connected-sosmed')) {
            return false;
        }
        
        //$(this).toggleClass('connected-sosmed');
        doingAuth($(this).attr('id'), 'in');
    });
//========================= end js customer connect sosial media ===============
    
    $('#newsletter-subscribe-header, #newsletter-subscribe-footer').submit(function() {
        var formId = $(this).attr('id');
        var formData = $(this).serialize();
        
        showLoader();
        disabledForm(formId);

        var request = $.ajax({
            url: baseUri + 'newsletter/subscribe',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == true) {
                    if (customerIsLogin) {
                        $('.free-voucher.sticky-highlight > .sticky-tooltip').remove();
                        $('.row.footer-subscribe').remove();
                    }
                }

                ajaxResponse(response);
                enabledForm(formId);
                resetForm(formId);
                hideLoader();
                
                return false;
            }
        });

        return false;
    });
});

$(document).ajaxStart(function() {
    //$('#ajax-loader').show();
});


$(function() {
    $("img.lazy").lazyload({
        threshold : 200
    });
});

$(document).ajaxStop(function() {
    //$('#ajax-loader').hide();
});

//- type: success, error, notice
function flashMessage(type, message) {
    var oldClass = 'green-flash red-flash yellow-flash';

    if (type == 'success') {
        var newClass = 'green-flash';
    }
    else if (type == 'error') {
        var newClass = 'red-flash';
    }
    else {
        var newClass = 'yellow-flash';
    }

    $('#message').removeClass(oldClass).addClass(newClass).html(message).show();
    $(window).scrollTop(0);
}

function resetForm(formId) {
    $('#' + formId).trigger('reset');
}

function enabledForm(formId) {
    $('#' + formId + ' :input').removeAttr('disabled');
}

function disabledForm(formId) {
    $('#' + formId + ' :input').attr('disabled', 'disabled');
}

function showLoader() {
    $('#ajax-loader').show();
}

function hideLoader() {
    $('#ajax-loader').hide();
}

function ajaxResponse(response) {
    if (response.status == true) {
        flashMessage('success', response.message);
    }
    else {
        flashMessage('error', response.message);
    }
}
