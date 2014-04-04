<?php
class Bilna_Formbuilder_IndexController extends Mage_Core_Controller_Front_Action 
{
	public function submitAction() 
	{
 		$postData		= $this->getRequest()->getPost();
		$form_id		= $postData['form_id'];
		$create_date	= now("Y-m-d H:i:s");
		$data			= array();

		//$record_id = $this->getRequest()->getPost('record_id');
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select max(record_id) as record_id from bilna_formbuilder_data where form_id = $form_id";
		$row = $connection->fetchRow($sql);

		if(is_null($row['record_id'])){
			$record_id = 1;
		}else{
			$record_id = $row['record_id']+1;
		}

		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select * from bilna_formbuilder_form where id = $form_id";
		$row = $connection->fetchRow($sql);

		//CHECK INPUTS SETTING
		$block = Mage::getModel('bilna_formbuilder/form')->getCollection();
		$block->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
		$block->addFieldToFilter('main_table.id', $form_id);

		foreach($block->getData() as $field){
			if($field["required"]==true){
				if($field["type"]=="checkbox"){
					$message = "You must agree with terms and conditions";
				}else{
					$message = $field["title"].' cannot be empty';
				}
				
				if(!isset($postData["inputs"][$field["group"]]) || empty($postData["inputs"][$field["group"]]) || is_null($postData["inputs"][$field["group"]])){
					Mage::getSingleton('core/session')->addError($message);
					
					$redirectPage = Mage::getBaseUrl().$field["url"];
					$this->_redirectPage($redirectPage);
				}
				if($field["type"]=="checkbox" && $postData["inputs"][$field["group"]] <> "on"){
					Mage::getSingleton('core/session')->addError($message);
						
					$redirectPage = Mage::getBaseUrl().$field["url"];
					$this->_redirectPage($redirectPage);
				}
			}

			if($field["unique"]==true){
				$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
				$collection->getSelect('main_table.form_id');
				$collection->addFieldToFilter('main_table.form_id', $form_id);
				$collection->addFieldToFilter('main_table.type', $field["group"]);
				$collection->addFieldToFilter('main_table.value', $postData["inputs"][$field["group"]]);
		
				$exist = $collection->getFirstItem();

				if(!is_null($exist["form_id"])){
					Mage::getSingleton('core/session')->addError($field["title"].' must be unique');
				
					$redirectPage = Mage::getBaseUrl().$field["url"];
					$this->_redirectPage($redirectPage);
				}
			}
		}

		foreach($postData["inputs"] as $type=>$value){				
			$insertData = $this->_insertData($form_id,$record_id,$type,$value,$create_date);

			if($row["sent_email"] == 1 && isset($postData["inputs"]["email"])){
				$data["email"] = $postData["inputs"]["email"];
				$data["name"] = (isset($postData["inputs"]["name"]))?$postData["inputs"]["name"]:"";
				$data["phone"] = (isset($postData["inputs"]["phone"]))?$postData["inputs"]["phone"]:"";
				$data["comment"] = (isset($postData["inputs"]["comment"]))?$postData["inputs"]["comment"]:"";

				$this->_prepareEmail($data, $row["email_id"]);
			}
		}

		if(!is_null($row["success_message"])){
			Mage::getSingleton('core/session')->addSuccess($row["success_message"]);
		}
		$redirectPage = Mage::getBaseUrl().$field["url"];
		
		$this->_redirectPage($redirectPage);
	}

	private function _prepareEmail($data, $templateId)
	{
		$emailVars = array (
			'name_from' => $data["name"],
			'email' => $data["email"],
			'phone' => $data["phone"],
			'comment' => $data["comment"],
			'name_to' => 'CS Bilna',
			'email_to' => 'cs@bilna.com'
		);

		$this->_sendEmail($data, $emailVars, $templateId);
	}

	// Redirect Page Function
	private function _redirectPage($url) {
		header("location:".$url);
		exit;
	}
	// End Redirect Page Function

	private function _sendEmail($data, $emailVars, $templateId) 
	{
		$emailSender = array (
			'name' => $data["name"],
			'email' => $data["email"]
		);
		//$storeId = Mage::app()->getStore()->getId();
		$translate = Mage::getSingleton('core/translate');
		$sendEmail = Mage::getModel('core/email_template')
		->sendTransactional($templateId, $emailSender, $emailVars['email'], $emailVars['name'], $emailVars);
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
		$connection	= Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql				= "select url from bilna_formbuilder_form where id=".$form_id." group by url";
		$row				= $connection->fetchRow($sql);
		$result			= $row['url'];
		return $result;
	}
}
