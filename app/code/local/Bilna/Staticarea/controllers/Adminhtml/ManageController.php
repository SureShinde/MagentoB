<?php

class Bilna_Staticarea_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu("staticarea/manage")
			->_addBreadcrumb(Mage::helper("adminhtml")
			->__("Manage Static Area"),Mage::helper("adminhtml")
			->__("Manage Static Area"));

		return $this;
	}
	public function indexAction() 
	{
	    $this->_initAction()
			->renderLayout();
	}
	public function editAction()
	{			    
	    $this->_title($this->__("Edit Static Area"));
		
		$id = $this->getRequest()->getParam("id");
		$model = Mage::getModel("staticarea/manage")->load($id);
		if ($model->getId() || $id == 0) {
			Mage::register("staticarea_data", $model);

			$this->loadLayout();
			$this->_setActiveMenu("staticarea/manage");

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Static Area"), Mage::helper("adminhtml")->__("Add New Static Area"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Description"), Mage::helper("adminhtml")->__("Manage Description"));


			$this->_addContent($this->getLayout()->createBlock("staticarea/adminhtml_manage_edit"))
				 ->_addLeft($this->getLayout()->createBlock("staticarea/adminhtml_manage_edit_tabs"));

			$this->renderLayout();
		} 
		else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("staticarea")->__("Item does not exist."));
			$this->_redirect("*/*/");
		}
	}

	public function newAction()
	{
		//$this->_forward('edit');
		$this->_title($this->__("Manage Static Area"));
		$this->_title($this->__("New Area"));

	    $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("staticarea/manage")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("staticarea_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("staticarea/manage");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Static Area"), Mage::helper("adminhtml")->__("Add New Static Area"));
		//$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Report Description"), Mage::helper("adminhtml")->__("Report Description"));


		$this->_addContent($this->getLayout()->createBlock("staticarea/adminhtml_manage_edit"))
			 ->_addLeft($this->getLayout()->createBlock("staticarea/adminhtml_manage_edit_tabs"));

		$this->renderLayout();

	}
	public function saveAction()
	{

		$post_data=$this->getRequest()->getPost();


			if ($post_data) {

				try {

					$model = Mage::getModel("wrappinggiftevent/manage")
					->addData($post_data)
					->setId($this->getRequest()->getParam("id"))
					->save();

					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Wrap Type was successfully saved"));
					Mage::getSingleton("adminhtml/session")->setReportData(false);

					if ($this->getRequest()->getParam("back")) {
						$this->_redirect("*/*/edit", array("id" => $model->getId()));
						return;
					}
					$this->_redirect("*/*/");
					return;
				} 
				catch (Exception $e) {
					Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
					Mage::getSingleton("adminhtml/session")->setReportData($this->getRequest()->getPost());
					$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
				return;
				}

			}
			$this->_redirect("*/*/");
	}



	public function deleteAction()
	{
			if( $this->getRequest()->getParam("id") > 0 ) {
				try {
					$model = Mage::getModel("wrappinggiftevent/manage");
					$model->setId($this->getRequest()->getParam("id"))->delete();
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
					$this->_redirect("*/*/");
				} 
				catch (Exception $e) {
					Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
					$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
				}
			}
			$this->_redirect("*/*/");
	}

	protected function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents')->toHtml());
    }

	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'report.csv';
		$grid       = $this->getLayout()->createBlock('couponsreport/adminhtml_report_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	} 
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'report.xml';
		$grid       = $this->getLayout()->createBlock('couponsreport/adminhtml_report_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	protected function ajaxformAction() {
        if($this->getRequest()->getParam('id')) {
            //loading image
            $_content = Mage::getModel('staticarea/contents')->load($this->getRequest()->getParam('id'));
            if($_content->getData()) {
                Mage::helper('staticarea')->setFormDataImage($_content);
            } else {
                $this->_getSession()->addError('Couldn\'t load image');
            }
        }
        $_block = $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents_container');
        $_block->setData('content_id', $this->getRequest()->getParam('id'));
        $_block->setData('content_pid', $this->getRequest()->getParam('pid'));
        $this->getResponse()->setBody($_block->toHtml());
    }
}
