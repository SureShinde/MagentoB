<script>
    $(window).load(function(){
        $('.{{load}}-{{content['identifier']}}').bxSlider({
            controls: false
        });
    });
</script>
<div    {% if content['size']['width'] is defined %}width  = "{{content['size']['width']}}px" {% endif %}
        {% if content['size']['width'] is defined %}height = "{{content['size']['height']}}px" {% endif %}
        class="wrapper-static-area-slider slider-image">
    
    <ul class="static-area {{load}}-{{content['identifier']}}">
        {% for key, data in content['contents'] %}
            <li>
                <a href="{{url(data['url'])}}">
                    <img src="{{path}}{{data['image']}}" data-original="{{path}}{{data['image']}}"
                         data-path="{{path}}"
                         data-img="{{data['image']}}"
                         data-big-img="{{data['largeImage']}}"
                         data-small-img="{{data['smallImage']}}"
                    />
                </a>
            </li>
        {% endfor %}
    </ul>
</div>

