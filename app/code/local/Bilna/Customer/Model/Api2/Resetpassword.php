<?php
class Bilna_Customer_Model_Api2_Resetpassword extends Mage_Api2_Model_Resource
{

    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE != $this->getUserType(), true);
    }
}
