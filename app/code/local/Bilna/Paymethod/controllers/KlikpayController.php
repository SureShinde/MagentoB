<?php
/**
 * Description of Bilna_Paymethod_KlikpayController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_KlikpayController extends Mage_Core_Controller_Front_Action {
    public function redirectAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
