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


class AW_Affiliate_Block_Adminhtml_Campaign_Edit_Tab_Productsgrid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awaffiliate_productsselector');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        //$this->setRowInitCallback('awfBForm.productGridRowInit.bind(awfBForm)');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareLayout()
    {
        $ret = parent::_prepareLayout();
        //$this->getChild('search_button')->setData('onclick', 'awfBForm.awf_filter()');
        return $ret;
    }

    protected function __escape($str)
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->quote($str);
    }

    protected function getWebsite()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->getWebsite();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
        ;
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }
        $_visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );
        $collection->addAttributeToFilter('visibility', $_visibility);

        $this->setCollection($collection);

        parent::_prepareCollection();
//$collection->printLogQuery(true);         
        //$this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection() && $column->getId() == 'websites') {
            $this->getCollection()->joinField(
                'websites',
                'catalog/product_website',
                'website_id',
                'product_id=entity_id',
                null,
                'left'
            );
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'products',
            array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'products[]',
                'align' => 'center',
                'index' => 'entity_id',
                'filter' => false,
                'disabled' => true,
                'renderer' => 'AW_Affiliate_Block_Campaign_Widget_Grid_Column_Renderer_Checkbox'
            )
        );

        /*$this->addColumn(
            'products',
            array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'products[]',
                'align' => 'center',
                'index' => 'entity_id',
                'renderer' => 'AW_Affiliate_Block_Widget_Grid_Column_Renderer_Checkbox'
            )
        );*/

        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('awfeatured')->__('ID'),
                'sortable' => true,
                'width' => '60',
                'index' => 'entity_id'
            )
        );

        $this->addColumn(
            'name',
            array(
                'header' => Mage::helper('awfeatured')->__('Product Name'),
                'index' => 'name'
            )
        );

        $this->addColumn(
            'sku',
            array(
                'header' => Mage::helper('awfeatured')->__('SKU'),
                'width' => '80',
                'index' => 'sku'
            )
        );

        $store = $this->_getStore();
        $this->addColumn(
            'price',
            array(
                'header' => Mage::helper('awfeatured')->__('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                array(
                    'header' => Mage::helper('awfeatured')->__('Websites'),
                    'width' => '100px',
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                )
            );
        }

        $this->addColumn(
            'image',
            array(
                'header' => $this->__('Product Image'),
                'width' => '150',
                'index' => 'entity_id',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'AW_Featured_Block_Widget_Grid_Column_Renderer_Imagepreview',
            )
        );
    }

    protected function _filterCheckedCondition($collection, $column)
    {
        if (!$this->getRequest()->getParam('awf_ids')) {
            $_data = Mage::getSingleton('adminhtml/session')->getData(AW_Featured_Helper_Data::FORM_DATA_KEY);
            if (is_array($_data)) {
                $_data = new Varien_Object($_data);
            }
            $fpFilter = array();
            if (is_object($_data)) {
                $fProducts = $_data->getAutomationData();
                if (is_array($fProducts) && array_key_exists('products', $fProducts)) {
                    $fpFilter = explode(',', $fProducts['products']);
                }
            }
        } else {
            $fpFilter = explode(',', $this->getRequest()->getParam('awf_ids'));
        }
        // if NO selected
        if ($column->getFilter()->getValue() == 0) {
            $collection->addAttributeToFilter(
                array(
                    array('attribute' => 'entity_id', 'nin' => $fpFilter),
                )
            );
            return;
        }
        // if YES selected   
        if ($column->getFilter()->getValue() == 1) {
            $collection->addAttributeToFilter(
                array(
                    array('attribute' => 'entity_id', 'in' => $fpFilter),
                )
            );
            return;
        }
    }

    protected function _getStore()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', array('_current' => true));
    }

    public function getRowUrl($item)
    {
        return null;
    }
}
