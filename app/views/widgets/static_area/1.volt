<ul class="static-area multiple-text">
{% for key, data in content['contents'] %}
    <li><a href="{{data['url']}}">{{data['text']}}</a></li>
{% endfor %}
</ul>
        
