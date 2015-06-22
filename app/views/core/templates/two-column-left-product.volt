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
    <body class="two-column-left-product {% block body_class %} {% endblock %}">
        {% include dirRoot~'core/header/content/gtm-script' with ['dirRoot': dirRoot] %}
        {% include dirRoot~'core/header/content/mobile-left-menu' with ['dirRoot': dirRoot] %}
	    <div class="wrap-body">
	        <div class="handling-sticky"></div>
	        <div class="wrap-header">
	            <div class="container">
	                <header class="row header">
                        {% include dirRoot~'core/header/default-header' with ['dirRoot': dirRoot] %}
	                </header>
	            </div>
	        </div>
	        <div class="bg-dark-megamenu"></div>    
	        <div class="wrap-middle-content container">
                {# include dirRoot~'core/templates/content/flash_message' with ['dirRoot': dirRoot] #}
	            {% block bellow_header %}{% endblock %}
	            <div class="wrap-content">
	                <div class="content">
	                    {% block content %}{% endblock %}
	                </div>
	                <div class="content-right">
	                    {% block content_right %}{% endblock %}
	                </div>
	            </div>
                <footer class="wrap-footer">
                    {% include dirRoot~'core/footer/default-footer' with ['dirRoot': dirRoot] %}
                </footer>
            </div>
        </div>
        {% include dirRoot~'core/js/default_footer' with ['dirRoot': dirRoot] %}
        {# include dirRoot~'core/footer/debug' with ['dirRoot': dirRoot] #}
    </body>
</html>
