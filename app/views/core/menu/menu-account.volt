<div class="customer-menu-left">
    <ul>
        <li {% if indexMenu is defined %} class="now_active" {% endif %}><a href="{{url('customer/dashboard')}}">{{t._('Account Dashboard')}}</a></li>
        <li {% if detailMenu is defined %} class="now_active" {% endif %}><a href="{{url('customer/detail')}}">{{t._('Customer Details')}}</a></li>
        <li {% if addressMenu is defined %} class="now_active" {% endif %}><a href="{{url('customer/addresses')}}">{{t._('Address Book')}}</a></li>
        <li {% if orderMenu is defined %} class="now_active" {% endif %}><a href="{{url('customer/orders')}}">{{t._('My Orders')}}</a></li>
        <li {% if myreviewMenu is defined %} class="now_active" {% endif %}><a href="{{url('customer/myreview')}}">{{t._('My Product Reviews')}}</a></li>
        <li {% if amycreditMenu is defined %} class="now_active" {% endif %}><a href="{{url('customer/mycredit')}}">{{t._('Bilna Credits')}}</a></li>
        <li {% if affiliate is defined %} class="now_active" {% endif %}><a href="{{url('affiliate')}}">{{t._('Affiliate')}}</a></li>
    </ul>
</div>