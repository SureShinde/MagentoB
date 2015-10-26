<?php
class Bilna_Fraud_Model_Observer {

    public function checkFraud(Varien_Event_Observer $observer) {
        $order_id = $observer->getEvent()->getOrder()->getId();
        $orderCollection = Mage::getModel('sales/order')->load($order_id);

        $address = str_replace("\n", '+', $orderCollection->getShippingAddress()->getData('street'));
        $address = str_replace(" ", '+', $address);
        $telephone = $orderCollection->getShippingAddress()->getData('telephone');
        $email = $orderCollection->getShippingAddress()->getData('email');

	    $roughRuleId = $orderCollection->getData('applied_rule_ids');
        $roughRuleId = str_replace(',', '%2C', $roughRuleId);
        $ruleId = explode(',', $orderCollection->getData('applied_rule_ids'));

        $ruleCollection = Mage::getModel('salesrule/rule')->getCollection();
        $ruleCollection->addFieldToFilter('rule_id', array('in' => $ruleId));
        $ruleCollection->addFieldToFilter('coupon_type', array('neq' => 1));
        $ruleCollection->load();

        $rowRuleCollection = $ruleCollection->getFirstItem()->getData();
        $fromDate = $rowRuleCollection['from_date'];
        $toDate = $rowRuleCollection['to_date'];
        $usesPerHousehold = $rowRuleCollection['uses_per_household'];

        if(isset($rowRuleCollection['rule_id'])) {
            $date = '['.$fromDate.'T23%3A59%3A59.999Z%2FDAY+TO+'.$toDate.'T23%3A59%3A59.999Z%2FDAY]';

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

            if(($emailScoreEnabled == 1) && ($addressScoreEnabled == 1) && ($telephoneScoreEnabled == 1)) {
                if(!empty($addressProximity)) {
                    $address = 'Shipping_Address%3A"'.$address.'"~'.$addressProximity;
                }
                else {
                    $address = 'Shipping_Address%3A"'.$address.'"';
                }

                if(!empty($emailProximity)) {
                    $email = '%0AEmail%3A"'.$email.'"~'.$emailProximity;
                }
                else {
                    $email = '%0AEmail%3A"'.$email.'"';
                }

                $telephone = '%0ATelephone%3A"'.$telephone.'"';
                //$telephone = '%2BTelephone%3A"'.$telephone.'"';
                $addressScore = '&qf=Shipping_Address^'.$addressScoreWeight;
                $emailScore = '+Email^'.$emailScoreWeight;
                $telephoneScore = '+Telephone^'.$telephoneScoreWeight;
            }
            elseif(($emailScoreEnabled == 0) && ($addressScoreEnabled == 1) && ($telephoneScoreEnabled == 1)) {
                if(!empty($addressProximity)) {
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

                if(!empty($emailProximity)) {
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
                if(!empty($addressProximity)) {
                    $address = 'Shipping_Address%3A"'.$address.'"~'.$addressProximity;
                }
                else {
                    $address = 'Shipping_Address%3A"'.$address.'"';
                }

                if(!empty($emailProximity)) {
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
                if(!empty($addressProximity)) {
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

                if(!empty($emailProximity)) {
                    $email = 'Email%3A"'.$email.'"~'.$emailProximity;
                }
                else {
                    $email = 'Email%3A"'.$email.'"';
                }

                $telephone = '';
                $addressScore = '';
                $emailScore = '&qfEmail^'.$emailScoreWeight;
                $telephoneScore = '';
            }

            $createdDate = '&fq=Created_Date%3A'.$date;

            if((empty($fromDate)) && (empty($toDate))) {
                $createdDate = '';
            }

            $ruleId = '&fq=Rule_ID%3A'.$roughRuleId;
            $string = '&start=0&rows=20&fl=*%2Cscore&wt=json&indent=true&defType=edismax';
            $string_2 = '&stopwords=true&lowercaseOperators=true&stats=true&stats.field=Subtotal_statsConfigurable';

            $url  = 'http://'.$host.':'.$port.$path.'/'.$core.'/select?q='.$address.$email.$telephone.$ruleId.$createdDate.$string.$addressScore.$emailScore.$telephoneScore.$string_2;
        }

        //curl_setopt($ch, CURLOPT_HEADER, 0);
        if($auth == 1) {
            $cleanPassword = Mage::helper('core')->decrypt($password);
            $url  = 'http://'.$username.':'.$cleanPassword.'@'.$host.':'.$port.$path.'/'.$core.'/select?q='.$address.$email.$telephone.$ruleId.$createdDate.$string.$addressScore.$emailScore.$telephoneScore.$string_2;
            /*$loginData = 'username='.$username.'&password='.$cleanPassword;
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);*/
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        $output = curl_exec($ch);

        curl_close($ch);

        $data = json_decode($output, true);
        if($data['response']['numFound'] > $usesPerHousehold) {
            if ($orderCollection->canCancel()) {
                /*$order->cancel();
                $order->setStatus('canceled');
                //$order->getStatusHistoryCollection(true);
                $order->save();*/
                
                $orderCollection->cancel();
                //$order->setStatus('canceled');
                //$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Cancel Transaction.');
                //$order->setStatus(Mage_Sales_Model_Order);
                $orderCollection->save();
                if($orderCollection->isCanceled()){

                }
            }
        }
    }
}
?>
