<?php

class Bilna_Customer_Model_Api2_Customer extends Mage_Api2_Model_Resource
{

    /**
     * Resource specific method to retrieve attributes' codes.
     * May be overriden in child.
     *
     * @return array
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE != $this->getUserType(), true);
    }
}
