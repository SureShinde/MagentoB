<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * Setup attributes data
 */
class Belvg_Brands_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{

    /**
     * Prepare catalog attribute values to save
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'frontend_input_renderer'       => $this->_getValue($attr, 'input_renderer'),
            'is_global'                     => $this->_getValue(
                $attr,
                'global',
                Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            ),
            'is_visible'                    => $this->_getValue($attr, 'visible', 1),
            'is_searchable'                 => $this->_getValue($attr, 'searchable', 0),
            'is_filterable'                 => $this->_getValue($attr, 'filterable', 0),
            'is_comparable'                 => $this->_getValue($attr, 'comparable', 0),
            'is_visible_on_front'           => $this->_getValue($attr, 'visible_on_front', 0),
            'is_wysiwyg_enabled'            => $this->_getValue($attr, 'wysiwyg_enabled', 0),
            'is_html_allowed_on_front'      => $this->_getValue($attr, 'is_html_allowed_on_front', 0),
            'is_visible_in_advanced_search' => $this->_getValue($attr, 'visible_in_advanced_search', 0),
            'is_filterable_in_search'       => $this->_getValue($attr, 'filterable_in_search', 0),
            'used_in_product_listing'       => $this->_getValue($attr, 'used_in_product_listing', 0),
            'used_for_sort_by'              => $this->_getValue($attr, 'used_for_sort_by', 0),
            'apply_to'                      => $this->_getValue($attr, 'apply_to', ''),
            'position'                      => $this->_getValue($attr, 'position', 0),
            'is_configurable'               => $this->_getValue($attr, 'is_configurable', 0),
            'is_used_for_promo_rules'       => $this->_getValue($attr, 'used_for_promo_rules', 0)
        ));
        return $data;
    }

    /**
     * Prepare EAV attributes to save
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        return array(
                'brands' => array(
                        'entity_model' => 'brands/brands',
                        'attribute_model' => 'brands/resource_eav_attribute',
                        'table' => 'brands/brands',
                        'attributes' => array(
                                'title' => array(
                                        'type' => 'varchar',
                                        'backend' => '',
                                        'frontend' => '',
                                        'label' => 'Title',
                                        'input' => 'text',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => TRUE,
                                        'filterable' => TRUE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 1,
                                ),
                                'description' => array(
                                        'type' => 'text',
                                        'backend' => '',
                                        'frontend' => '',
                                        'label' => 'Description',
                                        'input' => 'textarea',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => TRUE,
                                        'filterable' => TRUE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 2,
                                ),
                                'active' => array(
                                        'type' => 'int',
                                        'backend' => '',
                                        'frontend' => '',
                                        'label' => 'Active',
                                        'input' => 'select',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => FALSE,
                                        'filterable' => TRUE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => FALSE,
                                        'unique' => FALSE,
                                        'sort_order' => 3,
                                ),
                                'image' => array(
                                        'type' => 'varchar',
                                        'backend' => 'brands/entity_attribute_backend_image',
                                        'frontend' => '',
                                        'label' => 'Image',
                                        'input' => 'file',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => FALSE,
                                        'filterable' => FALSE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 4,
                                ),
                                'url_key' => array(
                                        'type' => 'varchar',
                                        'backend' => 'brands/entity_attribute_backend_url',
                                        'frontend' => '',
                                        'label' => 'Image',
                                        'input' => 'textarea',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => FALSE,
                                        'filterable' => FALSE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 5,
                                ),
                                'pagetitle' => array(
                                        'type' => 'varchar',
                                        'backend' => '',
                                        'frontend' => '',
                                        'label' => 'Page Meta Title',
                                        'input' => 'textarea',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => FALSE,
                                        'filterable' => FALSE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 6,
                                ),
                                'pagekeyword' => array(
                                        'type' => 'text',
                                        'backend' => '',
                                        'frontend' => '',
                                        'label' => 'Page Meta Keywords',
                                        'input' => 'text',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => FALSE,
                                        'filterable' => FALSE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 7,
                                ),
                                'pagedescription' => array(
                                        'type' => 'text',
                                        'backend' => '',
                                        'frontend' => '',
                                        'label' => 'Page Meta Description',
                                        'input' => 'text',
                                        'class' => '',
                                        'source' => '',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                        'visible' => TRUE,
                                        'required' => TRUE,
                                        'user_defined' => TRUE,
                                        'default' => '',
                                        'searchable' => FALSE,
                                        'filterable' => FALSE,
                                        'comparable' => FALSE,
                                        'visible_on_front' => TRUE,
                                        'unique' => TRUE,
                                        'sort_order' => 8,
                                ),
                        ),
                ),
                'catalog_product' => array(
                        'entity_model' => 'catalog/product',
                        'attribute_model' => 'catalog/resource_eav_attribute',
                        'table' => 'catalog/product',
                        'additional_attribute_table' => 'catalog/eav_attribute',
                        'entity_attribute_collection' => 'catalog/product_attribute_collection',
                        'attributes' => array(
                                'brands' => array(
                                        'group' => 'General',
                                        'label' => 'Brand',
                                        'type' => 'int',
                                        'input' => 'select',
                                        'default' => '0',
                                        'class' => '',
                                        'backend' => 'brands/entity_attribute_product_backend_unit',
                                        'frontend' => '',
                                        'source' => 'brands/entity_attribute_product_source_unit',
                                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                                        'visible' => TRUE,
                                        'required' => FALSE,
                                        'user_defined' => TRUE,
                                        'searchable' => TRUE,
                                        'filterable' => TRUE,
                                        'is_filterable' => TRUE,
                                        'is_filterable_in_search' => TRUE,
                                        'comparable' => TRUE,
                                        'visible_on_front' => FALSE,
                                        'visible_in_advanced_search' => TRUE,
                                        'unique' => FALSE,
                                        'sort_order' => 600,
                                )
                        )
                ),
        );
    }

}