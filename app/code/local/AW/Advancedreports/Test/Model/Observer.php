<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Advancedreports_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Instance of tested object
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    /**
     * Test productDeleteBefore()
     * Try to delelete product and check aw_ar_sku_relevance table
     *
     * @loadFixture
     * @return void
     */
    public function testProductDeleteBefore()
    {
        $product = Mage::getModel('catalog/product')->load(1);

        $event = new Varien_Object(array('product'=>$product));
        /** @var AW_Advancedreports_Model_Observer $observer  */
        $observer = Mage::getSingleton('advancedreports/observer');
        $observer->productDeleteBefore($event);

        /** @var AW_Advancedreports_Model_Sku $skuRelevance  */
        $sku = Mage::getModel('advancedreports/sku');
        $tableName = $sku->getResource()->getMainTable();
        $readAdapter = $this->_helper()->getReadAdapter();
        
        $select = new Zend_Db_Select($readAdapter);
        $select->from($tableName, array('count'=>'COUNT(*)'));
        $count = $readAdapter->fetchOne($select);
        $this->assertEquals(2, $count, "Sku relation not removed");

        $product->delete();
        $this->assertEventDispatched('catalog_product_delete_before', "Event dispatched");

    }

    /**
     * Test productSaveAfter()
     * Try to update product and check aw_ar_sku_relevance table
     *
     * @loadFixture
     * @return void
     */
    public function testProductSaveAfter()
    {
        $product = Mage::getModel('catalog/product')->load(1);
        $product->setSku('new_book_sku');
        $product->save();
        $this->assertEventDispatched('catalog_product_save_after');
        $event = new Varien_Object(array('product'=>$product));
        /** @var AW_Advancedreports_Model_Observer $observer  */
        $observer = Mage::getSingleton('advancedreports/observer');
        $observer->productSaveAfter($event);
        $skuModel = Mage::getModel('advancedreports/sku')->load('new_book_sku', 'sku');
        $this->assertEquals('1', $skuModel->getId(), "Sku Model loaded with right value");
    }

}