<?php
/**
 * Description of Bilna_Formbuilder_Block_Share
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Block_Share extends Mage_Core_Block_Template {
    public $currentUrl = null;
    
    public function __construct() {
        parent::__construct();
        
        $this->setTemplate('formbuilder/form/share.phtml');
        $this->currentUrl = Mage::helper('core/url')->getCurrentUrl();
    }
    
    public function getShareUrl() {
        $formId = $this->getRequest()->getParam('formId');
        $ref = $this->getRequest()->getParam('ref');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = sprintf("SELECT url FROM `bilna_formbuilder_form` WHERE id = %d LIMIT 1", $formId);
        $row = $read->fetchRow($sql);
        $url = $row['url'];
        $shareUrl = $this->getBaseUrl() . $url . "?ref=" . $ref;
        
        return $shareUrl;
    }
    
    public function getFormId() {
        return $this->getRequest()->getParam('formId');
    }
    
    public function getRecordId() {
        return $this->getRequest()->getParam('recordId');
    }
    
    public function getRef() {
        return $this->getRequest()->getParam('ref');
    }
}
