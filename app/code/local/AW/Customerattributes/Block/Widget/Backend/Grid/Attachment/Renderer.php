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


class AW_Customerattributes_Block_Widget_Backend_Grid_Attachment_Renderer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        if ($row->getData($this->getColumn()->getIndex())) {
            $downloadUrl = $this->_getAttachmentDownloadUrl($row);
            $filename = $row->getData($this->getColumn()->getIndex());
            return "<a href=\"{$downloadUrl}\">{$filename}</a>";
        }
        return $this->__('No Attachment');
    }

    private function _getAttachmentDownloadUrl($row)
    {
        return Mage::getModel('adminhtml/url')->getUrl(
            '*/adminhtml_customer/downloadAttachment',
            array(
                'attribute_code' => $this->getColumn()->getIndex(),
                'customer_id'    => $row->getEntityId(),
            )
        );
    }
}