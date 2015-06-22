{% if product %}
    <div class="prod-widget-horizontal {% if product['price']['discount'] > 0 %} with-badges {% endif %}">
        <div class="prod-image">
            <div class="wrap-badges-widget">
                {% if product['rating'] >= 3 %}
	            <div class="badges-widget-product badges-bottom-left">
	                <div>{{product['rating']}} <span class="star">★<span></div>
	            </div>
	            {% endif %}
	            {#
	            <div class="badges-widget-product badges-bottom-center">
	                <div>BUY 1 GET 1</div>
	            </div>
	            #}
	            {% if product['price']['discount'] > 0 %}
	            <div class="badges-widget-product badges-bottom-right">
	                <div>{{product['price']['discount']}} %</div>
	            </div>
	            {% endif %}
            </div>
        	<img class="lazy" 
                     data-original="{{product['image']['path']}}{{product['image']['baseImage']}}" 
                     data-path="{{product['image']['path']}}"
                     data-img="{{product['image']['baseImage']}}"
                />  
        </div>
        <div class="wrap-right-area">
            <label class="prod-name"><a href="{{url(product['path'])}}">{{product['name']}}</a></label>
            <div class="widget-price">
                {% if product['price']['minPrice'] != 0 AND product['price']['maxPrice'] != 0 %}
                    <p class="price-range">{{t._('Rp')}}. {{util.numberFormat(product['price']['minPrice'])}}</p> 
                    <span class="until">{{t._('s/d')}}</span>
                    <p class="price-range">{{t._('Rp')}}. {{util.numberFormat(product['price']['maxPrice'])}}</p>
                
                {% else %}
                    {% if product['price']['originalPrice'] > product['price']['finalPrice'] %}
                        <p class="onsale">
                            <span class="normal-price">{{t._('Rp')}}. {{util.numberFormat(product['price']['originalPrice'])}}</span>
                            <span class="disc-price">{{t._('Rp')}}. {{util.numberFormat(product['price']['finalPrice'])}}</span>
                        </p>
                    {% else %}
                        <p class="price">{{t._('Rp')}}. {{util.numberFormat(product['price']['finalPrice'])}}</p>
                    {% endif %}
                {% endif %}
            </div>
            <div class="wrap-widget-button">
                {% if product['notifyStock'] %}
                    {% if product['selectedWarehouse']['warehouseStock'] < product['selectedWarehouse']['notifyStockQty'] %}
                        <span class="stock-status">- {{t._('Only '~product['selectedWarehouse']['warehouseStock'])~' left.'}}</span>
                    {% endif %}
                    <button class="add-to-chart" onclick="addToCart({{ product['id'] }}, '{{ product['type'] }}');">{{ t._('ADD TO CART') }}</button>

                {% else %}
                    {% if product['isInStock'] %}
                    <button class="add-to-chart" onclick="addToCart({{ product['id'] }}, '{{ product['type'] }}');">{{ t._('ADD TO CART') }}</button>
                    {% else %}
                        <button class="notify-me">{{ t._('NOTIFY ME') }}</button>
                    {% endif %}

                {% endif %}
                
            </div>
        </div>
    </div>
{% endif %}