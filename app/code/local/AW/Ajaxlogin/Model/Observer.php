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
class AW_Ajaxlogin_Model_Observer {
    
    /**
     * 
     */
    public function controllerActionLayoutLoadBefore($theObserver) {
        if ( $this->__isModuleEnabled() ) {
            $__update = $theObserver->getEvent()->getLayout()->getUpdate();
            
            $__update->addHandle(AW_Ajaxlogin_Helper_Data::LAYOUT_HANDLER_DEFAULT);
            if (
                ( in_array(AW_Ajaxlogin_Helper_Data::HANDLER_CUSTOMERACCOUNTLOGIN, $__update->getHandles()) )
                OR
                ( in_array(AW_Ajaxlogin_Helper_Data::HANDLER_CHECKOUTMULTISHIPPINGLOGIN, $__update->getHandles()) )
                OR
                ( in_array('ajaxlogin_example_index', $__update->getHandles()) )
            ) {
                $__update->addHandle(AW_Ajaxlogin_Helper_Data::LAYOUT_HANDLER_CUSTOMERACCOUNTLOGIN);
            }
            if ( in_array(AW_Ajaxlogin_Helper_Data::HANDLER_CHECKOUTONEPAGEINDEX, $__update->getHandles()) ) {
                $__update->addHandle(AW_Ajaxlogin_Helper_Data::LAYOUT_HANDLER_CHECKOUTONEPAGEINDEX);
            }
        }
    }
    
    
    /**
     * 
     */
    public function coreBlockAbstractToHtmlAfter($theObserver) {
        if ( $this->__isModuleEnabled() ) {
            $__block = $theObserver->getBlock();
            if ( $__block ) {
                if ( $__block->getNameInLayout() == 'top.links' ) {
                    $__transport = $theObserver->getTransport();
                    $__extraBlock = $__block->getLayout()->createBlock('ajaxlogin/overwriterToplinks', 'al_ow_toplinks');
                    if ( $__extraBlock ) {
                        $__extraBlock->setTemplate('ajaxlogin/overwritter.topLinks.phtml');
                        $__transport->setHtml( $__transport->getHtml() . $__extraBlock->toHtml() );
                    }
                }
            }
        }
    }
    
    
    /**
     * 
     */
    protected function __isModuleEnabled() {
        return Mage::getStoreConfig(AW_Ajaxlogin_Helper_Data::XML_CONFIG_PATH_GENERAL_MODULE_ENABLED) ? true : false;
    }
}