{#
{% if categoryData %}
    {% if categoryData['totalProduct'] > 0 %}
        {% for index in 1 .. categoryData['totalProduct'] %}
            <div class="prod-widget-vertical">
                <div class="wrap-widget">
                    <div class="prod-image">
                        <div class="wrap-badges-widget">
                            <div class="badges-widget-product ratting-review">
                                <p class="ratting">10%</p>
                                <p class="review">5</p>
                            </div>
                            
                            <div class="badges-widget-product badges-top-left">
                                {{image('img/wysiwyg/sample-badges.png')}} 
                            </div>
                            
                            <div class="badges-widget-product badges-bottom-left">
                                 {{image('img/wysiwyg/sample-badges.png')}} 
                            </div>
                            
                            <div class="badges-widget-product badges-bottom-right">
                                 {{image('img/wysiwyg/sample-badges.png')}} 
                            </div>
                        </div>
                                 
                        {{image('img/product/sample-prod.jpg')}} 
                    </div>
                    
                    <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin</a></label>
                    
                    <div class="widget-price"><p class="onsale"><span class="normal-price">Rp 00.000.000 </span><span class="disc-price">Rp 00.000.000</span></p></div>
                    
                    <div class="wrap-widget-button">
                        <button class="add-to-chart conf-button">ADD TO CART</button>
                        <a class="add-to-wishlist">Add to wishlist</a>
                    </div>
                </div>
            </div>
        {% endfor %}
    {% endif %}
{% endif %}
#}

<!-- popup bundle n conf-->
<!--div id="config-prod" class="wrap-popup">
    <div class="box-popup">
        <div class="close-popup">{{image('img/skin/close-popup.png')}}</div>
        <div class="config-bundle">
            <table>
                <tr>
                    <td>
                        {{image('img/product/sample-prod-2.jpg')}} 
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>Beedreams Mattress Classic King Single</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Chose color :</label>
                        <select name="option">
                            <option value="volvo">Volvo</option>
                            <option value="saab">Saab</option>
                            <option value="fiat">Fiat</option>
                            <option value="audi">Audi</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="price">
                            <h4>Rp. 5,616,000<span>- In Stock.</span></h4>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="button-cart-area">
                        <label>Qty :</label><input type="text"><button class="add-to-chart">ADD TO CART</button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<!-- end popup bundle n conf-->


<!-- List product vertical-->
        <!--div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    <div class="wrap-badges-widget">
                        <div class="badges-widget-product discount-review">
                            <p class="discount">10%</p>
                            <p class="review">5</p>
                        </div>
                        <div class="badges-widget-product badges-top-left">
                            {{image('img/wysiwyg/sample-badges.png')}} 
                        </div>
                        <div class="badges-widget-product badges-bottom-left">
                             {{image('img/wysiwyg/sample-badges.png')}} 
                        </div>
                        <div class="badges-widget-product badges-bottom-right">
                             {{image('img/wysiwyg/sample-badges.png')}} 
                        </div>
                    </div>
                     {{image('img/product/sample-prod.jpg')}} 
                </div>
                <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin</a></label>
                <div class="widget-price"><p class="onsale"><span class="normal-price">Rp 00.000.000 </span><span class="disc-price">Rp 00.000.000</span></p></div>
                <div class="wrap-widget-button">
                    <button class="add-to-chart conf-button">ADD TO CART</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
            </div>
        </div>
        
         <div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    <div class="wrap-badges-widget">
                            <div class="badges-widget-product badges-top-left">
                                 {{image('img/wysiwyg/sample-badges.png')}} 
                            </div>
                            <div class="badges-widget-product badges-bottom-left">
                                 {{image('img/wysiwyg/sample-badges.png')}} 
                            </div>
                            <div class="badges-widget-product badges-bottom-right">
                                 {{image('img/wysiwyg/sample-badges.png')}} 
                            </div>
                        </div>
                    {{image('img/product/sample-prod.jpg')}} 
                </div>
                 <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin</a></label>

                <div class="widget-price"><p class="price-range">Rp 00.000.000</p> <span class="until">s/d</span> <p class="price-range">Rp 00.000.000</p></div>
                <div class="wrap-widget-button">
                    <button class="add-to-chart">ADD TO CART</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
             </div>
        </div>
        
        <div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    {{image('img/product/sample-prod.jpg')}} 
                </div>
                <label class="prod-name"><a href="#!">Nutrilon Royal </a></label>

                <div class="widget-price"><p class="price">Rp 00.000.000</p></div>
                <div class="wrap-widget-button">
                    <button class="add-to-chart">ADD TO CART</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
            </div>
        </div>
        
        <div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    {{image('img/product/sample-prod.jpg')}} 
                </div>
                <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin ProNutra Royal</a></label>

                <div class="widget-price"><p class="price">Rp 00.000.000</p></div>
                <div class="wrap-widget-button">
                    <button class="add-to-chart">ADD TO CART</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
            </div>
        </div>
        
         <div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    {{image('img/product/sample-prod.jpg')}} 
                </div>
                <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin</a></label>
                <div class="widget-price"><p class="onsale"><span class="normal-price">Rp 00.000.000 </span><span class="disc-price">Rp 00.000.000</span></p></div>
                <div class="wrap-widget-button">
                    <button class="add-to-chart">ADD TO CART</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
             </div>
        </div>
        
         <div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    {{image('img/product/sample-prod.jpg')}} 
                </div>
                <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin Nutrilon Royal ProNutra 3 Madu</a></label>
                <div class="widget-price"><p class="onsale"><span class="normal-price">Rp 00.000.000 </span><span class="disc-price">Rp 00.000.000</span></p></div>
                <div class="wrap-widget-button">
                    <button class="notify-me">NOTIFY ME</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
            </div>
        </div>
        
         <div class="prod-widget-vertical">
            <div class="wrap-widget">
                <div class="prod-image">
                    {{image('img/product/sample-prod.jpg')}} 
                </div>
                <label class="prod-name"><a href="#!">Nutrilon Royal ProNutra 3 Madu 800gr Tin</a></label>
                <div class="widget-price"><p class="onsale"><span class="normal-price">Rp 00.000.000 </span><span class="disc-price">Rp 00.000.000</span></p></div>
                <div class="wrap-widget-button">
                    <button class="add-to-chart">ADD TO CART</button>
                    <a class="add-to-wishlist">Add to wishlist</a>
                </div>
            </div>
        </div-->
 <!-- end List product vertical -->
