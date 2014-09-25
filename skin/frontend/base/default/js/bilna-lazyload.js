$j(document).ready(function(){
 var donkey_lazy = function() {
  $j('.lazy').each(function(){
   var scrollBottom = $j(window).scrollTop() + $j(window).height();
   
   var pos = $j(this).position();
   console.log(scrollBottom);
   console.log(pos.top);
   
   if(scrollBottom >= pos.top){
    console.log(pos.top);
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