<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Customerattributes_Block_Widget_Backend_Grid_Image_Renderer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        if ($row->getData($this->getColumn()->getIndex())) {
            $imageWidth = $this->getColumn()->getData('image_width');
            $imageHeight = $this->getColumn()->getData('image_height');
            return '<img' .
                ($imageWidth ? (' width="' . $imageWidth . '"') : '') .
                ($imageHeight ? (' height="' . $imageHeight . '"') : '') .
                ' src="' . $this->_getImageUrl($row) . '" />'
            ;
        }
        return $this->__('No Image');
    }

    private function _getImageUrl($row)
    {
        $imageWidth = $this->getColumn()->getData('image_width');
        $imageHeight = $this->getColumn()->getData('image_height');
        return Mage::helper('aw_customerattributes/image')->resizeImage(
            $this->getColumn()->getIndex(),
            $row->getData($this->getColumn()->getIndex()),
            $row->getData('entity_id'),
            $imageWidth,
            $imageHeight
        );
    }
}