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
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Block_Adminhtml_Widget_Blockchooser extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct($arguments = array()) {
        parent::__construct($arguments);
        $this->setUseAjax(true);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('awislider_admin/adminhtml_widget/blockchooser', array('uniq_id' => $uniqId));

        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);


        if ($element->getValue()) {
            $slider = Mage::getModel('awislider/slider')->load((int)$element->getValue());
            if ($slider->getId()) {
                $chooser->setLabel($slider->getName());
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;;
    }

    public function getRowClickCallback() {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var blockTitle = trElement.down("td").next().innerHTML;
                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                '.$chooserJsObject.'.setElementValue(blockId);
                '.$chooserJsObject.'.setElementLabel(blockTitle);
                '.$chooserJsObject.'.close();
            }
        ';
        return $js;
    }

    protected function _prepareCollection() {
        $this->setCollection(Mage::getModel('awislider/slider')->getCollection()->addEnabledFilter());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'index' => 'id',
            'width' => '100px'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name'
        ));

        $this->addColumn('block_id', array(
            'header' => $this->__('Block ID'),
            'index' => 'block_id'
        ));

        if(!Mage::app()->isSingleStoreMode())
            $this->addColumn('store', array(
                'header' => $this->__('Store View'),
                'index' => 'store',
                'sortable' => FALSE,
                'type' => 'store',
                'store_all' => TRUE,
                'store_view' => TRUE,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoreCondition')
            ));

        $this->addColumn('is_active', array(
            'header' => $this->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => Mage::getModel('awislider/source_status')->toShortOptionArray(),
            'filter' => false,
            'width' => '200px'
        ));

        return parent::_prepareColumns();
    }

    protected function _filterStoreCondition($collection, $column) {
        if(!($value = $column->getFilter()->getValue())) return;
        $collection->addStoreFilter($value);
    }

    public function getGridUrl() {
        return $this->getUrl('awislider_admin/adminhtml_widget/blockchooser', array('_current'=>true));
    }
}
