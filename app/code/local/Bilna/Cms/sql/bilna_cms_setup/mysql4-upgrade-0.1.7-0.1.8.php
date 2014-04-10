<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<div class="submenu popular-brand">
<span class="title">Popular Brands</span>
<ul>
<li><a href="#">MamyPoko</a></li>
<li><a href="#">Pampers</a></li>
<li><a href="#">Merries</a></li>
<li><a href="#">Nepia</a></li>
<li><a href="#">Huggies</a></li>
<li><a href="#">Ruparooz</a></li>
<li><a href="#">Okiedog</a></li>
</ul>
</div>    	<div class="submenu our-choice">
<span class="title">Our Choice</span>
<ul>
<li><a href="#">MamyPoko</a></li>
<li><a href="#">Pampers</a></li>
<li><a href="#">Merries</a></li>
<li><a href="#">Nepia</a></li>
<li><a href="#">Huggies</a></li>
<li><a href="#">Ruparooz</a></li>
<li><a href="#">Okiedog</a></li>
</ul>
</div>    	<div class="clear"></div>
<div class="featured-product">
<a href="http://dev-icube.bilna.com/19-widescreen-flat-panel-lcd-monitor.html" class="featured-image">
<img src="http://dev-icube.bilna.com/media/catalog/product/cache/1/small_image/131x192/9df78eab33525d08d6e5fb8d27136e95/1/9/19-widescreen-flat-panel-lcd-monitor.jpg" pagespeed_url_hash="3685818250">
</a>
</div>
EOT;

$staticBlock = array(
    'title' => 'Diapering menu',
    'identifier' => 'diapering-menu',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('diapering-menu');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
