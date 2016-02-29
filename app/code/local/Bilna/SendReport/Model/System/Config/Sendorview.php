<?php

class Bilna_SendReport_Model_System_Config_Sendorview {
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'send',
                'label' => 'Send by Email',
            ),
            array(
                'value' => 'show',
                'label' => 'Show on Page',
            ),
        );
    }
}