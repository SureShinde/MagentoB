<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Bilna_Paymentconfirmation_Block_Process extends Mage_Core_Block_Template {
    public function getFormActionUrl() {
        return sprintf("%spaymentconfirm/index/process", Mage::getBaseUrl());
    }
    public function getMessage(){
        return sprintf("%s","Terima kasih telah melakukan pemesanan di Bilna. Proses konfirmasi memakan waktu paling lama 2x24 jam (hari kerja).");
    }
}


