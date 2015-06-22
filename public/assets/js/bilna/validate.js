
//========================= js login ===================
$(document).ready(function () {
    /*
    $(".wrap-login-form #email, .register-form #email").focusout(function(){
        value = $(this).val();
        filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        if (filter.test(value)) {
            $(this).siblings("div.tooltip").remove();
            $(this).parent().css({"border":"0"});
            $(this).css({"border-color":"green"});
            //$("<div class='tooltip tooltip-green'>this true</div>").insertAfter(this);
            return true;
        }
        else if(value == null || value == ""){
            $(this).siblings("div.tooltip").remove();
            $(this).css({"border":"1px solid red"});
            $("<div class='tooltip tooltip-red'>cannot be empty</div>").insertAfter(this);
        }
        else{
            $(this).siblings("div.tooltip").remove();
            $(this).css({"border":"1px solid red"});
            //$(":after").css({"border-color":"red","content":"not an Email","position":"absolute","right":0});
            $("<div class='tooltip tooltip-red'>not an Email</div>").insertAfter(this);
            return false;
        }
    });
    $(".wrap-login-form #password, .register-form form input:not(#email)").focusout(function(){
        value = $(this).val();
        if(value == null || value == ""){
            $(this).siblings("div.tooltip").remove();
            $(this).css({"border":"1px solid red"});
            $("<div class='tooltip tooltip-red'>cannot be empty</div>").insertAfter(this);
        }
        else{
            $(this).siblings("div.tooltip").remove();
            $(this).css({"border":"1px solid #c9c9c9"});
            //$(this).css({"border-color":"green"});
        }
    });
    */
    $("#password").bind("keyup",function(){
       verifyPassword(); 
    });
    
    $("#confirm").bind("keyup",function(){
        confirm  = $("#confirm").val();
        password = $("#password").val();
      
        if(confirm != password){
            $("#complexity").removeAttr('class').addClass('pass-indicator').addClass("weak");
            $("#complexity").html('password not match.');
        }else{
            $("#complexity").removeAttr('class').addClass('pass-indicator').addClass("stronger");
            $("#complexity").html('password match');
        }
    });
});
//========================= end js login ===============
    
    
function validateEmail(email){
    var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if(pattern.test(email))
        return true;
    return false;
}

function verifyPassword(){
    var stringPassword = $("#password").val();
    
    
    
    var charPassword   = stringPassword.split("");
    
    var complexity = $("#complexity");
    
    var strPassword;
    var minPasswordLength = 8;
    var baseScore = 0; 
    var score = 0;

    var upperCase = '/[A-Z]/g';
    var lowerCase = '/[a-z]/g';
    var numericCase = '/[0-9]/';
    var symbolsCase = '/[\~\!\@\#\$\%\^\&\*\(\)\-\_\+\=]/g';

    var num = {};
    num.Excess = 0;
    num.Upper = 0;
    num.Numbers = 0;
    num.Symbols = 0;

    var bonus = {};
    bonus.Excess    = 5;
    bonus.Upper     = 10;
    bonus.Numbers   = 10;
    bonus.Symbols   = 100;
    
    bonus.Combo      = 0;
    bonus.FlatLower  = 0;
    bonus.FlatNumber = 0;
    
    if(charPassword.length > minPasswordLength){
        baseScore = 50;
        for(i = 0; i < charPassword.length; i++){
            if(charPassword[i].match(upperCase)){ num.Upper++}
            if(charPassword[i].match(lowerCase)){ num.Numbers++}
            if(charPassword[i].match(symbolsCase)){ num.Symbols++}
        }
        
        num.Excess = charPassword.length - minPasswordLength;
        
        if (num.Upper && num.Numbers && num.Symbols)
        {
            bonus.Combo = 40; 
        }

        else if ((num.Upper && num.Numbers) || (num.Upper && num.Symbols) || (num.Numbers && num.Symbols))
        {
            bonus.Combo = 30; 
        }

        if (stringPassword.match(/[\s]/))
        { 
            bonus.FlatLower = -100;
        }

        if (stringPassword.match(/^[\s0-9]+$/))
        { 
            bonus.FlatNumber = -35;
        }
        
        score = baseScore + (num.Excess*bonus.Excess) + (num.Upper*bonus.Upper) + (num.Numbers*bonus.Numbers) + 
                (num.Symbols*bonus.Symbols) + bonus.Combo + bonus.FlatLower + bonus.FlatNumber; 
        
    };
    
    if($("#confirm").val() != ""){
        confirm  = $("#confirm").val();
        password = $("#password").val();
        
        if(password != confirm){
            complexity.html("Password not match");
        }else{
            complexity.html("Password match");
        }
    }else{
        if ($("#password").val()== "")
        { 
            complexity.removeAttr('class').addClass('pass-indicator');
            complexity.html("");
        }else if (charPassword.length < minPasswordLength)
        {
            complexity.removeAttr('class').addClass('pass-indicator');
            complexity.html("At least " + minPasswordLength+ " characters please!").addClass("weak");
        }else if (score<50)
        {
            complexity.removeAttr('class').addClass('pass-indicator');
            complexity.html("Weak!").addClass("weak");
        }
        else if (score>=50 && score<75)
        {
            complexity.removeAttr('class').addClass('pass-indicator');
            complexity.html("Average!").addClass("strong");
        }
        else if (score>=75 && score<100)
        {
            complexity.removeAttr('class').addClass('pass-indicator');
            complexity.html("Strong!").addClass("stronger");
        }
        else if (score>=100)
        {
            complexity.removeAttr('class').addClass('pass-indicator');
            complexity.html("Secure!").addClass("strongest");
        }
    }
    
}

function validateName(fullname){
    var re = /^[a-zA-Z ]+$/;
    return re.test(fullname);
}
    
function validateDetail(detail){
    if(detail.length > 0 && detail.length < 1024)
        return true;
    else
        return false;
}

function validateNumber(number){
    var re = /^[0-9]+$/;
    return re.test(number);
}

function validateNotEmpty(field){
    if(field)
        return true;
    return false;
}

//========================= end js register ===============