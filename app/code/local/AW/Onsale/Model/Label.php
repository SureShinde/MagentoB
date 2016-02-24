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


class AW_Onsale_Model_Label extends Varien_Object
{
    const DEFAULT_PRODUCT_IMAGE_NAME = 'onsale.product.default.bg.png';
    const DEFAULT_CATEGORY_IMAGE_NAME = 'onsale.category.default.bg.png';
    const DEFAULT_IMAGE_WIDTH = 80;
    const DEFAULT_IMAGE_HEIGHT = 80;
    const DEFAULT_PRODUCT_POSITION = 'BR';
    const DEFAULT_CATEGORY_POSITION = 'BR';

    protected static $_ruleDataArray = null;

    public function getForProductPage($product, $websiteId, $customerGroupId)
    {

        if ($product->getData('aw_os_product_display')) {

            $data = array(
                'position' => $product->getData('aw_os_product_position'),
                'image' => $product->getData('aw_os_product_image'),
                'image_path' => $product->getData('aw_os_product_image_path'),
                'text' => $product->getData('aw_os_product_text'),
            );
        } else {

            $label = Mage::getModel('onsale/ruleProduct')
                    ->getCollection()
                    ->addFieldToFilter('main_table.product_id', array("eq" => $product->getId()))
                    ->addTimeFilter()
                    ->addRuleDataForProductPage()
                    ->addFieldToFilter('rule_table.is_active', array("eq" => '1'))
                    ->addFieldToFilter('rule_table.product_page_show', array("eq" => '1'))
                    ->addOrderField('rule_table.sort_order', 'ASC')
                    ->addCustomerGroupId()
                    ->addFieldToFilter('customer_group.customer_group_id', $customerGroupId)
                    ->getFirstItem();
            $data = $label->getData();
        }
        if ($data) {
            $this->addData($data);

            $this->setDefaultImage(self::DEFAULT_PRODUCT_IMAGE_NAME);

            if (!$this->getPosition()) {
                $this->setPosition(self::DEFAULT_PRODUCT_POSITION);
            }
        }
        return $this;
    }

    public function getForCategoryPage($product, $websiteId, $customerGroupId, $productIds = array())
    {

        $data = array();

        if ($product->getData('aw_os_category_display')) {

            $data = array(
                'position' => $product->getData('aw_os_category_position'),
                'image' => $product->getData('aw_os_category_image'),
                'image_path' => $product->getData('aw_os_category_image_path'),
                'text' => $product->getData('aw_os_category_text'),
            );
        } else {

            if (self::$_ruleDataArray === null) {

                self::$_ruleDataArray = array();

                $time = Mage::getModel('core/date')->gmtTimestamp();

                $arr = Mage::getModel('onsale/ruleProduct')
                        ->getCollection()
                        ->addFieldToFilter('main_table.product_id', array("in" => $productIds))
                        ->addTimeFilter()
                        ->addRuleDataForCategoryPage()
                        ->addFieldToFilter('rule_table.is_active', array("eq" => '1'))
                        ->addFieldToFilter('rule_table.category_page_show', array("eq" => '1'))
                        ->addOrderField('rule_table.sort_order', 'ASC')
                        ->addCustomerGroupId()
                        ->addFieldToFilter('customer_group.customer_group_id', $customerGroupId)
                        ->getData();

                foreach ($arr as $a) {
                    if (!isset(self::$_ruleDataArray[$a['product_id']])) {
                        self::$_ruleDataArray[$a['product_id']] = $a;
                    }
                }
            }

            if (self::$_ruleDataArray && isset(self::$_ruleDataArray[$product->getId()])) {
                $data = self::$_ruleDataArray[$product->getId()];
            }
        }

        if ($data) {
            $this->addData($data);

            $this->setDefaultImage(self::DEFAULT_CATEGORY_IMAGE_NAME);

            if (!$this->getPosition()) {
                $this->setPosition(self::DEFAULT_CATEGORY_POSITION);
            }
        }
        return $this;
    }

    public function getImageUrl()
    {
        $imageFile = $this->getImage();
        $imagePath = $this->getData('image_path');
        if ($imageFile) {
            $imageFile = str_replace('../../', '', $imageFile);
            if (strpos($imageFile, '/') === false) {
                $imageFile = 'onsale/uploaded/' . $imageFile;
            }
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $imageFile;
        } elseif ($imagePath) {
            $url = Mage::helper('onsale')->getImagePathUrl($imagePath);
        } else {
            $url = Mage::getDesign()->getSkinUrl('onsale/images/' . $this->getDefaultImage());
        }
        return $url;
    }

    public function getImageSize()
    {
        return Mage::helper('onsale')->getImageSize($this->getImageUrl());
    }

}