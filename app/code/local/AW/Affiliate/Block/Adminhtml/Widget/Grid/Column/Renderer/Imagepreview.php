<?php
/**
 *
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    
 * @copyright  Copyright (c) PT Bilna (http://www.bilna.com)
 * @license    
 */


class AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Imagepreview
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function _getValue(Varien_Object $row)
    {
        $_errMsg = $this->__('No Image');
        $_wrapper = '<div class="a-center">%s</div>';
        try {
            $data = parent::_getValue($row);
            if ($data) {
                $_product = Mage::getModel('catalog/product')->load($data);
                if ($_product->getData()) {
                    $_productImages='<img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(120).'" width="60px" height="60px" style="z-index: 200" />';
                }
            }
        } catch(Exception $ex) {
            $_imurl = false;
            $_errMsg = $ex->getMessage();
        }

        $image = $_productImages;
        return sprintf(
            $_wrapper, $image
        );
    }
}
