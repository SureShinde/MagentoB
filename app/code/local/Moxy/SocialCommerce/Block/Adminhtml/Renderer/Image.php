<?php
class Moxy_SocialCommerce_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	public function render(Varien_Object $row)   {
		$html = '<img height="100px" id="' . $this->getColumn()->getCoverId() . '" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$row->getData($this->getColumn()->getIndex()) . '"';
		$html .= '/>';
		return $html;
	}
}
?>
