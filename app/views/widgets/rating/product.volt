<script>
    $("document").ready(function(){
       loadProductReview(1); 
    });
    
    function loadProductReview(page){
        var item_id     = $("#productreview_item_id").val();
        var product_id  = $("#productreview_product_id").val();
        var dataPost    = {item_id : item_id, product_id : product_id, page : page};
        var url         = baseUri + 'rating/productList';
        
        $.ajax({
            url      : url,
            type     : 'POST',
            dataType : 'json',
            data     : dataPost,
            success  : function(data){
                if(data.status){
                    $("#product-review-ajax").html(data.data);
                }else{
                    $("#product-review-ajax").html(data.message);
                }
            }
        });
    }
    
    function submitReviewProduct(){
        var item_id         = $("#productreview_item_id").val();
        var product_id      = $("#productreview_product_id").val();
        var sold_by         = $("#productreview_sold_by").val();
        var ship_by         = $("#productreview_ship_by").val();
        var type            = '{{data['type']}}';
        var customerName    = $("#productreview_customerName").val();
        var customerEmail   = $("#productreview_customerEmail").val();
        var customerReview  = $("#productreview_customerReview").val();
        
        var errors = [];
        
        if(!validateNumber(item_id)){
            errors.push('{{t._('Item ID cant be empty.')}}');
        }
        
        if(!validateNumber(product_id)){
            errors.push('{{t._('Product ID cant be empty.')}}');
        }
        
        if(!validateNotEmpty(customerName)){
            errors.push('{{t._('Customer name cant be empty.')}}');
        }else if(!validateName(customerName)){
            errors.push('{{t._('Customer name is must be alphabet character.')}}');
        }
        
        if(!validateNotEmpty(customerEmail)){
            errors.push('{{t._('Customer email cant be empty.')}}');
        }else if(!validateEmail(customerEmail)){
            errors.push('{{t._('Customer email is not valid.')}}');
        }
        
        if(!validateNotEmpty(customerReview)){
            errors.push('{{t._('Customer review cant be empty.')}}');
        }else if(!validateDetail(customerReview)){
            errors.push('{{t._('Customer review is must be 1 - 1024 characters.')}}');
        }
        
        {% for key, form in data['additionalInput'] %}
            {% if form['type'] == 'star' %}
               var {{form['identifier']}} = $("#product-review-{{form['identifier']}}:checked").val();
               if(!validateNotEmpty({{form['identifier']}})){
                   errors.push('{{t._(form['title']~' cant be empty')}}');
               }
            {% endif %}
        {% endfor %}
        
        if(errors.length > 0){
            var message = '';
            for(i = 0; i < errors.length; i++){
                message += "<span class='error'>"+errors[i]+"</span><br>";
            }
            $("#product-review-message").html(message);
        }else{
            var dataPost = {item_id : item_id, 
                            ship_by : ship_by,
                            sold_by : sold_by,
                            product_id : product_id,
                            type : type,
                            customerName : customerName,
                            customerEmail : customerEmail,
                            customerReview : customerReview,
                            {% for key, form in data['additionalInput'] %}
                                {% if form['type'] == 'star' %}
                                    {{form['identifier']}} : $("#product-review-{{form['identifier']}}:checked").val(),
                                {% endif %}
                            {% endfor %}                       
                           };
                           
            console.log(JSON.stringify(dataPost));
            
            $.ajax({
                url       : baseUri + 'rating/productAdd',
                type      : 'POST',
                dataType  : 'json',
                data      : dataPost,
                success   : function(data){
                    if(data.status){
                        var count = data.message.length;
                        var message = '';
                        for(i = 0; i < count; i++){
                            message += "<span>"+data.message[i]+"</span><br>";
                        }
                        
                        $("#product-review-message").html(message).delay(2000).fadeOut('slow');
                    }else{
                        var count = data.message.length;
                        var message = '';
                        for(i = 0; i < count; i++){
                            message += "<span>"+data.message[i]+"</span><br>";
                        }
                        alert(message);
                        $("#product-review-message").html(message);
                    }
                    //window.location.reload();
                }
            });
            
        }
    }
</script>
<style>
    .error{
        color: red;
    }
</style>
<div class="product-review">
    <p>{{t._(data['type']|upper ~' REVIEWS')}}</p>
    <div id="product-review-ajax" class="wrap-comment"></div>
    <div class="wrap-form-comment">
        <div class="title-form-review">{{t._('Write Your Review')}}</div>
        <form>
            <div id="product-review-message" class="error"></div>
            <ul>
                <input type="hidden" id="productreview_item_id"    value="{{data['item']['id']}}">
                <input type="hidden" id="productreview_product_id" value="{{data['item']['productId']}}">
                <input type="hidden" id="productreview_sold_by"    value="{{data['item']['vendor']['name']}}">
                <input type="hidden" id="productreview_ship_by"    value="{{data['item']['selectedWarehouse']['warehouseVendorName']}}-{{data['item']['selectedWarehouse']['warehouseCityName']}}">
                <li>
                    <label>{{t._('Customer Name')}}*</label>
                    <input type="text" id="productreview_customerName">
                </li>
                <li>
                    <label>{{t._('Customer Email')}}*</label>
                    <input type="text" id="productreview_customerEmail">
                </li>
                <li>
                    <label>{{t._('Customer Review')}}*</label>
                    <textarea id="productreview_customerReview"></textarea>
                </li>
                {% for key, form in data['additionalInput'] %}
                <li>
                    <label>{{t._(form['title'])}}{% if form['required'] == 1 %}*{% endif %}</label>
                    {% if form['type'] == 'star' %}
                    <input id="product-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="1" class="star  required">
                    <input id="product-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="2" class="star">
                    <input id="product-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="3" class="star">
                    <input id="product-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="4" class="star">
                    <input id="product-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="5" class="star">
                    {% endif %}
                </li>
                {% endfor %}
            </ul>
            <br/>
            <button type="button" onclick="submitReviewProduct();">{{t._('Post Now !')}}</button>
        </form>
    </div>
</div>