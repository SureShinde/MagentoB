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


class AW_Onsale_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('onsale_rule_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection AW_Onsale_Model_Mysql4_Rule_Collection */
        $collection = Mage::getModel('onsale/rule')
                ->getResourceCollection();
        $collection->addWebsitesToResult();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => Mage::helper('onsale')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rule_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('onsale')->__('Rule Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('from_date', array(
            'header' => Mage::helper('onsale')->__('Date Start'),
            'align' => 'left',
            'width' => '120px',
            'type' => 'date',
            'default' => '--',
            'index' => 'from_date',
        ));

        $this->addColumn('to_date', array(
            'header' => Mage::helper('onsale')->__('Date Expire'),
            'align' => 'left',
            'width' => '120px',
            'type' => 'date',
            'default' => '--',
            'index' => 'to_date',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('onsale')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('onsale')->__('Active'),
                0 => Mage::helper('onsale')->__('Inactive')
            ),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('rule_website', array(
                'header' => Mage::helper('onsale')->__('Website'),
                'align' => 'left',
                'index' => 'website_ids',
                'type' => 'options',
                'sortable' => false,
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'width' => 200,
                'filter_condition_callback' => array($this, '_websiteFilterCallback'),
            ));
        }

        parent::_prepareColumns();
        return $this;
    }

    protected function _websiteFilterCallback($collection, $column)
    {
        $val = $column->getFilter()->getValue();
        if ($val) {
            $collection->getSelect()->join(
                    array('w' => $collection->getTable('onsale/website'))
                    , 'main_table.rule_id = w.rule_id'
                    , array('w.website_id')
            );
            $collection->addFieldToFilter('website_id', $val);
        }
    }

    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
    }

}
