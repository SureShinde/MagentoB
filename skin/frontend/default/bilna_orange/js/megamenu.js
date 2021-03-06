    wwidth = $j(window).width();
    $j(window).load(function() {
        
		$j(".megamenu.megamenu-active img, .homepage-banner img").lazyload({
			skip_invisible : true
		});
		$j(".megamenu .first-level > ul > li").hover(function(){
			$j(".homepage-banner .first-level-cat.hover-cat img, .megamenu.megamenu-active .first-level-cat.hover-cat img").lazyload();
        });
	});
    $j(window).scroll(function(){
        //if($j(window).scrollTop() == 0){
        //    $j('.header-container').css("top","41px");
        //}else if($j(window).scrollTop() >= 1){
        //    $j('.header-container').css("top","0");
        //};

        if($j(window).scrollTop() >= 500){
            $j('.header-container').addClass("sticky-mode");
        }else{
            $j('.header-container').removeClass("sticky-mode");
        };
    });
    $j(document).ready(function() {
        $j(".megamenu .first-level > ul").hover(function() {
            $j('.menu-navigation .megamenu').toggleClass("show_banner");
        });
        $j(".megamenu .first-level > ul > li").hover(function() {
            $j('.first-level > ul > li').removeClass("hover-cat");
            $j(this).addClass("hover-cat");
        });
        $j(".megamenu .first-level > ul").mouseleave(function() {
            $j('.first-level > ul > li').removeClass("hover-cat");
        });
        $j('.megamenu .first-level-url').click(function() {
			if($j(this).parent().hasClass("hover-cat") ){
				window.location.href = $j(this).attr("data-href");
			}else{
				$j('.megamenu .first-level > ul > li.active-cat .second-level').hide();
				$j('.megamenu .first-level > ul > li').removeClass("hover-cat");
				$j(this).parent().addClass("hover-cat");
			}
        });
		
        $j('.wrap-megamenu').click(function() {
            if($j("body").hasClass("cms-index-index")){
                if($j(window).scrollTop() >= 500){
                    $j('.bg-dark-megamenu').toggle();
                    $j('.megamenu').toggleClass("megamenu-active");
                    $j(".megamenu.megamenu-active img").lazyload({
                        skip_invisible : true
                    });
                }else{
                    $j('.bg-dark-megamenu').hide();
                    $j('.megamenu').removeClass("megamenu-active");
                }
            } else {
                $j('.bg-dark-megamenu').toggle();
                $j('.megamenu').toggleClass("megamenu-active");
                $j(".megamenu.megamenu-active img").lazyload({
                    skip_invisible : true
                });
            }
        });
        $j('.bg-dark-megamenu').click(function() {
            $j('.megamenu').removeClass("megamenu-active");
            $j(this).hide();
        });
    });