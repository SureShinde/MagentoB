<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        CREATE TABLE IF NOT EXISTS `{$this->getTable('awfeatured/productimages')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `block_id` int(10) unsigned NOT NULL,
            `product_id` int(10) unsigned NOT NULL,
            `image_id` int(11) unsigned NOT NULL,
            PRIMARY KEY  (`id`) ,
            CONSTRAINT `FK_BLOCK_ID` FOREIGN KEY (`block_id`) REFERENCES `{$this->getTable('awfeatured/blocks')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = 'Product Images Storage';
    ");
} catch (Exception $ex) {
    Mage::logException($ex);
}

/**
 * Creating folder for uploads storage
 */

$path = Mage::getBaseDir('media').DS.Mage::helper('awfeatured/images')->getFolderName();
if (!file_exists($path)) {
    @mkdir($path);
}

$installer->endSetup();
