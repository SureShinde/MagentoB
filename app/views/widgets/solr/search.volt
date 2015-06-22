<script>
    $("document").ready(function(){
        $( "#slider-range" ).slider({
          range: true,
          min: {{range_price['min']}},
          max: {{range_price['max']}},
          values: [ {{filter_price['min']}}, {{filter_price['max']}} ],
          slide: function( event, ui ) {
            $( "#amount" ).val( "Rp. " + ui.values[ 0 ] + " - Rp. " + ui.values[ 1 ] );
            $("#amount-price").val(ui.values[0] +"-"+ui.values[1]);
          }
        });
        $( "#amount" ).val( "Rp." + $( "#slider-range" ).slider( "values", 0 ) + " - Rp." + $( "#slider-range" ).slider( "values", 1 ) );
    });
</script>
<form id="form-filter" method="POST">
    <div class="box-filter price">
        <h6>{{t._('Price')}}</h6>
        <p>
          <input type="text"   id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
          <input type="hidden" id="amount-price">
        </p>
        <div id="slider-range"></div>
    </div>
{% if filter_menu is iterable %}    
    {% for tag, data in filter_menu %}
    <div class="box-filter">
        <h6>{{t._(myUtil.getFilterTitle(tag))}}</h6>
        <div class="wrap-list">
            <ul>
                {% for label, detail in data %}
                <label>
                    <li data-url="{{url(base_url)}}?{{detail['url']}}" data-attr="{{t._(myUtil.getFilterForm(tag))}}" data-label="{{label}}">
                        {% if filter_data[tag] is defined %}
                            {%if label in filter_data[tag] %}
                            <input type="checkbox" class="filter-choice" checked="checked">{{label}}({{detail['total']}})
                            {% else %}
                            <input type="checkbox" class="filter-choice">{{label}}({{detail['total']}})
                            {% endif %}
                        {% else %}
                        <input type="checkbox" class="filter-choice">{{label}}({{detail['total']}})
                        {% endif %}
                    </li>
                </label>
                {% endfor %}
            </ul>
        </div>
    </div>
    {% endfor %}
{% endif %}
</form>
