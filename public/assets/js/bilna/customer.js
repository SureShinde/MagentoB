$(document).ready(function(){
    
    var url = window.location.href;
    var location = url.search("#");
    var value_data_url = url.slice(location + 1);
    

    if (value_data_url == "edit_customer"){
        edit_profile();
    } else if (value_data_url == "edit_credential"){
        edit_credential()
    } else if (value_data_url == "edit_subscribe"){
        edit_subscribe();
    } else if (value_data_url == "create_credential"){
        create_credential(); 
    }
    
    $("#cancel_detail").click(function(){
        $("form#form_update_profile").addClass('not_edit');
        $("form#form_update_profile input, form#form_update_profile select").attr('disabled','disabled');
        resetValidation('form_update_profile');
    });
    $("#cancel_password").click(function(){
        $("form#form_update_password").addClass('not_edit');
        $("form#form_update_password input").attr('disabled','disabled');
        $("form#form_update_password li.pass_field").css({'display':'none'});
        resetValidation('form_update_password');
    });
    $("#cancel_subscribe").click(function(){
        $("form#form_update_newsletter").addClass('not_edit');
        $("form#form_update_newsletter input").attr('disabled','disabled');
    });
    $("#cancel_cred").click(function(){
        $("form#form_create_credential").addClass('not_edit');
        $("form#form_create_credential input").attr('disabled','disabled');
        $(".not-have-cred").css({'display':'block'});
    });
    
});



function edit_profile() {
    $("form#form_update_profile").removeClass('not_edit');
    $("form#form_update_profile input, form#form_update_profile select").removeAttr('disabled');
}
function edit_subscribe() {
    $("form#form_update_newsletter").removeClass('not_edit');
    $("form#form_update_newsletter input").removeAttr('disabled');
}
function edit_credential() {
    $("form#form_update_password").removeClass('not_edit');
    $("form#form_update_password input").removeAttr('disabled');
    $("form#form_update_password li.pass_field").css({'display':'block'});
}
function create_credential() {
    $("form#form_create_credential").removeClass('not_edit');
    $("form#form_create_credential input").removeAttr('disabled');
    $("form#form_create_credential li.pass_field").css({'display':'block'});
    $(".not-have-cred").css({'display':'none'});
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
                    window.location = baseUri + 'customer/addresses';
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
    showLoader();
    var data = {email : email};
    
    $.ajax({
        async: false,
       data : data,
       type : 'POST',
       dataType : 'json',
       url : baseUri + 'customer/unsubscribe',
       success : function(data){
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