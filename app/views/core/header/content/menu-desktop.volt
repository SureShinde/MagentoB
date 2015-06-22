<div class="wrap-membernav-top">
    <div class="membernav-top">
        <a href="#!" class="trackorder">{{t._('Track Order')}}</a> 

        <div class="log-reg">
            {% if customerData %}
                <div class="cust-name">
                    <a href="{{ url('customer') }}">
                        <b>{{ t._('Hi') }}, {{ firstname }}</b>
                    </a>
                    
                    <ul class="cust-after-login">
                        <li><a href="{{url('customer/detail')}}">{{t._('Customer Details')}}</a></li>
                        <li><a href="{{url('customer/addresses')}}">{{t._('Address Book')}}</a></li>
                        <li><a href="{{url('customer/orders')}}">{{t._('My Orders')}}</a></li>
                        <li><a href="{{url('customer/review')}}">{{t._('My Product Reviews')}}</a></li>
                        <li><a href="{{url('customer/credit')}}">{{t._('Bilna Credits')}}</a></li>
                        <li><a href="{{url('affiliate')}}">{{t._('Affiliate')}}</a></li>
                        <li><a href="{{url('logout')}}"><b>{{t._('Logout')}}</b></a></li>
                    </ul>
                </div>
            {% else %}
                <a class="reg" href="{{url('login')}}">{{t._('Login')}}</a><span>|</span><a href="{{url('register')}}">{{t._('Register')}}</a>
            {% endif %}
        </div>
        
        {{ widget('CartWidget')}}    
        <a class="link-blog">Blog</a>
    </div>
</div>