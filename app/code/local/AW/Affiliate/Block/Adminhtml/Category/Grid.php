<?php

class AW_Affiliate_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awaffCategoryGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId(1)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');
        $collection->getSelect()
            ->joinInner(
                array( 'awaffiliate_cat' => Mage::getSingleton('core/resource')->getTableName('awaffiliate/categories') ),
                "e.entity_id = awaffiliate_cat.category_id",
                array(
                    "category_id" => "awaffiliate_cat.category_id"
                )
            );
     
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('awaffiliate');
        
        $this->addColumn('id', array(
            'header' => Mage::helper('awaffiliate')->__('Category ID'),
            'index' => 'entity_id',
            'type' => 'number',
            'width' => '25px'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('awaffiliate')->__('Category Name'),
            'index' => 'name'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return parent::getGridUrl();
    }

    public function cccccgetRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }

}
