<?php
class Bilna_Fraud_Model_Observer {
    public function checkFraud(Varien_Event_Observer $observer) {
        $config = Mage::getStoreConfig('bilna_fraud/fraud');
        $configSolr = Mage::getStoreConfig('bilna_fraud/solr_server');
        $emailScoreEnabled = $config['email_scoring_status'];
        $emailProximity = $config['email_proximity'];
        $emailScoreWeight = $config['email_score_weight'];
        $addressScoreEnabled = $config['address_scoring_status'];
        $addressProximity = $config['address_proximity'];
        $addressScoreWeight = $config['address_score_weight'];
        $telephoneScoreEnabled = $config['telephone_scoring_status'];
        $telephoneScoreWeight = $config['phone_score_weight'];
        $host = $configSolr['host'];
        $port = $configSolr['port'];
        $path = $configSolr['path'];
        $core = $configSolr['core'];
        $auth = $configSolr['requires_authentication'];
        $username = $configSolr['username'];
        $password = $configSolr['password'];
        //$string = '&start=0&rows=20&fl=*%2Cscore&wt=json&indent=true&defType=edismax';
        $string = '&start=0&rows=20&wt=json&indent=true';
        $string_2 = '&stopwords=true&lowercaseOperators=true&stats=true&stats.field=Subtotal_statsConfigurable';
        $order_id = $observer->getEvent()->getOrder()->getId();
        $orderData = Mage::getModel('sales/order')->load($order_id);

        $entity_id = $orderData->getData('entity_id');
        $customer_name = trim($orderData->getData('customer_firstname').' '.$orderData->getData('customer_middlename').$orderData->getData('customer_lastname'));
        $shipping_address = $orderData->getShippingAddress()->getData('street');
        $billing_address = $orderData->getBillingAddress()->getData('street');
        $grand_total = $orderData->getData('grand_total');
        $payment_method = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode();
        $coupon_code = $orderData->getData('coupon_code');
        $rule_id_for_log = $orderData->getData('applied_rule_ids');

        $address = str_replace("\n", '+', $orderData->getShippingAddress()->getData('street'));
        $address = str_replace(" ", '+', $address);
        $telephone = $orderData->getShippingAddress()->getData('telephone');
        $email = $orderData->getShippingAddress()->getData('email');
	    $originalRuleId = $orderData->getData('applied_rule_ids');
        $originalRuleId = str_replace(',', '%2C', $originalRuleId);
        $ruleId = explode(',', $orderData->getData('applied_rule_ids'));
        $ruleCollection = Mage::getModel('salesrule/rule')->getCollection();
        $ruleCollection->addFieldToFilter('rule_id', array('in' => $ruleId));
        $ruleCollection->addFieldToFilter('coupon_type', array('neq' => 1));
        $ruleCollection->load();
        $ruleData = $ruleCollection->getFirstItem()->getData();
        $fromDate = $ruleData['from_date'];
        $toDate = $ruleData['to_date'];
        $usesPerHousehold = $ruleData['uses_per_household'];
        $ruleDataRuleId = $ruleData['rule_id'];
        if(($usesPerHousehold > 0) || (!empty($usesPerHousehold))) {
            if($ruleDataRuleId != 0) {
                $date = '['.$fromDate.'T23%3A59%3A59.999Z%2FDAY+TO+'.$toDate.'T23%3A59%3A59.999Z%2FDAY]';
                $address = 'address_ngram%3A"'.$address.'"';
                $telephone = '&fq=telp_clean%3A"'.$telephone.'"';
            /*if(($emailScoreEnabled == 1) && ($addressScoreEnabled == 1) && ($telephoneScoreEnabled == 1)) {
                $telephone_address = '%0Atele_address%3A"'.$telephone.' '.$address.'"';
                if((!is_null($addressProximity)) || ($addressProximity > 0)) {
                    $address = 'Shipping_Address%3A"'.$address.'"~'.$addressProximity;
                }
                else {
                    $address = 'Shipping_Address%3A"'.$address.'"';
                }
                if((!is_null($emailProximity)) || ($emailProximity > 0)) {
                    $email = '%0AEmail%3A"'.$email.'"~'.$emailProximity;
                }
                else {
                    $email = '%0AEmail%3A"'.$email.'"';
                }
                //$telephone = '%0ATelephone%3A"'.$telephone.'"';
                $telephone = '%2BTelephone%3A"'.$telephone.'"';
                $addressScore = '&qf=Shipping_Address^'.$addressScoreWeight;
                $emailScore = '+Email^'.$emailScoreWeight;
                $telephoneScore = '+Telephone^'.$telephoneScoreWeight;
            }
            elseif(($emailScoreEnabled == 0) && ($addressScoreEnabled == 1) && ($telephoneScoreEnabled == 1)) {
                if((!is_null($addressProximity)) || ($addressProximity > 0)) {
                    $address = 'Shipping_Address%3A"'.$address.'"~'.$addressProximity;
                }
                else {
                    $address = 'Shipping_Address%3A"'.$address.'"';
                }
                $telephone = '%0ATelephone%3A"'.$telephone.'"';
                $addressScore = '&qf=Shipping_Address^'.$addressScoreWeight;
                $emailScore = '';
                $telephoneScore = '+Telephone^'.$telephoneScoreWeight;
            }
            elseif(($emailScoreEnabled == 1) && ($addressScoreEnabled == 0) && ($telephoneScoreEnabled == 1)) {
                $address = '';
                if((!is_null($emailProximity)) || ($emailProximity > 0)) {
                    $email = 'Email%3A"'.$email.'"~'.$emailProximity;
                }
                else {
                    $email = 'Email%3A"'.$email.'"';
                }
                $telephone = '%0ATelephone%3A"'.$telephone.'"';
                $addressScore = '&qf=Shipping_Address^'.$addressScoreWeight;
                $emailScore = '+Email^'.$emailScoreWeight;
                $telephoneScore = '+Telephone^'.$telephoneScoreWeight;
            }
            elseif(($emailScoreEnabled == 1) && ($addressScoreEnabled == 1) && ($telephoneScoreEnabled == 0)) {
                if((!is_null($addressProximity)) || ($addressProximity > 0)) {
                    $address = 'Shipping_Address%3A"'.$address.'"~'.$addressProximity;
                }
                else {
                    $address = 'Shipping_Address%3A"'.$address.'"';
                }
                if((!is_null($emailProximity)) || ($emailProximity > 0)) {
                    $email = '%0AEmail%3A"'.$email.'"~'.$emailProximity;
                }
                else {
                    $email = '%0AEmail%3A"'.$email.'"';
                }
                $telephone = '';
                $addressScore = '&qf=Shipping_Address^'.$addressScoreWeight;
                $emailScore = '+Email^'.$emailScoreWeight;
                $telephoneScore = '';
            }
            elseif(($emailScoreEnabled == 0) && ($addressScoreEnabled == 0) && ($telephoneScoreEnabled == 1)) {
                $address = '';
                $email = '';
                $telephone = 'Telephone%3A"'.$telephone.'"';
                $addressScore = '';
                $emailScore = '';
                $telephoneScore = '&qf=Telephone^'.$telephoneScoreWeight;
            }
            elseif(($emailScoreEnabled == 0) && ($addressScoreEnabled == 1) && ($telephoneScoreEnabled == 0)) {
                if((!is_null($addressProximity)) || ($addressProximity > 0)) {
                    $address = 'Shipping_Address%3A"'.$address.'"~'.$addressProximity;
                }
                else {
                    $address = 'Shipping_Address%3A"'.$address.'"';
                }
                $email = '';
                $telephone = '';
                $addressScore = '&qf=Shipping_Address^'.$addressScoreWeight;
                $emailScore = '';
                $telephoneScore = '';
            }
            elseif(($emailScoreEnabled == 1) && ($addressScoreEnabled == 0) && ($telephoneScoreEnabled == 0)) {
                $address = '';
                if((!is_null($emailProximity)) || ($emailProximity > 0)) {
                    $email = 'Email%3A"'.$email.'"~'.$emailProximity;
                }
                else {
                    $email = 'Email%3A"'.$email.'"';
                }
                $telephone = '';
                $addressScore = '';
                $emailScore = '&qfEmail^'.$emailScoreWeight;
                $telephoneScore = '';
            }*/
            $createdDate = '&fq=Created_Date%3A'.$date;
            if((empty($fromDate)) && (empty($toDate))) {
                $createdDate = '';
            }
            $formattedRuleId = '&fq=Rule_ID%3A'.$originalRuleId;
            //$url  = $host.':'.$port.$path.'/'.$core.'/select?q='.$address.$email.$telephone.$telephone_address.$formattedRuleId.$createdDate.$string.$addressScore.$emailScore.$telephoneScore.$string_2;
            $url  = $host.':'.$port.$path.'/'.$core.'/select?q='.$address.$telephone.$formattedRuleId.$createdDate.$string;
        }

        if($auth == 1) {
            $cleanPassword = Mage::helper('core')->decrypt($password);
            $url  = $username.':'.$cleanPassword.'@'.$url;
        }

        $url = 'http://'.$url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($output, true);
        if($data['response']['numFound'] >= $usesPerHousehold) {
            if ($orderData->canCancel()) {
                /*$order->cancel();
                $order->setStatus('canceled');
                //$order->getStatusHistoryCollection(true);
                $order->save();*/
                
                $orderData->cancel();
                //$order->setStatus('canceled');
                //$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Cancel Transaction.');
                //$order->setStatus(Mage_Sales_Model_Order);
                $orderData->save();

                $log = Mage::getModel('bilna_fraud/log');
                $datas = array(
                    'order_id' => $order_id,
                    'entity_id' => $entity_id,
                    'customer_name' => $customer_name,
                    'email' => $email,
                    'shipping_address' => $shipping_address,
                    'billing_address' => $billing_address,
                    'grand_total' => $grand_total,
                    'payment_method' => $payment_method,
                    'coupon_code' => $coupon_code,
                    'rule_id' => $rule_id_for_log,
                    'created_at' => now()
                );
                $log->addData($datas);
                $log->save();

                $orderData->addStatusHistoryComment('This is a FRAUD order');
                $orderData->save();
            }
        }
        }
    }
}
?>
