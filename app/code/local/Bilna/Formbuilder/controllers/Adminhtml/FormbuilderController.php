<?php

class Bilna_Formbuilder_Adminhtml_FormbuilderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Bilna'))->_title($this->__('Bilna Form Builder Manager'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna/bilna');
        $this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder'));
        $this->renderLayout();

    }

	public function editAction()
    {
				$record_id = $this->getRequest()->getParam('record_id');
				$form_id = $this->getRequest()->getParam('form_id');
				$recform = array('record_id' => $record_id, 'form_id' => $form_id);

				$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
				$collection->getSelect()->where('record_id = '.$record_id.' and form_id = '.$form_id);

				Mage::register('formbuilder_form', $recform);

				if ($collection->count()>0) {
						
						$this->loadLayout();
						$this->_setActiveMenu('bilna/bilna');
						$this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit'))
								 ->_addLeft($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs'));
						$this->renderLayout();
				}
				else {
							$this->_redirect('*/*/index');
              return;
				}

/*Mage::register('collpur_deal', $deal);
 if ($deal->getId()) {

                if(!Mage::getModel('catalog/product')->load($deal->getProductId())->getId()) {
                      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('collpur')->__('Error: associated product has been deleted'));
                      return $this->_redirect('*//*/');
                }
            $breadcrumbTitle = $breadcrumbLabel = Mage::helper('collpur')->__('Edit Deal');
            $this->displayTitle('Edit Deal');
        } else {
            $breadcrumbTitle = $breadcrumbLabel = Mage::helper('collpur')->__('New Deal');
            $this->displayTitle('New Deal');
        }*/

    }

	public function newAction()
	{
		//forward new action to a blank edit form
		$this->_forward('edit');
	}	

	public function messageAction()
    {
        $data = Mage::getModel('bilna_formbuilder/formbuilder')->load($this->getRequest()->getParam('form_id'));
        echo $data->getContent();
    }

	/**
     * Initialize action
     * Here, we set the breadcrumbs and the active menu
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
