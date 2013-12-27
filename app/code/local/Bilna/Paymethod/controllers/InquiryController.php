<?php
/**
 * Description of Bilna_Paymethod_InquiryController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_InquiryController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'klikbca';
    protected $_file = 'inquiry';
    
    protected $_klikbcaUserId = ''; // klik bca user id
    protected $_AdditionalData = ''; // addition data
    protected $_currency = 'IDR';
    
    public function indexAction() {
        echo 'right here';
        exit;
        // logging
        $this->_writeTransactionLog(sprintf("%s | host_klikbca: %s:%s", $_GET['userid'], $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT']));
        $this->_writeTransactionLog(sprintf("%s | request_klikbca: %s", $_GET['userid'], json_encode($_GET)));
        
        if (isset ($_GET['userid'])) {
            $this->_klikbcaUserId = $_GET['userid'];
        }
        
        if (isset ($_GET['adddata'])) {
            $this->_AdditionalData = $_GET['adddata'];
        }
        
        $validationHours = Mage::getStoreConfig('payment/klikbca/order_validation');
        $validationOption = Mage::getStoreConfig('payment/klikbca/order_validation_option');
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('status', 'pending');
        $orderCollection->getSelect()->join(array ('SFOP' => 'sales_flat_order_payment'), 'klikbca_user_id="' . $this->_klikbcaUserId . '" AND SFOP.method="klikbca" AND main_table.entity_id=SFOP.parent_id', array ('klikbca_user_id'), null, 'left');
        
        header('Content-type: text/xml; charset=utf-8');
        $xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<OutputListTransactionPGW>';
        $xml .= "<userID>" . $this->_klikbcaUserId . "</userID>";
        $xml .= "<additionalData>" . $this->_AdditionalData . "</additionalData>";

        //$productCollection = Mage::getModel('catalog/product')->getCollection();
                 
        foreach ($orderCollection as $_order) {
            if ($validationHours) {
            	$orderCreatedDate = $_order->getCreatedAt();
                $orderCreatedDatePlus = date('Y-m-d H:i:s', strtotime($orderCreatedDate . ' + ' . $validationHours . ' ' . $validationOption));
                $now = date('Y-m-d H:i:s');

                if (strtotime($now) > strtotime($orderCreatedDatePlus)) {
                    continue;
                }
            }
            
            $items = $_order->getAllItems();
            $productName = '';
            
            foreach ($items as $itemId => $item) {
                $productName = $item->getName();
                break;
            }

            $xml .= "<OutputDetailPayment>";
            $xml .= "<transactionNo>" . $_order->getIncrementId() . "</transactionNo>";
            $xml .= "<transactionDate>" . date('m/d/Y H:i:s', strtotime($_order->getCreatedAt())) . "</transactionDate>";
            $xml .= "<amount>" . $this->_currency . number_format((int) $_order->getGrandTotal(), 2, null, '') . "</amount>";
            $xml .= "<description>" . Mage::helper('klikbca')->_removeSymbols($productName) . "</description>";
            $xml .= "</OutputDetailPayment>";                     
        }

        $xml .= "</OutputListTransactionPGW>";
        
        $this->_writeTransactionLog(sprintf("%s | response_klikbca: %s", $_GET['userid'], $xml));
        die ($xml);
    }
    
    private function _writeTransactionLog($content) {
        $trxLogPath = Mage::getStoreConfig('payment/klikbca/trx_log_path');
        $filename = sprintf("%s_%s.%s", $this->_code, $this->_file, date('Ymd'));
        
        return Mage::helper('klikbca')->_writeLogFile($trxLogPath, $filename, $content);
    }
}

