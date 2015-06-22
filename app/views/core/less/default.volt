{% block lessCss %}
    {% if lessArr %}
        {% for less in lessArr %}
            <link rel="stylesheet/less" type="text/css" href="{{ staticUri ~ less }}" />
        {% endfor %}
    {% endif %}
{% endblock %}