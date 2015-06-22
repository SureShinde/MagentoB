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
    
    <body class="one-column {% block body_class %} {% endblock %}">
        {% if analyticsGtmEnabled %}
            {{ analyticsGtmContent }}
            
            <script type="text/javascript">
                $(document).ready(function() {
                    {% if _customerIsLogin %}
                        var customer = {
                            id: '{{ _customerDataLayer['id'] }}',
                            email: '{{ _customerDataLayer['email'] }}',
                            name: '{{ _customerDataLayer['name'] }}'
                        };
                        
                        dataLayer.push({'customer': customer });
                    {% else %}
                        var customer = {
                            id: 2,
                            email: 2,
                            name: 2
                        };
                        
                        dataLayer.push({'customer': customer });
                    {% endif %}
                });
            </script>
        {% endif %}

        {% block content %}{% endblock %}
        {% include dirRoot~'core/js/default_footer' with ['dirRoot': dirRoot] %}
        {# include dirRoot~'core/footer/debug' with ['dirRoot': dirRoot] #}
    </body>
</html>
