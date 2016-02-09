<?php

class Moxy_SocialCommerce_Model_Mysql4_Profile_Collection
extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('socialcommerce/profile');
    }
}
