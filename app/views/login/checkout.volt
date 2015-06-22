{% extends "core/templates/one-column.volt" %}

{% block title %}Login Checkout{% endblock %}

{% block bellow_header %}
    {{widget('FlashMessageWidget')}}
{% endblock %}

{% block content %}
<div class="login-checkout">
    <div class="title-page-large">{{t._('Login Checkout')}}</div>
    <div class="wrap-logincheckout-form">
        <form  method="POST" action="{{url('register/checkout')}}">
            <label for="email">Email</label>
            <input id="email" name="email" type="text" size="30" value="{{email}}">
            <p>{{t._('Already registered?')}}</p>
            <div class="wrap-radio">
                <span>
                    <input type="radio" name="registered" value="1" checked><label for="registered">{{t._('Yes')}}</label>
                </span>
                <span class="password">
                    <label for="password">{{t._('Password')}}</label>
                    <input id="password" name="password" type="password" size="30">
                    <a href="#">{{t._('Forgot Password?')}}</a>
                </span>
                <span><input type="radio" name="registered" value="2" {% if registered == 2 %}checked{%endif%}><label for="registered">{{t._('No')}}</label></span>
                <span><input type="radio" name="registered" value="3" {% if registered == 3 %}checked{%endif%}><label for="registered">{{t._('Guest')}}</label></span>
            </div>
            <button>{{t._('SUBMIT')}}</button>
        </form>
    </div>
    <div class="or-login"> 
    <hr> <p>{{t._('Or Login using')}} :</p>
    </div>
    <div class="wrap-login-sosmed">
        <div class="login-sosmed">
            <div onclick="doingAuth('facebook');" class="sosmed-button facebook">
                <p>Facebook</p>
            </div>
            <div onclick="doingAuth('twitter');" class="sosmed-button twitter">
                <p>TWITTER</p>
            </div>
            <div class="sosmed-button google-plus">
                <p>Google +</p>
            </div>
        </div>
    </div>
</div>
{% endblock %}
