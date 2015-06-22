{% extends "core/templates/one-column.volt" %}

{% block title %}Forget Password{% endblock %}

{% block bellow_header %}
    {{widget('FlashMessageWidget')}}
{% endblock %}

{% block content %}
    <div class="login">    
    <div class="title-page-large">{{t._('Forget Password')}}</div>
    <div class="wrap-login-form">
        <form method="POST" action="{{url('forget-password')}}">
            <ul>
                <li> 
                    <label for="email">{{t._('Email')}}</label>
                    <input id="email" name="email" type="text" size="30">
                </li>
                <li>
                    <button>{{t._('SUBMIT')}}</button>
                </li>
            </ul>
        </form>
    </div>
</div>
{% endblock %}