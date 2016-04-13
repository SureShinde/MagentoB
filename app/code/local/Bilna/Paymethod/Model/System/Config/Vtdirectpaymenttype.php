<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Paymentactive
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Vtdirectpaymenttype {
    public function toOptionArray() {
        $types = array(
            array(
                'label' => 'Bank Transfer',
                'value' => 'bank_transfer'
            ),
            array(
                'label' => 'Credit Card',
                'value' => 'credit_card'
            )
        );
        
        return $types;
    }
}
