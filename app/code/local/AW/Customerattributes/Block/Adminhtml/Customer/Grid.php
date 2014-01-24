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

class AW_Customerattributes_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    public function setCollection($collection)
    {
        $collection = Mage::getResourceModel('aw_customerattributes/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->addAttributesToCustomerCollection()
        ;
        return parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this
            ->removeColumn('Telephone')
            ->removeColumn('billing_postcode')
            ->removeColumn('billing_country_id')
            ->removeColumn('billing_region')
            ->removeColumn('customer_since')
            ->removeColumn('action')
        ;

        foreach ($this->getCustomerAttributeCollection() as $attribute) {
            $gridRenderer = $attribute->unpackData()->getTypeModel()->getBackendGridRenderer();
            $this->addColumnAfter($gridRenderer->getColumnId(), $gridRenderer->getColumnProperties(), 'website_id');
        }

        $this->addColumn(
            'action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => 'adminhtml/customer/edit'),
                        'field'   => 'id',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );

        $this->sortColumnsByOrder();
        return $this;
    }

    protected function getCustomerAttributeCollection()
    {
        return Mage::helper('aw_customerattributes/customer')->getAttributeCollectionForCustomerGrid();
    }

    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
        }
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getId()));
    }
}