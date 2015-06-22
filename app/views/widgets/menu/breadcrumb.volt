{% if widget_breadcrumb is defined %} 
    {% if widget_breadcrumb is iterable %}
        <ol class="breadcrumb">
            <li><a href="{{url()}}">{{t._('Home')}}</a></li>
        {% for id, data in widget_breadcrumb %}
            {% if loop.last %}
                <li class="active">{{data['name']}}</li>
            {% else %}
            <li><a href="{{url(data['path'])}}">{{data['name']}}</a></li>
            {% endif %}
        {% endfor %}
        </ol>
    {% endif %}
{% endif %}