<?php
class Moxy_SocialCommerce_Model_Mysql4_Collectioncategory extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("socialcommerce/collectioncategory", "category_id");
    }
}