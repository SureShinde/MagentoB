$(document).ready(function(){
    
});


function editProfile(){
    window.location = baseUri + 'customer/detail';
}

function addAddress(){
    window.location = baseUri + 'customer/addresses/add';
}

function editAddress(id){
    window.location = baseUri + 'customer/addresses/edit/'+id;
}


function deleteAddress(addressId, customerId){
    var data = { addressId : addressId, customerId : customerId};
    var confirmation = confirm("Do you sure want to delete this data ?");
    
    if(confirmation == true){
        $.ajax({
            data     : data,
            url      : baseUri + 'customer/addresses/delete/',
            type     : 'POST',
            dataType : 'json',
            success  : function(data){
                if(data.status == true)
                    window.location.reload();
                if(data.status == false)
                    alert('Delete address is failed');
            }
        });
    }
    
}

function backToAddress(){
    window.location = baseUri + 'customer/addresses';
}

function updateDetail(note){
    $("#"+note).val(note);
    $("#form_"+note).submit();
}

function unsubscribe(email){
    var data = {email : email}
    
    $.ajax({
       data : data,
       type : 'POST',
       dataType : 'json',
       url : baseUri + 'customer/unsubscribe',
       success : function(data){
           if(data.status == true)
               window.location.reload();
       }
       
    });
}

function subscribeNow(){
    var email = $("#subscribe-email").val();
    var data = {email : email}
    $.ajax({
        data : data,
        type : 'POST',
        dataType : 'json',
        url : baseUri + 'customer/subscribe',
        success :function(data){
            if(data.true)
                window.location.reload();
        }
    });
}