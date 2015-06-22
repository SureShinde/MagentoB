$('form#form-social-additionalinfo').submit(function() {
    //- login/additional-info

    var formId = $(this).attr('id');
    var formData = $(this).serialize();

    showLoader();
    disabledForm(formId);
    
    var request = $.ajax({
        url: baseUri + 'social/checkadditional',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            enabledForm(formId);
            hideLoader();
            if(response.status == true){
                if(response.message != '')
                {
                    window.location.href = baseUri + 'customer/linked-account';
                } else {
                    window.location.href = baseUri;
                }
            }else{
                flashMessage('error', response.message);
            }
            return false;
        }
    });

    return false;
});