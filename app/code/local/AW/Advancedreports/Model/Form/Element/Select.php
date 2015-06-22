<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Advancedreports_Model_Form_Element_Select extends Varien_Data_Form_Element_Select
{
    public function getElementHtml()
    {       
        $this->_data['disabled'] = Mage::helper('advancedreports/setup')->isDefault($this->getId());
        return parent::getElementHtml().$this->_getDefaultCheckbox();
    }

    protected function _getDefaultCheckbox()
    {
        $html = '</td><td class="value use-default">';      
        $html .= Mage::helper('advancedreports/setup')->getCheckboxScopeHtml($this, $this->getFieldName(), $this->getDisabled());
        return $html;
    }



}
