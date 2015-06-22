<!DOCTYPE html>
<html lang="en">
    <head>
        <title>{% block title %}{% endblock %} - bilna.com</title>
        {% if viewInModule is defined and viewInModule == TRUE %}
        {% set dirRoot = "../../../views/" %}
        {% else %}
        {% set dirRoot = "" %}
        {% endif %}
    	{% include dirRoot~'core/templates/content/head' with ['dirRoot': dirRoot] %}
    </head>
    <body class="empty {% block body_class %} {% endblock %}">
        {% include dirRoot~'core/header/content/gtm-script' with ['dirRoot': dirRoot] %}
        {% block content %}{% endblock %}
        {% include dirRoot~'core/js/default_footer' with ['dirRoot': dirRoot] %}
        {% include dirRoot~'core/footer/debug' with ['dirRoot': dirRoot] %}
    </body>
</html>
