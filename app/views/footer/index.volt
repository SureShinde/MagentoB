{% extends "core/templates/one-column-footer.volt" %}

{% block content %}
<div class="wrapfooter col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="row footer-subscribe">
        <p class="col-lg-8 col-md-8">{{t._('Don’t want miss every single deals? Enter your email here')}}</p>
        <form class="col-lg-4 col-md-4">
            <input type="text col-lg-8 col-md-8" placeholder="{{t._('Enter your email')}}">
            <button type="submit col-lg-4 col-md-4">{{t._('Submit')}}</button>
        </form>
    </div>
    <div class="row footer-link">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
            <ul>
                <li class="head-list">{{t._('Company Info')}}</li>
                <li><a href="#">{{t._('About Us')}}</a></li>
                <li><a href="#">{{t._('Contact Us')}}</a></li>
                <li><a href="#">{{t._('Careers')}}</a></li>
                <li><a href="#">{{t._('Testimonials')}}</a></li>
                <li><a href="#">{{t._('Feature Your Brand')}}</a></li>   
            </ul>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
            <ul>
                <li class="head-list">{{t._('Shipping & Policies')}}</li>
                <li><a href="#">{{t._('COD Policy')}}</a></li>
                <li><a href="#">{{t._('Returns Policy')}}</a></li>
                <li><a href="#">{{t._('Terms of Use')}}</a></li>
                <li><a href="#">{{t._('Privacy Policy')}}</a></li>
                <li><a href="#">{{t._('Shipping Policy')}}</a></li>   
                <li><a href="#">{{t._('Reseller Program')}}</a></li>   
                <li><a href="#">{{t._('T & C Voucher')}}</a></li>   
            </ul>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
             <ul>
                <li class="head-list">{{t._('Shop')}}</li>
                <li><a href="#">{{t._('How to Buy')}}</a></li>
                <li><a href="#">{{t._('Brand List')}}</a></li>
                <li><a href="#">{{t._('FAQ')}}</a></li>  
            </ul>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 payment-footer">
            <ul>
                <li class="head-list">{{t._('Payment Partners')}}</li>
                <li class="thumbnail bca"></li>
                <li class="thumbnail visa"></li>
                <li class="thumbnail bca-klikpay"></li>
                <li class="thumbnail bni"></li>
                <li class="thumbnail klik-bca"></li>
                <li class="thumbnail mandiri"></li>
                <li class="thumbnail mastercard"></li>
            </ul>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 shipping-footer">
            <ul>
                <li class="head-list">{{t._('Shipping Partners')}}</li>
                <li class="thumbnail jne"></li>
                <li class="thumbnail sap"></li>
                <li class="thumbnail first-logistics"></li>
            </ul>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 connect-footer">
            <ul>
                <li class="head-list">{{t._('CONNECT WITH US')}}</li>
                <li class="facebook"></li>
                <li class="twitter"></li>
                <li class="path"></li>
                <li class="instagram"></li>
                <li class="gplus"></li>
                <li class="blog"></li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 about-bilna">
            Bilna.com merupakan online baby shop yang menjual berbagai perlengkapan bayi, ibu hamil, dan kebutuhan ibu menyusui dengan kualitas terbaik dan harga murah. Anda dapat menemukan ribuan produk yang lengkap, mulai dari diaper, susu formula, perlengkapan mandi, botol dot, mainan, pakaian bayi, tempat tidur bayi, kursi makan, alat gendong, baby bouncer, car seat, stroller hingga breast pump dan thermometer pun dapat anda temui di toko bayi Bilna. Produk yang kami jual berasal dari berbagai brand terkenal dan terpercaya seperti Pigeon, Nestle, SGM, Sebamed, Morinaga, Mamy Poko, Prenagen, Huggies, Dancow, Philips AVENT, dan masih banyak lagi.<br><br>

Anda dapat menghemat waktu dan tenaga dengan berbelanja perlengkapan bayi anda di Bilna.com karena belanja terasa mudah dan menyenangkan. Anda dapat pula menghubungi costumer care kami apabila ada produk yang anda butuhkan namun belum ada di bilna.com. Customer care kami yang ramah akan siap sedia untuk membantu Anda yang memerlukan informasi karena kami mengutamakan kepuasan pelanggan dalam berbelanja. Kami juga menawarkan pengiriman produk dengan pelayanan kurir terbaik untuk memastikan produk Anda tiba dengan kondisi baik dan tepat waktu.<br><br>

© 2012 - 2015 bilna.com. All rights reserved.
        </div>
    </div>
    
</div>
{% endblock %}                
