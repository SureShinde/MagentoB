<?php

class Moxy_SocialCommerce_Adminhtml_CollectioncoverController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
    	{
        return true;
    	}
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("socialcommerce/collectioncover")->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncover  Manager"),Mage::helper("adminhtml")->__("Collectioncover Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("SocialCommerce"));
			    $this->_title($this->__("Manager Collectioncover"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("SocialCommerce"));
				$this->_title($this->__("Collectioncover"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("socialcommerce/collectioncover")->load($id);
				if ($model->getId()) {
					Mage::register("collectioncover_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("socialcommerce/collectioncover");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncover Manager"), Mage::helper("adminhtml")->__("Collectioncover Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncover Description"), Mage::helper("adminhtml")->__("Collectioncover Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncover_edit"))->_addLeft($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncover_edit_tabs"));
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
		$this->_title($this->__("Collectioncover"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("socialcommerce/collectioncover")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("collectioncover_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("socialcommerce/collectioncover");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncover Manager"), Mage::helper("adminhtml")->__("Collectioncover Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Collectioncover Description"), Mage::helper("adminhtml")->__("Collectioncover Description"));


		$this->_addContent($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncover_edit"))->_addLeft($this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncover_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						
				 //save image
		try{

if((bool)$post_data['image']['delete']==1) {

	        $post_data['image']='';

}
else {

	unset($post_data['image']);

	if (isset($_FILES)){

		if ($_FILES['image']['name']) {

			if($this->getRequest()->getParam("id")){
				$model = Mage::getModel("socialcommerce/collectioncover")->load($this->getRequest()->getParam("id"));
				if($model->getData('image')){
						$io = new Varien_Io_File();
						$io->rm(Mage::getBaseDir('media').DS.implode(DS,explode('/',$model->getData('image'))));	
				}
			}
						$path = Mage::getBaseDir('media') . DS . 'socialcommerce' . DS .'collectioncover'.DS;
						$uploader = new Varien_File_Uploader('image');
						$uploader->setAllowedExtensions(array('jpg','png','gif'));
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						$destFile = $path.$_FILES['image']['name'];
						$filename = $uploader->getNewFileName($destFile);
						$uploader->save($path, $filename);

						$post_data['image']='socialcommerce/collectioncover/'.$filename;
		}
    }
}

        } catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
        }
//save image


						$model = Mage::getModel("socialcommerce/collectioncover")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Collectioncover was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setCollectioncoverData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));//$model->getCoverId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setCollectioncoverData($this->getRequest()->getPost());
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
						$model = Mage::getModel("socialcommerce/collectioncover");
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
				$ids = $this->getRequest()->getPost('cover_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("socialcommerce/collectioncover");
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
			$fileName   = 'collectioncover.csv';
			$grid       = $this->getLayout()->createBlock('socialcommerce/adminhtml_collectioncover_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'collectioncover.xml';
			$grid       = $this->getLayout()->createBlock('socialcommerce/adminhtml_collectioncover_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
