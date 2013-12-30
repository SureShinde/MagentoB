<?php
/**
 *
 * @category    Bilna
 * @package     Bilna_Wrappinggiftevent
 * @copyright   Copyright (c) 2014 PT Bilna. (http://www.bilna.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout 
 *
 * @category   Bilna
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Wrappinggiftevent_Block_Checkout_Onepage_Shipping_Method extends  Mage_Checkout_Block_Onepage_Shipping_Method
{

    protected function _construct()
    {
        $this->getCheckout()->setStepData('shipping_method', array(
            'label'     => Mage::helper('wrappinggiftevent')->__('Shipping Method'),
            'is_show'   => $this->isShow()
        ));
        parent::_construct();
        //Mage::helper('core')->isModuleEnabled('Bilna_Wrappinggiftevent');
    }

    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }

}
			
