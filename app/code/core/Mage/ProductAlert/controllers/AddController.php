<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * ProductAlert controller
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ProductAlert_AddController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        /*if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if(!Mage::getSingleton('customer/session')->getBeforeUrl()) {
                Mage::getSingleton('customer/session')->setBeforeUrl($this->_getRefererUrl());
            }
        }*/
    }

    public function testObserverAction()
    {
        $object = new Varien_Object();
        $observer = Mage::getSingleton('productalert/observer');
        $observer->process($object);
    }

    public function priceAction()
    {
error_log("\nppppppppppp", 3, '/tmp/alerts.log');        
        $session = Mage::getSingleton('catalog/session');
        $backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');
        
        $post = Mage::app()->getRequest()->getPost('data');

        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return ;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            /* @var $product Mage_Catalog_Model_Product */
            $session->addError($this->__('Not enough parameters.'));
            if ($this->_isUrlInternal($backUrl)) {
                $this->_redirectUrl($backUrl);
            } else {
                $this->_redirect('/');
            }
            return ;
        }
error_log("\nxxxxxxxxxxxxxxx", 3, '/tmp/alert.log');


        

        if( Mage::helper('customer')->isLoggedIn() ){
error_log("\nlogin", 3, '/tmp/alerts.log');            
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $email = $customer->getEmail();
            $customer_id = Mage::getSingleton('customer/session')->getId();
error_log("\n".print_r($customer,1), 3, '/tmp/alerts.log');            
        }else{
            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($post['email']);
            if($customer->getEmail())
            {
                $email = $customer->getEmail();
                $customer_id = $customer->getId();
            }else{
                $email = $post['email'];
                $customer_id = 0;
            }
        }
error_log("\n".print_r(Mage::getSingleton('customer/session'),1), 3, '/tmp/alerts.log');

        try {
            $model  = Mage::getModel('productalert/price')
                ->setCustomerId($customer_id)
                ->setEmail($email)
                ->setProductId($product->getId())
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $model->save();
            $session->addSuccess($this->__('The alert subscription has been saved.'));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        $this->_redirectReferer();
    }

    public function stockAction()
    {
error_log("\naaaaaaaaaaaa", 3, '/tmp/alerts.log');        
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');

        $product = Mage::getModel('catalog/product')->load($productId);

        $post = Mage::app()->getRequest()->getPost('data');
        
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return ;
        }

        if (!$product = Mage::getModel('catalog/product')->load($productId)) {
            /* @var $product Mage_Catalog_Model_Product */
            $session->addError($this->__('Not enough parameters.'));
            $this->_redirectUrl($backUrl);
            return ;
        }
error_log("\nqqqqq", 3, '/tmp/alert.log');


        

        if( Mage::helper('customer')->isLoggedIn() ){
error_log("\nlogin", 3, '/tmp/alerts.log');            
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $email = $customer->getEmail();
            $customer_id = Mage::getSingleton('customer/session')->getId();
error_log("\nemail".print_r($email,1), 3, '/tmp/alerts.log');            
error_log("\ncustomer_id".print_r($customer_id,1), 3, '/tmp/alerts.log');            
        }else{
            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($post['email']);
            if($customer->getEmail())
            {
                $email = $customer->getEmail();
                $customer_id = $customer->getId();
            }else{
                $email = $post['email'];
                $customer_id = 0;
            }
error_log("\nemail".print_r($email,1), 3, '/tmp/alerts.log');            
error_log("\ncustomer_id".print_r($customer_id,1), 3, '/tmp/alerts.log');                 
        }
//error_log("\n".print_r(Mage::getSingleton('customer/session'),1), 3, '/tmp/alerts.log');

        try {
            $model = Mage::getModel('productalert/stock')
                ->setCustomerId($customer_id)
                ->setEmail($email)
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $model->save();
            $session->addSuccess($this->__('Alert subscription has been saved.'));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        $this->_redirectReferer();
    }
}
