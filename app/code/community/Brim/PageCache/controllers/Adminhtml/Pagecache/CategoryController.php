<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


require_once('Mage/Adminhtml/controllers/Catalog/CategoryController.php');

class Brim_PageCache_Adminhtml_Pagecache_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{
    public function flushAction() {


        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $category = Mage::getModel('catalog/category')->load($id);

                /** @var Brim_PageCache_Model_Engine $engine */
                $engine = Mage::getSingleton('brim_pagecache/engine');

                $tags = array(
                    Brim_PageCache_Model_Engine::FPC_TAG . '_CATEGORY_' . $category->getId(),
                    Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_CATEGORY_' . $category->getId(),
                );
                $engine->getCacheInstance()->clean($tags);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('brim_pagecache')->__('The category has been flushed.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/catalog_category/edit', array('_current'=>true, 'id'=>$id)));
                return;
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('brim_pagecache')->__('An error occurred while trying to flush the category.'));
                $this->getResponse()->setRedirect($this->getUrl('*/catalog_category/edit', array('_current'=>true, 'id'=>$id)));
                return;
            }
        }

        $this->getResponse()->setRedirect($this->getUrl('*/catalog_category/edit', array('_current'=>true, 'id'=>$id)));
    }
}