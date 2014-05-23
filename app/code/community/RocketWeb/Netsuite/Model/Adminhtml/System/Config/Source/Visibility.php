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
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Visibility {
    public function toOptionArray()
    {
        return array(
            array('value'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,'label'=>'Not Visible'),
            array('value'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,'label'=>'Catalog'),
            array('value'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,'label'=>'Search'),
            array('value'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,'label'=>'Catalog, Search'),
        );
    }
}