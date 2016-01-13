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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


require_once 'Mage' . DS . 'Checkout' . DS . 'controllers' . DS . 'CartController.php';

class AW_Afptc_CartController extends Mage_Checkout_CartController
{     
    const SESSION_KEY = 'aw-afptc-session-key';
   
    public function addProductAction()
    {    
        $helper = Mage::helper('awafptc');       
        if(!$this->getRequest()->isPost()) {
            exit('Invalid session');
        }       
        $params = $this->getRequest()->getPost();          
        if(!isset($params[self::SESSION_KEY])) {
            exit('Invalid session');
        }
        if(!is_string($params[self::SESSION_KEY]) || strlen($params[self::SESSION_KEY]) > 500) {
            exit('Invalid session');
        }        
        if(!$helper->validateSessionKey($params[self::SESSION_KEY])) {
           Mage::getSingleton('checkout/session')->addError($this->__('Your session has expired, please resubmit data'));
           return $this->_redirectReferer();
        }         
        $rule = $helper->getRuleFromSession();        
        if(!$rule) {
           Mage::getSingleton('checkout/session')->addError($this->__('Your session has expired, please resubmit data'));
           return $this->_redirectReferer();
        }
       
        /* to add multiple products in cart add info as related products in request */
        Mage::register(AW_Afptc_Helper_Data::POPUP_PRODUCT_RULE, array($rule->getProductId() => $rule), true);
        
        Mage::app()->getRequest()->setParam('product', $rule->getProductId());
        
        return $this->addAction();
        
    }
}