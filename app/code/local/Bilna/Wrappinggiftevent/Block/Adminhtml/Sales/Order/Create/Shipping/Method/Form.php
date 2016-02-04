<?php
/**
 * Description of Bilna_Wrappinggiftevent_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form
 * 
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Wrappinggiftevent_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form {
    public function __construct() {
        parent::__construct();
    }

    public function getWrappingGiftEvent() {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('wrapping_gift_event');
        $select = $adapter->select()
            ->from($tableName)
            ->where(
                'DATE(NOW()) >= wrapping_startdate AND DATE(NOW()) <= wrapping_enddate'
            );
        $eventNames = $adapter->fetchAll($select);

        return $eventNames;
    }

    public function getAddressWrappingEvent() {
        return $this->getAddress()->getWrappingType();
    }
    
    public function getWrappinggiftData() {
        $tdate = date('Y-m-d H:i:s');
        $session = Mage::getSingleton('adminhtml/session_quote');
        $result = array (
            'use_wrappinggift' => $session->getData('use_wrappinggift'),
            'wrappinggift_amount' => $session->getData('wrappinggift_amount')
        );

        return $result;
    }
    
    public function urlToWrappinggiftSave() {
        return Mage::getUrl('wrappinggiftevent/adminhtml_sales_order/saveWrappinggift');
    }
}
