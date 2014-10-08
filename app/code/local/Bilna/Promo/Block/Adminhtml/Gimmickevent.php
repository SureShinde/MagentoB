<?php
class Bilna_Promo_Block_Adminhtml_Gimmickevent extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_gimmickevent';
		$this->_blockGroup = 'bilnapromo';
		$this->_headerText = Mage::helper('promo')->__('Gimmick Event');
		parent::__construct();
		$this->_removeButton('add'); 
	}
}