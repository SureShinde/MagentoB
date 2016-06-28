<?php
/**
 * Description of Bilna_Fraud_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Fraud_Helper_Data extends Mage_Core_Helper_Abstract {
    public function checkOrderStatus($orderId, $loadByIncrement = 0) {
        $canceled = 0;
        if($loadByIncrement == 1) {
            $orderData = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        }
        else {
            $orderData = Mage::getModel('sales/order')->load($orderId);
        }

        $order = $orderData->getData('status');
        if(strtolower($order) == 'canceled') {
            $canceled = 1;
        }

        return $canceled;
    }
    
    public function checkFraud($order) {
        $config = Mage::getStoreConfig('bilna_fraud/fraud');
        $configSolr = Mage::getStoreConfig('bilna_fraud/solr_server');
        $ADSEnabled = $config['enabled'];
        
        if ($ADSEnabled == 1) {
            $host = $configSolr['host'];
            $port = $configSolr['port'];
            $path = $configSolr['path'];
            $core = $configSolr['core'];
            $auth = $configSolr['requires_authentication'];
            $username = $configSolr['username'];
            $password = $configSolr['password'];
            $string = '&start=0&rows=20&wt=json&indent=true';
            $order_id = $order->getId();
            $order_number = $order->getIncrementId();
            //$orderData = Mage::getModel('sales/order')->load($order_id);

            $entity_id = $order->getData('entity_id');
            $customer_name = trim($order->getData('customer_firstname').' ' . $order->getData('customer_middlename') . $order->getData('customer_lastname'));
            $shipping_address = $order->getShippingAddress()->getData('street');
            $billing_address = $order->getBillingAddress()->getData('street');
            $grand_total = $order->getData('grand_total');
            $payment_method = $order->getPayment()->getMethodInstance()->getCode();
            $coupon_code = $order->getData('coupon_code');
            $rule_id_for_log = $order->getData('applied_rule_ids');
            $telephone = $order->getShippingAddress()->getData('telephone');
            $email = $order->getShippingAddress()->getData('email');
            $originalRuleId = $order->getData('applied_rule_ids');
            $originalRuleId = str_replace(',', '%2C', $originalRuleId);
            
            $ruleId = explode(',', $order->getData('applied_rule_ids'));
            $ruleCollection = Mage::getModel('salesrule/rule')->getCollection();
            $ruleCollection->addFieldToFilter('rule_id', ['in' => $ruleId]);
            $ruleCollection->addFieldToFilter('coupon_type', ['neq' => 1]);
            $ruleCollection->load();
            $ruleData = $ruleCollection->getFirstItem()->getData();
            $fromDate = $ruleData['from_date'];
            $toDate = $ruleData['to_date'];
            $usesPerHousehold = $ruleData['uses_per_household'];
            $ruleDataRuleId = $ruleData['rule_id'];
            
            if (($usesPerHousehold > 0) && (!empty ($usesPerHousehold)) && !is_null($ruleDataRuleId)) {
                if ($ruleDataRuleId != 0) {
                    $date = '[' . $fromDate . 'T17%3A00%3A00Z+TO+' . $toDate . 'T16%3A59%3A59Z]';
                }
                
                $telephone = '&fq=telp_clean%3A"'.str_replace(' ', '', trim($telephone)).'"';
                $createdDate = '&fq=Created_Date%3A'.$date;
                
                if ((empty ($fromDate)) && (empty ($toDate))) {
                    $createdDate = '';
                }
                
                $formattedRuleId = '&fq=Rule_ID%3A'.$originalRuleId;
                $orderNumber = '-Order_Number%3A"'.$order_number.'"';
                $url  = $host.':'.$port.$path.'/'.$core.'/select?q='.$orderNumber.$telephone.$formattedRuleId.$createdDate.$string;

                if ($auth == 1) {
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
                
                if ($data['response']['numFound'] >= $usesPerHousehold) {
                    if ($order->canCancel()) {
                        $order->cancel();
                        $order->save();

                        $log = Mage::getModel('bilna_fraud/log');
                        $datas = [
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
                        ];
                        $log->addData($datas);
                        $log->save();

                        $order->addStatusHistoryComment('This is a FRAUD order');
                        $order->save();
                    }
                }
            }
        }
    }
}
