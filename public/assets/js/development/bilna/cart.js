$("document").ready(function(){
    
});

function addToCart(vendorName, productId, name, qty, cartPrice, image, productType){
    vendorName  = typeof  vendorName === 'undefined' ? 'bilna' : 'other';
    productId   = productId;
    name        = name;
    qty         = qty;
    cartPrice   = cartPrice;
    image       = image;
    productType = productType;
    
    if(productType != 'simple'){
        
    }else{
        var data = {vendorName : vendorName, productId : productId,name : name, qty : qty, cartPrice : cartPrice, image : image, productType : productType}
        $.ajax({
            url         : baseUri + 'basket/add',
            data        : data,
            type        : 'POST',
            dataType    : 'json',
            success     : function(data){
                if(data.status == true){
                    window.location = baseUri + 'basket';
                }
            }
        });
    }
}

function updateCart(vendorName, productId){
    vendorName  = vendorName;
    productId   = productId;
    qty         = $("#qty-update-"+vendorName+"-"+productId).val();
    
    var data = {vendorName : vendorName, productId : productId, qty : qty }
    
    $.ajax({
       url  : baseUri + 'basket/update',
       data : data,
       type : 'POST',
       dataType : 'json',
       success : function(data){
           if(data.status == true){
               window.location.reload();
           }
       }
    });
}

function deleteCart(vendorName, productId){
    vendorName  = vendorName;
    productId   = productId;
    
    var data = {vendorName : vendorName, productId : productId}
    
    $.ajax({
       url  : baseUri + 'basket/delete',
       data : data,
       type : 'POST',
       dataType : 'json',
       success : function(data){
           if(data.status == true){
               window.location.reload();
           }
       }
    });
}


function validateVoucher(){
    var voucherCode = $("#voucher-code").val();
    
    
    
    var data = {voucher : voucherCode}
    
    $.ajax({
        url         : baseUri + 'basket/voucher',
        data        : data,
        type        : 'POST',
        dataType    : 'json',
        success     : function(data){
            window.location.reload();
        }
    });
}