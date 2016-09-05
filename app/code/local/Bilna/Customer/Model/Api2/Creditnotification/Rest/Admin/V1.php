<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Bilna_Customer_Model_Api2_Creditnotification_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Creditnotification_Rest
{
    public function _create(array $data)
    {
        $userID = $this->getRequest()->getParam('customer_id');
        $isSubsc = (int) $data['is_subscribed'];
        $summary = Mage::getModel('points/summary')->loadByCustomerID($userID);
        try {
            $summary->setBalanceUpdateNotification($isSubsc)->save();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }
    }
}
