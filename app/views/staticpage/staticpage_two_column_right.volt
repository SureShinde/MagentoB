{% extends 'core/templates/two-column-right.volt' %}

{% block title %}{{staticPage["name"] }} {% endblock %}

{% block description %} {{staticPage["metaDescription"] }} {% endblock %}

{% block keywords %} {{staticPage["metaKeywords"] }} {% endblock %}

{% block content_left %}
    {% if staticPage['tos'] is defined %}
        <ul class="tos-staticpage">
            <li><p>SHIPPING AND POLICIES</p></li>
        {% for key, data in staticPage['tos'] %}
            <li {% if data['id'] == staticPage['id'] %} class="active-tos" {% endif %}><a href="{{url(data['path'])}}">{{data['name']}}</a></li>
       {% endfor %}
        </ul>
    {% endif %}
    
    {{widget('StaticAreaWidget',['identifier' : 'staticpage-'~staticPage['key']~'-left'])}}
    
{% endblock %}

{% block content %}
    {{ staticPage['content'] }}
    
    {{widget('StaticAreaWidget',['identifier' : 'staticpage-'~staticPage['key']~'-right'])}}
    
{% endblock %}