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

class AW_Customerattributes_Block_Adminhtml_Customer_Attribute_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_customer_attribute';
        $this->_blockGroup = 'aw_customerattributes';
        parent::__construct();

        $this->_addButton(
            'saveandcontinueedit',
            array(
                 'label'   => $this->__('Save and Continue Edit'),
                 'onclick' => "saveAndContinueEdit('{$this->getSaveAndContinueUrl()}')",
                 'class'   => 'save',
                 'id'      => 'aw_sarp2-save-and-continue',
            ),
            -200
        );
    }

    public function getHeaderText()
    {
        if ($this->getAttribute()->getId()) {
            return $this->__('Edit Customer Attribute "%s"', $this->escapeHtml($this->getAttribute()->getLabel()));
        } else {
            return $this->__('Create Customer Attribute');
        }
    }

    protected function _prepareLayout()
    {
        $tabsBlockJsObject = 'attribute_tabsJsTabs';
        $tabsBlockPrefix = 'attribute_tabs_';

        $this->_formScripts[] = "
            function saveAndContinueEdit(urlTemplate) {
                var tabsIdValue = {$tabsBlockJsObject}.activeTab.id;
                var tabsBlockPrefix = '{$tabsBlockPrefix}';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = template.evaluate({tab_id:tabsIdValue});
                editForm.submit(url);
            }
        ";
        return parent::_prepareLayout();
    }

    public function getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            array(
                 '_current'   => true,
                 'back'       => 'edit',
                 'active_tab' => '{{tab_id}}',
            )
        );
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }

    public function getAttribute()
    {
        return Mage::registry('current_attribute');
    }
}