<?php
class Bilna_Orderdetail_ProcessController extends Mage_Core_Controller_Front_Action {
    public function IndexAction() {
        $orderData = $this->getRequest()->getPost('orderdetail');

        // redirect to form if email & orderid is empty
        if (!isset ($orderData['email']) || !isset ($orderData['orderid'])) {
            Mage::getSingleton('core/session')->addError($this->__('Invalid Email'));
            $this->_redirect('orderdetail/index');
            return;
        }
        
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Order Detail'));
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array (
            'label' => $this->__('Home'),
            'title' => $this->__('Home'),
            'link' => Mage::getBaseUrl()
        ));
        $breadcrumbs->addCrumb('guestorder', array (
            'label' => $this->__('Guest Order'),
            'title' => $this->__('Guest Order')
        ));
        $breadcrumbs->addCrumb('orderid', array (
            'label' => $this->__('Order #' . $orderData['orderid']),
            'title' => $this->__('Order #' . $orderData['orderid'])
        ));
        $this->renderLayout();
    }
}
