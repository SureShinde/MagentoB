<?php


class Bilna_Customreports_Block_Adminhtml_Couponsreport extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
        $this->_controller = 'adminhtml_couponsreport';
		$this->_blockGroup = "customreports";
        $this->_headerText = Mage::helper('customreports')->__('Coupons Report');
        parent::__construct();
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('customreports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
	}

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/*', array('_current' => true));
    }

}