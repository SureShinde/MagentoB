function addToCart(groupId, productId, productType, qty) {
    var currentUrl = document.URL;
    var data = {
        groupId: groupId,
        productId: productId,
        qty: qty
    };
    
    if (productType == 'simple') {
        $.ajax({
            async: false,
            url: baseUri + 'addtocart',
            data: data,
            type: 'POST',
            dataType: 'json',
            success : function(response) {
                document.location = currentUrl;
            }
        });
    }
    else {
        //- for product bundle or configurable
        alert('soon');
    }
}

function updateCart(vendorId, vendorOrigin, groupId, productId) {
    var currentUrl = document.URL;
    var qty = $('#qty-update-' + vendorId + '-' + vendorOrigin + '-' + productId).val();
    var data = {
        vendorId: vendorId,
        vendorOrigin: vendorOrigin,
        groupId: groupId,
        productId: productId,
        qty: qty
    };
    
    $.ajax({
        url: baseUri + 'basket/update',
        data: data,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            document.location = currentUrl;
        }
    });
}

function deleteCart(vendorId, vendorOrigin, productId) {
    var currentUrl = document.URL;
    var data = {
        vendorId: vendorId,
        vendorOrigin: vendorOrigin,
        productId: productId
    };
    
    $.ajax({
        async: false,
        url: baseUri + 'basket/delete',
        data: data,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.status == true) {
                document.location = currentUrl;
            }
            else {
                alert(response.message);
            }
        }
    });
}

function validateVoucher() {
    var currentUrl = document.URL;
    var voucherCode = $('#voucher-code').val();
    var data = { voucherCode: voucherCode };
    
    $.ajax({
        url: baseUri + 'basket/addvoucher',
        data: data,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            document.location = currentUrl;
        }
    });
}

function clearVoucher() {
    var currentUrl = document.URL;
    var voucherCode = $('#voucher-code').val();
    var data = { voucherCode: voucherCode };
    
    $.ajax({
        url: baseUri + 'basket/deletevoucher',
        data: data,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
           document.location = currentUrl;
        }
    });
}