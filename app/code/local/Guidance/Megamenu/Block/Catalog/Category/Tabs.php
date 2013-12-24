<?php
/**
 * @author      Guidance Magento Team <magento@guidance.com>
 * @category    Guidance
 * @package     Megamenu
 * @copyright   Copyright (c) 2013 Guidance Solutions (http://www.guidance.com)
 */
class Guidance_Megamenu_Block_Catalog_Category_Tabs extends Mage_Adminhtml_Block_Catalog_Category_Tabs
{
    /**
     * Override to show our featured product select above the grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tabs
     */
    protected function _prepareLayout()
    {
        $categoryAttributes = $this->getCategory()->getAttributes();
        if (!$this->getCategory()->getId()) {
            foreach ($categoryAttributes as $attribute) {
                $default = $attribute->getDefaultValue();
                if ($default != '') {
                    $this->getCategory()->setData($attribute->getAttributeCode(), $default);
                }
            }
        }

        $attributeSetId     = $this->getCategory()->getDefaultAttributeSetId();
        /** @var $groupCollection Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection */
        $groupCollection    = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();
        $defaultGroupId = 0;
        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            if ($defaultGroupId == 0 or $group->getIsDefault()) {
                $defaultGroupId = $group->getId();
            }
        }

        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            $attributes = array();
            foreach ($categoryAttributes as $attribute) {
                /* @var $attribute Mage_Eav_Model_Entity_Attribute */
                if ($attribute->isInGroup($attributeSetId, $group->getId())
                    && $attribute->getAttributeCode() != 'featuredproduct' && $attribute->getAttributeCode() != 'staticblock' && $attribute->getAttributeCode() != 'shortname') {
                    $attributes[] = $attribute;
                }
            }

            // do not add groups without attributes
            if (!$attributes) {
                continue;
            }

            $active  = $defaultGroupId == $group->getId();
            $block = $this->getLayout()->createBlock($this->getAttributeTabBlock(), '')
                ->setGroup($group)
                ->setAttributes($attributes)
                ->setAddHiddenFields($active)
                ->toHtml();
            $this->addTab('group_' . $group->getId(), array(
                'label'     => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                'content'   => $block,
                'active'    => $active
            ));
        }

        $attribute = array(Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'featuredproduct'));
        $attributeStatic = array(Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'staticblock'));
        $attributes = array_merge($attribute, $attributeStatic);
        $this->addTab('group_category_products', array(
            'label'   => Mage::helper('catalog')->__('Category Products / Mega Menu Feature'),
            'content' => $this->getLayout()->createBlock(
                'guidance_megamenu/catalog_category_tab_product', 'megamenu.product.grid')
                ->setAttributes($attributes)
                ->setGroup($group)
                ->setAddHiddenFields($active)
                ->toHtml(),
            'active' => false,
        ));

        // dispatch event add custom tabs
        Mage::dispatchEvent('adminhtml_catalog_category_tabs', array(
            'tabs'  => $this
        ));

        return Mage_Adminhtml_Block_Widget_Tabs::_prepareLayout();
    }
}
