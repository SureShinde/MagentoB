<div class="static-area singel-text">
    {% for key, data in content['contents'] %}
        <a href="{{data['url']}}">{{data['text']}}</a>
    {% endfor %}
</div>
        
 