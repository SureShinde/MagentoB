<?php


class Moxy_SocialCommerce_Block_Adminhtml_Collectioncover extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_collectioncover";
	$this->_blockGroup = "socialcommerce";
	$this->_headerText = Mage::helper("socialcommerce")->__("Collectioncover Manager");
	$this->_addButtonLabel = Mage::helper("socialcommerce")->__("Add New Item");
	parent::__construct();
	
	}

	public function saveAction() {
		echo "bla bla bla";
	}
}
