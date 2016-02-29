<?php
class Moxy_SocialCommerce_Adminhtml_SocialcommercebackendController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Social Commerce"));
	   $this->renderLayout();
    }
}