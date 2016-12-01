<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->removeAttribute('catalog_product', 'on_sale_discount');
$setup->removeAttribute('catalog_product', 'on_sale_code');
$setup->removeAttribute('catalog_product', 'on_sale_from');
$setup->removeAttribute('catalog_product', 'on_sale_to');
$setup->addAttribute('catalog_product', 'on_sale_discount', array(
        'type'                       => 'varchar',
        'label'                      => 'On Sale Discount',
        'input'                      => 'text',
        'required'                   => false,
        'sort_order'                 => 112,
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'group'                      => 'General',
));
$setup->addAttribute('catalog_product', 'on_sale_code', array(
        'type'                       => 'varchar',
        'label'                      => 'On Sale Code',
        'input'                      => 'text',
        'required'                   => false,
        'sort_order'                 => 113,
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'group'                      => 'General',
));
$setup->addAttribute('catalog_product', 'on_sale_from', array(
        'type'                       => 'datetime',
        'label'                      => 'On Sale From',
        'input'                      => 'date',
        'required'                   => false,
        'sort_order'                 => 114,
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'group'                      => 'General',
));
$setup->addAttribute('catalog_product', 'on_sale_to', array(
        'type'                       => 'datetime',
        'label'                      => 'On Sale To',
        'input'                      => 'date',
        'required'                   => false,
        'sort_order'                 => 115,
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'group'                      => 'General',
));

$installer->endSetup();
