<?php
class Bilna_Promo_Block_Adminhtml_Giftvoucherapplicant extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_giftvoucherapplicant';
		$this->_blockGroup = 'bilnapromo';
		$this->_headerText = Mage::helper('promo')->__('Gift Voucher Applicant');
		parent::__construct();
		$this->_removeButton('add');
	}
}