<?php

/**
 * Created by PhpStorm.
 * User: core
 * Date: 20/05/16
 * Time: 14:07
 */
class Moxy_MoxySellerCenter_Model_Observer
{
    private $requestTimeout = 60;

    /**
     * Observer to notify Seller Center after invoice has been created
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function updateQtySellerCenter(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $invoiceItems = $invoice->getAllItems();

        $sellerCenterConfig = Mage::getStoreConfig('moxysellercenter/configuration');
        $updateStockEndPoint = trim($sellerCenterConfig['update_stock_end_point']);
        $enableUpdateStock = $sellerCenterConfig['enable_update_stock'];

        if (!empty($invoiceItems) && !empty($updateStockEndPoint) && $enableUpdateStock == 1) {
            foreach ($invoiceItems as $item) {
                $requestBody = "sku=".$item->getSku();
                $this->_doRequestToSellerCenter($updateStockEndPoint, $requestBody);
            }
        }

        return $this;
    }

    /*
     * Function to send request to seller center
     */
    protected function _doRequestToSellerCenter($notificationUrl, $request)
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $notificationUrl);
            curl_setopt($curl, CURLOPT_VERBOSE, FALSE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, FALSE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->requestTimeout);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $ex = new Exception(curl_error($curl));
                Mage::logException($ex);
                throw $ex;
            }
            curl_close($curl);
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }

        return;
    }
}