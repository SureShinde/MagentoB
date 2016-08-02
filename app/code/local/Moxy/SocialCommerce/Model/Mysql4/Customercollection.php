<?php
class Moxy_SocialCommerce_Model_Mysql4_Customercollection extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("socialcommerce/customercollection", "map_id");
    }
}
