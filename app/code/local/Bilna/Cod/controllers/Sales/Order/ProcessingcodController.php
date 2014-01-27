<?php
/**
 * Description of Bilna_Cod_Sales_Order_ProcessingcodController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Cod_Sales_Order_ProcessingcodController extends Mage_Core_Controller_Varien_Action {
    public function saveAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        //appending username with markup to comment
        $order->addStatusHistoryComment('', 'processing_cod')
            ->setIsVisibleOnFront(true)
            ->setIsCustomerNotified(true);
        $order->save();
        $this->generateCsv($order);

        $translate = Mage::getSingleton('core/translate');
        $email = Mage::getModel('core/email_template');

        $sender['name'] = Mage::getStoreConfig('trans_email/ident_support/name', Mage::app()->getStore()->getId());
        $sender['email'] = Mage::getStoreConfig('trans_email/ident_support/email', Mage::app()->getStore()->getId());
        
        $guess = $order->getCustomerIsGuest();
        
        if (!isset ($guess) || $guess == 0) {
            //login user
            $customerName = $order->getShippingAddress()->getFirstname() . " " . $order->getShippingAddress()->getLastname();

            //must change this id to actual template id
            $template = Mage::getStoreConfig('bilna_module/cod/template_email_user');
        }
        else {
            //guest
            $customerName = "Moms and Dads";
        	
            //must change this id to actual template id
            $template = Mage::getStoreConfig('bilna_module/cod/template_email_guest');
        }
                
        $customerEmail = $order->getPayment()->getOrder()->getCustomerEmail();
        
        $vars = array ('order' => $order);
        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        Mage::getModel('core/email_template')->sendTransactional($template, $sender, $customerEmail, $customerName, $vars, $storeId);
        $translate->setTranslateInline(true);
        
        /**
         * Clear old values for shipment qty's
         */
    	$this->_redirect('*/sales_order/view', array ('order_id' => $this->getRequest()->getParam('order_id')));
    }
    
    public function startAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $order->setStatus('processing_cod');
        $transactionSave = Mage::getModel('core/resource_transaction')->addObject($order);
        $this->generateCsv($order);
        
        /**
         * Clear old values for shipment qty's
         */
    	$this->_redirect('*/sales_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
    }
    
    public function generateCsv($order) {
        $data = $this->getRequest()->getPost('shipment');
        $comment = (!empty ($data['comment_text'])) ? $data['comment_text'] : "";
        $date = ((int)date("H") >= 9)?date("Y_m_d", strtotime('+1 day', strtotime("NOW"))):date("Y_m_d");

        $filename = Mage::getBaseDir().'/files/rpx_'.$date.'.csv';
        
        $header = array ();
        $header[] = "origin";
        $header[] = "origin_city";
        $header[] = "destination";
        $header[] = "destination_city";
        $header[] = "shipper_account";
        $header[] = "shipper_name";
        $header[] = "identity_no";
        $header[] = "shipper_company";
        $header[] = "shipper_address1";
        $header[] = "shipper_address2";
        $header[] = "shipper_mobile_no";
        $header[] = "shipper_email";
        $header[] = "shipper_city";
        $header[] = "shipper_state";
        $header[] = "shipper_country_id";
        $header[] = "shipper_zip";
        $header[] = "shipper_phone";
        $header[] = "consignee_account";
        $header[] = "order_number";
        $header[] = "consignee_name";
        $header[] = "consignee_company";
        $header[] = "consignee_address1";
        $header[] = "consignee_address2";
        $header[] = "consignee_mobile_no";
        $header[] = "consignee_email";
        $header[] = "consignee_city";
        $header[] = "consignee_state";
        $header[] = "consignee_zip";
        $header[] = "consignee_phone";
        $header[] = "service_type_id";
        $header[] = "package_type_id";
        $header[] = "bill_trans_type_id";
        $header[] = "special_handling_type_id";
        $header[] = "actual_weight";
        $header[] = "tot_weight";
        $header[] = "tot_weight_type";
        $header[] = "rpx_pack";
        $header[] = "rpx_box_a";
        $header[] = "rpx_box_b";
        $header[] = "rpx_box_s";
        $header[] = "others";
        $header[] = "flag_hold";
        $header[] = "flag_holiday_delivery";
        $header[] = "flag_mp_spec_handling";
        $header[] = "flag_heavy_weight";
        $header[] = "flag_dangerous_goods";
        $header[] = "high_value";
        $header[] = "value_docs";
        $header[] = "time_critical";
        $header[] = "electronic";
        $header[] = "others_handling";
        $header[] = "others_handling_desc";
        $header[] = "";
        $header[] = "flag_holiday_pickup";
        $header[] = "boxes";
        $header[] = "packing";
        $header[] = "insurance";
        $header[] = "tot_package";
        $header[] = "desc_of_goods";
        $header[] = "awb";
        $header[] = "tot_declare_value";
        $header[] = "tot_dimensi";
        $header[] = "bill_trans_acct";
        $header[] = "bill_trans_desc";
        $header[] = "bill_card_expired";
		
        if (file_exists($filename)) {
            $fp = fopen($filename, 'a');
        }
        else {
            $fp = fopen($filename, 'w');
            fputcsv($fp, $header);
        }
        
        $shipping = $order->getShippingAddress()->getData();
        
        $content = array ();
        $content[] = "";
        $content[] = "";
        $content[] = "";
        $content[] = "";
        $content[] = "123456789";
        $content[] = "BILNA";
        $content[] = "COD";
        $content[] = "PT. BILNA";
        $content[] = "GREEN GARDEN BLOCK D1A NO. 1";
        $content[] = "";
        $content[] = "021-5809885";
        $content[] = "cs@bilna.com & dedi@bilna.com & husni@bilna.com";
        $content[] = "JAKARTA BARAT";
        $content[] = "DKI JAKARTA";
        $content[] = "INDONESIA";
        $content[] = "11480";
        $content[] = "021-5809885";
        $content[] = $shipping["customer_id"];
        $content[] = $order->getIncrementId();
        $content[] = $shipping["firstname"]." ".$shipping["lastname"];
        $content[] = "";
        $content[] = $shipping["street"];
        $content[] = "";
        $content[] = $shipping["telephone"];
        $content[] = $shipping["email"];
        $content[] = $shipping["city"];
        $content[] = $shipping["region"];
        $content[] = $shipping["postcode"];
        $content[] = "";
        $content[] = "ECP";
        $content[] = "3";
        $content[] = "1";
        $content[] = "0";
        $content[] = ceil($order->getWeight());
        $content[] = ceil($order->getWeight());
        $content[] = "KG";
        $content[] = "0";
        $content[] = "0";
        $content[] = "0";
        $content[] = "0";
        $content[] = "1";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "";
        $content[] = "";
        $content[] = "";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "N";
        $content[] = "";
        $content[] = "Baby Product(s)";
        $content[] = "";
        $content[] = (int)$order->getGrandTotal();
        $content[] = "0";
        $content[] = "";
        $content[] = "";
        $content[] = "";

        fputcsv($fp, $content);
        fclose($fp);
    }
}
