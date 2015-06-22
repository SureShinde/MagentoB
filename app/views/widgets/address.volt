{#{{dump(listAddresses)}}#}
<div class="box-account col-lg-12 col-md-12 col-sm-12 col-xs-12" itemscope itemtype="http://schema.org/Place">
    <div class="title-box">
        {{t._('Address')}} - {{type_address[data['type']]}}
        <div class="button-top-address">
            <button class="button-box-customer edit-button" onclick="editAddress('{{data['addressId']}}');">{{t._('edit')}}</button>
            <button class="button-box-customer delete-button" onclick="deleteAddress('{{data['addressId']}}','{{data['customerId']}}');">{{t._('delete')}}</button>
        </div>
    
    </div>
    <p class="profile-add-name">{{type_address[data['type']]|capitalize}} - <strong itemprop="name">{{data['firstName']}} {{data['lastName']}}</strong></p>
    <p>
    {% if type_address[data['type']] == 'office'%}
    <span>Nama Perusahaan : {{data['company']}}</span></br>
    {% endif %}
    
    {% if type_address[data['type']] != 'residence'%}
    <span>gedung : {{data['building']}}</span></br>
    <span>Lantai : {{data['floor']}}</span></br>
    <span>Blok : {{data['block']}}</span></br>
    {% endif %}
    
    
    <span itemprop="streetAddress">{{data['address']}}</span><br>
    {% if data['additional'] %}
        <span>{{data['additional']}}</span>
    {% endif %}
    <span itemprop="addressLocality">{{data['district']}}</span><br>
    <span itemprop="addressRegion">{{data['city']}}</span><br>
    <span>{{data['province']}}</span><br>
    <span itemscope="postalCode">{{data['zipcode']}}</span><br>
    {#<span>Mobile : {{value['mobile']}}</span><br>#}
    {#<span itemprop="telephone">Phone : {{value['phone']}}</span><br>#}
    </p>
</div>
