<?php
class Bilna_Staticarea_Model_Mysql4_Contents_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
		$this->_init("staticarea/contents");
	}

	public function addContentFilter($staticarea_id) {
        $this->getSelect()->where('staticarea_id = ?', $staticarea_id);
        return $this;
    }
}