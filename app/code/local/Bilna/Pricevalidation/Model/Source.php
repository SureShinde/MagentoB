<?php
class Bilna_Pricevalidation_Model_Source extends Bilna_Pricevalidation_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('bilna_pricevalidation');
        $options = array();
        switch ($this->getPath()) {
        case 'profile_status':
            $options = array(
                'enabled' => $this->__('Enabled'),
                'disabled' => $this->__('Disabled'),
            );
            break;
        case 'profile_type':
            $options = array(
                'import' => $this->__('Import')
            );
            break;
        case 'run_status':
            $options = array(
                'idle' => $this->__('Idle'),
                'running' => $this->__('Running'),
                'finished' => $this->__('Finished'),
            );
            break;
        case 'separator':
            $options = array(
                ';' => $this->__(';'),
                ',' => $this->__(','),
                '|' => $this->__('|'),
            );
            break;
        case 'invoke_status':
            $options = array(
                'none' => $this->__('None'),
                'ondemand' => $this->__('On Demand'),
            );
            break;
        case 'data_type':
            $dataTypes = Mage::getSingleton('bilna_pricevalidation/config')->getDataTypes();
            foreach ($dataTypes as $k=>$c) {
                $options[$k] = $this->__((string)$c->title);
            }
            break;
        case 'stores':
            $options = $this->getStores();
            break;
        default:
            Mage::throwException($this->__('Invalid request for source options: '.$this->getPath()));
        }
        if ($selector) {
            $options = array(''=>$this->__('* Please select')) + $options;
        }
        return $options;
    }
    public function toOptionArray($selector=false)
    {
        switch ($this->getPath()) {
        }
        return parent::toOptionArray($selector);
    }
    protected $_withDefaultWebsite = true;
    public function withDefaultWebsite($flag)
    {
    	$oldFlag = $this->_withDefaultWebsite;
    	$this->_withDefaultWebsite = (bool)$flag;
    	return $oldFlag;
    }
    
    public function getStores()
    {
        $options = array();
        foreach (Mage::app()->getWebsites((bool)$this->_withDefaultWebsite) as $website) {
            foreach ($website->getStores() as $sId=>$store) {
                $options[$website->getName()][$sId] = '['.$store->getCode().'] '.$store->getName();
                break;
            }break;
        }
        return $options;
    }
}
