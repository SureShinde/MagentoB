<!DOCTYPE html>
<html lang="en">
    <head>
        <title>bilna.com - {% block title %}{% endblock %}</title>
            <meta http-equiv="content-type" content="textface/html" charset="utf-8">
            <meta name="application-name" content="WOLF">
            <meta name="description" content="{% block description %}WOLF{% endblock %}">
            <meta name="keywords"           content="{% block keyword %}WOLF{% endblock %}">
            <meta name="author" content="{% block author %}PT Bilna{% endblock %}">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="robots" content="noindex, nofollow">
            <meta property="fb:app_id" content="" />
            {% block opengraph %}{% endblock %}
            <link rel="icon" type="image/png" href="" />
            <link rel="shortcut icon" type="image/ico" href="/img/skin/favicon.ico" />
            {{ assets.outputCss() }}
            {% block lessCss %}
                {% if lessArr %}
                    {% for less in lessArr %}
                        <link rel="stylesheet/less" type="text/css" href="{{ staticUri ~ less }}" />
                    {% endfor %}
                {% endif %}
            {% endblock %}
            {{ assets.outputJs('header') }}
            <script>
                var baseUri         = "{{ baseUri }}";
                var customerIsLogin = '{{ isCustomerLogin }}';
            </script>
            {% include "core/js/social.volt" %}
    </head>
    <body class="one-column {% block body_class %} {% endblock %}">
        {% include 'core/header/content/gtm-script.volt' %}
        {% include 'core/header/content/mobile-left-menu.volt' %}
        <div class="wrap-body">
            <div class="handling-sticky"></div>
            <div class="wrap-header">
                <div class="container">
                    <header class="row header">
                        {% include 'core/header/default-header.volt' %}
                    </header>
                </div>
            </div>
            <div class="bg-dark-megamenu"></div>
            <div class="wrap-middle-content container">            
                {# include 'core/templates/content/flash_message.volt' #}
                {% block bellow_header %}{% endblock %}
                
                <div class="wrap-content">
                    <div class="content">
                        {% block content %}{% endblock %}
                    </div>
                </div>
                <footer class="wrap-footer">
                    {% include 'core/footer/default-footer.volt' %}
                </footer>
            </div>
        </div>
        {% include 'core/js/default_footer.volt' %}
        {# include 'core/footer/debug.volt' #}
    </body>
</html>
