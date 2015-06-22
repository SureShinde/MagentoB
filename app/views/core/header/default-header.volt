<div class="floating-right">
    <a class="backtotop">
         <span>▲</span> <br> Back to Top
    </a>
    <div class="onlinechat">
        <div class="link-chat-desktop">{{ image('img/skin/livechat.png')}}<br>LIVE CHAT</div>
        <a class="link-chat-mobile" href="{{url()}}/chat">{{ image('img/skin/livechat.png')}}<br>LIVE CHAT</a>
        <div class="wrap-online-chat">{{ image('img/frontend/base/zopim.jpg')}}</div>
    </div>
</div>
<div id="ajax-loader" class="wrap-loader">
    {{ image('img/skin/loader.gif')}}
</div>
<div class="logo-sticky">
    <a href="{{url()}}">{{ image('img/frontend/base/logo-bilna-sticky.png')}}</a>
</div>
<div class="header-top">
    <div class="logo">
        {% block logo %}
            {% include dirRoot~'core/header/content/logo' with ['dirRoot': dirRoot] %}
        {% endblock %}
    </div>
    <div class="wrap-sticky-highlight">
        <!--<div class="today-promo sticky-highlight">
            <div class="sticky-highlight-icon"></div>
            
            <p>{{ t._('Today Promo') }}</p>
            
            <div class="sticky-tooltip">
                <ul>
                    <li><a href="#"><span>Discount 10% for AK-47</span> just 2 day left</a></li>
                    <li><a href="#"><span>Discount 5%</span>  just 2 day left for <span>Hand Granate</span></a></li>
                    <li><a href="#"><span>Discount 10% for AK-47</span> just 2 day left</a></li>
                    <li><a href="#"><span>Discount 10% for AK-47</span> just 2 day left</a></li>
                </ul>
                <p>{{t._('See more products on sale!')}}</p>
            </div>
        </div>-->
        
        {% set newsletterValue = '' %}
    
        {% if isCustomerLogin == true %}
            {% if customerData['newsletterSubscriber']['status'] == false %}
                {% set newsletterShow = true %}
                {% set newsletterValue = customerData['newsletterSubscriber']['email'] %}
            {% else %}
                {% set newsletterShow = false %}
            {% endif %}
        {% else %}
            {% set newsletterShow = true %}
        {% endif %}

        {% if newsletterShow == true %}
            <div class="free-voucher sticky-highlight">
                <div class="sticky-highlight-icon"></div>
                <p>{{t._('Get FREE')}}<br>{{t._('Voucher')}}</p>

                <div class="sticky-tooltip">
                    <p><span>{{t._('GET FREE RP50,000 VOUCHER!')}}</span><br>{{t._('Sign up to Bilna Newsletter and get your voucher!')}}</p>
                    <form id="newsletter-subscribe-header">
                        <input type="text" id="newsletter-email" name="newsletter-email" placeholder="{{ t._('Enter your email') }}" value="{{ newsletterValue }}" />
                        <button type="submit">{{ t._('Submit') }}</button>
                    </form>
                </div>
            </div>
        {% endif %}
        
        <div class="care-center sticky-highlight">
            <div class="sticky-highlight-icon"></div>
            <p>{{t._('Care')}}<br>{{t._('Center')}}</p>
            <div class="sticky-tooltip">
                <p><span>{{t._('Perlu bantuan?')}}</span></br>
{{t._('Hubungi kami di (021) 2902 2090 atau')}}<br>{{t._('kirim email ke cs@bilna.com')}}
                <button>{{t._('Create message')}}</button></label>
                </p>
                
            </div>
        </div>
    </div>
    <div class="wrap_tagline">
        <div class="tagline">
            <p><span>{{t._('GRATIS PENGIRIMAN')}}<br></span> {{t._('Order di atas')}} Rp. 200,000,-</p>
        </div>
        <div class="tagline">
            <p><span>{{t._('BAYAR DI TEMPAT')}}<br></span>{{t._('Di 56 Kota')}}</p>
        </div>
        <div class="tagline">
            <p><span>{{t._('PENGEMBALIAN MUDAH')}}<br></span>{{t._('7 Hari Pengembalian')}}</p>
        </div>
        <div class="tagline">
            <p><span>{{t._('LAYANAN PELANGGAN')}}<br></span>(021) 2902 2090</p>
        </div>
    </div>
    <div class="wrap_membernav">
        {% block menu_desktop %}
            {% include dirRoot~'core/header/content/menu-desktop' with ['dirRoot': dirRoot] %}
        {% endblock %}
    </div>
</div>
<div class="menu-navigation">
    {% block megamenu_header %}
            {% include dirRoot~'core/header/content/megamenu' with ['dirRoot': dirRoot] %}
        {% endblock %}
</div>

<div class="col-xs-12 mobile-header">
    {% block menu_mobile %}
        {% include dirRoot~'core/header/content/menu-mobile' with ['dirRoot': dirRoot] %}
    {% endblock %}
</div>