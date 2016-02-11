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

class AW_Islider_Block_Adminhtml_Slider_Edit_Tabs_Images extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('awislider_images')
            ->setSaveParametersInSession(true)
            ->setDefaultSort('sort_order', 'ASC')
            ->setUseAjax(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('location', array(
            'header' => $this->__('Image preview'),
            'index' => 'location',
            'width' => '150px',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Imagepreview'
        ));

        $this->addColumn('is_active', array(
            'header' => $this->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => Mage::getModel('awislider/source_status')->toShortOptionArray(),
            'width' => '200px',
            'sortable' => false
        ));

        $this->addColumn('url', array(
            'header' => $this->__('URL'),
            'index' => 'url',
            'sortable' => false
        ));

        $this->addColumn('active_from', array(
            'header' => $this->__('Active From'),
            'index' => 'active_from',
            'type' => 'date',
            'sortable' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('active_to', array(
            'header' => $this->__('Active To'),
            'index' => 'active_to',
            'type' => 'date',
            'sortable' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('clicks_total', array(
            'header' => $this->__('Total Clicks'),
            'index' => 'clicks_total',
            'width' => '150px',
            'sortable' => false
        ));

        $this->addColumn('clicks_unique', array(
            'header' => $this->__('Unique Clicks'),
            'index' => 'clicks_unique',
            'width' => '150px',
            'sortable' => false
        ));

        $this->addColumn('sort_order', array(
            'header' => $this->__('Sort Order'),
            'index' => 'sort_order',
            'width' => '150px'
        ));

        if(Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new')) {
            $this->addColumn('actions', array(
                'header' => $this->__('Actions'),
                'width' => '100px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Remove'),
                        'url' => array('base' => '*/adminhtml_image/remove', 'params' => array('continue_tab' => 'images', 'pid' => $this->getRequest()->getParam('id'))),
                        'field' => 'id',
                        'confirm' => $this->__('Are you sure you want do this?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $_collection = Mage::getModel('awislider/image')->getCollection();
        if($this->getRequest()->getParam('id')) {
            $_collection->addSliderFilter($this->getRequest()->getParam('id'));
        } else {
            $_collection->addSliderFilter(-1);
        }
        $this->setCollection($_collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->getUrl('*/adminhtml_image/grid', array('id' => $this->getRequest()->getParam('id')));
    }

    public function getRowUrl($row) {
        return 'javascript:awISAjaxForm.showForm(null, '.$row->getId().');';
    }
}
