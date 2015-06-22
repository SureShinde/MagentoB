<div class="button-left-menu"></div>
<div class="logo">
    {% block logo %}
        {% include dirRoot~'core/header/content/logo' %}
    {% endblock %}
</div>
<div class="member-nav-mobile">
    <ul class="">
        <li class="cart"><a href="{{url('cart')}}">Checkout </a></li>
        <li class="account"><a href="login"></a></li>
        <!--<li class="link-blog"></li>
        <li class="search"></li>
        <li class="lang-link"></li>-->
    </ul>
</div>
<div class="wrap-search-mobile">
    <div class="search-mobile">
        <form method="GET" action="{{url('search')}}" id="search_mobile">
            {#widget('CategorySelectWidget')#}
            <div class="input-area">
                <input type="text" id="query" name="q" value="{#_query#}"></input>
                <button type="button" class="search-button"></button>
            </div>
        </form>
    </div>
</div>