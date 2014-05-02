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
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Attributeset {
    public function toOptionArray()
    {
        $options = array();
        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityTypeId);
        foreach($attributeSetCollection as $attributeSetCollectionItem) {
            $options[]=array('value'=>$attributeSetCollectionItem->getAttributeSetId(),'label'=>$attributeSetCollectionItem->getAttributeSetName());
        }
        return $options;
    }
}