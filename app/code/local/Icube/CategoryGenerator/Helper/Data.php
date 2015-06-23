<?php
class Icube_CategoryGenerator_Helper_Data extends Mage_Core_Helper_Abstract
{
    const FORM_DATA_KEY = 'generator_form_data';
    protected $logfile = 'icube_categorygenerator.log';

    public function removeEmptyItems($var)
    {
        return !empty($var);
    }

    public function prepareArray($var)
    {
        if (is_string($var)) {
            $var = @explode(',', $var);
        }
        if (is_array($var)) {
            $var = array_unique($var);
            $var = array_filter($var, array(Mage::helper('categorygenerator'), 'removeEmptyItems'));
            $var = @implode(',', $var);
        }
        return $var;
    }

    public function logprogress($message) {
        Mage::log($message, null, $this->logfile);
    }	
}
