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
class RocketWeb_Netsuite_Model_System_Config_Backend_Serialized_Orderstatusmap extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array {
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $this->setValue($value);
        parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getValue() === false)
            $this->setDefaultMapProductColumns();
    }

    protected function setDefaultMapProductColumns()
    {
        $this->setValue(Mage::getSingleton('rocketweb_netsuite/config')->convertDefaultMapOrderstatuses());
    }
}