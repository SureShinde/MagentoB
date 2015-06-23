<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<img src="{{skin url="images/banner-category-small1.jpg"}}"/>
EOT;

$staticBlock = array(
    'title' => 'Advertise Category Left',
    'identifier' => 'banner_category_left',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('banner_category_left');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<img src="{{skin url="images/banner-category-small2.jpg"}}"/>
EOT;

$staticBlock = array(
    'title' => 'Advertise Category Right',
    'identifier' => 'banner_category_right',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('banner_category_right');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
