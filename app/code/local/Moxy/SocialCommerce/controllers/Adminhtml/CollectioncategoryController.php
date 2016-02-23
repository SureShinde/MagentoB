<?php

class Moxy_SocialCommerce_Adminhtml_CollectioncategoryController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
    	{
        return true;
    	}
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("socialcommerce/collectioncategory")->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncategory  Manager"),Mage::helper("adminhtml")->__("Collectioncategory Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("SocialCommerce"));
			    $this->_title($this->__("Manager Collectioncategory"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("SocialCommerce"));
				$this->_title($this->__("Collectioncategory"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("socialcommerce/collectioncategory")->load($id);
				if ($model->getId()) {
					Mage::register("collectioncategory_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("socialcommerce/collectioncategory");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncategory Manager"), Mage::helper("adminhtml")->__("Collectioncategory Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncategory Description"), Mage::helper("adminhtml")->__("Collectioncategory Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncategory_edit"))->_addLeft($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncategory_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("socialcommerce")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("SocialCommerce"));
		$this->_title($this->__("Collectioncategory"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("socialcommerce/collectioncategory")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("collectioncategory_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("socialcommerce/collectioncategory");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncategory Manager"), Mage::helper("adminhtml")->__("Collectioncategory Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncategory Description"), Mage::helper("adminhtml")->__("Collectioncategory Description"));


		$this->_addContent($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncategory_edit"))->_addLeft($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncategory_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("socialcommerce/collectioncategory")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Collectioncategory was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setCollectioncategoryData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setCollectioncategoryData($this->getRequest()->getPost());
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
						$model = Mage::getModel("socialcommerce/collectioncategory");
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
				$ids = $this->getRequest()->getPost('category_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("socialcommerce/collectioncategory");
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
			$fileName   = 'collectioncategory.csv';
			$grid       = $this->getLayout()->createBlock('socialcommerce/adminhtml_collectioncategory_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'collectioncategory.xml';
			$grid       = $this->getLayout()->createBlock('socialcommerce/adminhtml_collectioncategory_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
