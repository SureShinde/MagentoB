<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Amasty_Xlanding_Block_Adminhtml_Page_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $model = Mage::registry('amlanding_page');

        /* @var $helper Amasty_Xlanding_Helper_Data */
        $helper = Mage::helper('amlanding');

        /*
         * Menu 
         */

        /*
          $fieldset = $form->addFieldset('menu', array(
          'legend' => Mage::helper('amlanding')->__('Menu'),
          ));

          $fieldset->addField('include_menu', 'select', array(
          'label'     => $helper->__('Include In Top Menu'),
          'title'     => $helper->__('Include In Top Menu'),
          'name'      => 'include_menu',
          'options'   => $helper->getMenuPositions()
          ));

          $fieldset->addField('include_menu_position', 'text', array(
          'label'     => $helper->__('Sort Order'),
          'title'     => $helper->__('Sort Order'),
          'note'      => $helper->__('Specify order of landing pages included in top menu'),
          'name'      => 'include_menu_position',
          ));
         */

        /*
         * Page Design
         */
        $fieldset = $form->addFieldset('page_design', array(
            'legend' => Mage::helper('amlanding')->__('Page Design'),
        ));

        $fieldset->addField('columns_count', 'text', array(
            'name' => 'columns_count',
            'label' => $helper->__('Columns Count'),
            'note' => $helper->__('Count of columns in products grid')
        ));

        $fieldset->addField('include_navigation', 'select', array(
            'name' => 'include_navigation',
            'label' => $helper->__('Include Navigation'),
            'values' => Mage::getSingleton('amlanding/source_navigation')->toFlatArray(),
        ));

        $options = Mage::getResourceModel('cms/block_collection')->load();
        $identifiersList = array();
        $identifiersList[] = array(
            'value' => '',
            'label' => Mage::helper('amlanding')->__('Please select a static block ...')
        );
        foreach ($options as $option) {
            $identifiersList[] = array(
                'value' => $option->getIdentifier(),
                'label' => $option->getTitle()
            );
        }

        $fieldset->addField('layout_static_top', 'select', array(
            'name' => 'layout_static_top',
            'label' => Mage::helper('amlanding')->__('Top Static Block'),
            'values' => $identifiersList,
            'note' => Mage::helper('amlanding')->__('Choose Static Block to show Above Products List'),
        ));

        $fieldset->addField('layout_static_bottom', 'select', array(
            'name' => 'layout_static_bottom',
            'label' => Mage::helper('amlanding')->__('Bottom Static Block'),
            'values' => $identifiersList,
            'note' => Mage::helper('amlanding')->__('Choose Static Block to show Below Products List'),
        ));

        $fieldset->addField('banner', 'image', array(
            'label' => Mage::helper('amlanding')->__('Banner'),
            'required' => false,
            'name' => 'banner'
        ));
        $fieldset->addField('background', 'image', array(
            'label' => Mage::helper('amlanding')->__('Background'),
            'required' => false,
            'name' => 'background'
        ));
        $fieldset->addField('rack', 'image', array(
            'label' => Mage::helper('amlanding')->__('Rack'),
            'required' => false,
            'name' => 'rack'
        ));

        /*
         * Layout Design
         */
        $layoutFieldset = $form->addFieldset('layout_fieldset', array(
            'legend' => Mage::helper('amlanding')->__('Page Layout'),
            'class' => 'fieldset-wide',
        ));


        $layoutFieldset->addField('root_template', 'select', array(
            'name' => 'root_template',
            'label' => Mage::helper('amlanding')->__('Layout'),
            'required' => true,
            'values' => Mage::getSingleton('page/source_layout')->toOptionArray(),
        ));
        if (!$model->getId()) {
            $model->setRootTemplate(Mage::getSingleton('page/source_layout')->getDefaultValue());
        }

        $layoutFieldset->addField('layout_update_xml', 'textarea', array(
            'name' => 'layout_update_xml',
            'label' => Mage::helper('amlanding')->__('Layout Update XML'),
            'style' => 'height:24em;',
        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return Mage::helper('amlanding')->__('Design');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return Mage::helper('amlanding')->__('Design');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden() {
        return false;
    }

}
