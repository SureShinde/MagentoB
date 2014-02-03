<?php
/**
 * Bilna_Whitelistemail_Helper_Data
 * 
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_ProcessingController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'whitelistemail';
    
    public function readAction() {
        $key = $this->getRequest()->getParam('key');
        $data = Mage::Helper('whitelistemail')->getCustomerDecodeKey($key);
        $update = Mage::getModel('whitelistemail/processing')->updateCustomerReadEmail($data);
        $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . Mage::getStoreConfig('bilna_whitelistemail/whitelistemail/image_view');
        
        echo $imageUrl;
        exit;
    }
}
