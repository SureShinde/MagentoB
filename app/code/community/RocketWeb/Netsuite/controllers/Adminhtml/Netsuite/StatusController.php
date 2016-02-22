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
class RocketWeb_Netsuite_Adminhtml_Netsuite_StatusController extends Mage_Adminhtml_Controller_Action {
	
	public function _initAction()
	{
		$this->loadLayout()->_addBreadcrumb(Mage::helper('adminhtml')->__('Netsuite Export Status'), Mage::helper('adminhtml')->__('Netsuite Export Status'));
		return $this;
	}
	
	public function exportstatusAction() {
		$this->_title($this->__('Netsuite Export Status'))->_title($this->__('Netsuite Export Status'))->_title($this->__('Netsuite Export Status'));
		$this->_initAction()->_setActiveMenu('adminhtml/netsuite_status/exportstatus')->renderLayout();
	}

    public function importstatusAction() {
        $this->_title($this->__('Netsuite Import Status'))->_title($this->__('Netsuite Import Status'))->_title($this->__('Netsuite Import Status'));
        $this->_initAction()->_setActiveMenu('adminhtml/netsuite_status/importstatus')->renderLayout();
    }

    public function deletestatusAction() {
        $this->_title($this->__('Netsuite Delete Status'))->_title($this->__('Netsuite Delete Status'))->_title($this->__('Netsuite Delete Status'));
        $this->_initAction()->_setActiveMenu('adminhtml/netsuite_status/deletestatus')->renderLayout();
    }

    public function massDeleteExportAction() {
        $this->_doMassDelete();
        $this->getResponse()->setRedirect($this->getUrl('*/netsuite_status/exportstatus'));
    }

    public function massDeleteImportAction() {
        $this->_doMassDelete();
        $this->getResponse()->setRedirect($this->getUrl('*/netsuite_status/importstatus'));
    }

    public function massDeleteDeleteAction() {
        $this->_doMassDelete();
        $this->getResponse()->setRedirect($this->getUrl('*/netsuite_status/deletestatus'));
    }

    protected function _doMassDelete() {
        $isError = false;
        $ids = $this->getRequest()->getParam('massaction');
        if(count($ids)) {
            foreach($ids as $id) {
                try {
                    $message = Mage::getModel('rocketweb_netsuite/queue_message')->load($id);
                    $message->delete();
                }
                catch(Exception $ex) {
                    $isError = true;
                    $this->_getSession()->addError($ex->getMesage());
                }
            }
        }
        if(!$isError) {
            $this->_getSession()->addSuccess("Messages deleted");
        }
    }
	
	public function deleteAction() {
		$messageId = (int) $this->getRequest()->getParam('message_id');
		
		$message = Mage::getModel('rocketweb_netsuite/queue_message')->load($messageId);
        $queueType = $message->getQueueType();

		if($message && $message->getMessageId()) {
			try {
				$message->delete();
				$this->_getSession()->addSuccess("Message deleted");
			}
			catch (Exception $ex) {
				$this->_getSession()->addError($ex->getMesage());
			}
		}
		else {
			$this->_getSession()->addError("Message does not exist");
		}
		if($queueType == RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE) {
            $this->getResponse()->setRedirect($this->getUrl('*/netsuite_status/exportstatus'));
        }
        else if($queueType == RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE) {
            $this->getResponse()->setRedirect($this->getUrl('*/netsuite_status/importstatus'));
        }
        else {
            $this->getResponse()->setRedirect($this->getUrl('*/netsuite_status/deletestatus'));
        }
		return;
	}
} 