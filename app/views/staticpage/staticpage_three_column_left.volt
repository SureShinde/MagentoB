{% extends 'core/templates/three-column-left.volt' %}

{% block title %}{{staticPage["name"] }} {% endblock %}

{% block description %} {{staticPage["metaDescription"] }} {% endblock %}

{% block keywords %} {{staticPage["metaKeywords"] }} {% endblock %}

{% block content_left %}
    {{ staticPage['content'] }}
    
    {{widget('StaticAreaWidget',['identifier' : 'staticpage-'~staticPage['key']~'-left'])}}
{% endblock %}

{% block content_center %}
    {% if staticPage['tos'] is defined %}
        <ul class="tos-staticpage">
            <li><p>SHIPPING AND POLICIES</p></li>
        {% for key, data in staticPage['tos'] %}
            <li {% if data['id'] == staticPage['id'] %} class="active-tos" {% endif %}><a href="{{url(data['path'])}}">{{data['name']}}</a></li>
       {% endfor %}
        </ul>
    {% endif %}
    
    {{widget('StaticAreaWidget',['identifier' : 'staticpage-'~staticPage['key']~'-center'])}}
    
    
{% endblock %}

{% block content_right %}
    {{widget('StaticAreaWidget',['identifier' : 'staticpage-'~staticPage['key']~'-right'])}}
{% endblock %}