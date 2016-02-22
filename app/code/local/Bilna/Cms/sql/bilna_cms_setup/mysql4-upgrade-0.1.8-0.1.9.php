<?php

$installer = $this;
$installer->startSetup();

$cmsPage = Mage::getModel('cms/page')->load('home', 'identifier');

$pageContent =<<<EOF
<div class="homepage">
<div class="homepage-top">
<div class="homepage-top-block left">{{block type="cms/block" block_id="shopby"}}</div>
<div class="homepage-top-block middle" id="homepage-middle">
<div class="slider-wrapper">
{{block type="awislider/block" id="homepage"}}
</div>
<div class="advertise">
<ul>
<li>{{block type="cms/block" block_id="small_banner_left"}}</li>
<li>{{block type="cms/block" block_id="small_banner_right"}}</li>
</ul>
</div>
</div>
<div class="homepage-top-block right">{{block type="cms/block" block_id="banner_right"}}</div>
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
{{block type="cms/block" block_id="block_home"}}
{{block type="core/template" template="page/template/testimonial.phtml" identifier="testimonial_"}}
{{block type="cms/block" block_id="media_partners"}}
<div class="clear">&nbsp;</div>
</div>
{{block type="core/template" template="page/template/bottommenu.phtml"}}
</div>
<script type="text/javascript">// <![CDATA[
jQuery(window).load(function() {
jQuery('#slides').slidesjs({
width: 376,
height: 120,
navigation: false,
pagination: false
});
if(wwidth > 768) {
jQuery("html, body").animate({ scrollTop: 660 }, 1000);
}
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
