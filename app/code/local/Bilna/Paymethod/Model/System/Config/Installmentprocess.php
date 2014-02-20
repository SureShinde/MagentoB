<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Installmentprocess
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Installmentprocess {
    public function toOptionArray() {
        $data = array ('manual', 'automatic');
        $result = array ();
        
        foreach ($data as $dt) {
            $result[] = array (
                'value' => $dt,
                'label' => $dt
            );
        }
        
        return $result;
    }
}
