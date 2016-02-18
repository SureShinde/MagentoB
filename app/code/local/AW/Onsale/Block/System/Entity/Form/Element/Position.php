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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Onsale_Block_System_Entity_Form_Element_Position extends Varien_Data_Form_Element_Select
{
    /**
     * Retrives element's html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementHtml()
    {
        $select = new AW_Onsale_Block_System_Entity_Form_Element_Position_Render($this->getData());
        $select->setLayout(Mage::app()->getLayout());

        if (Mage::registry('current_product')){            
            $select->setData('name', 'product['.$select->getName().']');
        }

        $html = '';
        $html .= $select->toHtml();

        $html.= $this->getAfterElementHtml();
        return $html;
    }
}