{% for type, messages in flash.getMessages() %}
    {% if type == 'debug' %}
        {% continue %}
    {% endif %}
    {% if type == 'error' %}
        <div class="red-flash flash-message">
    {% elseif type == 'success' %}
        <div class="green-flash flash-message">
    {% elseif type == 'notice' %}
        <div class="yellow-flash flash-message">
    {% endif %}
    {% for message in messages %}
        <p>{{t._(message)}}</p>
    {% endfor %}
    <span>x</span>
    </div>
{% endfor %}
<div id="message" class="flash-message" style="display:none;"></div>