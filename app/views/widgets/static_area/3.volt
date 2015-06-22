<ul class="static-area static-area-img multiple-image">
    {% for key, data in content['contents'] %}
        <li>
            <a href="{{data['url']}}">
                <img class="lazy" data-original="{{path}}{{data['image']}}"
                     data-path="{{path}}"
                     data-img="{{data['image']}}"
                     data-big-img="{{data['largeImage']}}"
                     data-small-img="{{data['smallImage']}}"
                     {% if content['size']['width'] is defined %}width  = "{{content['size']['width']}}" {% endif %}
                     {% if content['size']['width'] is defined %}height = "{{content['size']['height']}}" {% endif %}
                />
            </a>
        </li>
    {% endfor %}
</ul>
        
 