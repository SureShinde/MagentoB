<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('category_generator_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('categorygenerator/generator')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('categorygenerator')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('categorygenerator')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('description', array(
            'header' => Mage::helper('categorygenerator')->__('Description'),
            'align' => 'left',
            'index' => 'description',
        ));
        
        $this->addColumn('is_active', array(
            'header' => Mage::helper('categorygenerator')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('categorygenerator')->__('Active'),
                0 => Mage::helper('categorygenerator')->__('Inactive')
            ),
        ));

        parent::_prepareColumns();
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
}