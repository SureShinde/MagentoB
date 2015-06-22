<script> 
    $("document").ready(function(){
       loadVendorReview(1); 
    });
    
    function loadVendorReview(page){
        var vendor_id = $("#vendorreview_vendor_id").val();
        var dataPost  = {vendor_id : vendor_id, page : page};
        var url       = baseUri + 'rating/vendorList';
        
        $.ajax({
            url      : url,
            type     : 'POST',
            dataType : 'json',
            data     : dataPost,
            success  : function(data){
                if(data.status){
                    $("#vendor-review-ajax").html(data.data);
                }else{
                    $("#vendor-review-ajax").html(data.message);
                }
            }
        });
    }
    
    
    function submitVendorReview(){
        var vendor_id       = $("#vendorreview_vendor_id").val();
        var type            = '{{data['type']}}';
        var customerName    = $("#vendorreview_customerName").val();
        var customerEmail   = $("#vendorreview_customerEmail").val();
        var customerReview  = $("#vendorreview_customerReview").val();
        
        var errors = [];
        
        if(!validateNumber(vendor_id)){
            errors.push('{{t._('Vendor ID cant be empty.')}}');
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
               var {{form['identifier']}} = $("#vendor-review-{{form['identifier']}}:checked").val();
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
            $("#vendor-review-message").html(message);
        }else{
            var dataPost = {vendor_id : vendor_id,
                            type : type,
                            customerName : customerName,
                            customerEmail : customerEmail,
                            customerReview : customerReview,
                            {% for key, form in data['additionalInput'] %}
                                {% if form['type'] == 'star' %}
                                    {{form['identifier']}} : $("#vendor-review-{{form['identifier']}}:checked").val()                                                                      ,
                                {% endif %}
                            {% endfor %}                       
                           };
                           
            //alert(JSON.stringify(dataPost));
            
            $.ajax({
                url       : baseUri + 'rating/vendorAdd',
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
                        
                        $("#vendor-review-message").html(message).delay(2000).fadeOut('slow');
                    }else{
                        var count = data.message.length;
                        var message = '';
                        for(i = 0; i < count; i++){
                            message += "<span>"+data.message[i]+"</span><br>";
                        }
                        alert(message);
                        $("#vendor-review-message").html(message);
                    }
                    //window.location.reload();
                }
            });
            
        }
    }
</script>
<style>
    .error{
        color : red;
    }
</style>
<div class="product-review">
    <p>{{t._(data['type']|upper ~' REVIEWS')}}</p>
    <div id="vendor-review-ajax" class="wrap-comment"></div>
    <div class="wrap-form-comment">
        <div class="title-form-review">{{t._('Write Your Review')}}</div>
        <form>
            <div id="vendor-review-message" class="error"></div>
            <ul>
                <input type="hidden" id="vendorreview_vendor_id" value="{{item['vendor']['id']}}">
                <li>
                    <label>{{t._('Customer Name')}}*</label>
                    <input type="text" id="vendorreview_customerName">
                </li>
                <li>
                    <label>{{t._('Customer Email')}}*</label>
                    <input type="text" id="vendorreview_customerEmail">
                </li>
                <li>
                    <label>{{t._('Customer Review')}}*</label>
                    <textarea id="vendorreview_customerReview"></textarea>
                </li>
                {% for key, form in data['additionalInput'] %}
                <li>
                    <label>{{t._(form['title'])}}{% if form['required'] == 1 %}*{% endif %}</label>
                    {% if form['type'] == 'star' %}
                    <input id="vendor-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="1" class="star  required">
                    <input id="vendor-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="2" class="star">
                    <input id="vendor-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="3" class="star">
                    <input id="vendor-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="4" class="star">
                    <input id="vendor-review-{{form['identifier']}}" name="{{form['identifier']}}" type="radio" value="5" class="star">
                    {% endif %}
                </li>
                {% endfor %}
            </ul>
            <br/>
            <button type="button" onclick="submitVendorReview();">{{t._('Post Now !')}}</button>
        </form>
    </div>
</div>