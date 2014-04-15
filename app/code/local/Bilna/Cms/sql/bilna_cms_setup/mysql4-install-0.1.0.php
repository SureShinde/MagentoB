<?php

$installer = $this;
$installer->startSetup();

$cmsPage = Mage::getModel('cms/page')->load('home', 'identifier');

$pageContent =<<<EOF
<div class="homepage">
<div class="homepage-top">
<div id="shopby" class="homepage-top-block left">
<ul id="shop-by">
<li><span class="title">Popular Brand</span></li>
<li><a href="#">Mamy Poko</a></li>
<li><a href="#">Pampers</a></li>
<li><a href="#">Goon</a></li>
<li><a href="#">Nutrilon</a></li>
<li><a href="#">Bebelac</a></li>
<li><a href="#">Morinaga</a></li>
<li><a href="#">Nan</a></li>
<li><a href="#">Sebamed</a></li>
<li><a href="#">Cussons</a></li>
<li><a href="#">Zwitsal</a></li>
<li><a href="#">Philips Avent</a></li>
<li><a href="#">Dr. Browns</a></li>
<li><a href="#">Chicco</a></li>
<li><a href="#">Merries</a></li>
<li><a href="#">Maoo</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
<ul id="shop-by">
<li><span class="title">Gift Ideas</span></li>
<li><a href="#">Checklist For New Moms</a></li>
<li><a href="#">Hospital Bag Checklist</a></li>
<li><a href="#">Birthday Gift Ideas</a></li>
<li><a href="#">Baby Shower Gift Ideas</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
<ul id="shop-by" style="background: none;">
<li><span class="title">Product Highlights</span></li>
<li><a href="#">Baby Monitor</a></li>
<li><a href="#">Stroller</a></li>
<li><a href="#">Baby Box</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
</div>
<div class="homepage-top-block middle">
<div class="slider-wrapper">
<div id="slider" class="nivoSlider"><img src="{{skin url=" alt="" data-thumb="{{skin url="images/banner.jpg"}}" alt="" data-transition="slideInLeft" /> <img src="{{skin url="images/banner.jpg"}}" alt="" data-thumb="{{skin url="images/banner.jpg"}}" /></div>
</div>
<div class="advertise">
<ul>
<li><a href="#"><img src="{{skin url="images/banner-advertise-1.jpg"}}" alt="" /></a></li>
<li><a href="#"><img src="{{skin url="images/banner-advertise-2.jpg"}}" alt="" /></a></li>
</ul>
</div>
</div>
<div class="homepage-top-block right"><a href="#"><img src="{{skin url="images/banner-small.jpg"}}" alt="" /></a> <a href="#"><img src="{{skin url="images/banner-gift-voucher.png"}}" alt="" /></a> <a href="#"><img src="{{skin url="images/banner-small1.jpg"}}" alt="" /></a></div>
<div class="clear">&nbsp;</div>
</div>
<div class="homepage-middle">
<div class="homepage-product onsale">
<h2 class="title">Products on Sale!!</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
<div class="homepage-product newarrival">
<h2 class="title">New Arrival</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
<div class="homepage-product mostpopular">
<h2 class="title">Most Popular</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
</div>
<div class="homepage-bottom">
<ul class="block-wrapper">
<li>
<div class="block-bottom">
<p>Shopping Tips &amp; Tricks:</p>
<span>Diapers!</span><br /><br />
<p><a class="block-link" href="#">Read</a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>Weekly Pregnancy Stage</span>
<p>Check Your Pregnancy!</p>
<br /><br />
<p><a class="block-link" href="#">Check Now!</a></p>
</div>
</li>
<li>
<div class="block-bottom">
<p>Punya pertanyaan<br /> seputar gizi &amp; kesehatan si kecil?</p>
<span>ask bilna&rsquo;s team!</span>
<p><a class="block-link" href="#">Ask Now!</a></p>
</div>
</li>
</ul>
<ul class="block-wrapper">
<li>
<div class="block-bottom">
<p>Nikmati</p>
<span>Cicilan %!</span><br /><br />
<p><a class="block-link" href="#">Syarat &amp; Ketentuan</a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>Help</span><br /><br />
<p><a class="block-link link-how-to-buy" href="#">How to Buy?</a><a class="block-link link-shipping-table" href="#">Shipping Cost Table</a><a class="block-link link-other-help" href="#">Other Helps</a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>More on Bilna</span><br /><br />
<p><a class="block-link link-bilna-credit" href="#">Bilna Credits</a><a class="block-link link-reseller-program" href="#">Reseller Program</a><a class="block-link link-gift-voucher" href="#">Gift Voucher</a></p>
</div>
</li>
</ul>
<div class="testimonial-wrapper">
<h4><span>What Our Customer Says:</span></h4>
<div id="slides">{{block type="cms/block" block_id="testimonial_1"}}
<div>
<div class="testimonial">
<p>"Wow, pengiriman Bilna.com cepet banget ya? Baru sore kemarin saya pesan, besok siangnya sudah sampai. Sampai speechless loh! Biasanya kalau belanja online, pengiriman membutuhkan beberapa hari sampai di tempat. Bahkan ada yang berakhir dengan SMS, "Maaf Mbak, stoknya kosong. Mau diganti produk apa?" Kalau di Bilna.com, selain cepat sampai, packagingnya juga rapih. Serasa dapet kado dari Bilna.com loh! Puas deh sama Bilna.com."</p>
<p><strong>Nila Sarie Hermanto, Jakarta</strong></p>
</div>
</div>
<div>
<div class="testimonial">
<p>"Wow, pengiriman Bilna.com cepet banget ya? Baru sore kemarin saya pesan, besok siangnya sudah sampai. Sampai speechless loh! Biasanya kalau belanja online, pengiriman membutuhkan beberapa hari sampai di tempat. Bahkan ada yang berakhir dengan SMS, "Maaf Mbak, stoknya kosong. Mau diganti produk apa?" Kalau di Bilna.com, selain cepat sampai, packagingnya juga rapih. Serasa dapet kado dari Bilna.com loh! Puas deh sama Bilna.com."</p>
<p><strong>Nila Sarie Hermanto, Jakarta</strong></p>
</div>
</div>
<div>
<div class="testimonial">
<p>"Wow, pengiriman Bilna.com cepet banget ya? Baru sore kemarin saya pesan, besok siangnya sudah sampai. Sampai speechless loh! Biasanya kalau belanja online, pengiriman membutuhkan beberapa hari sampai di tempat. Bahkan ada yang berakhir dengan SMS, "Maaf Mbak, stoknya kosong. Mau diganti produk apa?" Kalau di Bilna.com, selain cepat sampai, packagingnya juga rapih. Serasa dapet kado dari Bilna.com loh! Puas deh sama Bilna.com."</p>
<p><strong>Nila Sarie Hermanto, Jakarta</strong></p>
</div>
</div>
<a class="slidesjs-previous slidesjs-navigation" href="#">Prev</a> <a class="slidesjs-next slidesjs-navigation" href="#">Next</a></div>
</div>
<div class="media-partners"><span class="title">As Featured On:</span>
<ul class="media-partners-wrapper">
<li><img src="{{skin url="images/urbanmama.png"}}" alt="" /></li>
<li><img src="{{skin url="images/ayahbunda.png"}}" alt="" /></li>
<li><img src="{{skin url="images/mommiesdaily.png"}}" alt="" /></li>
<li><img src="{{skin url="images/parent.png"}}" alt="" /></li>
</ul>
</div>
<div class="clear">&nbsp;</div>
</div>
</div>
<script type="text/javascript">// <![CDATA[
$j(window).load(function() {
$j('#slider').nivoSlider();
$j('#slides').slidesjs({
width: 376,
height: 120,
navigation: false,
pagination: false
});
});
// ]]></script>
EOF;


