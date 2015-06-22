{% if item %}
    {% if item['layout'] == 1 %}
    <div class="prod-widget-vertical {% if item['selectedPrice']['discount'] > 0 %} with-badges {% endif %}">
        <div class="wrap-widget">
            
            <div class="prod-image">
                {#
                {% if data['discount'] > 0 %}
                <div class="wrap-badges-widget">
                    <div class="badges-widget-product badges-bottom-left">
                        <div>2 <span class="star">★<span></div>
                    </div>
                    <div class="badges-widget-product badges-bottom-center">
                        <div>BUY 1 GET 1</div>
                    </div>
                    <div class="badges-widget-product discount-review badges-bottom-right">
                        <div class="discount">{{data['discount']}} %</div>
                    </div>
                </div>
                {% endif %}
                #}
                <div class="wrap-badges-widget">
                    <div class="badges-widget-product badges-bottom-left">
                        <div>2 <span class="star">★<span></div>
                    </div>
                    <div class="badges-widget-product badges-bottom-center">
                        <div>BUY 1 GET 1</div>
                    </div>
                    <div class="badges-widget-product badges-bottom-right">
                        <div class="discount">{{item['selectedPrice']['discount']}} %</div>
                    </div>
                </div>
                {{image(item['displayImage'])}} 
            </div>
            
            <label class="prod-name"><a href="{{url(item['path'])}}">{{item['name']}}</a></label>
            
            <div class="widget-price">
                <div class="widget-price">
                    {% if item['selectedPrice']['minPrice'] != 0 AND item['selectedPrice']['maxPrice'] != 0 %}
                        <p class="price-range">{{ t._('Rp.') ~ util.numberFormat(item['selectedPrice']['minPrice']) }}</p> 
                        <span class="until">{{ t._('s/d') }}</span>
                        <p class="price-range">{{ t._('Rp.') ~ util.numberFormat(item['selectedPrice']['maxPrice']) }}</p>
                    
                    {% else %}
                        {% if item['selectedPrice']['originalPrice'] > item['selectedPrice']['finalPrice'] %}
                            <p class="onsale">
                                <span class="normal-price">{{ t._('Rp.') ~ util.numberFormat(item['selectedPrice']['originalPrice']) }}</span>
                                <span class="disc-price">{{ t._('Rp.') ~ util.numberFormat(item['selectedPrice']['finalPrice']) }}</span>
                            </p>
                            
                            <p class="percent">{{ item['selectedPrice']['discount'] }} %</p>
                        
                        {% else %}
                            <p class="price">{{ t._('Rp.') ~ util.numberFormat(item['selectedPrice']['finalPrice']) }}</p>
                        
                        {% endif %}
                    {% endif %}
                </div>
                
                <div class="wrap-widget-button">
                    {% if item['selectedWarehouse']['notifyStock'] %}
                        {% if item['selectedWarehouse']['warehouseStock'] < item['selectedWarehouse']['notifyStockQty'] %}
                            <span class="stock-status">- {{t._('Only '~item['selectedWarehouse']['warehouseStock'])~' left.'}}</span>
                        {% endif %}
                        <button class="add-to-chart" onclick="addToCart({{ item['id'] }}, '{{ item['type'] }}', {{ item['selectedWarehouse']['minQty'] }});">{{ t._('ADD TO CART') }}</button>
                        
                    {% else %}
                        {% if item['selectedWarehouse']['isInStock'] %}
                        <button class="add-to-chart" onclick="addToCart({{ item['id'] }}, '{{ item['type'] }}', {{ item['selectedWarehouse']['minQty'] }});">{{ t._('ADD TO CART') }}</button>
                        {% else %}
                            <button class="notify-me">{{ t._('NOTIFY ME') }}</button>
                        {% endif %}
                    
                    {% endif %}
                    <a class="add-to-wishlist">{{ t._('Add to wishlist') }}</a>
                </div>
            </div>
        </div>
    </div>
                
    {% elseif item['layout'] == 2 %}
    <div class="prod-widget-horizontal {% if item['selectedPrice']['discount'] > 0 %} with-badges {% endif %}">
        <div class="prod-image">
            
            
            <div class="wrap-badges-widget">
            <div class="badges-widget-product badges-bottom-left">
                <div>2 <span class="star">★<span></div>
            </div>
            <div class="badges-widget-product badges-bottom-center">
                <div>BUY 1 GET 1</div>
            </div>
            <div class="badges-widget-product badges-bottom-right">
                <div>{{item['selectedPrice']['discount']}} %</div>
            </div>
            </div>
            {{image('src':'skin/loader.gif','data-echo':item['displayImage'])}}  
        </div>
        <div class="wrap-right-area">
            <label class="prod-name"><a href="{{url(item['path'])}}">{{item['name']}}</a></label>
            <div class="widget-price">
                {% if item['selectedPrice']['minPrice'] != 0 AND item['selectedPrice']['maxPrice'] != 0 %}
                    <p class="price-range">{{t._('Rp')}}. {{util.numberFormat(item['selectedPrice']['minPrice'])}}</p> 
                    <span class="until">{{t._('s/d')}}</span>
                    <p class="price-range">{{t._('Rp')}}. {{util.numberFormat(item['selectedPrice']['maxPrice'])}}</p>
                
                {% else %}
                    {% if item['selectedPrice']['originalPrice'] > item['selectedPrice']['finalPrice'] %}
                        <p class="onsale">
                            <span class="normal-price">{{t._('Rp')}}. {{util.numberFormat(item['selectedPrice']['originalPrice'])}}</span>
                            <span class="disc-price">{{t._('Rp')}}. {{util.numberFormat(item['selectedPrice']['finalPrice'])}}</span>
                        </p>
                        <p class="percent">{{item['selectedPrice']['discount']}} %</p>
                
                    {% else %}
                        <p class="price">{{t._('Rp')}}. {{util.numberFormat(item['selectedPrice']['finalPrice'])}}</p>
                    
                    {% endif %}
                {% endif %}
            </div>
            <div class="wrap-widget-button">
                {% if item['selectedWarehouse']['notifyStock'] %}
                    {% if item['selectedWarehouse']['warehouseStock'] < item['selectedWarehouse']['notifyStockQty'] %}
                        <span class="stock-status">- {{t._('Only '~item['selectedWarehouse']['warehouseStock'])~' left.'}}</span>
                    {% endif %}
                    <button class="add-to-chart" onclick="addToCart({{ item['id'] }}, '{{ item['type'] }}', {{ item['selectedWarehouse']['minQty'] }});">{{ t._('ADD TO CART') }}</button>

                {% else %}
                    {% if item['selectedWarehouse']['isInStock'] %}
                    <button class="add-to-chart" onclick="addToCart({{ item['id'] }}, '{{ item['type'] }}', {{ item['selectedWarehouse']['minQty'] }});">{{ t._('ADD TO CART') }}</button>
                    {% else %}
                        <button class="notify-me">{{ t._('NOTIFY ME') }}</button>
                    {% endif %}

                {% endif %}
                
            </div>
        </div>
    </div>
    {% endif %}
{% endif %}