function resetValidation(formId) {
    $('form#' + formId + ' input, form#' + formId + ' select, form#' + formId + ' textarea').removeClass('input-red');
    $('form#' + formId + ' div.tooltip-red').remove();
}

function validationBlur(formId) {
    $('form#' + formId + ' input, form#' + formId + ' select, form#' + formId + ' textarea').each(function(i) {
        var fieldId = $(this).attr('id');
        
        $('form#' + formId + ' #' + fieldId).blur(function() {
            return validateField($(this));
        });
    });
}

function validationSubmit(formId) {
    $('form#' + formId).submit(function() {
        var status = true;
        
        $('form#' + formId + ' input, form#' + formId + ' select, form#' + formId + ' textarea').each(function(i) {
            var fieldId = $(this).attr('id');
            var classes = $(this).attr('class');
            
            if (classes) {
                var classesArr = classes.split(' ');

                $.each(classesArr, function(i, action) {
                    if (validate(formId, fieldId, action) != false) {
                        if (status != false) {
                            status = true;
                        }
                    }
                    else {
                        status = false;
                        return false;
                    }
                });
            }
        });

        return status;
    });
}

function validateField(field) {
    var formId = $(field).closest('form').attr('id');
    var fieldId = $(field).attr('id');
    var classes = $(field).attr('class');
    var status = true;
        
    if (classes) {
        var classesArr = classes.split(' ');

        $.each(classesArr, function(i, action) {
            if (validate(formId, fieldId, action) != false) {
                if (status != false) {
                    status = true;
                }
            } else {
                status = false;
                return false;
            }
        });
    }
    
    return false;
}

