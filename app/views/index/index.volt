{% extends "core/templates/one-column.volt" %}

{% block title %}Homepage{% endblock %}

{% block body_class %} homepage {% endblock %}

{% block bellow_header %}
    {#widget('FlashMessageWidget')#}
{% endblock %}

{% block content %}
<div class="row">
    <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12 banner-homepage-area">
        <div class="first-level">
            {#widget('MenuMegaWidget',['load' : 'index'])#}
        </div>
    </div>
    <div class="col-lg-12 col-md-12 col-xs-12 wrap-slider-homepage-mobile">
        <ul class="slider-homepage-mobile">
            <li>{{image('img/wysiwyg/homepage-banner-mobile.jpg')}}</li>
            <li>{{image('img/wysiwyg/homepage-banner-mobile.jpg')}}</li>
            <li>{{image('img/wysiwyg/homepage-banner-mobile.jpg')}}</li>
        </ul>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 product-area">
        <div class="homepage-tab">
            <ul class="row">
                <li class="col-md-3 col-lg-3 col-sm-3 col-xs-3 active-tab">
                    <a class="tabs-mobile">{{image('img/skin/icon_tab_mainannmedia.png')}}</a>
                    <a class="tabs-desktop" href="#tab-cat-1">{{('Mainan & media')}}</a>
                </li>
                <li class="col-md-3 col-lg-3 col-sm-3 col-xs-3">
                    <a class="tabs-desktop" href="#tab-cat-2">{{('Personal Care')}}</a>
                    <a class="tabs-mobile">{{image('img/skin/icon_tab_personalcare.png')}}</a>
                </li>
                <li class="col-md-3 col-lg-3 col-sm-3 col-xs-3">
                    <a class="tabs-desktop" href="#tab-cat-3">{{('Health Care')}}</a>
                    <a class="tabs-mobile">{{image('img/skin/icon_tab_healthcare.png')}}</a>
                </li>
                <li class="col-md-3 col-lg-3 col-sm-3 col-xs-3">
                    <a class="tabs-desktop" href="#tab-cat-4">{{('Rumah, Dekorasi & Furniture')}}</a>
                    <a class="tabs-mobile">{{image('img/skin/icon_tab_dekorasi.png')}}</a>
                </li>
            </ul>
            <div class="content-tab" id="tabs-homepage-1">
                <select id="select-tabs-1" name="featured" class="select-featured-mobile">
                    <option value="select-value-1">{{t._('NEW ARRIVAL')}}</option>
                    <option value="select-value-2">{{t._('Most Popular')}}</option>
                    <option value="select-value-3">{{t._('Products on sale')}}</option>
                </select>
                <div class="list-prod-area col-md-4 col-lg-4 col-sm-12 col-xs-12 featured-1">
                    <p class="title-tabs">{{('NEW ARRIVAL')}}</p>
                    {#
                    {{ widget('ProductWidget',['productId' : 1492,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 1879,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 1424,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 997 ,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    #}
                </div>
                <div class="list-prod-area col-md-4 col-lg-4 col-sm-12 col-xs-12 featured-2">
                    <p class="title-tabs">{{t._('Most Popular')}}</p>
                    {#
                    {{ widget('ProductWidget',['productId' : 1492,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 1879,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 1424,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 997 ,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    #}
                </div>
                <div class="list-prod-area col-md-4 col-lg-4 col-sm-12 col-xs-12 featured-3">
                    <p class="title-tabs">{{t._('Products on sale!!')}}</p>
                    {#
                    {{ widget('ProductWidget',['productId' : 1492,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 1879,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 1424,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    {{ widget('ProductWidget',['productId' : 997 ,'customerGroupPrice' : _customerGroupPrice, 'qty' : _qty, 'layout' : 2]) }}
                    #}
                </div>
            </div>
        </div>
    </div>
    {#
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">            
    {{ widget('StaticAreaWidget',['identifier' : "home-promo-highlight"])}}
    </div>
    #}
    
    <div class="testimonial col-md-12 col-lg-12 col-sm-12">
        <div class="col-md-3 col-lg-3 col-sm-3 wrap-image-testimonial">
                <img src="{{url('img/skin/loader_small.gif')}}" class="lazy" data-original="{{url('img/frontend/base/testimonials-image.jpg')}}">
        </div>
        
        <div class="col-md-9 col-lg-9 col-sm-9 hidden-xs wrap-slider-testimonial">
        <ul class="slider-testimonial">
          <li>pan terbaik - "Seneng deh berbelanja di Bilna.com Belanja mudah, murah dengan pelayanan super cepat :) Ditambah free ongkir untuk pembelanjaan di atas 200.000 Bilna.com sangat membantu working mom yang super sibuk tapi tetap ingin menyediakan perlengkapan terbaik untuk anak dengan tetap mempertimbangkan nilai yang ekonomis ^^ Thanks Bilna! "</li>
          <li>pan terbaik - "Seneng deh berbelanja di Bilna.com Belanja mudah, murah dengan pelayanan super cepat :) Ditambah free ongkir untuk pembelanjaan di atas 200.000 Bilna.com </li>
          <li>pan terbaik - "Seneng super sibuk tapi tetap ingin menyediakan perlengkapan terbaik untuk anak dengan tetap mempertimbangkan nilai yang ekonomis ^^ Thanks Bilna! "</li>
          <li>pan terbaik - "Seneng deh berbelanja di Bilna.com Belanja mudah, murah dengan pelayanan super cepat :) Ditambah free ongkir untuk pembelanjaan di atas 200.000 Bilna.com sangat membantu working mom yang super sibuk tapi tetap ingin menyediakan perlengkapan terbaik untuk anak dengan tetap mempertimbangkan nilai yang ekonomis ^^ Thanks Bilna! "</li>
        </ul>
        <p><span id="slider-prev"></span> | <span id="slider-next"></span></p>
        </div>
    </div>
    
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">  
       <!--------------------------------------------------------- 6 grey -------------------------------------------------------->
        <ul class="wrap-block-link-bottom">
            <li>
                <div class="block-link-bottom">
                <img src="{{url('img/skin/loader_small.gif')}}" class="lazy" data-original="{{url('img/frontend/base/belanja-hemat-image.jpg')}}">
                <div class="wrap-bottom">
                    <p><span>Belanja Hemat</span>dengan program cicilan bank</p>
                    <p>
                    <a class="block-link" href="promo-bank">PROMO BANK </a>
                    <a class="block-link" href="tnc-cicilan">KETENTUAN</a>
                    </p>
                    </div>
                </div>
            </li>
            <li>
                <div class="block-link-bottom">
                <img src="{{url('img/skin/loader_small.gif')}}" class="lazy" data-original="{{url('img/frontend/base/pasarkan-image.jpg')}}">
                <div class="wrap-bottom">
                    <p><span>Pasarkan Produk Anda</span>di bilna.com</p>
                    <p><a class="block-link" href="feature-your-brand">KETENTUAN </a> </p>
                    </div>
                </div>
            </li>
            <li>
                <div class="block-link-bottom customer-service">
                <img src="{{url('img/skin/loader_small.gif')}}" class="lazy" data-original="{{url('img/frontend/base/cs-image.jpg')}}">
                <div class="wrap-bottom">
                    <p><span> 
                    Customer Care <br>(021) 2902 2090
                    </span>
                    Senin-Jumat 08.00-19.00<br>
                    Sabtu 08.00-17.00
                    </p>
                    <p>
                    <a class="mailto block-link" href="mailto:cs@bilna.com">cs@bilna.com</a>
                    </p>
                    </div>
                </div>
            </li>
        </ul>
        <!---------------------------- Bottom ---------------------------------->
        <ul class="wrap-block-link-bottom">
            <li>
                <div class="block-link-bottom">
                    <div class="wrap-bottom">
                    <p><span>Bantuan Berbelanja</span>di bilna.com</p>
                    <p>
                    <a class="block-link" href="how-to-buy">HOW TO BUY</a>
                    <a class="block-link" href="shipping-policy">SHIPPING POLICY</a>
                    </p>
                    </div>
                </div>
            </li>
            <li>
                <div class="block-link-bottom">
                    <div class="wrap-bottom">
                        <p><span>Belanja & Berbagi</span>di bilna.com</p>
                        <p>
                        <a class="block-link" href="bilna-credits">BILNA CREDIT</a>
                        <a class="block-link" href="baby/set-hadiah-bayi-dan-anak/gift-voucher.html">GIFT VOUCHER</a>
                        </p>
                    </div>
                </div>
            </li>
            <li>
                <div class="block-link-bottom manfaat-lain">
                    <div class="wrap-bottom">
                    <p><span>Dapatkan Manfaat Lain</span>di bilna.com</p>
                    <p>
                    <a class="block-link" href="affiliate-program">AFFILIATE PROGRAM</a>
                    <a class="block-link" href="reseller-program">RESELLER PROGRAM</a>
                    </p>
                    </div>
                </div>
            </li>
        </ul>            
    </div>
    
    
    {#
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 banner-bottom-homepage">
        <div class="col-md-4 col-lg-4 col-sm-4">
            <div class="banner-large">
            </div>
            <div class="banner-small">
            </div>
            <div class="banner-small">
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 banner-bottom-homepage">
        <div class="col-md-4 col-lg-4 col-sm-4">
            <div class="banner-large">
            </div>
            <div class="banner-small">
            </div>
            <div class="banner-small">
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 banner-bottom-homepage">
        <div class="col-md-4 col-lg-4 col-sm-4">
            <div class="banner-large">
            </div>
            <div class="banner-small">
            </div>
            <div class="banner-small">
            </div>
        </div>
    </div>
    <div class="featured_on col-md-5 col-lg-5 col-sm-6">
        <h4>{{('As Featured On')}}:</h4>
        {{image('img/frontend/base/featured/urbanmama.png')}}
        {{image('img/frontend/base/featured/ayahbunda.png')}}
        {{image('img/frontend/base/featured/mommiesdaily.png')}}
        {{image('img/frontend/base/featured/parent.png')}}
    </div>
    #}
</div>
{% endblock %}