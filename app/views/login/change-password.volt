{% extends "core/templates/one-column.volt" %}

{% block title %}Change Password{% endblock %}

{% block bellow_header %}
    {{widget('FlashMessageWidget')}}
{% endblock %}

{% block content %}
<div class="login">
    <div class="title-page-large">{{t._('RESET PASSWORD')}}</div>
    <div class="wrap-login-form">
        <form method="POST" id="reset_password" action="{{url('change-password')}}">
            <ul>
                <li>
                    <label>{{t._('Please enter your new password on the field below')}},</label>
                </li>
                <li>
                    <label for="password">{{t._('Enter new Password')}}</label>
                    <input id="password" name="password" type="password" size="30">
                </li>
                <li>
                    <label for="confirm">{{t._('Re-enter Password')}}</label>
                    <input id="confirm" name="confirm" type="password" size="30">
                </li>
                <li>
                    <div class="pass-indicator" id='complexity'></div>
                </li>
                <li>
                    <button>{{t._('SUBMIT')}}</button>
                </li>
            </ul>
            
        </form>
    </div>
</div>
{% endblock %}
