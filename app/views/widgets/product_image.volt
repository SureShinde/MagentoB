{% if widget_image is iterable %}
<div class="image-product-area">
    <div class="main-image">
            {{image(widget_image['large_path']~''~widget_image['id']~'/'~widget_image['largeImage'],'data-img' : 0, 'id':'main-image','class' : 'active-img', 'data-zoom-image':widget_image['large_path']~''~widget_image['id']~'/'~widget_image['largeImage'])}}
    </div>
    <div class="thumb-image">
        <div class="thumb_area">
            <div class="wrap_thumb">
                {% set i = 1 %}
                {% for key, data in widget_image['images'] %}
                    {% if i < 12 %}
                        {% if data['smallImage'] == 1 %}
                            {% if data['filename'] == widget_image['largeImage'] %}
                                <div class="thumb active-img" data-img="{{i}}">{{image(widget_image['small_path']~''~widget_image['id']~'/'~data['filename'],'alt': data['alt'], 'data-img' : i, 'data-src' : widget_image['large_path']~''~widget_image['id']~'/'~data['filename'], 'class' : 'active-img')}}</div>
                            {% else %}
                                <div class="thumb" data-img="{{i}}">{{image(widget_image['small_path']~''~widget_image['id']~'/'~data['filename'],'alt': data['alt'], 'data-img' : i, 'data-src' : widget_image['large_path']~''~widget_image['id']~'/'~data['filename'])}}</div>
                            {% endif %}
                        {% endif %}
                    {% endif %}
                    {% set i += 1 %}
                {% endfor %}
            </div>
        </div>
        <div class="nav-prev"></div>
        <div class="nav-next"></div>
    </div>
    {{image('img/skin/swap_icon.png', 'class' : 'swap_mobile_icon')}}
</div>
{% endif %}