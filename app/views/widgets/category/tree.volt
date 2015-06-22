{% if current_menu is iterable %}
<div class="categories-wrap">
    <div class="second-level-wrap">
        <div class="title-page-large">{{current_menu['name']}}</div>
        {% for key, data in current_menu['categories'] %}
        <div class="{% if parent_name == data['name'] %}active{% endif %} wrap-secondlevel-category">
            <p class="second-level"><a href="{{url(data['path'])}}">{{t._(data['name'])}}</a></p>
            <ul>
            {% if data['categories'] is defined %}
            {% for key2, data2 in data['categories']%}
                <li class="third-level"><a href="{{url(data2['path'])}}" {% if data2['name'] == current_name %}class="active"{% endif %}>{{t._(data2['name'])}}</a></li>
            {% endfor %}
            {% endif %}
            </ul>
        </div>
    {% endfor %}
    </div>
</div>
{% endif %}
