$(document).ready(function(){
    $('.bxslider').bxSlider(
	{
      pager:true,
	  pagerCustom: '#bx-pager',
      controls: false
	}
   );
   
//========================= end js slider category ======================
    
//========================= js slider testimonial ======================
    $('.slider-testimonial').bxSlider(
	{
      pagerCustom: '#bx-pager',
      nextSelector: '#slider-next',
      prevSelector: '#slider-prev',
      nextText: 'Onward →',
      prevText: '← Go back',
      controls: true
	}
  );
  
  $('ul.slider-homepage-mobile').bxSlider(
	{
      pagerType: 'full',
      controls: false
	}
  );
});