<?php
class Alw_Customercity_Model_Customercity extends Mage_Core_Model_Abstract
{
      public function _construct()
	  {
	    parent::_construct();
        $this->_init('customercity/customercity');
	  }

}