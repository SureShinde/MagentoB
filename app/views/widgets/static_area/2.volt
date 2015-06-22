<div class="static-area static-area-img singel-image">
    {% for key, data in content['contents'] %}
        <a href="{{data['url']}}">
        <img class="lazy" data-original="{{url(path)}}{{data['image']}}" 
             data-path="{{path}}"
             data-img="{{data['image']}}" 
             data-big-img="{{data['largeImage']}}" 
             data-small-img="{{data['smallImage']}}"
             {% if content['size']['width'] is defined %} width  = "{{content['size']['width']}}" {% endif %}
             {% if content['size']['width'] is defined %} height = "{{content['size']['height']}}" {% endif %} />    
        </a>
    {% endfor %}
</div>
        
 