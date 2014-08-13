<?php
class Bilna_Formbuilder_IndexController extends Mage_Core_Controller_Front_Action 
{
	public function submitAction() 
	{
 		$postData       = $this->getRequest()->getPost();
		$form_id        = $postData['form_id'];
		//$create_date  = datetime('Y-m-d H:i:s');
		$create_date    = Mage::getModel('core/date')->date();
		$data           = array();
		//$record_id    = $this->getRequest()->getPost('record_id');
		$connection     = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql            = "select max(record_id) as record_id from bilna_formbuilder_data where form_id = $form_id";
		$row            = $connection->fetchRow($sql);
		$session        = Mage::getSingleton('core/session');
		Mage::getSingleton('core/session')->setFormbuilderSubmited(true);

		if(is_null($row['record_id'])){
			$record_id = 1;
		}else{
			$record_id = $row['record_id']+1;
		}

		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select * from bilna_formbuilder_form where id = $form_id";
		$row        = $connection->fetchRow($sql);

// 		//CHECK INPUTS SETTING
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
					if(!is_null($row["static_failed"]) || $row["static_failed"]!==""){
						Mage::getSingleton('core/session')->setFormbuilderFailed(true);
					}
					Mage::getSingleton('core/session')->addError($message);
					$redirectPage = Mage::getBaseUrl().$field["url"];
					$this->_redirectPage($redirectPage);
				}

				if($field["type"]=="checkbox" && $postData["inputs"][$field["group"]] <> "on"){
					if(!is_null($row["static_failed"]) || $row["static_failed"]!==""){
						Mage::getSingleton('core/session')->setFormbuilderFailed(true);
					}
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
				$jumlah=$collection->getSize();
				if($jumlah!=0){
					if(!is_null($row["static_failed"]) && $row["static_failed"]!==""){
						Mage::getSingleton('core/session')->setFormbuilderFailed(true);
					}
					elseif(is_null($row["static_failed"]) || $row["static_failed"]==""){
						Mage::getSingleton('core/session')->addError($field["title"].' already exists in our database');
					}
					
					$redirectPage = Mage::getBaseUrl().$field["url"];
					$this->_redirectPage($redirectPage);
				}
			}
		}

		foreach($postData["inputs"] as $type=>$value){				
			$insertData = $this->_insertData($form_id,$record_id,$type,$value,$create_date);
		}
		
		$freeProducts = json_decode($row["freeproducts"]);
		foreach($freeProducts->sku as $sku){
			$productModel = Mage::getModel('catalog/product');
			$productId = $productModel->getIdBySku($sku);
			
			if(!is_null($productId)){
				$productOnCart	= false;
				
				//Check if product already exist on the cart
		        $quote = Mage::getSingleton('checkout/session')->getQuote();
		        foreach ($quote->getAllItems() as $item) {
		        	if($item->getSku() == $sku){
		        		$productOnCart	= true;
		        	}
		        }
		        
	        	if($productOnCart!==true){
	        		$params = array(
	        				'product' => $productId,
	        				'qty' => 1,
	        		);
	        		$cart = Mage::getSingleton('checkout/cart');
	        		$product = new Mage_Catalog_Model_Product();
	        		$product->load($productId);
	        		$cart->addProduct($product, $params);
	        		$cart->save();
	        		Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	        	}
			}
		}

		if($row["sent_email"] == 1 && isset($postData["inputs"]["email"])){

			$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
			$collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array(
				'record_id'=>'record_id',
				'type'     =>'type',
				'value'    =>'value'
			));
			$collection->addFieldToFilter('main_table.form_id', (int) $form_id);
			$collection->addFieldToFilter('main_table.record_id', (int) $record_id);

			$data = array ();

			foreach ($collection as $collect) {
				$data[$collect->getType()] = $collect->getValue();
			}

			//Zend_Debug::Dump($collection->printLogQuery(true)); die;
			$this->_prepareEmail($data, $row['email_id']);
		}

		if(!is_null($row["static_success"]) && $row["static_success"]!==""){
			Mage::getSingleton('core/session')->setFormbuilderSuccess(true);
		}
		elseif(is_null($row["static_success"]) || $row["static_success"]==""){
			Mage::getSingleton('core/session')->addSuccess($row["success_message"]);
		}
		$redirectPage = Mage::getBaseUrl().$field["url"];
		
		$this->_redirectPage($redirectPage);
	}

	private function _prepareEmail($data, $templateId)
	{
		$this->_sendEmail($data, $templateId);
	}

	private function _redirectPage($url) {
		header("location:".$url);
		exit;
	}

	private function _sendEmail($data, $templateId) 
	{
		$sender = array('name'  => Mage::getStoreConfig('trans_email/ident_support/name'),
						'email' => Mage::getStoreConfig('trans_email/ident_support/email'));

		$translate = Mage::getSingleton('core/translate');
		$sendEmail = Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $data['email'], $data['name'], $data);
		$translate->setTranslateInline(true);

		if ($sendEmail) return true;
		return false;
	}

	private function _insertData($form_id,$record_id,$type,$value,$create_date) 
	{
		$write   = Mage::getSingleton('core/resource')->getConnection('core_write');
		$dataArr = array (
			$form_id,
			$record_id,
			$type,
			$value
			);

		$sql   = "insert into bilna_formbuilder_data (form_id, record_id, type, value, create_date) values ('$form_id','$record_id','$type','$value','$create_date')";
		$query = $write->query($sql, $dataArr);

		if ($query) return true;
		return false;
	}

	private function _backurl($form_id) 
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select url from bilna_formbuilder_form where id=".$form_id." group by url";
		$row        = $connection->fetchRow($sql);
		$result     = $row['url'];
		
		return $result;
	}
}