function validate(formId, fieldId, action) {
    var fieldValue = $('form#' + formId + ' #' + fieldId).val();
    
    if (action == 'nempty') {
        if (nempty(fieldValue) == false) {
            errorHandling(formId, fieldId, 'This is a required field.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fselect') {
        if (fselect(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please select an option.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fcheck') {
        if (fcheck(formId, fieldId) == false) {
            errorHandling(formId, fieldId, 'Please checked an option.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'femail') {
        if (femail(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please enter a valid email address. For example johndoe@domain.com.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fphone') {
        if (fphone(fieldValue, formId, fieldId) == false) {
            errorHandling(formId, fieldId, 'Please enter a valid phone number. For example 0212902213 or 08123456789.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fdate') {
        if (fdate(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please enter a valid date.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fdatetime') {
        if (fdatetime(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please enter a valid datetime.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'falnumspc') {
        if (falnumspc(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'falnum') {
        if (falnum(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'falphaspc') {
        if (falphaspc(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please use letters only (a-z or A-Z) or spaces only in this field.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'falpha') {
        if (falpha(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please use letters only (a-z or A-Z) in this field.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'falphaaddress') {
        if (falphaaddress(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please use letters only (A - Z, 0-9, ". , ( ) : / # -") in this field.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fnumeric') {
        if (fnumeric(fieldValue) == false) {
            errorHandling(formId, fieldId, 'Please enter a valid number in this field.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fminlen') {
        var fieldValue = $('form#' + formId + ' #' + fieldId).val();
        var minlength = $('form#' + formId + ' #' + fieldId).attr('data-min-length');
        
        if (typeof minlength == 'undefined' || minlength == false) {
            return true;
        }
    
        if (fminlen(fieldValue, minlength) == false) {
            errorHandling(formId, fieldId, 'Please enter minimum ' + minlength + ' characters.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action == 'fmaxlen') {
        var fieldValue = $('form#' + formId + ' #' + fieldId).val();
        var maxlength = $('form#' + formId + ' #' + fieldId).attr('data-max-length');
        
        if (typeof maxlength == 'undefined' || maxlength == false) {
            return true;
        }
    
        if (fmaxlen(fieldValue, maxlength) == false) {
            errorHandling(formId, fieldId, 'Please enter maximum ' + maxlength + ' characters.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    if (action.indexOf('fmatch') != -1) {
        var actionArr = action.split('-');
        var fieldMatchValue = $('form#' + formId + ' #' + actionArr[1]).val();
        
        if (fmatch(fieldValue, fieldMatchValue) == false) {
            errorHandling(formId, fieldId, 'Please make sure your passwords match.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            return true;
        }
    }
    
    return true;
}

function validateMinMax(formId, fieldId) {
    var fieldValue = $('form#' + formId + ' #' + fieldId).val();
    var minlength = $('form#' + formId + ' #' + fieldId).attr('data-min-length');
    var maxlength = $('form#' + formId + ' #' + fieldId).attr('data-max-length');
    var status = true;
    
    if (typeof minlength !== typeof undefined && minlength !== false) {
        if (fminlen(fieldValue, minlength) == false) {
            errorHandling(formId, fieldId, 'Please enter minimum ' + minlength + ' characters.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            status = true;
        }
    }
    
    if (typeof maxlength !== typeof undefined && maxlength !== false) {
        if (fmaxlen(fieldValue, maxlength) == false) {
            errorHandling(formId, fieldId, 'Please enter maximum ' + maxlength + ' characters.');
            return false;
        }
        else {
            successHandling(formId, fieldId);
            status = true;
        }
    }
    
    return status;
}

function isEmpty(v) {
    if (v == '' || v == null) {
        return true;
    }
    
    return false;
}

function nempty(v) {
    if (v == '' || v == '0' || v == null) {
        return false;
    }
    
    return true;
}

function fselect(v) {
    if (v == '' || v == '0') {
        return false;
    }
    
    return true;
    
}

function fcheck(frm, fld) {
    if ($('form#' + frm + ' #' + fld).is(':checked')) {
        return true;
    }
    
    return false;
}

function femail(v) {
    return isEmpty(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v);
}

function fphone(v, frm, fld) {
    if (nempty(v)) {
        var prefixLength = 2;
        var prefix = v.substr(0, prefixLength);
        var phoneLength = v.length;
        var phone = v;

        if (prefix == '62') {
            phone = '0' + v.substr(prefixLength, phoneLength);
            $('form#' + frm + ' #' + fld).val(phone);
        }
    }
    
    return (fnumeric(phone) && fminlen(phone, 7) && fmaxlen(phone, 20));
}

function fdate(v) {
    parseDate = v.split('/');
    var day     = parseDate[0];
    var month   = parseDate[1];
    var year    = parseDate[2];
    
    var test = new Date(year, month, day);
    return isEmpty(v) || !isNaN(test);
}

function fdatetime(v) {
    var vArr = v.split(' ');
    var test = new Date(v[0]);
    
    if (isEmpty(v[0]) || !isNaN(test)) {
        return /^(\d{2}):(\d{2}):(\d{2})$/.text(v[1]);
    }
    
    return false;
}

function falnumspc(v) {
    return isEmpty(v) || /^[a-zA-Z0-9 ]+$/.test(v);
}

function falnum(v) {
    return isEmpty(v) || /^[a-zA-Z0-9]+$/.test(v);
}

function falphaspc(v) {
    return isEmpty(v) || /^[a-zA-Z ]+$/.test(v);
}

function falphaaddress(v) {
    return isEmpty(v) || /^[a-zA-Z0-9.,():\/\#\-\"\' ]+$/.test(v);
}

function falpha(v) {
    return isEmpty(v) || /^[a-zA-Z]+$/.test(v);
}

function fnumeric(v) {
    return isEmpty(v) || /^[0-9]+$/.test(v);
}

function fmatch(v, v2) {
    return v == v2;
}

function fminlen(v, l) {
    return isEmpty(v) || v.length >= l;
}

function fmaxlen(v, l) {
    return isEmpty(v) || v.length <= l;
}

function errorHandling(formId, fieldId, message) {
    $('form#' + formId + ' #' + fieldId).addClass('input-red');
            
    if ($('form#' + formId + ' #advice-validate-entry-' + fieldId).length == 0) {
        $('form#' + formId + ' #' + fieldId).parent().append('<div class="tooltip-red" id="advice-validate-entry-' + fieldId + '">' + message + '</div>');
    }
    else {
        $('form#' + formId + ' #advice-validate-entry-' + fieldId).html(message);
    }
}

function successHandling(formId, fieldId) {
    $('form#' + formId + ' #' + fieldId).removeClass('input-red');
    $('form#' + formId + ' #advice-validate-entry-' + fieldId).remove();
}