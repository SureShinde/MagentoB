<div class="paging">
    <p class="showing">{{t._('Showing')}} {{from}} {{t._('to')}} {{to}} {{t._('out of')}} {{total_data}} 
    {% if total_paging > 0 %}
    <ul class="pagination">
        {% if page > 1 %}
            <li><a href="{{url(url_paging)}}1"><span aria-hidden="true">{{t._('First')}}</span></a></li>
            <li><a href="{{url(url_paging)}}{{page-1}}"><span aria-hidden="true">{{t._('Previous')}}</span></a></li>
        {% endif %}
        
        {% if total_paging < 7 %}
            {% if total_paging > 1 %}
            {% for i in 1..total_paging %}
               {% if page == i %}
                   <li><a href="">{{i}}</a></li>
                {% else %}
                   <li><a href="{{url(url_paging)}}{{i}}">{{i}}</a></li> 
                {% endif %}
            {% endfor %}
            {% endif %}
        {% elseif total_paging > 7 %}
            {% if page < (1 + 4) %}
                {% for i in 1..7 %}
                    <li><a href="{{url(url_paging)}}{{i}}">{{i}}</a></li>
                {% endfor %}
            
            {% elseif (total_paging - 4) > page %}
                
                {% for i in page-3..page+3%}
                <li><a href="{{url(url_paging)}}{{i}}">{{i}}</a></li>
                {% endfor %}
            
            {% else %}
                {#<li><a href="">...</a></li>#}
                {% for i in total_paging - 6..total_paging %}
                    {% if page == i%}
                    <li><a href="">{{i}}</a></li>
                    {%else %}
                    <li><a href="{{url(url_paging)}}{{i}}">{{i}}</a></li>
                    {%endif%}
                {% endfor %}
            {% endif %}
        {% else %}
            {% for i in 1..total_paging %}
               {% if page == i %}
                   <li><a href="">{{i}}</a></li>
                {% else %}
                   <li><a href="{{url(url_paging)}}{{i}}">{{i}}</a></li> 
                {% endif %}
            {% endfor %}
        {% endif %}
            
        
        
        {% if page != total_paging %}
            <li><a href="{{url(url_paging)}}{{page + 1}}"><span aria-hidden="true">{{t._('Next')}}</span></a></li>
            <li><a href="{{url(url_paging)}}{{total_paging}}"><span aria-hidden="true">{{t._('Last')}}</span></a></li>
        {%endif %}    
    </ul>
    {% endif %}
</div>
