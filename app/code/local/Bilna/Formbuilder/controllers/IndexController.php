<?php
class Bilna_Formbuilder_IndexController extends Mage_Core_Controller_Front_Action 
{
	public function indexAction()
	{	echo 5; die;
		$this->loadLayout();     
		$this->renderLayout();										
	}

	public function submitAction() 
	{		
		$title = $this->getRequest()->getPost('name');		
		$static_info = $this->getRequest()->getPost('static_info');
		$static_thank = $this->getRequest()->getPost('static_thank');
		$url = $this->getRequest()->getPost('url');
		$active_from = $this->getRequest()->getPost('active_from');
		$active_to = $this->getRequest()->getPost('active_to');
		$status = $this->getRequest()->getPost('status');
		
			$insertForm = $this->_insertForm();

		$form_id = $this->getRequest()->getPost('form_id');
		$name = $this->getRequest()->getPost('name');
		$group = $this->getRequest()->getPost('group');
		$title = $this->getRequest()->getPost('title');
		$type = $this->getRequest()->getPost('type');
		$required = $this->getRequest()->getPost('required');
		$unique = $this->getRequest()->getPost('unique');
		$order = $this->getRequest()->getPost('order');

			$insertInput = $this->_insertInput();

		$form_id = $this->getRequest()->getPost('form_id');
		$record_id = $this->getRequest()->getPost('record_id');
		$type = $this->getRequest()->getPost('type');
		$value = $this->getRequest()->getPost('value');
		$created_at = $this->getRequest()->getPost('created_at');

			$insertData = $this->_insertData();

		if ($insertData) {

			$urlform = $this->_backurl($form_id);
			$redirectPage = Mage::getBaseUrl().$urlform;
			$this->_prepareEmail($name, $type, $value, $templateId);

			$message = $this->getRequest()->getPost('static_thank');

			Mage::getSingleton('core/session')->addSuccess($message);

			$this->_redirectPage($redirectPage);

			}
			else 
			{ 
			echo "failed";
			}
	}

	private function _prepareEmail($name, $type, $value) 
	{
		$emailVars = array (
		'name_from' => $name,
		'email' => $type,
		'phone' => $value,
		'name_to' => 'CS Bilna',
		'email_to' => 'cs@bilna.com'
		);

		$this->_sendEmail($name, $type, $emailVars);
	}

	// Redirect Page Function
	private function _redirectPage($url) {
	header("location:".$url);
	exit;
	}
	// End Redirect Page Function

	private function _sendEmail($name, $email, $emailVars, $templateId) 
	{
		$emailSender = array (
				'name' => $name,
				'email' => $email
				);
		$storeId = Mage::app()->getStore()->getId();
		$translate = Mage::getSingleton('core/translate');
		$sendEmail = Mage::getModel('core/email_template')
		->sendTransactional($templateId, $emailSender, $emailVars['email'], $emailVars['name'], $emailVars, $storeId);
		$translate->setTranslateInline(true);

		if ($sendEmail) 
		{
			return true;
		}

		return false;
	}

	private function _insertForm() 
	{
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$dataArr = array (
			$this->getRequest()->getPost('name'),
			$this->getRequest()->getPost('static_info'),
			$this->getRequest()->getPost('static_thank'),
			$this->getRequest()->getPost('url'),
			$this->getRequest()->getPost('active_from'),
			$this->getRequest()->getPost('active_to'),
			$this->getRequest()->getPost('status')
			);

		$sql = "insert into bilna_formbuilder_form (title, static_info, static_thank, url, active_from, active_to, status) values (?,?,?,?,?,?,?)";
		$query = $write->query($sql, $dataArr);

		if ($query)
		return true;
		else
		return false;
	}

	private function _insertInput() 
	{
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$dataArr = array (
			$this->getRequest()->getPost('form_id'),
			$this->getRequest()->getPost('name'),
			$this->getRequest()->getPost('group'),
			$this->getRequest()->getPost('title'),
			$this->getRequest()->getPost('type'),
			$this->getRequest()->getPost('required'),
			$this->getRequest()->getPost('unique'),
			$this->getRequest()->getPost('order')
			);

		$sql = "insert into bilna_formbuilder_input (form_id, name, group, title, type, required, unique, order) values (?,?,?,?,?,?,?,?)";
		$query = $write->query($sql, $dataArr);

		if ($query)
		return true;
		else
		return false;
	}

	private function _insertData() 
	{
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$created_at = date("Y-m-d H:i:s");
		$dataArr = array (
			$this->getRequest()->getPost('form_id'),
			$this->getRequest()->getPost('record_id'),
			$this->getRequest()->getPost('type'),
			$this->getRequest()->getPost('value'),
			$created_at
			);

		$sql = "insert into bilna_formbuilder_data (form_id, record_id, type, value, created_at) values (?,?,?,?,?)";
		$query = $write->query($sql, $dataArr);

		if ($query)
		return true;
		else
		return false;
	}

	private function _backurl($form_id) 
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select url from bilna_formbuilder_form where id=".$form_id." group by url";
		$row       = $connection->fetchRow($sql);
		$result 	= $row['url'];
		return $result;
	}
}
