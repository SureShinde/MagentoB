<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<div class="shopby">
<ul class="shop-by">
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
<ul class="shop-by">
<li><span class="title">Gift Ideas</span></li>
<li><a href="#">Checklist For New Moms</a></li>
<li><a href="#">Hospital Bag Checklist</a></li>
<li><a href="#">Birthday Gift Ideas</a></li>
<li><a href="#">Baby Shower Gift Ideas</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
<ul class="shop-by" style="background: none;">
<li><span class="title">Product Highlights</span></li>
<li><a href="#">Baby Monitor</a></li>
<li><a href="#">Stroller</a></li>
<li><a href="#">Baby Box</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Shop by',
    'identifier' => 'shopby',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('shopby');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$cmsPage = Mage::getModel('cms/page')->load('home', 'identifier');

$pageContent =<<<EOF
<div class="homepage">
<div class="homepage-top">
<div class="homepage-top-block left">{{block type="cms/block" block_id="shopby"}}</div>
<div class="homepage-top-block middle">
<div class="slider-wrapper">
{{block type="awislider/block" id="homepage"}}
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
{{block type="awfeatured/block" id="product_sale"}}
<div class="homepage-product newarrival">
<h2 class="title">New Arrival</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
{{block type="awfeatured/block" id="new_arrival"}}
<div class="homepage-product mostpopular">
<h2 class="title">Most Popular</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
{{block type="awfeatured/block" id="most_popular"}}
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
{{block type="core/template" template="page/template/testimonial.phtml" identifier="testimonial_"}}
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
jQuery(window).load(function() {
jQuery('#slides').slidesjs({
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

$cmsPage->setStores(0)
		->setContent($pageContent)
		->setIsActive(1)
		->setRootTemplate('one_column')
		->save();

$installer->endSetup();
