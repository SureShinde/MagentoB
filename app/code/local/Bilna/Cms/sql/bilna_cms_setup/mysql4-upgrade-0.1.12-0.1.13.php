<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<img src="{{skin url='images/left-banner.png'}}" />
EOT;

$staticBlock = array(
    'title' => 'Left Banner Category',
    'identifier' => 'left_banner_category',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('left_banner_category');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
{{block type="awfeatured/block" id="most_popular"}}
EOT;

$staticBlock = array(
    'title' => 'Featured Category',
    'identifier' => 'featured_category',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('featured_category');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
