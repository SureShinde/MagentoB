<?php
class Bilna_Promo_Block_Adminhtml_Giftvoucher extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_giftvoucher';
		$this->_blockGroup = 'bilnapromo';
		$this->_headerText = Mage::helper('promo')->__('Manage Event');
		$this->_addButtonLabel = Mage::helper('promo')->__('Add Giftvoucher Event');
		parent::__construct();
	}
}