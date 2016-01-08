<?php


class Bilna_Staticarea_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
		$this->_controller = "adminhtml_manage";
		$this->_blockGroup = "staticarea";
		$this->_headerText = Mage::helper("staticarea")->__("Manage Static Area");
		$this->_addButtonLabel = Mage::helper("staticarea")->__("Add New Static Area");
		parent::__construct();
	}

}