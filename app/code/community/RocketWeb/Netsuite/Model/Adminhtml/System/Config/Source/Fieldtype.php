<?php
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Fieldtype {
    const FIELD_TYPE_STANDARD = 'standard';
    const FIELD_TYPE_CUSTOM = 'custom';

    public function toOptionArray()
    {
        return array(
            array('value'=>self::FIELD_TYPE_STANDARD,'label'=>'Standard'),
            array('value'=>self::FIELD_TYPE_CUSTOM,'label'=>'Custom')
        );
    }
}