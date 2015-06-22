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

$setup->addAttribute('catalog_product', 'netsuite_last_import_date', array(
    'label'                      => 'Netsuite last import date',
    'sort_order'                 => 100,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'General',
    'required'                   => false,
    'type'                       => 'datetime',
    'input'                      => 'text',
    'backend'                    => 'eav/entity_attribute_backend_datetime',
));

$installer->endSetup();