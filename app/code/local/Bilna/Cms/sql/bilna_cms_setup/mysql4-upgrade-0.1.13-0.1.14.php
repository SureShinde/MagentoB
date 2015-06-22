<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<img src="{{skin url='images/banner-category-small1.jpg'}}" />
<img src="{{skin url='images/banner-category-small2.jpg'}}" />
EOT;

$staticBlock = array(
    'title' => 'Banner Diapering',
    'identifier' => 'banner_diappering',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('banner_diappering');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
