<?php
class Bilna_Formbuilder_IndexController extends Mage_Core_Controller_Front_Action {
    protected $read;
    
    protected function _construct() {
        $this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
    }
    
    public function submitAction() {
        $postData       = $this->getRequest()->getPost();
        
        //unused code
        $datedrop       = $postData['inputs']['dob']['date_day'].'-'.$postData['inputs']['dob']['date_month'].'-'.$postData['inputs']['dob']['date_year'];
        
        
        $form_id        = $postData['form_id'];
        //$create_date  = datetime('Y-m-d H:i:s');
        $create_date    = Mage::getModel('core/date')->date();
        $data           = array();
        //$record_id    = $this->getRequest()->getPost('record_id');
        $connection     = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql            = "select max(record_id) as record_id from bilna_formbuilder_data where form_id = $form_id";
        $row            = $connection->fetchRow($sql);
        $session        = Mage::getSingleton('core/session');
        $codeShare = false;

        if(is_null($row['record_id'])){
            $record_id = 1;
        }else{
            $record_id = $row['record_id']+1;
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql        = "select * from bilna_formbuilder_form where id = $form_id";
        $row        = $connection->fetchRow($sql);
        
        $fueRuleId = $row['fue'];
        $fueUrl = $row['url'];
        
        //CHECK INPUTS SETTING
        $block = Mage::getModel('bilna_formbuilder/form')->getCollection();
        $block->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
        $block->addFieldToFilter('main_table.id', $form_id);

        //required, checkbox, terms and empty
        foreach($block->getData() as $field){
            if ($field['type'] == 'codeshare') {
                $codeShare = true;
                $codeShareField = $field['name'];
                continue;
            }
            
            if ($field['type'] == 'dob') {
                $postData['inputs']['dob'] = $datedrop;
            }
            
            if($field["required"]==true){
                if($field["type"]=="checkbox"){
                    $message = "You must agree with terms and conditions";
                }else{
                    $message = $field["title"].' cannot be empty';
                }
                
                if(!isset($postData["inputs"][$field["group"]]) || empty($postData["inputs"][$field["group"]]) || is_null($postData["inputs"][$field["group"]])){
                    if(!is_null($row["static_failed"]) || $row["static_failed"]<>""){
                        Mage::getSingleton('core/session')->setFormbuilderFailed(false);
                    }
                    Mage::getSingleton('core/session')->addError($message);
                    $redirectPage = Mage::getBaseUrl().$field["url"];
                    $this->_redirectPage($redirectPage);
                }

                //type : checkbox
                if($field["type"]=="checkbox" && $postData["inputs"][$field["group"]] <> "on"){
                    if(!is_null($row["static_failed"]) || $row["static_failed"]<>""){
                        Mage::getSingleton('core/session')->setFormbuilderFailed(false);
                    }
                    Mage::getSingleton('core/session')->addError($message); 
                    $redirectPage = Mage::getBaseUrl().$field["url"];
                    $this->_redirectPage($redirectPage);
                }

                //type : dropdown
                if($field["type"]=="dropdown" && $postData["inputs"][$field["group"]] <> "on"){
                    if(!is_null($row["static_failed"]) || $row["static_failed"]<>""){
                        Mage::getSingleton('core/session')->setFormbuilderFailed(false);
                    }
                    Mage::getSingleton('core/session')->addError($message); 
                    $redirectPage = Mage::getBaseUrl().$field["url"];                   
                    $this->_redirectPage($redirectPage);
                }

                //date of birth (dob)
                if($field["id"]=="dob" && $postData["inputs"][$field["group"]] <> "on"){
                    if(!is_null($row["static_failed"]) || $row["static_failed"]<>""){
                        Mage::getSingleton('core/session')->setFormbuilderFailed(false);
                    }
                    Mage::getSingleton('core/session')->addError($message); 
                    $redirectPage = Mage::getBaseUrl().$field["url"];
                    $this->_redirectPage($redirectPage);
                }
            }

            //unique
            if($field["unique"]==true){
                $collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
                $collection->getSelect('main_table.form_id');
                $collection->addFieldToFilter('main_table.form_id', $form_id);
                $collection->addFieldToFilter('main_table.type', $field["group"]);
                $collection->addFieldToFilter('main_table.value', $postData["inputs"][$field["group"]]);
                $jumlah=$collection->getSize();
                if($jumlah <> 0){
                    if(!is_null($row["static_failed"]) && $row["static_failed"]<>""){
                        Mage::getSingleton('core/session')->setFormbuilderFailed(false);
                    }
                    elseif(is_null($row["static_failed"]) || $row["static_failed"]==""){
                        Mage::getSingleton('core/session')->addError($field["title"].' already exists in our database');
                    }
                    
                    $redirectPage = Mage::getBaseUrl().$field["url"];
                    $this->_redirectPage($redirectPage);
                }
            }
        }

        //echo "<pre>";     
        //print_r($postData["inputs"]); die;
        $valueEncrypt = '';

        foreach($postData["inputs"] as $type=>$value){              
            $insertData = $this->_insertData($form_id,$record_id,$type,$value,$create_date);
            
            if ($codeShare && $type == 'email') {
                $type = $codeShareField;
                $helper = Mage::helper('bilna_formbuilder');
                $valueEncrypt = $helper->encrypt($value);
                $this->_insertData($form_id, $record_id, $type, $valueEncrypt, $create_date);
                $codeShare = false;
            }
        }
        
        $freeProducts = json_decode($row["freeproducts"]);
        foreach($freeProducts->sku as $sku){
            $productModel = Mage::getModel('catalog/product');
            $productId = $productModel->getIdBySku($sku);
            
            if(!is_null($productId)){
                $productOnCart  = false;
                
                //Check if product already exist on the cart
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                foreach ($quote->getAllItems() as $item) {
                    if($item->getSku() == $sku){
                        $productOnCart  = true;
                    }
                }
                
                if($productOnCart<>true){
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

        //sent email
        if ($row["sent_email"] == 1 && isset ($postData["inputs"]["email"])) {
            $collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array (
                'record_id' => 'record_id',
                'type' => 'type',
                'value' => 'value'
            ));
            $collection->addFieldToFilter('main_table.form_id', (int) $form_id);
            $collection->addFieldToFilter('main_table.record_id', (int) $record_id);

            $data = array ();

            foreach ($collection as $collect) {
                $data[$collect->getType()] = $collect->getValue();
            }

            if ($fueRuleId) {
                //send email via FUE
                $shareUrl = Mage::getBaseUrl() . $fueUrl . "?ref=" . $valueEncrypt;
                $this->_sendEmailViaFue($fueRuleId, $record_id, $postData, $shareUrl);
            }
            else {
                $this->_prepareEmail($data, $row['email_id']);
            }
        }

        //static block
        if($field["success_redirect"]==1){
            $ref = '';
            
            if ($row['email_share_apps']) {
                $ref = "&ref=" . $valueEncrypt;
            }
            
            $queryString = "formId=".$form_id."&recordId=".$record_id;
            $field["url"] = $field["url_success"] . "?" . $queryString . $ref;

        }else if(!is_null($row["static_success"]) || $row["static_success"]<>""){
            Mage::getSingleton('core/session')->setFormbuilderSuccess(true);
        }
        elseif(is_null($row["static_success"]) || $row["static_success"]==""){
            Mage::getSingleton('core/session')->addSuccess($row["success_message"]);
        }
        $redirectPage = Mage::getBaseUrl().$field["url"];       
        $this->_redirectPage($redirectPage);

    }

    private function _prepareEmail($data, $templateId) {
        $this->_sendEmail($data, $templateId);
    }

    private function _redirectPage($url) {
        header("location:".$url);
        exit;
    }

    private function _sendEmail($data, $templateId) {
        $sender = array(
            'name'  => Mage::getStoreConfig('trans_email/ident_support/name'),
            'email' => Mage::getStoreConfig('trans_email/ident_support/email')
        );

        $translate = Mage::getSingleton('core/translate');
        $sendEmail = Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $data['email'], $data['name'], $data);
        $translate->setTranslateInline(true);

        if ($sendEmail) {
            return true;
        }
        
        return false;
    }
    
    private function _sendEmailViaFue($fueRuleId, $recordId, $data, $shareUrl) {
        $fueRule = Mage::getModel('followupemail/rule')->load($fueRuleId);
        $sequenceNumber = $recordId;
        
        foreach (unserialize($fueRule->getChain()) as $chain) {
            $params = array ();
            $params['share_apps'] = true;
            $params['object_id'] = $recordId;
            $params['store_id'] = Mage::app()->getStore()->getStoreId();
            $params['customer_email'] = $data['inputs']['email'];
            $params['share_url'] = $shareUrl;
            
            $templateId = $chain['TEMPLATE_ID'];
            $timeDelay = $chain['BEFORE'] * $chain['DAYS'];
            $hourDelay = $chain['BEFORE'] * $chain['HOURS'];
            
            AW_Followupemail_Model_Log::log('emailShareApps event processing, rule_id=' . $fueRuleId . ', customerEmail=' . $params['customer_email'] . ', store_id=' . $params['store_id']);
            $fueRule->processShareApps($params, $templateId, $timeDelay, $hourDelay, $sequenceNumber);
        }
    }

    private function _insertData($form_id,$record_id,$type,$value,$create_date) {
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

    private function _backurl($form_id) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql        = "select url from bilna_formbuilder_form where id=".$form_id." group by url";
        $row        = $connection->fetchRow($sql);
        $result     = $row['url'];
        
        return $result;
    }
    
    public function shareAction() {
        $posts = $this->getRequest()->getPost();
        $formId = $posts['form_id'];
        $recordId = $posts['record_id'];
        $ref = $posts['ref'];
        $urlRef = $posts['url_ref'];
        $emails = $posts['email'];
        $subject = $posts['subject'];
        $content = $posts['content'];
        $urlShare = $posts['url_share'];
        
        $formBuilder = $this->getFormBuilderData($formId);
        
        if ($formBuilder) {
            $urlSuccess = $formBuilder['url_success'];
            $urlThankyou = $formBuilder['static_success'];
            $emailId = $formBuilder['email_id'];

            $emailSuccess = array ();
            $emailFailed = array ();

            foreach ($emails as $email) {
                if (!empty ($email)) {
                    $data = array (
                        'email' => $email,
                        'email_ref' => Mage::helper('bilna_formbuilder')->decrypt($ref),
                        'name' => $email,
                        'url_ref' => $urlRef,
                        'subject' => $subject,
                        'content' => $content,
                    );

                    if ($this->_sendEmail($data, $emailId)) {
                        $emailSuccess[] = $email;
                    }
                    else {
                        $emailFailed[] = $email;
                    }
                }
            }

            if ($emailSuccess && count($emailSuccess) > 0) {
                $successMessage = "Successfully send an email to: " . implode(', ', $emailSuccess);
                //Mage::getSingleton('core/session')->addSuccess($successMessage);
            }

            if ($emailFailed && count($emailFailed) > 0) {
                $failedMessage = "Failed to send an email to: " . implode(', ', $emailFailed);
                //Mage::getSingleton('core/session')->addError($failedMessage);
            }
            
            $redirectPage = sprintf("%s%s?formId=%s&recordId=%s&ref=%s", Mage::getBaseUrl(), $urlThankyou, $formId, $recordId, $ref);
        }
        else {
            $failedMessage = "Failed to send an email.";
            Mage::getSingleton('core/session')->addError($failedMessage);
            
            $queryString = sprintf("?formId=%d&recordId=%d&ref=%s", $formId, $recordId, $ref);
            $redirectPage = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getBaseUrl();
            $redirectPage .= $queryString;
        }
        
        $this->_redirectPage($redirectPage);
    }
    
    private function getFormBuilderData($formId) {
        $collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
        $collection->addFieldToFilter('main_table.id', $formId);
        $collection->getFirstItem();
        
        if ($collection->getSize() > 0) {
            $collectionData = $collection->getData();
            
            return $collectionData[0];
        }
        
        return false;
    }
}
