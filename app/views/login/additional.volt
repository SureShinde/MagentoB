{% extends "core/templates/one-column.volt" %}

{% block title %}login checkout{% endblock %}

{% block bellow_header %}
    {{widget('FlashMessageWidget')}}
{% endblock %}

{% block content %}
<div class="register col-lg-4 col-md-5 col-sm-6 col-xs-12">
    <div class="title-page-large">{{t._('REGISTER')}}</div>
    <div class="register-form">
        <div class="logo-sosmed">

        </div>
        <form method="POST" action="{{url('login/additional-info')}}">
            <ul>
                <li>
                    <label for="email">{{t._('Email')}}</label>
                    <input id="email" name="email" type="text" size="30" placeholder="{{t._('Enter your email')}}">
                </li>
                
                <li>
                    <label for="gender">{{t._('Gender')}}</label>
                    <select id="gender" name="gender" class="fselect">
                        <option value="">{{t._('Pilih Gender')}}</option>
                    </select>
                </li>
                
                <li>
                    <label for="birthday">{{t._('Birthday')}}</label>
                    <input id="birthday" type="text" name="birthday" size="30" class="nempty fdate" readonly="readonly" />
                </li>
                
                <li>
                    <button>{{t._('SUBMIT')}}</button>
                </li>
                
            </ul>
        </form>
    </div>
</div>
{% endblock %}
