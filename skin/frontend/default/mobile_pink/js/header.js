    wwidth = $j(window).width();
    
    $j(window).load(function() {
		$j(".megamenu.megamenu-active img, .homepage-banner img").lazyload({
			skip_invisible : true
		});
        $j("img.lazy").lazyload();
	});
    
    $j(document).ready(function() {
        $j(".search-menu").click(function(){
            $j(".search-container-new-mobile").show();
            $j("#twotabsearchtextbox").attr("autofocus");
        });
        $j(".close_search").click(function(){
            $j(".search-container-new-mobile").hide();
        });
        $j(".link-myaccount-mobile").click(function(){
            $j(".header .links li.myaccount").toggle();
        });
        
        $j( ".product-image-zoom" ).mouseover('mousewheel',function(e) {
                e.originalEvent.wheelDelta = 100;
        });
        $j('.wrap-megamenu').click(function() {
            $j('.bg-dark-megamenu').toggle();
            $j('.megamenu').toggleClass("megamenu-active");
        });
         $j('.bg-dark-megamenu').click(function() {
            $j('.megamenu').removeClass("megamenu-active");
            $j(this).hide();
        });

        //================mobile left menu js========================

        $j('.nav-mobile-trigger span.notclick').click(function() {
            $j(this).hide();
            $j('.nav-mobile-trigger span.clicked').show();
            $j('.mobile-left-menu').css({"left":"0","right":"45px"});
            $j('body').css({"overflow":"hidden"});
            $j('.back-left').toggle();
        });
        $j('.direktori_belanja').click(function() {
            $j('.nav-mobile-trigger span.clicked').show();
            $j('.nav-mobile-trigger span.notclick').hide();
            $j('.mobile-left-menu').css({"left":"0","right":"45px"});
            $j('body').css({"overflow":"hidden"});
            $j('.back-left').toggle();
        });
        $j('.mobile-left-menu .wrap-menu-cat > ul > li p').click(function() {
            var data_left_menu_val = $j(this).attr("data-left-menu");
                $j('.mobile-left-menu .wrap-menu-cat .wrap-first-level.' + data_left_menu_val ).addClass("menu-active");
        });

        $j('.back-level-1').click(function() {
                $j('.mobile-left-menu .wrap-menu-cat .wrap-first-level.menu-active').removeClass("menu-active");
                $j('.category-level-2 li').removeClass("open-cat-3");
        });

        $j('.mobile-left-menu .wrap-menu-cat .wrap-first-level .wrap-level2 .category-level-2 li > a,.mobile-left-menu .wrap-menu-cat .wrap-first-level .wrap-level2 .category-level-2 li > .fa').click(function() {
                $j(this).parent().toggleClass("open-cat-3");
        });
        
        //================mobile left menu js========================

        $j('.nav-mobile-trigger span.clicked, .back-button').click(function() {
            $j('.back-level-1').triggerHandler("click");
            $j('.category-level-2 li').removeClass("open-cat-3");
            $j('.nav-mobile-trigger span.clicked').hide();
            $j('.nav-mobile-trigger span.notclick').show();
            $j('.mobile-left-menu').css({"left":"-100%"});
            $j('.wrapper').css({"float":"none","margin-left":"0","position":"relative"});
            $j('body').css({"overflow":"auto"});
            $j('.back-left').toggle();
        });
        $j(window).scroll(function() {
            if ($j(this).scrollTop() > 100) {
                $j(".back-to-top").fadeIn();
            }
            else {
                $j(".back-to-top").fadeOut();
            }
        });
    });
        
$j(document).ready(function() {
	$j(".nav-container ul#nav li.parent a.level-top").live('click', function() {
            if (wwidth < 768) {
                var allPanels = $j('.nav-container li.parent ul').hide();
                
                if ($j(this).hasClass('active-submenu')) {
                    var href = $j(this).attr('href');
                    window.location.href = href;
                }
                else {
                    $j(".nav-container li.parent a.level-top").removeClass("active-submenu");
                    allPanels.slideUp();
                    $j(this).parent().next().slideDown();

                    $j(this).addClass("active-submenu");
                    //$j(this).prev().removeClass("active-submenu");
                    //$j(this).next().removeClass("active-submenu");
                    $j(this).next().css({"display":"block"});
                    return false;
                }
            }	
            else {
                var href = $j(this).attr('href');
                window.location.href = href;
            }
	});
});