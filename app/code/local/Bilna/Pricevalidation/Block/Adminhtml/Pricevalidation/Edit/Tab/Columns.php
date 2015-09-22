<?php

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Tab_Columns
    extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bilna_pricevalidation/columns.phtml');
    }

    public function getColumnsFields()
    {
        $groups = array(
            'attributes' => array(
                'label' => 'Product Attributes',
                'fields' => array(
                    'SKU' => array(
                        'attribute_code' => 'SKU',
                        'backend_type' => 'varchar',
                        'is_required' => 1
                    ),
                    'price' => array(
                        'attribute_code' => 'price',
                        'backend_type' => 'double',
                        'is_required' => 0
                    ),
                    'cost' => array(
                        'attribute_code' => 'cost',
                        'backend_type' => 'double',
                        'is_required' => 0
                    ),
                    'special_price' => array(
                        'attribute_code' => 'special_price',
                        'backend_type' => 'double',
                        'is_required' => 0
                    ),
                    'ignore_flag' => array(
                        'attribute_code' => 'ignore_flag',
                        'backend_type' => 'int',
                        'is_required' => 0
                    ),
                    'new_from_date' => array(
                        'attribute_code' => 'new_from_date',
                        'backend_type' => 'date',
                        'is_required' => 0
                    ),
                    'new_to_date' => array(
                        'attribute_code' => 'new_to_date',
                        'backend_type' => 'date',
                        'is_required' => 0
                    ),
                    'special_from_date' => array(
                        'attribute_code' => 'special_from_date',
                        'backend_type' => 'date',
                        'is_required' => 0
                    ),
                    'special_to_date' => array(
                        'attribute_code' => 'special_to_date',
                        'backend_type' => 'date',
                        'is_required' => 0
                    ),
                    'enabled' => array(
                        'attribute_code' => 'status',
                        'backend_type' => 'string',
                        'is_required' => 0
                    )
                )
            )
        );


        return $groups;
    }

    public function sortFields($a, $b)
    {
        return $a['frontend_label']<$b['frontend_label'] ? -1 : ($a['frontend_label']>$b['frontend_label'] ? 1 : 0);
    }

    public function getColumns()
    {
        return (array)$this->getProfile()->getColumns();
    }
}
