<?php
class RocketWeb_Netsuite_Block_Rewrite_Adminhtml_Tax_Rate_Grid extends Mage_Adminhtml_Block_Tax_Rate_Grid {
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter('tax_city', array(
            'header'        => Mage::helper('tax')->__('City'),
            'align'         =>'left',
            'index'         => 'tax_city',
        ),'region_name');

        $this->sortColumnsByOrder();

        return $this;
    }
}