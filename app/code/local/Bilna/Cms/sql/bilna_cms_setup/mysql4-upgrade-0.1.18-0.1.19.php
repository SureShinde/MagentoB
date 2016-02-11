<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<div>Get <span>50% OFF</span> on reusable diapers.</div>
<div>Buy 2 Get 1 <span>FREE</span> for Cussons Baby Wipes.</div>
<div><span>CLEARANCE SALE</span> on baby food and formula</div>
EOT;

$staticBlock = array(
    'title' => 'Shopping Cart Promo',
    'identifier' => 'shopping_cart_promo',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('shopping_cart_promo');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
