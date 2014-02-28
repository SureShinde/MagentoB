<?php

class AW_Blog_SearchController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		/*$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Search Blog Articles'));

		$this->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('blog/blog/layout'));
		$this->renderLayout();
        return true;*/

        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate(Mage::helper('blog')->getLayout());
        $this->renderLayout();
        return true;
	}
}