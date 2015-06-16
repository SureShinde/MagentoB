<?php
/**
 * Description of Bilna_Visa_Model_System_Config_Validationoption
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Model_System_Config_Validationoption {
    public function toOptionArray() {
        return array (
            array ('value' => 'hours', 'label' => 'Hours'),
            array ('value' => 'minutes', 'label' => 'Minutes')
        );
    }
}
