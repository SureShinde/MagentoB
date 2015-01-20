<?php
/**
 * Description of Bilna_Formbuilder_Block_Share
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Block_Thankyou extends Mage_Core_Block_Template {
    public $formId = null;
    public $recordId = null;
    public $ref = null; 
    public $formData = null;

    public function __construct() {
        parent::__construct();
        
        $this->setTemplate('formbuilder/form/thankyou.phtml');
        
        $gets = $this->getRequest()->getParams();
        
        $this->formId = $gets['formId'];
        $this->recordId = $gets['recordId'];
        $this->ref = $gets['ref'];
        $this->formData = $this->getFormData();
    }
    
    public function getShareUrl() {
        if (!$this->formData) {
            $this->formData = $this->getFormData();
        }
        
        $url = $this->formData['url_success'];
        $shareUrl = sprintf("%s%s?formId=%d&recordId=%d&ref=%s", $this->getBaseUrl(), $url, $this->formId, $this->recordId, $this->ref);
        
        return $shareUrl;
    }
    
    protected function getFormData() {
        $collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
        $collection->addFieldToFilter('main_table.id', $this->formId);
        $collection->getFirstItem();
        
        if ($collection->getSize() > 0) {
            $collectionData = $collection->getData();
            
            return $collectionData[0];
        }
        
        return false;
    }
}
