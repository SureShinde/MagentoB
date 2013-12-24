<?php

class Bilna_Newpage_Adminhtml_PagenewController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("newpage/pagenew")->_addBreadcrumb(Mage::helper("adminhtml")->__("Pagenew  Manager"),Mage::helper("adminhtml")->__("Pagenew Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Newpage"));
			    $this->_title($this->__("Manager Pagenew"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Newpage"));
				$this->_title($this->__("Pagenew"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("newpage/pagenew")->load($id);
				if ($model->getId()) {
					Mage::register("pagenew_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("newpage/pagenew");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pagenew Manager"), Mage::helper("adminhtml")->__("Pagenew Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pagenew Description"), Mage::helper("adminhtml")->__("Pagenew Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("newpage/adminhtml_pagenew_edit"))->_addLeft($this->getLayout()->createBlock("newpage/adminhtml_pagenew_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("newpage")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Newpage"));
		$this->_title($this->__("Pagenew"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("newpage/pagenew")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("pagenew_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("newpage/pagenew");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pagenew Manager"), Mage::helper("adminhtml")->__("Pagenew Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pagenew Description"), Mage::helper("adminhtml")->__("Pagenew Description"));


		$this->_addContent($this->getLayout()->createBlock("newpage/adminhtml_pagenew_edit"))->_addLeft($this->getLayout()->createBlock("newpage/adminhtml_pagenew_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("newpage/pagenew")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Pagenew was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setPagenewData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setPagenewData($this->getRequest()->getPost());
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
						$model = Mage::getModel("newpage/pagenew");
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

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("newpage/pagenew");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'pagenew.csv';
			$grid       = $this->getLayout()->createBlock('newpage/adminhtml_pagenew_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'pagenew.xml';
			$grid       = $this->getLayout()->createBlock('newpage/adminhtml_pagenew_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
