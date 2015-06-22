{% extends 'core/templates/one-column.volt' %}

{% block title %}{{staticPage["name"] }} {% endblock %}

{% block description %} {{staticPage["metaDescription"] }} {% endblock %}

{% block keywords %} {{staticPage["metaKeywords"] }} {% endblock %}

{% block content %}
    {{ staticPage['content'] }}
{% endblock %}