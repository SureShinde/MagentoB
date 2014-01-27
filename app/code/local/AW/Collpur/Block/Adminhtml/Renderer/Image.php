<?php
	class AW_Collpur_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	   public function render(Varien_Object $row)   {
			$html = '<img style="width:100px;height:100px;" id="' . $this->getColumn()->getId() . '"
			src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."aw_collpur/deals/".$row->getData($this->getColumn()->getIndex()) . '"';
			$html .= '/>';
			return $html;
		}
	}
?>