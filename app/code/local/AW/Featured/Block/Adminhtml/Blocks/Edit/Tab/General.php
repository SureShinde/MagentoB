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


class AW_Featured_Block_Adminhtml_Blocks_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::getSingleton('adminhtml/session')->getData(AW_Featured_Helper_Data::FORM_DATA_KEY);
        if (!is_object($_data)) {
            $_data = new Varien_Object($_data);
        }

        $_fieldset = $_form->addFieldset('block_fieldset', array('legend' => $this->__('General')));
        $_fieldset->addField(
            'block_name', 'text',
            array(
                'name' => 'block_name',
                'label' => $this->__('Name'),
                'required' => TRUE
            )
        );

        $_fieldset->addField(
            'block_id', 'text',
            array(
                'name' => 'block_id',
                'label' => $this->__('Block ID'),
                'required' => TRUE
            )
        );
        if (is_null($_data->getIsActive())) {
            $_data->setIsActive(TRUE);
        }

        $_fieldset->addField(
            'is_active', 'select',
            array(
                'name' => 'is_active',
                'label' => $this->__('Status'),
                'required' => TRUE,
                'values' => Mage::getModel('awfeatured/source_status')->toOptionArray()
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $_fieldset->addField(
                'store', 'multiselect',
                array(
                    'name'      => 'store[]',
                    'label'     => $this->__('Store View'),
                    'required'  => TRUE,
                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
                )
            );
        } else {
            if ($_data->getStore() && is_array($_data->getStore())) {
                $_stores = $_data->getStore();
                if (isset($_stores[0]) && $_stores[0] != '') {
                    $_stores = $_stores[0];
                } else {
                    $_stores = 0;
                }
                $_data->setStore($_stores);
            }

            $_fieldset->addField('store', 'hidden', array('name' => 'store[]'));
        }
        if (!$_data->getStore()) {
            $_data->setStore(0);
        }

        $_fieldset->addField(
            'type', 'select',
            array(
                'name' => 'type',
                'label' => $this->__('Representation'),
                'values' => Mage::getModel('awfeatured/source_representation')->toOptionArray()
            )
        );

        $_fieldset->addField(
            'autoposition', 'select',
            array(
                'name' => 'autoposition',
                'label' => $this->__('Automatic layout position'),
                'values' => Mage::getModel('awfeatured/source_autoposition')->toOptionArray()
            )
        );

        $_fieldset = $_form->addFieldset('types_data_fset', array('legend' => $this->__('Representation settings')));
        $_fieldset->setFieldsetContainerId('awf_types_settings');

        $_fieldset->addField(
            'types_data', 'note',
            array(
                'text' => $this->__('No representation has been selected')
            )
        );
        $_form->setValues($_data);
    }
}
