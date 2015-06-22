<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<div class="keypoint">
<ul>
<li class="free-shipping"><span class="title">Free Shipping</span>
<p>For minimum order Rp 200.000,-</p>
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

$installer->endSetup();
