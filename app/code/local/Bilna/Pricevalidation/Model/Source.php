<?php
class Bilna_Pricevalidation_Model_Source extends Bilna_Pricevalidation_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
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
        default:
            Mage::throwException($this->__('Invalid request for source options: '.$this->getPath()));
        }
        if ($selector) {
            $options = array(''=>$this->__('* Please select')) + $options;
        }
        return $options;
    }
    protected $_withDefaultWebsite = true;
    public function withDefaultWebsite($flag)
    {
    	$oldFlag = $this->_withDefaultWebsite;
    	$this->_withDefaultWebsite = (bool)$flag;
    	return $oldFlag;
    }
}
