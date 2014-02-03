<?php
 class Alw_Customercity_Model_Mysql4_Customercity extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('customercity/customercity', 'id');
    }
}
