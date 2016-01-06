<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Mandiriecash
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Mandiriecash extends Mage_Payment_Model_Method_Banktransfer {
    protected $_code = 'mandiriecash';
    protected $_formBlockType = 'paymethod/form_mandiriecash';
    protected $_infoBlockType = 'paymethod/info_mandiriecash';
}
