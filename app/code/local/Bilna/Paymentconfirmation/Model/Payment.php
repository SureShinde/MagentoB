<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Bilna_Paymentconfirmation_Model_Payment extends Varien_Object{
    private $orderTable = 'sales_flat_order';
    private $confirmationTable = 'bilna_payment_confirmation';
    public function isValidOrder($orderNumber){
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = sprintf( "SELECT entity_id,grand_total,total_paid,status,customer_email FROM %s ".
                        "WHERE increment_id = '%s'", $this->orderTable,$orderNumber);
        return $db->fetchAll($sql);
    }
    
    public function insertPayment($param){
        //print_r($param);//exit;
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $sql = sprintf( "INSERT INTO %s set created_at = NOW(),".
                        "order_id = %s ,email = %s, nominal = %s, dest_bank = %s, transfer_date = %s, ".
                        "source_bank = %s, source_acc_number = %s, source_acc_name = %s, comment = %s",$this->confirmationTable,
                        !empty($param['order_number']) ? "'".$param['order_number']."'" : "NULL",
                        !empty($param['email']) ? "'".$param['email']."'" : "NULL",
                        !empty($param['nominal']) ? $param['nominal'] : "NULL",
                        !empty($param['bank_to']) ? "'".$param['bank_to']."'" : "NULL",
                        !empty($param['transfer_date']) ? "'".$param['transfer_date']."'" : "NULL",
                        !empty($param['bank_from']) ? "'".$param['bank_from']."'" : "NULL",
                        !empty($param['acc_from']) ? "'".$param['acc_from']."'" : "NULL",
                        !empty($param['name_from']) ? "'".$param['name_from']."'" : "NULL",
                        !empty($param['comment']) ? "'".$param['comment']."'" : "NULL");
        //print $sql;
        //exit;
        $query = $db->query($sql);
        return $query;
    }
    
}

