$("document").ready(function(){
    document.addEventListener("keyup",verifyPassword,true);
    $("#confirm").bind("keyup",function(){
        confirm  = $("#confirm").val();
        password = $("#password").val();
      
        if(confirm != password){
            $("#complexity").html('password and confirm password not match.');
        }else{
            $("#complexity").html('password and confirm password match');
        }
    });
});


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
    var minPasswordLength = 4;
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
        
    }
    
    if ($("#password").val()== "")
    { 
        complexity.html("Enter a random value").addClass("default");
    }
    else if (charPassword.length < minPasswordLength)
    {
        complexity.html("At least " + minPasswordLength+ " characters please!").addClass("weak");
    }
    else if (score<50)
    {
        complexity.html("Weak!").addClass("weak");
    }
    else if (score>=50 && score<75)
    {
        complexity.html("Average!").addClass("strong");
    }
    else if (score>=75 && score<100)
    {
        complexity.html("Strong!").addClass("stronger");
    }
    else if (score>=100)
    {
        complexity.html("Secure!").addClass("strongest");
    }
    
    
}