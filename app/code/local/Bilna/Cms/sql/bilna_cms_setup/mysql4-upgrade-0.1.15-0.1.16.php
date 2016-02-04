<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<div class="keypoint">
<ul>
<li class="free-shipping"><span class="title">Free Shipping</span>
<p>For orders under Rp. 200,000,-</p>
</li>
<li class="cod"><span class="title">COD Payment</span>
<p>For Jadetabek area only</p>
</li>
<li class="return"><span class="title">EASY RETURNS</span>
<p>For Jadetabek area only</p>
</li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Keypoint',
    'identifier' => 'keypoint',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('keypoint');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="payment-partners">
<span class="title">Payment Partners</span>
<table>
<tr>
<td class="payment-left"><a href="#"><img src="{{skin url="images/visa.png"}}" alt="" /></a></td>
<td class="payment-right"><a href="#"><img src="{{skin url="images/klikbca.png"}}" alt="" /></a></td>
</tr>
<tr>
<td class="payment-left"><a href="#"><img src="{{skin url="images/klikpay.png"}}" alt="" /></a></td>
<td class="payment-right"><a href="#"><img src="{{skin url="images/bca.png"}}" alt="" /></a></td>
</tr>
<tr>
<td class="payment-left"><a href="#"><img src="{{skin url="images/bni.png"}}" alt="" /></a></td>
<td class="payment-right"><a href="#"><img src="{{skin url="images/mandiri.png"}}" alt="" /></a></td>
</tr>
</table>
</div>
EOT;

$staticBlock = array(
    'title' => 'Payment Partners',
    'identifier' => 'payment_partners',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('payment_partners');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
