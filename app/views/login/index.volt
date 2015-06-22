{% extends "core/templates/one-column.volt" %}

{% block title %}Login{% endblock %}

{% block bellow_header %}
    {{widget('FlashMessageWidget')}}
{% endblock %}

{% block content %}
<div class="login">           
    <div class="title-page-large">{{t._('Login')}}</div>
    
    <div class="wrap-login-form">
        <form id="form-login" method="POST" action="{{url('login')}}">
            <ul>
                <li>
                    <label for="email">{{t._('Email')}}</label>
                    <input id="email" name="email" type="text" size="20">
                </li>
                <li>
                    <label for="password">{{t._('Password')}}</label>
                    <input id="password" name="password" type="password" size="20">
                </li>
                <li>
                    <a href="{{url('forget-password')}}">{{t._('Forgot your password?')}}</a>
                </li>
                
                <li id="recaptcha" style="display:none;">
                    <script src='https://www.google.com/recaptcha/api.js'></script>
                    <div class="g-recaptcha" data-sitekey="{{ recaptchaAppId }}"></div>
                    <input type="hidden" id="recaptcha-enabled" name="recaptcha-enabled" value="0" />
                </li>
                
                <li>
                    <button>{{t._('SUBMIT')}}</button>
                    <a class="button-reg" href="{{url('register')}}">{{t._('Register now')}}</a> 
                </li>
            </ul>
        </form>
    </div>
    <div class="or-login"> 
    <hr> <p>{{t._('Or Login using :')}}</p>
    </div>
    <div class="wrap-login-sosmed">
        <div class="login-sosmed">
            <div onclick="doingAuth('facebook','out');" class="sosmed-button facebook">
                <p>Facebook</p>
            </div>
            <div onclick="doingAuth('twitter','out');" class="sosmed-button twitter">
                <p>TWITTER</p>
            </div>
            <div onclick="doingAuth('google-plus','out');"class="sosmed-button google-plus" id="gplus">
                <p>Google +</p>
            </div>
        </div>
    </div>
</div>
{% endblock %}