<?php

class Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Edit_Tab_Renderer_Incategory extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if (empty($value)) {
            return "No";
        }
        if ($this->getRequest()->getParams()['id'] != $value) {
            return "No";
        } else {
            return "Yes";
        }
    }
}
