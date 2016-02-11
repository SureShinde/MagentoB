<?php
class Bilna_Wrappinggiftevent_Model_Custom_Quote extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('wrappinggiftevent/custom_quote');
	}
	
	public function deteleByQuote($quote_id)
	{
		$this->_getResource()->deteleByQuote($quote_id,$var);
	}
	
	public function getByQuote($quote_id)
	{
		return $this->_getResource()->getByQuote($quote_id);
	}
}