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
 * Brands backend controller
 */
class Belvg_Brands_Adminhtml_BrandsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Check ACL rules
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
                        ->isAllowed('catalog/brands');
    }

    /**
     * Set page title
     *
     * @return Belvg_Brands_Adminhtml_BrandsController
     */
    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('catalog/brands')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Catalog'), Mage::helper('brands')->__('Brands'))
                ->_title(Mage::helper('brands')->__('Manage Brands'));
        return $this;
    }

    /**
     * Index action, grid view
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Edit / create brand page
     *
     * @return void
     */
    public function editAction()
    {
        $brands_id = (int) $this->getRequest()->getParam('id', 0);

        $store_id = $this->getStoreId();
        $brands_model = Mage::getModel('brands/brands')
                ->setStoreId($store_id);
        /* @var $brands_model Belvg_Brands_Model_Brands */

        if ($brands_id) {
            $brands_model->load($brands_id);
        }

        if ($brands_model->getId() || $brands_id == 0) {
            Mage::register('brands_data', $brands_model);

            $this->_initAction();
            $this->renderLayout();
        } else {
            $this->_getSession()->addError(Mage::helper('brands')->__('Item does not exist'));

            $this->_redirect('*/*/');
        }
    }

    /**
     * New brand plcaseholder
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save brand
     *
     * @return void
     */
    public function saveAction()
    {
        $store_id = $this->getStoreId();

        $brand_id = (int) $this->getRequest()->getParam('id', 0);

        $redirectBack = $this->getRequest()->getParam('back', FALSE);

        try {
            $post_data = $this->getRequest()->getPost();

            $post_data['id'] = $brand_id;
            $post_data['store_id'] = $store_id;

            $brand = Mage::getModel('brands/brands')->setData($post_data);
            /* @var $brand Belvg_Brands_Model_Brands */

            $validate = $brand->validate();

            if ($validate === TRUE) {
                $brand->prepareBrand();
                $brand->setId($brand_id)->save();

                $success_message = Mage::helper('adminhtml')->__('Item was successfully saved');

                $this->_getSession()
                        ->addSuccess($success_message)
                        ->setBrandsData(FALSE);

                Mage::dispatchEvent('brands_controller_brand_save', array('brand' => $brand, 'store' => $store_id));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $brand->getID(), 'store' => $store_id, '_current' => TRUE));
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                Mage::throwException(implode('<br />', $validate));
            }
        } catch (Exception $e) {
            $this->_getSession()
                    ->addError($e->getMessage())
                    ->setBrandsData($this->getRequest()->getPost());
            $this->_redirect('*/*/edit', array('id' => $brand_id, 'store' => $store_id));
        }
    }

    /**
     * Delete brand
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($brand_id = (int) $this->getRequest()->getParam('id', 0)) {
            try {
                $brand = Mage::getModel('brands/brands');
                /* @var $brands_model Belvg_Brands_Model_Brands */

                Mage::dispatchEvent('brands_controller_brand_delete', array('brand' => $brand));
                $brand->setId($brand_id)->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('brands')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Process mass delete action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $brands_ids = $this->getRequest()->getParam('brand', NULL);
        if (!is_array($brands_ids)) {
            $this->_getSession()->addError($this->__('Please select brands(s).'));
        } else {
            if (!empty($brands_ids)) {
                try {
                    foreach ($brands_ids as $brands_id) {
                        $brand = Mage::getSingleton('brands/brands')->load($brands_id);
                        /* @var $brand Belvg_Brands_Model_Brands */

                        Mage::dispatchEvent('brands_controller_brand_delete', array('brand' => $brand));
                        $brand->delete();
                    }

                    $this->_getSession()->addSuccess(
                            $this->__('Total of %d record(s) have been deleted.', count($brands_ids))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Process mass change status action
     *
     * @return void
     */
    public function massStatusAction()
    {
        $brands_ids = $this->getRequest()->getParam('brand', NULL);

        $storeId = $this->getStoreId();

        $status = (int) $this->getRequest()->getParam('status', 0);

        if (!is_array($brands_ids)) {
            $this->_getSession()->addError($this->__('Please select brands(s).'));
        } else {
            if (!empty($brands_ids)) {
                try {
                    foreach ($brands_ids as $brands_id) {
                        $brand = Mage::getSingleton('brands/brands')->setStoreId($storeId)->load($brands_id);
                        /* @var $brand Belvg_Brands_Model_Brands */

                        $brand->setActive($status)->setId($brands_id)->save();
                        Mage::dispatchEvent('brands_controller_brand_status_update', array('brand' => $brand, 'store' => $storeId, 'status' => $status));
                    }

                    $this->_getSession()->addSuccess(
                            $this->__('Total of %d record(s) have been updated.', count($brands_ids))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*/index', array('store' => $storeId));
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return parent::_getSession();
    }

    /**
     * Return current store id
     *
     * @return int
     */
    private function getStoreId()
    {
        return (int) $this->getRequest()->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID);
    }

}
