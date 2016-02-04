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
 * @package    AW_Ajaxlogin
 * @version    1.0.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * 
 */
class AW_Ajaxlogin_Model_AuthorizationAbstract extends Varien_Object {
    
    /**
     * 
     */
    protected function __attemptToLoginEmail($email) {
        $__customer = Mage::getModel('customer/customer');
        $__customer
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($email)
        ;
        
        if ( $__customer->getId() ) {
            if ($__customer->getConfirmation() && $__customer->isConfirmationRequired()) {
                $__value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                throw new Exception(Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $__value));
            }
            else {
                $__session = Mage::getSingleton('customer/session');
                $__session->setCustomerAsLoggedIn($__customer);
                
                /* CE 1.7.* and above */
                if ( method_exists($__session, 'renewSession') ) $__session->renewSession();
                
                return true;
            }
        }
        
        return false;
    }
}