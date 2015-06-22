<div class="wrap-megamenu">
        <div class="button-megamenu">{{t._('Select Category')}}</div>
</div>
<div class="link-space-header col-md-12">
    <div class="search-desktop">
        <form method="GET" action="{{url('search')}}" id="search">
            {#widget('CategorySelectWidget')#}
            <div class="input-area">
                <input type="text" id="query" name="q" value="{#query#}"></input>
                <button type="button" class="search-button"></button>
            </div>
        </form>
    </div>
    
    <div class="language">
        <select id="change_language" name="change_language">
            {% if translationArr %}
                {% set selected = '' %}
                {% for k, v in translationArr %}
                    {% if k == language %}
                        {% set selected = 'selected="selected"' %}
                    {% else %}
                        {% set selected = '' %}
                    {% endif %}
                    <option value="{{ k }}" {{ selected }}>{{ v }}</option>
                {% endfor %}
            {% endif %}
        </select>
    </div>
</div>
<div class="megamenu">
    <div  class="first-level">
        {#widget('MenuMegaWidget',['load' : 'menu'])#}
    </div>
</div>