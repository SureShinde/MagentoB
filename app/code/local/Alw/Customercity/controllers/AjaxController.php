<?php

class Alw_Customercity_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function suggestAction()
    {
		//echo $citys = $this->getRequest()->getParam('query');
        $this->getResponse()->setBody($this->getLayout()->createBlock('customercity/autocomplete')->toHtml());
    }
}