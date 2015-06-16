<?php

class AW_Affiliate_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'awaffiliate';
        $this->_headerText = Mage::helper('awaffiliate')->__('Manage Categories');
        parent::__construct();
    }
}
