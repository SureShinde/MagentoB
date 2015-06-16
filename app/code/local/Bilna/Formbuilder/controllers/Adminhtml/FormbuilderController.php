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
		$record_id	= $this->getRequest()->getParam('record_id');
		$form_id 		= $this->getRequest()->getParam('form_id');
		$id 				= $this->getRequest()->getParam('id');
		$recform 		= array('record_id' => $record_id, 'form_id' => $form_id);

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

	public function editInputAction()
	{
		$record_id	= $this->getRequest()->getParam('record_id');
		$form_id 		= $this->getRequest()->getParam('form_id');
		$id 				= $this->getRequest()->getParam('id');
		$recform 		= array('record_id' => $record_id, 'form_id' => $form_id);

		$collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
		//$collection->getSelect()->where('record_id = '.$record_id.' and form_id = '.$form_id);
		$collection->getSelect()->where('id = '.$id);
		//$collection->printLogQuery(true); //die;
		Mage::register('formbuilder_form', $recform);

		if ($collection->count()>0) {
			
			$this->loadLayout();
			$this->_setActiveMenu('bilna/bilna');
			$this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_edit'))
			//$this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_edit_detail'))
					 ->_addLeft($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_edit_tabs'));
			$this->renderLayout();
		}
		else {
			$this->_redirect('*/*/index');
      return;
		}
	}

	public function editDataAction()
	{
		$record_id	= $this->getRequest()->getParam('record_id');
		$form_id 		= $this->getRequest()->getParam('form_id');
		$id 				= $this->getRequest()->getParam('id');
		$recform 		= array('record_id' => $record_id, 'form_id' => $form_id);

		$collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
		//$collection->getSelect()->joinInner(array('bfd' => 'bilna_formbuilder_data'), 'main_table.form_id = bfd.form_id','');
		//$collection->getSelect()->where('bfd.record_id = '.$record_id.' and main_table.form_id = '.$form_id);
		$collection->getSelect()->where('form_id = '.$form_id);
		//$collection->printLogQuery(true); //die;
		Mage::register('formbuilder_form', $recform);

		if ($collection->count()>0) {
			
			$this->loadLayout();
			$this->_setActiveMenu('bilna/bilna');
			$this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_edits'))
					 ->_addLeft($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_edit_tab'));
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

  public function gridInputsAction()
  {
    $this->loadLayout();
    $this->getResponse()->setBody(
    $this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_inputs')->toHtml()
    );
  }

  public function gridDataAction()
  {
    $this->loadLayout();
    $this->getResponse()->setBody(
    $this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_edit_tabs_data')->toHtml()
    );
  }

  //Export order grid to CSV format 
  /*public function exportCsvAction() 
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
  }*/

  public function exportCsvAction()
  {
		//$fileName = 'bilna_formbuilder.csv';
		$fileName		= 'bilna_formbuilder'.'_'.date('dmYHis').'.csv';
		//$grid 		= $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid');
		$grid 			= $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Data');																								 
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
  }

	public function ajaxTabGeneralAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
		$this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_General')->toHtml()
		);
	}

	public function ajaxTabInputsAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
		$this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Inputs')->toHtml()
		);
	}

	public function ajaxTabDataAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
		$this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Data')->toHtml()
		);
	}

	public function ajaxTabDetailAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
		$this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Detail')->toHtml()
		);
	}

	public function ajaxTabDetailsAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
		$this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Details')->toHtml()
		);
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('bilna_formbuilder/form');
			$model->setId($this->getRequest()->getParam('id'));

			try {
				//title, url, active_from, active_to, status
				$activeform	= Mage::getModel('core/date')->date('Y-m-d H:i:s', $data['active_from']);
				$activeto	= Mage::getModel('core/date')->date('Y-m-d H:i:s', $data['active_to']);

				$model->setTitle($data['title']);
				$model->setUrl($data['url']);
				$model->setActiveFrom($activeform);
				$model->setActiveTo($activeto);
				$model->setStatus($data['status']);
				$model->save(); 

				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e) {
				//die (print_r ($e));
			}
	}
				$this->_redirect('*/*/');
				return;
	}

}
