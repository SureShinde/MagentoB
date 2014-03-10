<?php  

class Bilna_Barcode_Block_Adminhtml_Upload extends Mage_Adminhtml_Block_Template {

	public function __construct() {
		parent::__construct();
	
		$this->setTemplate('barcode/upload.phtml');
		$this->setFormAction(Mage::getUrl('*/*/save'));
	}
}