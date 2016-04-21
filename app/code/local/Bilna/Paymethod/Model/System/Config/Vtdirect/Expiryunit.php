<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Paymentactive
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Vtdirect_Expiryunit {
    public function toOptionArray() {
        $types = array(
            array(
                'label' => 'day',
                'value' => 'day'
            ),
            array(
                'label' => 'hour',
                'value' => 'hour'
            ),
            array(
                'label' => 'minute',
                'value' => 'minute'
            )
        );
        
        return $types;
    }
}
