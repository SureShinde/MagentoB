<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Adminhtml_Netsuite_ChangelogController extends Mage_Adminhtml_Controller_Action {
    public function _initAction()
    {
        $this->loadLayout()->_addBreadcrumb(Mage::helper('adminhtml')->__('Change Log'), Mage::helper('adminhtml')->__('Change Log'));
        return $this;
    }

    public function viewAction() {
        $this->_title($this->__('Change Log'))->_title($this->__('Change Log'))->_title($this->__('Change Log'));
        $this->_initAction()->_setActiveMenu('adminhtml/netsuite_changelog/view')->renderLayout();
    }

    public function deleteAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $item = Mage::getModel('rocketweb_netsuite/changelog')->load($id);

        if($item && $item->getId()) {
            try {
                $item->delete();
                $this->_getSession()->addSuccess("Item deleted");
            }
            catch (Exception $ex) {
                $this->_getSession()->addError($ex->getMesage());
            }
        }
        else {
            $this->_getSession()->addError("Item does not exist");
        }

        $this->getResponse()->setRedirect($this->getUrl('*/*/view'));
    }

    public function massDeleteAction() {
        $isError = false;
        $ids = $this->getRequest()->getParam('id');
        if(count($ids)) {
            foreach($ids as $id) {
                try {
                    $message = Mage::getModel('rocketweb_netsuite/changelog')->load($id);
                    $message->delete();
                }
                catch(Exception $ex) {
                    $isError = true;
                    $this->_getSession()->addError($ex->getMesage());
                }
            }
        }
        if(!$isError) {
            $this->_getSession()->addSuccess("Items deleted");
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/view'));
    }
}