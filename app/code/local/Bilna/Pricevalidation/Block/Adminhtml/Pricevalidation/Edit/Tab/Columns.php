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
        $groups = array();

        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
        $attrs = $entityType->getAttributeCollection();
        $fields = array();
        $hidden = array();
        $removeFields = array('has_options', 'required_options', 'category_ids', 'minimal_price');
        if ($this->getProfile()->getProfileType()=='import') {
            $removeFields = array_merge($removeFields, array('created_at', 'updated_at'));
        }
        foreach ($attrs as $k=>$a) {
            $attr = $a->toArray();
            if ($attr['frontend_input']=='gallery' || in_array($attr['attribute_code'], $removeFields)) {
                continue;
            }
            if (empty($attr['frontend_label'])) {
                $attr['frontend_label'] = $attr['attribute_code'];
            }
            if (in_array($attr['frontend_input'], array('select', 'multiselect'))) {
                try {
                    if (!$a->getSource()) {
                        continue;
                    }
                    $opts = $a->getSource()->getAllOptions();
                    foreach ($opts as $o) {
                        if (is_array($o['value'])) {
                            foreach ($o['value'] as $o1) {
                                $attr['options'][$o['label']][$o1['value']] = $o1['label'];
                            }
                        } elseif (is_scalar($o['value'])) {
                            $attr['options'][$o['value']] = $o['label'];
                        }
                    }
                } catch (Exception $e) {
                    // can be all kinds of custom source models, just ignore
                }
            }
            if (!empty($attr['is_visible'])) {
                $fields[$attr['attribute_code']] = $attr;
            } else {
                unset($attr['is_required']);
                $hidden[$attr['attribute_code']] = $attr;
            }
        }
        $groups['attributes'] = array('label'=>$this->__('Product Attributes'), 'fields'=>$fields);
        $groups['hidden'] = array('label'=>$this->__('Hidden Attributes'), 'fields'=>$hidden);

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
