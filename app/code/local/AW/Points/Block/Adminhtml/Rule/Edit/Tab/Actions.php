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
 * @package    AW_Points
 * @version    1.6.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Adminhtml_Rule_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('points_rule_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $helper = Mage::helper('points');

        $fieldset = $form->addFieldset('action_fieldset', array('legend' => $helper->__('Actions')));

        $fieldset->addField('apply_on', 'select', array(
        		'label'     => Mage::helper('salesrule')->__('Apply'),
        		'name'      => 'apply_on',
        		'options'    => array(
        				Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('salesrule')->__('Percent of product price discount'),
        				Mage_SalesRule_Model_Rule::BY_PERCENT_PRODUCT => Mage::helper('salesrule')->__('Percent point reward for qualifying items'),
        				Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount'),
        				Mage_SalesRule_Model_Rule::BY_FIXED_PRODUCT => Mage::helper('salesrule')->__('Fixed point reward for each item qualified items'),
        				Mage_SalesRule_Model_Rule::CART_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
        		),
        ));
        $fieldset->addField('points_change', 'text', array(
            'label' => $helper->__('Add Reward Points'),
            'name' => 'points_change',
        ));
        $fieldset->addField('max_points_change', 'text', array(
            'label' => $helper->__('Max Reward Points'),
            'name' => 'max_points_change',
        ));

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
