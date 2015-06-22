<?php
/**
 * Description of Bilna_Visa_Block_Processing
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once Mage::getBaseDir('lib') . '/veritrans/veritrans.php';
                                        
class Bilna_Visa_Block_Processing extends Mage_Payment_Block_Info {
    private $_code = 'visa';
    private $_file = 'redirect';
    
    public $_merchantId;
    public $_orderId;
    public $_tokenBrowser;
    public $_tokenMerchant;
    
    protected function _construct() {
        parent::_construct();
    }
    
    public function getRedirectPage() {
        return Mage::helper('veritrans')->getRedirectPage();
    }
    
    public function getMerchantId() {
        return Mage::helper('veritrans')->getMerchantId();
    }
    
    public function getOrderId() {
        return Mage::helper('veritrans')->getOrderId();
    }
    
    public function getTokenVeritrans($orderId) {
        $helper = Mage::helper('veritrans');
        
        $merchantId = $this->getMerchantId();
        $sessionId = $helper->getSessionId();
        //$order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementId = $order->getIncrementId();
        $orderData = $order->getData();
        $billingAddress = $order->getBillingAddress();
        $billingAddressSplit = $helper->splitAddress($billingAddress);
        
        $request = new Veritrans;
        $request->merchant_id = $merchantId;
        $request->merchant_hash = $helper->getMerchantHashKey();
        $request->order_id = $incrementId;
        $request->session_id = $sessionId;
        $request->settlement_type = '01';
        $request->gross_amount = (int) $orderData['grand_total'];
        $request->first_name = $billingAddress->getData('firstname');
        $request->last_name = $billingAddress->getData('lastname');
        $request->address1 = $billingAddressSplit['address1'];
        $request->address2 = $billingAddressSplit['address2'];
        $request->city = $billingAddress->getData('city');
        $request->country_code = $helper->getCountryCode($billingAddress->getData('country_id'));
        $request->postal_code = $billingAddress->getData('postcode');
        $request->phone = $billingAddress->getData('telephone');
        $request->email = $billingAddress->getData('email');
        $request->shipping_flag = 0;
        $request->customer_specification_flag = $helper->useBillingAddressForShippingAddress() === true ? 0 : 1;
        
        if ($request->customer_specification_flag == 1) {
            $shippingAddress = $order->getShippingAddress();
            $shippingAddressSplit = $helper->splitAddress($shippingAddress);
            $request->shipping_first_name = $shippingAddress->getData('firstname');
            $request->shipping_last_name = $shippingAddress->getData('lastname');
            $request->shipping_address1 = $shippingAddressSplit['address1'];
            $request->shipping_address2 = $shippingAddressSplit['address2'];
            $request->shipping_city = $shippingAddress->getData('city');
            $request->shipping_country_code = $helper->getCountryCode($shippingAddress->getData('country_id'));
            $request->shipping_postal_code = $shippingAddress->getData('postcode');
            $request->shipping_phone = $shippingAddress->getData('telephone');
        }
        
        $request->lang_enable_flag = '';
        $request->lang = '';
        $request->commodity = $helper->getCommodityInformation($orderData);
        $request->finish_payment_return_url = $helper->getFinishUrl();
        $request->unfinish_payment_return_url = $helper->getUnfinishUrl();
        $request->error_payment_return_url = $helper->getFailureUrl();
        //$request->promo_bins = $this->getPromoBinsSingle($order->getEntityId());
        $promoBinsVisa = $this->getPromoBinsSingle($order->getEntityId());
        $request->promo_bins = $promoBinsVisa[0];
        $response = $request->get_keys();
        
        //$this->writeTransactionLog(sprintf("%s | url_vtweb_key: %s", $incrementId, Veritrans::REQUEST_KEY_URL));
        //$this->writeTransactionLog(sprintf("%s | url_vtweb_redirect: %s", $incrementId, Veritrans::PAYMENT_REDIRECT_URL));
        $this->writeTransactionLog(sprintf("%s | request_veritrans: %s", $incrementId, str_replace('\\u0000Veritrans\\u0000', 'Veritrans->', json_encode((array) $request))));
        $this->writeTransactionLog(sprintf("%s | response_veritrans: %s", $incrementId, json_encode($response)));
        
        if (array_key_exists('token_merchant', $response) && array_key_exists('token_browser', $response)) {
            //save to table veritrans_track on database
            $data = array ();
            $data['order_id'] = $incrementId;
            $data['session_id'] = $sessionId;
            $data['gross_amount'] = (int) $orderData['grand_total'];
            $data['status'] = isset ($response['error_message']) ? '0' : '1';
            $data['token_browser'] = $response['token_browser'];
            $data['token_merchant'] = $response['token_merchant'];
            $data['message'] = $response['error_message'];
            
            $modelVeritrans = Mage::getModel('veritrans/veritrans');
            $modelVeritrans->setData($data)->addData();
            
            if ($modelVeritrans->save()) {
                $result = array ();
                $result['url_redirection'] = $helper->getRedirectUrl();
                $result['merchant_id'] = $merchantId;
                $result['order_id'] = $incrementId;
                $result['token_browser'] = $response['token_browser'];
                $result['token_merchant'] = $response['token_merchant'];
                
                $this->writeTransactionLog(sprintf("%s | save_table: success", $incrementId));
                
                return (object) $result;
            }
            
            $this->writeTransactionLog(sprintf("%s | save_table: failed", $incrementId));
            
            return false;
        }
        
        $this->writeTransactionLog(sprintf("%s | response_veritrans: failed", $incrementId));

        return false;
    }
    
    protected function getPromoBinsSingle($entityId) {
        return Mage::getModel('visa/visa')->getCCBinsByEntityId($entityId);
    }
    
    protected function getPromoBins() {
        $issuer = (string) Mage::getStoreConfig('payment/visa/issuer');
        $promoBins = Mage::getModel('visa/bincode')
            ->getCollection()
            ->addFieldToFilter('UPPER(issuer)', array ('eq' => strtoupper($issuer)));
        $result = array ();
        
        foreach ($promoBins as $promoBin) {
            $result[] = $promoBin->getCode();
        }
        
        $response = implode(",", $result);
        
        return $response;
    }
    
    public function getRedirectMessage() {
        $timeout = $this->getRedirectTimeout() / 1000;
        
        return sprintf("This page will be redirected to Veritrans page in %d seconds.", (int) $timeout);
    }

    public function getRedirectTimeout() {
        return Mage::helper('veritrans')->getRedirectTimeout();
    }
    
    protected function writeTransactionLog($content) {
        $trxLogPath = Mage::helper('veritrans')->getTrxLogPath();
        $filename = sprintf(
            "%s_%s.%s",
            $this->_code,
            $this->_file,
            date('Ymd')
        );

        return Mage::helper('veritrans')->writeLogFile($trxLogPath, $filename, $content);
    }
}
