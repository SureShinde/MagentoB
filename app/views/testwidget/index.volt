{% extends "core/templates/no-column.volt" %}

{% block title %}Test{% endblock %}

{% block content %}
{{ widget('ProductWidget', ['productID' : 1 ,'itemID' : 2]) }}
{{ widget('ProductWidget', ['productID' : 2 ,'itemID' : 5, 'layout': 2]) }}
{{ widget('ProductWidget', ['productID' : 3]) }}
{% endblock %}