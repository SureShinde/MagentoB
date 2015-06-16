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


class AW_Onsale_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'onsale';
        $this->_controller = 'adminhtml_rule';
        

        parent::__construct();

        $this->_addButton('save_apply', array(
            'class' => 'save',
            'label' => Mage::helper('onsale')->__('Save and Apply'),
            'onclick' => "$('rule_auto_apply').value=1; editForm.submit()",
        ));

        $this->_addButton('save_and_continue_edit', array(
            'class' => 'save',
            'label' => Mage::helper('onsale')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
                ), 10);
    }

    /**
     * Getter for form header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $rule = Mage::registry('current_onsale_rule');
        if ($rule->getRuleId()) {
            return Mage::helper('onsale')->__("Edit Rule '%s'", $this->escapeHtml($rule->getName()));
        } else {
            return Mage::helper('onsale')->__('New Rule');
        }
    }

}
