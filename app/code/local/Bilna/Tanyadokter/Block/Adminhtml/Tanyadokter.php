<?php
class Bilna_Tanyadokter_Block_Adminhtml_Tanyadokter extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_blockGroup = 'bilna_tanyadokter';
	$this->_controller = 'adminhtml_tanyadokter';
    //$this->_headerText = Mage::helper('bilna_tanyadokter')->__('Tanya Dokter Manager');
	$this->_headerText = $this->__('Tanya Dokter Manager');
    parent::__construct();
	 $this->_removeButton('add'); 
  }
}