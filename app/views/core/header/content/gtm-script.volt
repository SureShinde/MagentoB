{% if analyticsGtmEnabled %}
    {{ analyticsGtmContent }}

    <script type="text/javascript">
        $(document).ready(function () {
        {% if isCustomerLogin %}
                var customer = {
                    id: '{{ _customerDataLayer['id'] }}',
                    email: '{{ _customerDataLayer['email'] }}',
                    name: '{{ _customerDataLayer['name'] }}'
                };

                dataLayer.push({'customer': customer});
        {% else %}
                var customer = '';

                dataLayer.push({'customer': customer});
        {% endif %}

        {% if item is defined and item %}
                var product = {
                    sku: '{{item['sku']}}',
                    name: '{{item['name']}}',
                    brand: '{{item['brand']['name']}}',
                    vendor: '{{item['vendor']['name']}}',
                    warehouseName: '{{item['selectedWarehouse']['warehouseName']}}',
                    pathUrl: '{{url(item['fullPathURL'])}}',
                    originalPrice: '{{item['selectedWarehouse']['price']['originalPrice']}}',
                    finalPrice: '{{item['selectedWarehouse']['price']['finalPrice']}}',
                    stock: '{{item['selectedWarehouse']['warehouseStock']}}',
                    thirdPary: '{{item['thirdPartyIntegration']}}'
                }

                dataLayer.push({'product': product});
        {% else %}
                var product = '';
                dataLayer.push({'product': product});
        {% endif %}    

        {% if path is defined %}
                var path = {path: document.URL}
                dataLayer.push({'path': path});
        {% else %}
                var path = '';
                dataLayer.push({'path': path});
        {% endif %} 

        {% if pages is defined %}
                var pages = {page: '{{pages}}'}
                dataLayer.push({'page': pages});
        {% else %}
                var pages = '';
                dataLayer.push({'page': pages});
        {% endif %} 

        {% if brand is defined %}
                var brand = {id: '{{brand['id']}}', name: '{{brand['name']}}'}
                dataLayer.push({'brand': brand});
        {% else %}
                var brand = '';
                dataLayer.push({'brand': brand});
        {% endif %}


        {% if vendor is defined %}
                var vendor = {id: '{{vendor['id']}}', name: '{{vendor['name']}}', rating: '{{vendor['rating']}}'}
                dataLayer.push({'vendor': vendor});
        {% else %}
                var vendor = '';
                dataLayer.push({'vendor': vendor});
        {% endif %}



                dataLayer.push({'event': 'callGTM'});
            });
    </script>
{% endif %}
