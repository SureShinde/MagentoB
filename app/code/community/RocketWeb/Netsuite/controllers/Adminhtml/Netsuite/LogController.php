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
class RocketWeb_Netsuite_Adminhtml_Netsuite_LogController extends Mage_Adminhtml_Controller_Action {
	public function _initAction()
	{
		$this->loadLayout()->_addBreadcrumb(Mage::helper('adminhtml')->__('API call log'), Mage::helper('adminhtml')->__('API call log'));
		return $this;
	}
	
	public function apiAction() {
		$this->loadLayout();
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('API call log'), Mage::helper('adminhtml')->__('API call log'));
		$this->renderLayout();
	}

    public function massDeleteApiAction() {
        $ids = $this->getRequest()->getParam('massaction');
        $dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('netsuite_api_log');
        foreach($ids as $key=>$id) $ids[$key]= (int) $id;
        $query = "DELETE FROM {$tableName} WHERE id in (".implode(',',$ids).")";
        $dbConnection->query($query);

        $this->_getSession()->addSuccess("Items deleted");
        $this->getResponse()->setRedirect($this->getUrl('*/*/api'));
    }
	
	public function generalAction () {
		Mage::register('general_log_num_lines',$this->_getNumLines());
		$this->loadLayout();
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('General log'), Mage::helper('adminhtml')->__('General log'));
		$this->renderLayout();
	}
	
	public function changelinesAction() {
		$numLines = (int) $this->getRequest()->getParam('num_lines');
		$this->_getSession()->setGeneraLogNumLines($numLines);
		$this->_redirect('*/*/general');
	}
	
	protected function _getNumLines() {
		if($this->getRequest()->getParam('num_lines')) {
			$this->_getSession()->setGeneraLogNumLines((int) $this->getRequest()->getParam('num_lines'));
			return (int) $this->getRequest()->getParam('num_lines');
		}
		else {
			if($this->_getSession()->getGeneraLogNumLines()) {
				return $this->_getSession()->getGeneraLogNumLines();
			}
			else {
				return 300;
			}
		}
	}
	
}