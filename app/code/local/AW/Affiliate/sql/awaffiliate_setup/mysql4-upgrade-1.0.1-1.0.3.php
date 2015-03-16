<?php

$installer = $this;
$installer->startSetup();
$sql = <<<SQL

ALTER TABLE `{$this->getTable('awaffiliate/campaign')}` ADD `image_name` VARCHAR( 200 ) NULL AFTER `name` ;
ALTER TABLE `{$this->getTable('awaffiliate/campaign')}` ADD `url` VARCHAR( 250 ) NULL AFTER `image_name` ;
ALTER TABLE `{$this->getTable('awaffiliate/campaign')}` ADD `campaign_type` VARCHAR( 250 ) default 'banner' AFTER `name`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/products')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/categories')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL;

try {
    $installer->run($sql);
} catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();