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
				$post_data['storeview'] = implode(",", $post_data['storeview']);	
				$post_data['area_createddate'] = date("Y-m-d H:i:s");
				try {

					$model = Mage::getModel("staticarea/manage")
					->addData($post_data)
					->setId($this->getRequest()->getParam("id"))
					->save();

					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Static area was successfully saved"));
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

	protected function deleteAction() {
        $_area = Mage::getModel('staticarea/manage')->load($this->getRequest()->getParam('id'));
        if($_area->getData()) {
            foreach($_area->getContentCollection() as $content) {
                $content->delete();
            }
            $_area->delete();
            $this->_getSession()->addSuccess($this->__('Static Area has been successfully deleted'));
        }
        return $this->_redirect('*/*/index');
    }

	protected function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents')->toHtml());
    }

	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		/*$fileName   = 'report.csv';
		$grid       = $this->getLayout()->createBlock('couponsreport/adminhtml_report_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());*/
	} 
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		// $fileName   = 'report.xml';
		// $grid       = $this->getLayout()->createBlock('couponsreport/adminhtml_report_grid');
		// $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	protected function ajaxformAction() {
        if($this->getRequest()->getParam('id')) {
            //loading image
            $_content = Mage::getModel('staticarea/contents')->load($this->getRequest()->getParam('id'));
            if($_content->getData()) {
                Mage::helper('staticarea')->setFormDataContent($_content);
            } else {
                $this->_getSession()->addError('Couldn\'t load image');
            }
        }
        
        $_block = $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents_container');
        $_block->setData('content_id', $this->getRequest()->getParam('id'));
        $_block->setData('content_pid', $this->getRequest()->getParam('pid'));
        $this->getResponse()->setBody($_block->toHtml());
    }

    protected function savecontentAction()
    {
    	$_result = array();
        $_request = $this->getRequest();

        $_data = array();
        $_errors = array();

        $_data['id'] = $_request->getParam('id');
        $_data['staticarea_id'] = $_request->getParam('staticarea_id');
        $_data['status'] = $_request->getParam('status');
        $_data['active_from'] = $_request->getParam('active_from');
        $_data['active_to'] = $_request->getParam('active_to');
        $_data['order'] = $_request->getParam('sort_order');
        $_data['content'] = $_request->getParam('content');
        $_data['url'] = $_request->getParam('url');
        $_data['url_action'] = $_request->getParam('url_action');

        $active_from = Mage::app()->getLocale()->date($_data['active_from'],null,null,false);
        $active_to = Mage::app()->getLocale()->date($_data['active_to'],null,null,false);

        if($_data['active_from'] != NULL ) {
            $_data['active_from'] = $active_from->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }
        if($_data['active_to'] != NULL) {
            $_data['active_to'] = $active_to->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }

        // Additional checks
        $_datesCheck = true;
        if($_data['active_from'] && $active_from->toValue() < 1) {
            $_errors[] = $this->__('Wrong value for \'Date From\' field');
            $_datesCheck = false;
        }
        if($_data['active_to'] && $active_to->toValue() < 1) {
            $_errors[] = $this->__('Wrong value for \'Date To\' field');
            $_datesCheck = false;
        }
        if($_data['active_from'] && $_data['active_to'] && $_datesCheck && $active_to->toValue() < $active_from->toValue())
            $_errors[] = $this->__('Value of \'Date To\' should be equal or greater than value of \'Date From\' field');
        if(filter_var($_data['order'], FILTER_VALIDATE_INT) === false)
            $_errors[] = $this->__('Sort order should be integer');

        $_content = Mage::getModel('staticarea/contents')->load($_request->getParam('id'));

        if(!$_errors)
        {
        	$_content->setData($_data);
            $_content->save();
        }else {
            $_messagesBlock = Mage::getSingleton('core/layout')->getMessagesBlock();
            foreach($_errors as $error) {
                $_messagesBlock->addMessage(Mage::getModel('core/message')->error($error));
            }
            $_errors = $_messagesBlock->getGroupedHtml();
        }

        $_result = array(
            's' => $_errors ? false : true,
            'errors' => $_errors
        );

        $_responseBlock = $this->getLayout()->createBlock('adminhtml/template')
            ->setTemplate('bilna_staticarea/contents/ajaxresponse.phtml');
        $_responseBlock->setData('resp_object', Zend_Json::encode($_result));
        
        $response = Mage::app()->getResponse();
        $response->setBody($_responseBlock->toHtml());

    }

}