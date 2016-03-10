<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Bilna_Paymentconfirmation_Model_Payment extends Mage_Core_Model_Abstract{
    private $confirmationTable = 'bilna_payment_confirmation';
    public function _construct()
    {
        parent::_construct();
        $this->_init('Paymentconfirmation/payment');
    }
    
    public function isValidOrder($orderNumber){
        $order = Mage::getModel("sales/order")->loadByIncrementId($orderNumber);
        return $order;
    }
    
    public function isPaymentExists($orderNumber){
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $field = array("order_id"=>"order_id","email"=>"email","nominal"=>"nominal",
                       "dest_bank"=>"dest_bank","transfer_date"=>"transfer_date",
                       "source_bank"=>"source_bank","source_acc_number"=>"source_acc_number",
                       "source_acc_name"=>"source_acc_name","comment"=>"comment","entity_id"=>"entity_id");
        $query = $db->select()->from($this->confirmationTable,$field)->where(sprintf("order_id = %s",$orderNumber))->order('transfer_date','DESC')->limit(1);
	return $db->fetchAll($sql);
    }
    
}


