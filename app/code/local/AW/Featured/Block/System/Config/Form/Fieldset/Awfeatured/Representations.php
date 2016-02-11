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
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_System_Config_Form_Fieldset_Awfeatured_Representations
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $_helper = Mage::helper('awfeatured');
        $representationsList = Mage::getSingleton('awfeatured/representations_config')->getRepresentations();

        $html .= '<table class="form-list" cellspacing="">';
        $i = 0;
        foreach ($representationsList as $r) {
            $html .= '<tr>
                <td class="label">
                    <label for="awr'.$i.'">
                        <img src="'.$this->getSkinUrl('aw_all/images/ok.gif').'" />
                        <span id="awr'.$i.'">'.$_helper->__($r->getLabel()).'</span>
                    </label>
                </td>
                <td class="value">'.$r->getVersion().'</td>
            </tr>';
        }
        $html .= '</table>';
        $html .= '<a href="' . $this->getUrl('awfeatured/adminhtml_blocks/refreshcache', array('configuration' => true))
            . '">' . $_helper->__('Clear Configuration Cache') . '</a><br />'
        ;
        $html .= '<a href="' . $this->getUrl('awfeatured/adminhtml_blocks/refreshcache', array('thumbnails' => true))
            . '">' . $_helper->__('Clear Thumbnails Cache') . '</a>'
        ;
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
