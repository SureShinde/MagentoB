$j(document).ready(function() {   
	$j('#custom_coupon_code').keyup(function(){
		var val_input=$j('#custom_coupon_code').val().length;
		if( val_input > 0){
			$j("#custom-apply-voucher span").css("background","#ff6c6c");
		}else{
			$j("#custom-apply-voucher span").css("background","#d7d7d7");
		}
	});
});