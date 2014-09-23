jQuery(document).ready(function () {
	jQuery("#closebtn").click(function () {
		jQuery("#dlg").fadeOut();
		jQuery("#bkg, .success-form-popup").fadeOut();
	});
	jQuery("#bkg").click(function(){
		jQuery("#dlg").fadeOut();
		jQuery("#bkg, .success-form-popup").fadeOut();
	});
	jQuery("#friso-cancel").click(function(){
		jQuery("#dlg").fadeOut();
		jQuery("#bkg, .success-form-popup").fadeOut();
	});

	// Prevent events from getting pass .popup
	jQuery("#dlg").click(function(e){
		e.stopPropagation();
	});
	
	var _frisoCheckout = function (){
	    var ajaxURL     = window.location.protocol+"//"+window.location.host+"/ajaxrequest/gimmickEvent/cartcheckout";

		var data = {};
		data.orderId = jQuery('#order-id').val();
	
	    jQuery.ajax({
	        type: "POST",
	        url : ajaxURL,
	        data: {	data: data },
	        //dataType: 'json',
	        success: function(data){
	        	response = jQuery.parseJSON(data);
	        	
	        	if(response.status == true){
		        	jQuery("#event-form").attr("action", response.promo.callback_url);
		        	jQuery("#gimmick-banner").attr("src", response.promo.banner);
		        	jQuery("#gimmick-tos").html(response.promo.tos);
		        	
		        	jQuery("#event-data").val(JSON.stringify(response.data));
		        	
		        	if(response.status == true){ 
						jQuery("#bkg").fadeIn(200);
						jQuery("#dlg, .success-form-popup").fadeIn(200);
		        	}
		        }
	        },
	        error: function() {
	        }
	    });
	};
    
    _frisoCheckout();
}); 

var _frisoSave = function (){
    var ajaxURL     = window.location.protocol+"//"+window.location.host+"/ajaxrequest/gimmickEvent/accept";

	var data = {};
	data.orderId = jQuery('#order-id').val();

    jQuery.ajax({
        type: "POST",
        url : ajaxURL,
        data: {	data: data },
        //dataType: 'json',
        success: function(data){
        	response = jQuery.parseJSON(data);
        	
        	if(response.status == true){
        		jQuery("#dlg").fadeOut();
        		jQuery("#bkg").fadeOut();
        	}
        },
        error: function() {
        }
    });
};