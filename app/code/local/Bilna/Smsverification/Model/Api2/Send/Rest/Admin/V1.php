<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Smsverification_Model_Api2_Send_Rest_Admin_V1 extends Bilna_Smsverification_Model_Api2_Send_Rest
{
    protected function _create(array $data)
    {
        print json_encode($data);exit;
        return true;
    }

}
