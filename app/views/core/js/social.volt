<div id="fb-root"></div>           
<script src="https://apis.google.com/js/client:platform.js" async defer></script>
<script>
    window.fbAsyncInit = function() {
      FB.init({
        appId      : '{{facebookAppId}}', 
        cookie     : true,  // enable cookies to allow the server to access                           // the session
        xfbml      : true,  // parse social plugins on this page
        version    : 'v2.1' // use version 2.1
      });
    };

    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));


    function doingAuth(type,origin){
        switch(type){
            case 'facebook':
                authFacebook(type,origin);
                break;

            case 'twitter':
                authTwitter(type,origin);
                break;

            case 'google-plus':
                authGoogle(origin);
                break;
        }
    }

    function authFacebook(type,origin){
        FB.login(function(response){
                if(response.authResponse){
                    window.location = '{{url('login-check')}}?method='+type+'&origin='+origin;
                }
            },
            {scope:'email,public_profile,user_birthday'}
        );
    }

    function authTwitter(type,origin){
        window.location = '{{url('login-check')}}?method='+type+'&origin='+origin;
        //ajaxAuth(type);
    }
    
    function authGoogle(origin){
        var additionalParams = {
            'callback'              : function(authResult){
                                        if(authResult['code']){
                                            code = authResult['code'];
                                            window.location = '{{url('login-check')}}?method=google&origin='+origin+'&code='+code;
                                        }else{

                                        }
                                      },
            'clientid'              : '{{googleAppId}}',
            'cookiepolicy'          : 'single_host_origin',
            'requestvisibleactions' : 'http://schema.org/AddAction',
            'scope'                 : 'https://www.googleapis.com/auth/plus.profile.emails.read https://www.googleapis.com/auth/plus.login'
        }
        gapi.auth.signIn(additionalParams);
    }
</script>