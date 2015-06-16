$j(window).load(function() {
    var donkey_check_menuactive = function() {
        $j(('.nav-container')).hide();
        
        if ($j('#baby').hasClass('active')) {
            $j('.nav-container.nav-baby').show();
	}
        else if ($j('#perlengkapan-rumah').hasClass('active')) {
            $j('.nav-container.nav-perlengkapan-rumah').show();
	}
        else if ($j('#grocery').hasClass('active')) {
            $j('.nav-container.nav-grocery').show();
        }
        else if ($j('#supermarket').hasClass('active')) {
            $j('.nav-container.nav-supermarket').show();
        }
        else if ($j('#kosmetik').hasClass('active')) {
            $j('.nav-container.nav-kosmetik').show();
        }
    };
 
    donkey_check_menuactive();
 
 var donkey_lazy = function() {
  $j('.lazy').each(function(){
   var scrollBottom = $j(window).scrollTop() + $j(window).height();
   
   var pos = $j(this).offset();
   if(scrollBottom >= pos.top){
   
    $j(this).addClass("donkey-image");
    $j(this).removeClass("lazy");
    
    $j(this).attr('src', $j(this).data('src'));
   }
  });
 };
 
 donkey_lazy();
 
 $j(window).scroll(function(){
  donkey_lazy();
 });
});