$('form#form-login').submit(function() {
    var formId = $(this).attr('id');
    var formData = $(this).serialize();

    showLoader();
    disabledForm(formId);
    
    var request = $.ajax({
        url: baseUri + 'login/logincheck',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            ajaxResponse(response);
            
            if (response.status == true) {
                if (response.redirectPage) {
                    window.location.href = response.redirectPage;
                }
                else {
                    enabledForm(formId);
                    resetForm(formId);
                    hideLoader();
                }
            }
            else {
                enabledForm(formId);
                resetForm(formId);
                
                if (response.recaptcha == true) {
                    $('#' + formId + ' #recaptcha').show();
                    $('#' + formId + ' #recaptcha-enabled').val(1);
                    grecaptcha.reset();
                }
                
                hideLoader();
            }
            
            return false;
        }
    });

    return false;
});