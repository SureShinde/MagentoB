{% extends "core/templates/one-column.volt" %}
{% block title %}{{t._('Registration')}}{% endblock %}
{% block head %}{% include 'core/templates/content/head.volt' %}{% endblock %}


{% block bellow_header %}
    {{widget('FlashMessageWidget')}}
{% endblock %}

{% block content %}
<div class="register">
    <div class="title-page-large">{{t._('Register')}}</div>
    <div class="register-form">
        <form id="form-register" method="POST" action="{{url('register')}}">
            <ul>
                <li class="">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="firstname">{{t._('First Name')}}</label>
                        <input id="firstname" name="firstname" type="text" size="30" placeholder="{{ t._('e.g John') }}" value="{#register['firstName']#}" class="nempty falpha fmaxlen" maxlength="32" data-max-length="32" />
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="lastname">{{t._('Last Name')}}</label>
                        <input id="lastname" name="lastname" type="text" size="30" placeholder="{{ t._('e.g Doe') }}" value="{#register['lastName']#}" class="falpha fmaxlen" maxlength="32" data-max-length="32" />
                    </div>
                </li>
                <li>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label for="email">{{t._('Email')}}</label>
                        <input id="email" name="email" type="text" size="30" placeholder="{{ t._('e.g johndoe@domain.com') }}" value="{#register['email']#}" class="nempty femail" />
                    </div>
                </li>
                <li>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="password">{{t._('Password')}}</label>
                        <input id="password" name="password" type="password" size="30" placeholder="" class="nempty fminlen" data-min-length="8" />
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="confirm">{{t._('Retype Password')}}</label>
                        <input id="confirm" name="confirm" type="password" size="30" placeholder="" class="nempty fmatch-password" />
                        <div class="pass-indicator" id='complexity'></div>
                    </div>
                </li>
                <li>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="gender">{{ t._('Gender') }}</label>
                        <select id="gender" name="gender" class="fselect">
                            <option value="">{{ t._('Please Select') }}</option>
                            {#
                            {% if register['genderData'] %}
                                {% for k,v in register['genderData'] %}
                                    {% if k == register['gender'] %}
                                        {% set selected = 'selected="selected"' %}
                                    {% else %}
                                        {% set selected = '' %}
                                    {% endif %}
                                    <option value="{{ k }}" {{ selected }}>{{ t._(v) }}</option>
                                {% endfor %}
                            {% endif %}
                            #}
                        </select>
                    </div>
                </li>
                <li>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label for="birthday">{{t._('Date of Birth')}}</label>
                    <input id="birthday" type="text" name="birthday" value="{#register['birthday']#}" size="30" class="nempty fdate" readonly="readonly" />
                    </div>
                </li>
                <li class="check-newsletter">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <input id="newsletter" type="checkbox" name="newsletter" value="1">     
                    <label for="newsletter">{{t._('Sign Up for Newsletter')}}</label>
                    </div>
                </li>
                <li class="termncond">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <label>
                            <input id="agreement" type="checkbox" name="agreement" value="1" class="fcheck" />
                            {{t._('Saya setuju dengan')}} <a href="#">{{t._('syarat dan ketentuan')}}</a> {{t._('yang berlaku di')}} <a href="#">{{t._('bilna.com')}}</a>
                        </label>
                    </div>
                </li>
                <li class="captcha">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <script src='https://www.google.com/recaptcha/api.js'></script>         
                    <div class="g-recaptcha" data-sitekey="{{recaptchaAppId}}"></div>
                    </div>
                </li>
                
                <li>
                <button>{{t._('SUBMIT')}}</button>
                </li>
            </ul>
        </form>
    </div>
    <div class="or-login"> 
    <hr> <p>{{t._('Or Login using')}} :</p>
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
 
{#    
<script type="text/javascript">
    $(document).ready(function() {
        validationBlur('form-register');
        validationSubmit('form-register');
    });
</script>
#}
{% endblock %}
