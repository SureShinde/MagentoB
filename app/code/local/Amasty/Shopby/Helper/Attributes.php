<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Shopby_Helper_Attributes
{
	protected $_optionsHash;
	protected $_attributes;
	protected $_options;
	protected $_optionsLabels = array();
	
	/**
	 * @return array
	 */
	public function getAllFilterableOptionsAsHash()
	{
		if (is_null($this->_optionsHash)) {
			$hash = array();
            $attributes = $this->getFilterableAttributes();
            
            /* @var $helper Amasty_Shopby_Helper_Url */
            $helper = Mage::helper('amshopby/url');
            
            $options = $this->getAllOptions();
            
            foreach ($attributes as $a){
                $code        = $a->getAttributeCode();
                $code = str_replace(array('_', '-'), Mage::getStoreConfig('amshopby/seo/special_char'), $code);
                $hash[$code] = array();
                foreach ($options as $o){
                    if ($o['value'] && $o['attribute_id'] == $a->getId()) { // skip first empty
                        $unKey = $helper->createKey($o['value']);
                        while(isset($hash[$code][$unKey])){
                            $unKey .= '_';
                        }
                        $hash[$code][$unKey] = $o['option_id'];
                        /*
                         * Keep original label for further use
                         */
                        $this->_optionsLabels[$o['option_id']] = $o['value'];
                    }
                }
            }
            $this->_optionsHash = $hash;
		}
		return $this->_optionsHash;
	}
	
	public function getFilterableAttributes()
    {
        if (is_null($this->_attributes)) {
			$collection = Mage::getResourceModel('catalog/product_attribute_collection');
          	$collection
	            ->setItemObjectClass('catalog/resource_eav_attribute')            
	            ->addStoreLabel(Mage::app()->getStore()->getId())
	            ->addIsFilterableFilter()
	            ->setOrder('position', 'ASC');        
            $collection->load();
            $this->_attributes = $collection;
        }
        return $this->_attributes;
    }
    
    /**
     * Get option for specific attribute
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeOptions($attributeCode)
    {
    	$options = array();
    	$all = $this->getAllFilterableOptionsAsHash();    	
    	$attributeCode = str_replace(array('_', '-'), Mage::getStoreConfig('amshopby/seo/special_char'), $attributeCode);
    	if (isset($all[$attributeCode])) {
    		$attributeOptions = $all[$attributeCode];
    		foreach ($attributeOptions as $label => $value) {
    			$options[] = array(
    				'value' => $value,
    				'label' => $this->_optionsLabels[$value]
    			);
    		}
    	}
    	return $options;
    }
    
	protected function getAllOptions()
	{
		if (is_null($this->_options)) {
			$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setStoreFilter()
				->toArray();
			$this->_options = $valuesCollection['items'];
		}
		return $this->_options;
	}
}