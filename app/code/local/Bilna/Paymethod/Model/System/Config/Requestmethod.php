<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Requestmethod
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Requestmethod {
    public function toOptionArray() {
        $data = array ('GET', 'POST');
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
