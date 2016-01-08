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
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Trackingtype {
    public function toOptionArray()
    {
        $options = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();

        $options[]=array('value'=>'custom','label'=>Mage::helper('sales')->__('Custom Value'));
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $options[] = array('value'=>$code,'label'=>$carrier->getConfigData('title'));
            }
        }

        return $options;
    }
}