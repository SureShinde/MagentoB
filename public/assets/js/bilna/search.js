$("document").ready(function(){
   $(".search-button").click(function(){
      var category = $("#category").val();
      var query    = $("#query").val();
      
      if(!category && !query){
          
      }else{
          $("#search").submit();
      }
   });
});