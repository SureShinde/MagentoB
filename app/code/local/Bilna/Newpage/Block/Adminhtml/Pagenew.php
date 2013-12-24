<?php


class Bilna_Newpage_Block_Adminhtml_Pagenew extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_pagenew";
	$this->_blockGroup = "newpage";
	$this->_headerText = Mage::helper("newpage")->__("Pagenew Manager");
	$this->_addButtonLabel = Mage::helper("newpage")->__("Add New Item");
	parent::__construct();
	
	}

}