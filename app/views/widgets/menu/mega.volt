{% if widget_megamenu is iterable %}
<ul>
{%- for id, properties in widget_megamenu %}
    <li>
        <a href="{{url(properties['path'])}}">{{t._(properties['name'])}}</a>
        <div class="second-level">
            <div class="wrap-second-cat">
                {% if properties['categories'] is defined %}
                {%- for key_child, value_child in properties['categories'] %}
                <ul>
                    <li><p><a href="{{url(value_child['path'])}}">{{t._(value_child['name'])}}</a></p></li>
                    {% if value_child['categories'] is defined %}
                    {% for key_child2, value_child2 in value_child['categories'] %}
                    <li><a href="{{url(value_child2['path'])}}">{{t._(value_child2['name'])}}</a></li>
                    {% endfor %}
                    {% endif %}
                </ul>
                {%- endfor %}
                {% endif %}
            </div>
            <div class="wrap-banner">
                <div class="first-banner-megamenu">
                    <div class="main-banner">
                        {{widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-0",'load' : load])}}
                    </div>
                    
                    <div class="bottom-left-firstbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-1",'load' : load])}}
                    </div>
                    
                    <div class="bottom-right-firstbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-2",'load' : load])}}
                    </div>
                </div>
                <div class="second-banner-megamenu">
                    <div class="top-secondbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-3",'load' : load])}}
                    </div>
                    
                    <div class="middle-secondbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-4",'load' : load])}}
                    </div>
                    
                    <div class="bottom-secondbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-5",'load' : load])}}
                    </div>
                </div>
                <div class="third-banner-megamenu">
                    <div class="top-thirdbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-6",'load' : load])}}
                    </div>
                    
                    <div class="middle-thirdbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-7",'load' : load])}}
                    </div>
                    
                    <div class="bottom-thirdbanner">
                        {{ widget('StaticAreaWidget',['identifier' : "megamenu-"~properties['slug']~"-8",'load' : load]) }}
                    </div>
                </div>
            </div>
        </div>
    </li>
{%- endfor %}
</ul>
{% endif %}