<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Emailtransaction
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Cod_Model_System_Config_Emailtransaction {
    public function toOptionArray() {
        $templateCollection = Mage::getResourceSingleton('core/email_template_collection');
        $templateInfo = array ();
        
        foreach ($templateCollection as $template) {
            $templateInfo[] = array (
                'label' => $template->getTemplateCode(),
                'value' => $template->getTemplateId()
            );
        }
        
        return $templateInfo;
    }
}
