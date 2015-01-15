<?php
/**
 * Description of Bilna_Formbuilder_Block_Share
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Block_Share extends Mage_Core_Block_Template {
    public $formId = null;
    public $recordId = null;
    public $formData = null;
    public $currentUrl = null;
    public $ref = null;
    
    public function __construct() {
        parent::__construct();
        
        $this->setTemplate('formbuilder/form/share.phtml');
        
        $this->formId = $this->getRequest()->getParam('formId');
        $this->recordId = $this->getRequest()->getParam('recordId');
        $this->formData = $this->getFormData();
        $this->currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $this->ref = $this->getRequest()->getParam('ref');
    }
    
    public function getFormData() {
        $collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
        $collection->addFieldToFilter('main_table.id', $this->formId);
        $collection->getFirstItem();
        
        if ($collection->getSize() > 0) {
            $collectionData = $collection->getData();
            
            return $collectionData[0];
        }
        
        return false;
    }
    
    public function getRefUrl() {
        if (!$this->formData) {
            $this->formData = $this->getFormData();
        }
        
        $url = $this->formData['url'];
        $shareUrl = $this->getBaseUrl() . $url . "?ref=" . $this->ref;
        
        return $shareUrl;
    }
    
    public function getShareUrl() {
        return Mage::helper('core/url')->getCurrentUrl();
    }
    
    public function getMediaUrl() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
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
