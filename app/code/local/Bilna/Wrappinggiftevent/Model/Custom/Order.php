<?php
class Bilna_Wrappinggiftevent_Model_Custom_Order extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('wrappinggiftevent/custom_order');
	}
	
	public function deleteByOrder($order_id){
		$this->_getResource()->deteleByOrder($order_id,$var);
	}
	
	public function getByOrder($order_id){
		return $this->_getResource()->getByOrder($order_id);
	}
	
	
}