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
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Taxclass {
    public function toOptionArray()
    {
        $options = array();

        $options[]=array('value'=>0,'label'=>'None');
        $taxColection = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_type',Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
        foreach($taxColection as $taxCollectionItem) {
            $options[]=array('value'=>$taxCollectionItem->getClassId(),'label'=>$taxCollectionItem->getClassName());
        }

        return $options;
    }
}