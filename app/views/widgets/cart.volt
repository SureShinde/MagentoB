<div class="checkout">
    <div class="button-cart">
        {{ t._('CART') }}
        <span>
            {% if basketData['totalProduct'] is defined %}
                {{ basketData['totalProduct'] }}
            {% else %}
                0
            {% endif %}
        </span>
    </div>
        
    <div class="button-checkout">
        <a href="{{ url('cart') }}">{{ t._('CHECKOUT') }}</a>
    </div>
    
    {% if basketData['basket'] is defined %}
        <div class="cart-list-product">
            <table>
                <thead>
                    <tr>
                        <th align="center">{{ t._('Item') }}</th>
                        <th align="center">{{ t._('Qty') }}</th>
                        <th align="center">{{ t._('Price') }}</th>
                        <th></th>
                    </tr>
                </thead>
                
                <tbody>
                    {% for vendorId, vendors in basketData['basket'] %}
                        {% for key, vendor in vendors %}
                            {% for no, product in vendor['products'] %}
                                <tr>
                                    <td>
                                        <table class="item">
                                            <tr>
                                                <td>{{ image(product['baseImage']) }}</td>
                                                <td align="left">{{ product['name'] }}</td>
                                            <tr>
                                        </table>
                                    </td>
                                    <td>{{ product['qty'] }}</td>
                                    <td>{{ util.numberFormat(product['price']) }}</td>
                                    <td>
                                        <a onclick="deleteCart({{ vendorId }}, {{ vendor['vendorOrigin'] }}, {{ product['id'] }});">X</a>
                                    </td>
                                </tr>
                            {% endfor %}
                        {% endfor %}
                    {% endfor %}
                </tbody>
                
                <tfoot>
                    {% if basketData['totals'] is defined %}
                        {% for key, total in basketData['totals'] %}
                            <tr>
                                <td colspan="2"><b>{{ t._(total['totalName']) }}</b></td>
                                <td colspan="2"><b>{{ t._('Rp.') ~ util.numberFormat(total['price']) }}</b></td>
                            </tr>
                        {% endfor %}
                    {% endif %}
                    
                    
                    {% if basketData['grandTotal'] is defined %}
                        <tr>
                            <td colspan="2"><b>{{ t._('Grand Total') }}</b></td>
                            <td colspan="2"><b>{{ t._('Rp.') ~ util.numberFormat(basketData['grandTotal']) }}</b></td>
                        </tr>
                    {% endif %}
                </tfoot>
            </table>
                
            <table>
                <tr>
                    <td>
                        <div class="continue-cart">{{ t._('Continue Shopping') }}</div>
                        <div class="checkout-cart">{{ t._('Checkout') }}</div>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <div class="trackorder-cart">{{ t._('Track Order') }}</div>
                    </td>
                </tr>
            </table>
        </div>
    {% endif %}
</div>