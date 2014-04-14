<?php

class Bilna_Staticarea_Model_Source_Status{
    const ENABLED = 1;
    const DISABLED = 0;

    const ENABLED_LABEL = 'Enabled';
    const DISABLED_LABEL = 'Disabled';

    public function toOptionArray() {
        $_helper = Mage::helper('staticarea');
        return array(
            array('value' => self::ENABLED, 'label' => $_helper->__(self::ENABLED_LABEL)),
            array('value' => self::DISABLED, 'label' => $_helper->__(self::DISABLED_LABEL))
        );
    }

    public function toShortOptionArray() {
        $_options = array();
        foreach($this->toOptionArray() as $option)
            $_options[$option['value']] = $option['label'];
        return $_options;
    }
}
