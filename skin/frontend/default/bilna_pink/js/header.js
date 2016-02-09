
    wwidth = $j(window).width();
    $j(window).load(function() {

		$j(".megamenu.megamenu-active img, .homepage-banner img").lazyload({
			skip_invisible : true
		});
		$j(".homepage-banner .first-level > ul > li").hover(function(){
			$j(".homepage-banner .first-level-cat.hover-cat img").lazyload();
        });
        $j("img.lazy").lazyload();
	});
    $j(document).ready(function() {
		$j("img.lazy, .wrap-banner img:not(.image-slider), .warp-banner img").after('<div class="loader"><img class="loader-img" src="<?php echo Mage::getBaseUrl(); ?>skin/frontend/base/default/images/loading.gif"></div>');
		$j(".homepage-banner .first-level > ul > li:first-of-type").addClass("active-cat");
        $j(".homepage-banner .first-level > ul > li").hover(function() {
            $j(".homepage-banner .first-level-cat.hover-cat img").lazyload();
            $j('.homepage-banner .first-level > ul > li.active-cat .second-level').hide();
			$j('.homepage-banner .first-level > ul > li.active-cat').css("background","#fff");
			$j('.homepage-banner .first-level > ul > li.active-cat .fa').css("color","#b3b3b3");
            $j('.homepage-banner .first-level > ul > li').removeClass("hover-cat");
            $j(this).addClass("hover-cat");
        });
        $j('.homepage-banner .first-level-url').click(function() {
			if($j(this).parent().hasClass("hover-cat") ){
				window.location.href = $j(this).attr("data-href");
			}else{
				$j('.homepage-banner .first-level > ul > li.active-cat .second-level').hide();
				$j('.homepage-banner .first-level > ul > li').removeClass("hover-cat");
				$j(this).parent().addClass("hover-cat");
			}
        });
		$j( ".product-image-zoom" ).mouseover('mousewheel',function(e) {
				e.originalEvent.wheelDelta = 100;

		});
		 $j('.bg-dark-megamenu').click(function() {
            $j('.megamenu').removeClass("megamenu-active");
            $j(this).hide();
        });


        $j('.web_feature .inspirasi,.wrap-navigation.inspirasi').hover(function() {
            $j('.wrap-navigation.inspirasi').toggle();
            $j('.wrap-navigation.belanja').toggle();
            
        });
        $j('.nav-mobile-trigger').click(function() {
            $j('.mobile-left-menu').css({"width":"100%","left":"0"});
            $j('.wrapper').css({"float":"left","margin-left":"70%","position":"fixed"});
            $j('body').css({"overflow":"hidden"});
            $j('.mobile-left-menu .back-left').css({"position":"fixed"});
        });

        $j('.mobile-left-menu .wrap-menu-cat > ul > li p').click(function() {
			var data_left_menu_val = $j(this).attr("data-left-menu");
			if ($j('.mobile-left-menu .wrap-menu-cat > ul > li.' + data_left_menu_val).hasClass("menu-active")){
				$j('.mobile-left-menu .wrap-menu-cat > ul > li.' + data_left_menu_val).removeClass("menu-active");
			} else {
				$j('.mobile-left-menu .wrap-menu-cat > ul > li').removeClass("menu-active");
				$j('.mobile-left-menu .wrap-menu-cat > ul > li.' + data_left_menu_val ).addClass("menu-active");
			};
        });
        //================mobile left menu js========================

        $j('.back-button').click(function() {
            $j('.mobile-left-menu').css({"width":"100%","left":"-100%"});
            $j('.wrapper').css({"float":"none","margin-left":"0","position":"relative"});
            $j('body').css({"overflow":"auto"});
			$j('.mobile-left-menu .back-left').css({"position":"absolute"});
        });

        if (wwidth < 768) {
            $j('.nav-mobile-trigger').click(function(e) {
                e.preventDefault();
                $j('.mini-cart-mobile').slideUp();
                $j('.my-account-mobile').slideUp();
                $j('.nav-container').slideToggle();
            });

            $j('#link-minicart').click(function(e) {
                e.preventDefault();
                $j('.nav-container').slideUp();
                $j('.my-account-mobile').slideUp();
                $j('.mini-cart-mobile').slideToggle();
            });

            $j('.link-myaccount-mobile').click(function(e) {
                e.preventDefault();
                $j('.nav-container').slideUp();
                $j('.mini-cart-mobile').slideUp();
                $j('.my-account-mobile').slideToggle();
            });
        }
        else {
            $j('#link-minicart').click(function(e) {
                e.preventDefault();
                $j('.mini-cart').slideToggle();
            });

            $j('#link-minicart-sticky').click(function(e) {
                e.preventDefault();
                $j('.mini-cart-sticky').slideToggle();
            });
	}

	$j(".nav-container ul#nav li.parent a").live("mouseover", function() {
            if (wwidth < 768) {
                return  false;
            }
	});

	$j(".nav-container ul#nav li.active-submenu").live("mouseover", function() {
            if (wwidth < 768) {
                return  false;
            }
	});

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
$j(window).scroll(function() {
    if ($j(this).scrollTop() > 100) {
        $j(".back-to-top").fadeIn();
    }
    else {
        $j(".back-to-top").fadeOut();
    }
});