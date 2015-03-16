<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<p>{{block type="awautorelated/blocks" block_id="1"}}</p>
EOT;

$staticBlock = array(
    'title' => 'More Buying Choice',
    'identifier' => 'buying_choice',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('buying_choice');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}


$content = <<<EOT
<p>{{block type="awautorelated/blocks" block_id="2"}}</p>
EOT;

$staticBlock = array(
    'title' => 'Related Product',
    'identifier' => 'related_product',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('related_product');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}


$installer->endSetup();
