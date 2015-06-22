$("document").ready(function(){
    $("#slider-range").on("slidechange", function(){
        var priceRange     = $("#amount-price").val().split("-");
        var currentUrl     = document.URL;

        lowerPrice     = priceRange[0];
        upperPrice     = priceRange[1];
        
        var rangeQueryString    = 'price='+lowerPrice+'-'+upperPrice;
        var splitUrl            = currentUrl.split("?");
        var base_url            = splitUrl[0];
        var queryString         = splitUrl[1];
        
        var getData      = new Array();
        if(queryString){
            parsingQueryString = new Array();
            parsingQueryString = queryString.split("&");
            parsingQueryString.sort();
            for(i = 0; i< parsingQueryString.length; i++){
                if(parsingQueryString[i].indexOf('price') != -1){
                    continue;
                }
                getData.push(parsingQueryString[i]);
            }
        }   
        getData.push(rangeQueryString);
        var newCurrentUrl       = base_url +'?'+getData.join('&');
        window.location.href    = newCurrentUrl;
    });
    
    
    $(".filter-choice").change(function(){
       var url   = $(this).parent().attr('data-url'); 
       var attr  = $(this).parent().attr('data-attr');
       var label = $(this).parent().attr('data-label');
       
       var isChecked = $(this).is(":checked");
       if(!isChecked){
            var splitUrl     = url.split("?");
            var base_url     = splitUrl[0];
            var queryString  = splitUrl[1];
            
            var getData      = new Array();
            getData          = queryString.split("&");
            getData.sort();
            var currentQuery        = "";
            var otherQuery          = new Array();
            for(i = 0; i < getData.length; i++){
                if(getData[i].indexOf(attr) != -1){
                   currentQuery = getData[i];
                }else{
                    otherQuery.push(getData[i]);
                }
            }
            
            
            var splitQuery   = currentQuery.split("=");
            var attrGet      = splitQuery[0];
            var attrParam    = splitQuery[1].split(",");
            attrParam.sort();
            var newParam     = new Array();
            for(j = 0; j < attrParam.length; j++){
                if(attrParam[j] == label){
                    continue;
                }
                newParam.push(attrParam[j]);
            }
            
            if(newParam.length > 0){
                var params = newParam.join(",");
                var newQueryString = attrGet+'='+params;
                otherQuery.push(newQueryString);
            }
            allQueryString = otherQuery.sort().join("&");
            
            url = base_url + (allQueryString != '' ? "?" + allQueryString : "");
        }
       
       window.location.href = url;
    });
    
    
});