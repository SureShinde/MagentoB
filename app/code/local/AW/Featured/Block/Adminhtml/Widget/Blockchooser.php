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


class AW_Featured_Block_Adminhtml_Widget_Blockchooser extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($arguments = array())
    {
        parent::__construct($arguments);
        $this->setUseAjax(true);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('awfeatured/adminhtml_widget/blockchooser', array('uniq_id' => $uniqId));

        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId)
        ;

        if ($element->getValue()) {
            $block = Mage::getModel('awfeatured/blocks')->load((int)$element->getValue());
            if ($block->getId()) {
                $chooser->setLabel($block->getBlockName());
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    public function getRowClickCallback()
    {
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

    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('awfeatured/blocks')->getCollection()->setEnabledFilter());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => $this->__('ID'),
                'index' => 'id',
                'width' => '100px'
            )
        );

        $this->addColumn(
            'block_name',
            array(
                'header' => $this->__('Name'),
                'index' => 'block_name'
            )
        );

        $this->addColumn(
            'block_id',
            array(
                'header' => $this->__('Block ID'),
                'index' => 'block_id'
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store',
                array(
                    'header' => $this->__('Store View'),
                    'index' => 'store',
                    'sortable' => FALSE,
                    'type' => 'store',
                    'store_all' => TRUE,
                    'store_view' => TRUE,
                    'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                    'filter_condition_callback' => array($this, '_filterStoreCondition')
                )
            );
        }

        $this->addColumn(
            'representation',
            array(
                'header' => $this->__('Representation'),
                'index' => 'type',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_representation')->toShortOptionArray()
            )
        );

        $this->addColumn(
            'autoposition',
            array(
                'header' => $this->__('Automatic Position'),
                'index' => 'autoposition',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_autoposition')->toShortOptionArray()
            )
        );

        $this->addColumn(
            'automation_type',
            array(
                'header' => $this->__('Automation Type'),
                'index' => 'automation_type',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_automation')->toShortOptionArray()
            )
        );
        return parent::_prepareColumns();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $collection->setStoreFilter($value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('awfeatured/adminhtml_widget/blockchooser', array('_current'=>true));
    }
}
