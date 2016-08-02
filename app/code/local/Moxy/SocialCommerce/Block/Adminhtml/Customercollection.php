<?php


class Moxy_SocialCommerce_Block_Adminhtml_Customercollection extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
    	$this->_controller = "adminhtml_customercollection";
    	$this->_blockGroup = "socialcommerce";
    	$this->_headerText = Mage::helper("socialcommerce")->__("Customercollection Manager");
    	parent::__construct();
        $this->_removeButton("add");
	}
}
