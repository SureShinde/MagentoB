<ul class="static-area multiple-html">
    {% for key, data in content['contents'] %}
        <li>{{data['text']}}</li>
    {% endfor %}
</ul> 