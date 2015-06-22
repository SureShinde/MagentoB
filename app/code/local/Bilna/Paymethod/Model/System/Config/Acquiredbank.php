<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Acquiredbank
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Acquiredbank {
    public function toOptionArray() {
        $banks = array ('bni', 'cimb', 'mandiri');
        $result = array ();
        
        foreach ($banks as $bank) {
            $result[] = array (
                'value' => $bank,
                'label' => strtoupper($bank)
            );
        }
        
        return $result;
    }
}
