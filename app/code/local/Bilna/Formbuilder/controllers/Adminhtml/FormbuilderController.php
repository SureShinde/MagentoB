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
			$id = $this->getRequest()->getParam('id');
			$recform = array('record_id' => $record_id, 'form_id' => $form_id);

			$collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
			//$collection->getSelect()->where('record_id = '.$record_id.' and form_id = '.$form_id);
			$collection->getSelect()->where('id = '.$id);

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

    //Initialize action
    //set the breadcrumbs and the active menu
    //@return Mage_Adminhtml_Controller_Action
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

    //Check currently called action by permissions for current user
    //@return bool
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

    //Export order grid to CSV format 
    public function exportCsvAction() 
    { 
			$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();

$collection->getSelect()
			->join(array('bff' => 'bilna_formbuilder_form'), 'main_table.form_id = bff.id',array('main_table.record_id', 'main_table.form_id', 'bff.title','main_table.create_date'));


			foreach ($collection->getData() as $data) {
			//fputcsv($f, $data, ";"); 
			//$collection->addFieldToSelect($data['title']);
			$collection->getSelect()
			->joinLeft(array('bfd_'.$data['id'] => 'bilna_formbuilder_data'), "main_table.record_id = ".'bfd_'.$data['id'].".record_id AND ".'bfd_'.$data['id'].".type = '".$data['type']."'",array($data['type'] => ''.'bfd_'.$data["id"].'.value'));
			}

			$collection->getSelect()->group('main_table.record_id');
			$collection->getSelect()->group('main_table.form_id');

      //$collection->printLogQuery(true);//die;
      $fileName   = 'bilna_formbuilder'. date('dmYHis') .'.csv';

      //$content = array();
			//$content["test"] = "qwerty";
			//$content["test2"] = "qwerty2";
			//$content["test3"] = "qwerty3";

      //$grid       = $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid');
			//$grid       = $this->getLayout()->createBlock('adminhtml/bilna_formbuilder_grid');
      //$this->_prepareDownloadResponse($fileName, $grid->getCsv());

    //fseek($f, 0);
    //header('Content-Type: application/csv');
    //header('Content-Disposition: attachement; filename="'.$filename.'";');
    //fpassthru($f);			
		$this->_prepareDownloadResponse($fileName, $collection); 
    }		

}
