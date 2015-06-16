<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<ul class="block-wrapper">
<li>
<div class="block-bottom">
<p>Shopping Tips &amp; Tricks:</p>
<span>Diapers!</span><br /><br />
<p><a class="block-link" href="#">Read <img src="{{skin url="images/arrow-1.png"}}"/></a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>Weekly Pregnancy Stage</span>
<p>Check Your Pregnancy!</p>
<br /><br />
<p><a class="block-link" href="#">Check Now! <img src="{{skin url="images/arrow-1.png"}}"/></a></p>
</div>
</li>
<li>
<div class="block-bottom">
<p>Punya pertanyaan<br /> seputar gizi &amp; kesehatan si kecil?</p>
<span>ask bilna&rsquo;s team!</span>
<p><a class="block-link" href="#">Ask Now! <img src="{{skin url="images/arrow-1.png"}}"/></a></p>
</div>
</li>
</ul>
<ul class="block-wrapper">
<li>
<div class="block-bottom">
<p>Nikmati</p>
<span>Cicilan %!</span><br /><br />
<p><a class="block-link" href="#">Syarat &amp; Ketentuan <img src="{{skin url="images/arrow-1.png"}}"/></a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>Help</span><br /><br />
<table>
<tbody>
<tr>
<td><a class="block-link link-how-to-buy" href="#">How to Buy? <img src="{{skin url="images/arrow-1.png"}}"/></a></td>
<td><a class="block-link link-shipping-table" href="#">Shipping Cost Table <img src="{{skin url="images/arrow-1.png"}}"/></a></td>
<td><a class="block-link link-other-help" href="#">Other Helps <img src="{{skin url="images/arrow-1.png"}}"/></a></td>
</tr>
</tbody>
</table>
</div>
</li>
<li>
<div class="block-bottom"><span>More on Bilna</span><br /><br />
<table>
<tbody>
<tr>
<td><a class="block-link link-bilna-credit" href="#">Bilna Credits <img src="{{skin url="images/arrow-1.png"}}"/></a></td>
<td><a class="block-link link-reseller-program" href="#">Reseller Program <img src="{{skin url="images/arrow-1.png"}}"/></a></td>
<td><a class="block-link link-gift-voucher" href="#">Gift Voucher <img src="{{skin url="images/arrow-1.png"}}"/></a></td>
</tr>
</tbody>
</table>
</div>
</li>
</ul>
EOT;

$staticBlock = array(
    'title' => 'Block Home',
    'identifier' => 'block_home',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('block_home');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