if($cmsPage->getId()){
	$cmsPage->setTitle('Home page')->setIdentifier('home');
}

$cmsPage->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->setRootTemplate('one_column')
		->save();


$cmsBlock = Mage::getModel('cms/block')->load('footer_links', 'identifier');
$pageContent =<<<EOF
<ul>
<li><span class="title">Company Info</span></li>
<li><a href="{{store direct_url="about-magento-demo-store"}}">About Us</a></li>
<li><a href="#">Contact Us</a></li>
<li><a href="{{store direct_url="customer-service"}}">Customer Service</a></li>
<li class="last privacy"><a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}">Privacy Policy</a></li>
</ul>
<ul>
<li><span class="title">Shipping &amp; Policies</span></li>
<li><a href="{{store direct_url="about-magento-demo-store"}}">About Us</a></li>
<li><a href="{{store direct_url="customer-service"}}">Customer Service</a></li>
<li class="last privacy"><a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}">Privacy Policy</a></li>
</ul>
<ul>
<li><span class="title">Company Info</span></li>
<li><a href="{{store direct_url="about-magento-demo-store"}}">About Us</a></li>
<li><a href="{{store direct_url="customer-service"}}">Customer Service</a></li>
<li class="last privacy"><a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}">Privacy Policy</a></li>
</ul>
EOF;


