<?php
/**
 * @author      Guidance Magento Team <magento@guidance.com>
 * @category    Guidance
 * @package     Megamenu
 * @copyright   Copyright (c) 2013 Guidance Solutions (http://www.guidance.com)
 */

class Guidance_Megamenu_Block_Html_Topmenu extends Mage_Page_Block_Html_Topmenu
{
    /** @var array of product urls */
    protected $_productUrl = array();

    /**
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string|Varien_Data_Tree_Node
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        Mage::dispatchEvent('megamenu_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }

    /**
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @return string|Varien_Data_Tree_Node
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        return $menuTree;
    }

    /**
     * create block for subcategory list and category attribute display
     * @param $child
     * @return mixed
     */
    protected function getSubMenuHtml($child)
    {
        return $this->getLayout()
            ->createBlock('guidance_megamenu/html_topmenu')
            ->setTemplate('page/html/submenu.phtml')
            ->setData('child', $child)
            ->toHtml();
    }

    /**
     * Add featured product data to the category nodes
     *
     * @param Varien_Data_Tree_Node_Collection $children
     * @return Varien_Data_Tree_Node_Collection
     */
    public function addFeaturedProductData($children)
    {
        $catIds = array();
        foreach ($children as $child) {
            $code = $child->getId();
            $codeParts = explode('-', $code);
            $id = end($codeParts);
            $catIds[$code] = $id;
        }

        $productData = $this->getProductData($catIds);

        foreach ($children as $child) {
            $code = $child->getId();
            if (!empty($productData[$code])) {
                $child->setData('featured_product', $productData[$code]);
            }
        }

        return $children;
    }

    /**
     * Get the featured product data from an array of category ids
     *
     * @param array $catIds assoc. array of cat code => cat id
     * @return array $result assoc. array of cat id => array of product info
     */
    protected function getProductData($catIds)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $resource->getConnection('core_read');
        /** @var $select Varien_Db_Select */
        $select = $db->select();

        $select->from(
            array('category' => $resource->getTableName('catalog/category')),
            array('entity_id' => 'category.entity_id')
        );
        $this->joinCategoryAttributeToSelect($select, 'featuredproduct');
        $this->joinProductAttributeToSelect($select,  'name');
        $this->joinProductAttributeToSelect($select,  'image');
        $this->joinProductAttributeToSelect($select,  'short_description');
        $this->joinProductAttributeToSelect($select,  'url_key');

        $select->where('category.entity_id IN (?)', $catIds);

        $results = array();
        $catKeys = array_flip($catIds);
        $stmt = $db->query($select);
        while ($row = $stmt->fetch()) {
            $key = $catKeys[$row['entity_id']];
            $data = array(
                'product_id'                => $row['featuredproduct'],
                'product_name'              => $row['name'],
                'product_image'             => $row['image'],
                'product_url_key'           => $row['url_key'],
                'product_short_description' => $row['short_description'],
            );
            $results[$key] = array_filter($data);
        }

        return $results;
    }

    /**
     * Join a category attribute to select object
     *
     * @param Varien_Db_Select $select
     * @param string $attrCode the attribute code
     * @return Varien_Db_Select
     */
    protected function joinCategoryAttributeToSelect($select, $attrCode)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', $attrCode);
        $attrId = $attribute->getAttributeId();
        $select->joinLeft(
            array($attrCode => $attribute->getBackendTable()),
            '(' . $attrCode . '.entity_id = category.entity_id) AND (' . $attrId . ' = ' . $attrCode . '.attribute_id)',
            array($attrCode => $attrCode . '.value')
        );

        return $select;
    }

    /**
     * Join a product attribute to select object
     *
     * @param Varien_Db_Select $select
     * @param string $attrCode the attribute code
     * @return Varien_Db_Select
     */
    protected function joinProductAttributeToSelect($select, $attrCode)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attrCode);
        $attrId = $attribute->getAttributeId();
        $select->joinLeft(
            array($attrCode => $attribute->getBackendTable()),
            '(' . $attrCode . '.entity_id = featuredproduct.value) AND (' . $attrId . ' = ' . $attrCode . '.attribute_id)',
            array($attrCode => $attrCode . '.value')
        );

        return $select;
    }

    /**
     * Get the url to a product from it's url key and product id attributes
     *
     * @param string $urlKey the product url key
     * @param int $productId
     * @return string product url
     */
    public function getProductUrl($urlKey, $productId)
    {
        if (isset($this->_productUrl[$productId])) {
            return $this->_productUrl[$productId];
        }

        /** @var $urlModel Enterprise_Catalog_Model_Product_Url */
        $urlModel = Mage::getModel('catalog/product')->getUrlModel();

        $routePath = $urlKey;
        $routeParams = array();
        $routeParams['_query']  = array();
        $storeId = Mage::app()->getStore()->getId();

        $requestPath = Mage::helper('enterprise_catalog')->getProductRequestPath($routePath, $storeId);
        $this->_productUrl[$productId] = $urlModel->getUrlInstance()->getDirectUrl($requestPath, $routeParams);

        return $this->_productUrl[$productId];
    }

    /**
     * Get a product image, resized
     *
     * @param string $image the product image
     * @param int $size the size needed
     * @return string $imageUrl the url to the resized image
     */
    public function getProductImage($image, $size)
    {
        $model = Mage::getModel('catalog/product_image');

        if ($model->isCached()) {
            return $model->getUrl();
        }

        try {
            $model->setWidth($size)
                ->setBaseFile($image)
                ->setQuality(100)
                ->setDestinationSubdir('image')
                ->resize();
            $imageUrl = $model->saveFile()->getUrl();

        } catch (Exception $e) {
            //got nothing
            $imageUrl = '';
        }

        return $imageUrl;
    }
}