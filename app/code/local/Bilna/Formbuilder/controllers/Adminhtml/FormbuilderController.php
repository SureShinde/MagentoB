<?php

class Bilna_Formbuilder_Adminhtml_FormbuilderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Bilna'))->_title($this->__('Bilna Form Builder Manager'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna/bilna');
        $this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder'));
        //$this->renderLayout();
		$this->_initAction()
			 ->renderLayout();
    }

	public function newAction()
	{
		//forward new action to a blank edit form
		$this->_forward('edit');
	}

	public function editAction()
	{
		$this->_initAction()

		//get id if available
		$form_id = $this->getRequest()->getParam('form_id');
		$model = Mage::getModel('bilna_formbuilder/formbuilder');

		if ($form_id) {
			//load record
			$model->load($form_id);

			//check if record is loaded
			if (!$model->getForm_Id) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('This formbuilder no longer exists.'));
				$this->_redirect('*/*/');

				return;
			}
		}

		$this->_title($model->getForm_Id() ? $model->getName() : $this->__('New Formbuilder'));

		$data = Mage::getSingleton('adminhtml/session')->getFormbuilderData(true);
		if (!empty($data)) {
			$model->setData($data);		
		}

		Mage::register('bilna_formbuilder', $model);

		$this->_initAction()
			->_addBreadcrumb($form_id ? $this->__('Edit Formbuilder') : $this->__('New Formbuilder'), $form_id ? $this->__('Edit Formbuilder') : $this->__('New Formbuilder'))
			->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit')->setData('action', $this->getUrl('*/*/save')))
			->renderLayout();
	}

	public function saveAction()
	{
		if ($postData = $this->getrequest()->getPost()) {
		$model = Mage::getSingleton('bilna_formbuilder/formbuilder');
		$model->setData($postData);

		try {
				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The formbuilder has been saved'));
				$this->_redirect('*/*/');

				return;
			}
			catch (Mage_Core_Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured while saving this formbuilder'));
			}

			Mage::getSingleton('adminhtml/session')->setFormbuilderData($postData);
			$this->_redirectReferer();
		}
	}

	public function messageAction()
    {
        $data = Mage::getModel('bilna_formbuilder/formbuilder')->load($this->getRequest()->getParam('form_id'));
        echo $data->getContent();
    }

	/**
     * Initialize action
     *
     * Here, we set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('bilna/bilna_formbuilder_formbuilder')
            ->_title($this->__('Bilna'))->_title($this->__('Formbuilder'))
            ->_addBreadcrumb($this->__('Bilna'), $this->__('Bilna'))
            ->_addBreadcrumb($this->__('Formbuilder'), $this->__('Formbuilder'));
         
        return $this;
    }

	/**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('bilna/bilna_formbuilder_formbuilder');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_grid')->toHtml()
        );
    }
	/** 
     * Export order grid to CSV format 
     */ 
    public function exportCsvAction() 
    { 
        $fileName   = 'bilna_formbuilder'. date('dmYHis') .'.csv';
        $grid       = $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid');
		//$grid       = $this->getLayout()->createBlock('adminhtml/bilna_formbuilder_grid');
        //$this->_prepareDownloadResponse($fileName, $grid->getCsv());
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile()); 
    }

}
