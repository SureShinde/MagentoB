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

    /*protected function _toHtml() {
        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;
        if (Mage::helper('points')->magentoLess14())
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;
        $this->setTemplate('wrappinggiftevent/checkout/onepage/shipping_method/available.phtml');

        return parent::_toHtml();
    }*/


    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }
    
    /*protected function _toHtml()
    {
        $this->setTemplate('wrappinggiftevent/onepage/shipping_method/available.phtml');

        return parent::_toHtml();
    }*/

}
			
