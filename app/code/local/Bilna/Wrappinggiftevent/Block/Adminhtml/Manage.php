<?php


class Bilna_Wrappinggiftevent_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
		$this->_controller = "adminhtml_manage";
		$this->_blockGroup = "wrappinggiftevent";
		$this->_headerText = Mage::helper("wrappinggiftevent")->__("Manage Wrap");
		$this->_addButtonLabel = Mage::helper("wrappinggiftevent")->__("Add New Wrap");
		parent::__construct();
	}

}