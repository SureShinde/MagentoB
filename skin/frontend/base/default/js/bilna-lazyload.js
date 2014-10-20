$j(window).load(function(){
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