if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Footer Links')->setIdentifier('footer_links');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();


$cmsBlock = Mage::getModel('cms/block')->load('keypoint', 'identifier');
$pageContent =<<<EOF
<div class="keypoint">
<ul>
<li class="free-shipping"><span class="title">Free Shipping</span>
<p>For order under Rp. 200.000,-</p>
</li>
<li class="cod"><span class="title">COD Payment</span>
<p>For Jadetabek area only</p>
</li>
<li class="return"><span class="title">Free Shipping</span>
<p>For Jadetabek area only</p>
</li>
</ul>
</div>
EOF;

if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Keypoint')->setIdentifier('keypoint');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();


$cmsBlock = Mage::getModel('cms/block')->load('store_phone', 'identifier');
$pageContent =<<<EOF
<div class="store-phone">Care Center {{config path="general/store_information/phone"}}</div>
EOF;

if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Store Phone')->setIdentifier('store_phone');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();
		
		
$cmsBlock = Mage::getModel('cms/block')->load('conect_us', 'identifier');
$pageContent =<<<EOF
<div class="conect-us"><span class="title">Conect With Us</span> <a class="fb" href="#"><img src="{{skin url="images/fb.png"}}" alt="" /></a><a class="twitter" href="#"><img src="{{skin url="images/twitter.png"}}" alt="" /></a><a class="gplus" href="#"><img src="{{skin url="images/gplus.png"}}" alt="" /></a></div>
EOF;

if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Conect Us')->setIdentifier('conect_us');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();

		
$cmsBlock = Mage::getModel('cms/block')->load('payment_partners', 'identifier');
$pageContent =<<<EOF
<div class="payment-partners"><span class="title">Payment Partners</span> <a href="#"><img src="{{skin url="images/visa.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/klikbca.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/klikpay.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/bca.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/bni.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/mandiri.png"}}" alt="" /></a></div>
EOF;

if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Payment Partners')->setIdentifier('payment_partners');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();
	
		
$cmsBlock = Mage::getModel('cms/block')->load('shipping_partners', 'identifier');
$pageContent =<<<EOF
<div class="shipping-partners"><span class="title">Shipping Partners</span> <a href="#"><img src="{{skin url="images/jne.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/rpx.png"}}" alt="" /></a></div>
EOF;

if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Shipping Partners')->setIdentifier('shipping_partners');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();


$cmsBlock = Mage::getModel('cms/block')->load('testimonial_1', 'identifier');
$pageContent =<<<EOF
<div>
<div class="testimonial">
<p>"Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>
<p><strong>Nila Sarie Hermanto, Jakarta</strong></p>
</div>
</div>
EOF;

if(!$cmsBlock->getId()){
	$cmsBlock->setTitle('Testimonial 1')->setIdentifier('testimonial_1');
}

$cmsBlock->setStoreId(0)
		->setContent($pageContent)
		->setIsActive(1)
		->save();


$installer->endSetup();