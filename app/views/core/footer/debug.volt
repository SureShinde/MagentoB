<style type="text/css">
    ul.debug {
        width: 100%;
        padding: 0;
        margin: 0;
        background-color: #f47b6A;
    }
    ul.debug > li {
        padding: 5px 10px;
        color: #ffffff;
        border-top: 1px dotted #ffffff;
    }
    ul.debug > li.first {
        border-top: none;
    }
</style>
    
{% for type, messages in flash.getMessages() %}
    {% if type == 'debug' %}
        <ul class="debug">
            {% for message in messages %}
                {% if loop.first %}
                    <li class="first">{{ message }}</li>
                {% else %}
                    <li>{{ message }}</li>
                {% endif %}
            {% endfor %}
        </ul>
    {% else %}
        {% continue %}
    {% endif %}
{% endfor %}