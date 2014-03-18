<?php
class Bilna_Formbuilder_IndexController extends Mage_Core_Controller_Front_Action 
{
	public function indexAction()
	{	
	$this->loadLayout();     
	$this->renderLayout();										
	}

	public function submitAction() 
	{
 		$postData = $this->getRequest()->getPost();
		$form_id = $postData['form_id'];
		$create_date = now("Y-m-d H:i:s");

		//$record_id = $this->getRequest()->getPost('record_id');
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select max(record_id) from bilna_formbuilder_data where form_id = $form_id";
		$row = $connection->fetchRow($sql);

		if(is_null($row['record_id'])){
			$record_id = 1;
		}else{
			$record_id = $row['record_id']+1;
		}

		foreach($postData["inputs"] as $type=>$value){				
			$insertData = $this->_insertData($form_id,$record_id,$create_date,$type,$value);			
			//echo $form_id."-".$record_id."-".$create_date."-".$type."-".$value;
		}

		//$insertData = $this->_insertData();

		//if ($insertData) {

		//	$urlform = $this->_backurl($form_id);
		//	$redirectPage = Mage::getBaseUrl().$urlform;
		//	$this->_prepareEmail($name, $type, $value, $templateId);

		//	$message = $this->getRequest()->getPost('static_thank');

		//	Mage::getSingleton('core/session')->addSuccess($message);

		//	$this->_redirectPage($redirectPage);

		//	}
		//	else 
		//	{ 
		//	echo "failed";
		//	}
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

	private function _insertData($form_id,$record_id,$type,$value,$create_date) 
	{
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		//$created_at = date("Y-m-d H:i:s");
		$dataArr = array (
			$form_id,
			$record_id,
			$type,
			$value
			//$created_at
			);

		$sql = "insert into bilna_formbuilder_data (form_id, record_id, type, value, create_date) values ('$form_id','$record_id','$type','$value','$create_date')";

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
