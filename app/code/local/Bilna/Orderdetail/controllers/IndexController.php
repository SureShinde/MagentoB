<?php
class Bilna_Orderdetail_IndexController extends Mage_Core_Controller_Front_Action {
    public function IndexAction() {
        $this->loadLayout();   
        $this->getLayout()->getBlock('head')->setTitle($this->__('Order Detail'));
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array (
            'label' => $this->__('Home Page'),
            'title' => $this->__('Home Page'),
            'link' => Mage::getBaseUrl()
        ));
        $breadcrumbs->addCrumb('orderdetail', array (
            'label' => $this->__('Order Detail'),
            'title' => $this->__('Order Detail')
        ));
        $this->renderLayout();
    }
}
