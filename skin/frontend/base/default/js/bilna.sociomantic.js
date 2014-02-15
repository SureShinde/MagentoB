jQuery(document).ready(function () {
	var _sociomanticCall = function (){
	    var s   = document.createElement('script');
	    var x   = document.getElementsByTagName('script')[0];
	    s.type  = 'text/javascript';
	    s.async = true;
	    s.src   = ('https:'==document.location.protocol?'https://':'http://')
	            + 'ap-sonar.sociomantic.com/js/2010-07-01/adpan/bilna-id';
	    x.parentNode.insertBefore( s, x );
	};
	
	var _sociomanticTrigger = function (){
		var event		= jQuery("#3rdparty-type").val();
		var event_value	= jQuery("#3rdparty-value").val();
		var product		= false;
		var basket		= false;

		console.log(1);
		if(event == "category"){
			product = retrieveCategory(event_value);
			console.log(2);
		}else if(event == "product"){
			product = retrieveProduct(event_value);
			console.log(3);
		}else if(event == "cart"){
			basket = retrieveCart();
			console.log(4);
		}else if(event == "cartsuccess"){
			basket = retrieveCartConfirm(event_value);
			console.log(5);
		}else{
			_sociomanticCall();
			console.log(6);
		}
		
	};

	var retrieveCategory = function (category_id){
	    var ajaxURL     = window.location.protocol+"//"+window.location.host+"/baby/ajaxrequest/data/retrievecategory";
	    
		var data = {};
		data.categoryId = category_id;
		
	    jQuery.ajax({
	        type: "POST",
	        url : ajaxURL,
	        data: {	data: data },
	        success: function(data){
	        	response = jQuery.parseJSON(data);
	        	
	        	if(response.status == true){
	        		window.sonar_product = response.data;
	    			
	    		    var s   = document.createElement('script');
	    		    var x   = document.getElementsByTagName('script')[0];
	    		    s.type  = 'text/javascript';
	    		    s.async = true;
	    		    s.src   = ('https:'==document.location.protocol?'https://':'http://')
	    		            + 'ap-sonar.sociomantic.com/js/2010-07-01/adpan/bilna-id';
	    		    x.parentNode.insertBefore( s, x );
	    		    
					return true;
	        	}else{
					return false;
	        	}
	        },
	        error: function() {
				return false;
	        }
	    });
	};
 
	var retrieveProduct = function (product_id){
	    var ajaxURL     = window.location.protocol+"//"+window.location.host+"/baby/ajaxrequest/data/retrieveproduct";
	    
		var data = {};
		data.productId = product_id;
		
	    jQuery.ajax({
	        type: "POST",
	        url : ajaxURL,
	        data: {	data: data },
	        success: function(data){
	        	response = jQuery.parseJSON(data);
	        	
	        	if(response.status == true){
	        		sonar_product = response.data;
	    			
	    		    var s   = document.createElement('script');
	    		    var x   = document.getElementsByTagName('script')[0];
	    		    s.type  = 'text/javascript';
	    		    s.async = true;
	    		    s.src   = ('https:'==document.location.protocol?'https://':'http://')
	    		            + 'ap-sonar.sociomantic.com/js/2010-07-01/adpan/bilna-id';
	    		    x.parentNode.insertBefore( s, x );

					return true;
	        	}else{
					return false;
	        	}
	        },
	        error: function() {
				return false;
	        }
	    });
	};
 
	var retrieveCart = function (){
	    var ajaxURL     = window.location.protocol+"//"+window.location.host+"/baby/ajaxrequest/data/retrievecart";
	    jQuery.ajax({
	        type: "POST",
	        url : ajaxURL,
	        data: {	 },
	        success: function(data){
	        	response = jQuery.parseJSON(data);
	        	
	        	if(response.status == true){
					sonar_basket = response.data;
	    			
	    		    var s   = document.createElement('script');
	    		    var x   = document.getElementsByTagName('script')[0];
	    		    s.type  = 'text/javascript';
	    		    s.async = true;
	    		    s.src   = ('https:'==document.location.protocol?'https://':'http://')
	    		            + 'ap-sonar.sociomantic.com/js/2010-07-01/adpan/bilna-id';
	    		    x.parentNode.insertBefore( s, x );

					return true;
	        	}else{
					return false;
	        	}
	        },
	        error: function() {
				return false;
	        }
	    });
	};
 
	var retrieveCartConfirm = function (order_id){
	    var ajaxURL     = window.location.protocol+"//"+window.location.host+"/baby/ajaxrequest/data/retrieveconfirm";
	    
		var data = {};
		data.orderId = order_id;
		
	    jQuery.ajax({
	        type: "POST",
	        url : ajaxURL,
	        data: {	data: data },
	        success: function(data){
	        	response = jQuery.parseJSON(data);
	        	
	        	if(response.status == true){
					sonar_basket = response.data;
	    			
	    		    var s   = document.createElement('script');
	    		    var x   = document.getElementsByTagName('script')[0];
	    		    s.type  = 'text/javascript';
	    		    s.async = true;
	    		    s.src   = ('https:'==document.location.protocol?'https://':'http://')
	    		            + 'ap-sonar.sociomantic.com/js/2010-07-01/adpan/bilna-id';
	    		    x.parentNode.insertBefore( s, x );

					return true;
	        	}else{
					return false;
	        	}
	        },
	        error: function() {
				return false;
	        }
	    });
	};
	
	_sociomanticTrigger();
}); 