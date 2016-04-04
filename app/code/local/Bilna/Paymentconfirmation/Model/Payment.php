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
    
    
}


