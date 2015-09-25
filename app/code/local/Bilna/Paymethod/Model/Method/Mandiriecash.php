<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Mandiriecash
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Mandiriecash extends Bilna_Paymethod_Model_Method_Vtdirect {
    protected $_code = 'mandiri_ecash';
    protected $_formBlockType = 'paymethod/form_mandiriecash';
    protected $_infoBlockType = 'paymethod/info_mandiriecash';
}
