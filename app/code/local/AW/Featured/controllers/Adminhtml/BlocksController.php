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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Adminhtml_BlocksController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('catalog/awfeatured');
    }

    private function hasErrors()
    {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function indexAction()
    {
        return $this->_redirect('awfeatured/adminhtml_blocks/list');
    }

    protected function newAction()
    {
        return $this->_redirect('*/*/edit');
    }

    protected function duplicateAction()
    {
        if ($this->getRequest()->getParam('id')) {
            $_block = Mage::getModel('awfeatured/blocks');
            $_block->load($this->getRequest()->getParam('id'));
            $_block->setData('id', null);
            $_block->setData('is_active', false);
            $_block->save();
            $_block->setData(
                'block_name', AW_Featured_Helper_Data::FORM_DUPLICATE_NAME .$_block->getBlockName()
            );
            $_block->setData(
                'block_id', AW_Featured_Helper_Data::FORM_DUPLICATE_ID  . $_block->getBlockId() . '_' . $_block->getId()
            );
            $_block->save();
            
            $this->_getSession()->addSuccess($this->__('Block succesfully saved and duplicated'));
            return $this->_redirect(
                '*/*/edit',
                array(
                    'id' => $_block->getId(),
                    'continue_tab' => $this->getRequest()->getParam('continue_tab')
                )
            );
        }
        return $this->_redirect('*/*/list');
    }

    protected function editAction()
    {
        if (!$this->getRequest()->getParam('fswe')
            || !$this->_getSession()->getData(AW_Featured_Helper_Data::FORM_DATA_KEY)) {
            $_formData = Mage::getModel('awfeatured/blocks')->load($this->getRequest()->getParam('id'));
            $this->_getSession()->setData(AW_Featured_Helper_Data::FORM_DATA_KEY, $_formData);
        }
        if (!$this->getRequest()->getParam('id')) {
            $this->_getSession()->addNotice($this->__('You can change product images right after saving this block'));
            $this->_title($this->__('Featured Products'))->_title($this->__('Add Block'));
        } else {
            $this->_title($this->__('Featured Products'))->_title($this->__('Edit Block'));
        }
        $this->_initAction();
        $this
            ->_addContent($this->getLayout()->createBlock('awfeatured/adminhtml_blocks_edit'))
            ->_addLeft($this->getLayout()->createBlock('awfeatured/adminhtml_blocks_edit_tabs'))
        ;
        $this->renderLayout();
    }

    protected function gettypeoptionsAction()
    {
        if (!$this->_validateFormKey()) {
            $this->getResponse()->setRawHeader('HTTP/1.1 403 Forbidden');
            return;
        }
        $html = Mage::getSingleton('core/layout')
            ->createBlock('awfeatured/adminhtml_blocks_edit_tab_general_typeoptions')
            ->setData('type', $this->getRequest()->getParam('type'))
            ->setData('is_init', $this->getRequest()->getParam('isInit'))
            ->toHtml()
        ;
        $this->getResponse()->setBody(Zend_Json::encode(array('text' => $html)));
        return;
    }

    protected function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/list');
        }
        $_data = array();
        $_request = $this->getRequest();
        if ($_request->getParam('block_name')) {
            $_data['block_name'] = $_request->getParam('block_name');
            if ($_request->getParam('block_id')) {
                $_data['block_id'] = $_request->getParam('block_id');
                if (preg_match('/^[a-zA-Z0-9-_]*$/', $_data['block_id'])) {
                    if (!is_null($_request->getParam('store'))) {
                        $_data['store'] = $_request->getParam('store');
                        $_blocks = Mage::getModel('awfeatured/blocks')->getCollection();
                        $_blocks->setStoreFilter($_data['store'], true)
                                ->setBlockIdFilter($_data['block_id'])
                                ->setIdNotFilter($this->getRequest()->getParam('id'))
                        ;
                        if ($_blocks->getSize() == 0) {
                            if ($_request->getParam('type')) {
                                $_data['type'] = $_request->getParam('type');
                                $_data['type_data'] = $_request->getParam('type_data');
                                if (!is_null($_request->getParam('autoposition'))) {
                                    $_data['autoposition'] = $_request->getParam('autoposition');
                                    if (!is_null($_request->getParam('automation_type'))) {
                                        $_data['automation_type'] = $_request->getParam('automation_type');
                                        $_automationData = $_request->getParam('automation_data');
                                        if ($_data['automation_type'] == AW_Featured_Model_Source_Automation::NONE) {
                                            if (is_array($_automationData) && $_automationData['products']) {
                                                $_products = Mage::helper('awfeatured')->prepareArray(
                                                    $_automationData['products']
                                                );
                                                $_data['automation_data'] = array(
                                                    'products' => $_products,
                                                    'product_sorting_type'=>$_request->getParam('product_sorting_type')
                                                );
                                            } else {
                                                $this->_getSession()->addError($this->__('Products aren\'t specified'));
                                            }
                                        } else {
                                            if (is_array($_automationData) && $_automationData['quantity_limit']
                                                && ($_data['automation_type'] == AW_Featured_Model_Source_Automation::CURRENTCATEGORY
                                                    || ($_data['automation_type'] != AW_Featured_Model_Source_Automation::CURRENTCATEGORY && $_request->getParam('category_ids')))) {
                                                if ($_data['automation_type'] == AW_Featured_Model_Source_Automation::CURRENTCATEGORY) {
                                                    $_categories = null;
                                                } else {
                                                    $_categories = Mage::helper('awfeatured')->prepareArray($_request->getParam('category_ids'));
                                                }
                                                if ($_categories || $_data['automation_type'] == AW_Featured_Model_Source_Automation::CURRENTCATEGORY) {
                                                    $_data['automation_data'] = array(
                                                        'categories' => $_categories,
                                                        'current_category_type' => $_request->getParam('current_category_type'),
                                                        'quantity_limit' => $_automationData['quantity_limit']
                                                    );
                                                } else {
                                                    $this->_getSession()->addError(
                                                        $this->__('No one category has been selected')
                                                    );
                                                }
                                            } else {
                                                $this->_getSession()->addError(
                                                    $this->__('Categories aren\'t specified or quantity limit is empty')
                                                );
                                            }
                                        }
                                    } else {
                                        $this->_getSession()->addError(
                                            $this->__('Automation type isn\'t specified')
                                        );
                                    }
                                } else {
                                    $this->_getSession()->addError(
                                        $this->__('Automatic layout position isn\'t specified')
                                    );
                                }
                            } else {
                                $this->_getSession()->addError(
                                    $this->__('Representation isn\'t specified')
                                );
                            }
                        } else {
                            $this->_getSession()->addError(
                                $this->__('Block with the same ID already exists in selected store views')
                            );
                        }
                    } else {
                        $this->_getSession()->addError(
                            $this->__('Store ID isn\'t specified')
                        );
                    }
                } else {
                    $this->_getSession()->addError(
                        $this->__('The following symbols are allowed to be used in the \'Block ID\' field: a-z 0-9 - _')
                    );
                }
            } else {
                $this->_getSession()->addError($this->__('Block ID isn\'t specified'));
            }
        } else {
            $this->_getSession()->addError($this->__('Block name can\'t be empty'));
        }

        if ($this->hasErrors()) {
            $this->_getSession()->setData(AW_Featured_Helper_Data::FORM_DATA_KEY, $_request->getParams());
            return $this->_redirect('*/*/edit', array('id' => $_request->getParam('id'), 'fswe' => 1));
        } else {
            $_block = Mage::getModel('awfeatured/blocks');
            if ($_request->getParam('id')) {
                $_block->load($_request->getParam('id'));
                $_data['id'] = $_block->getId();
            }
            $_block->setData($_data);
            $_block->setData('is_active', intval($this->getRequest()->getParam('is_active')));
            $_block->save();

            if ($this->getRequest()->getParam('duplicate')) {
                return $this->_redirect(
                    '*/*/duplicate',
                    array(
                         'id' => $_block->getId(),
                        'continue_tab' => $this->getRequest()->getParam('continue_tab')
                    )
                );
            }

            $this->_getSession()->addSuccess($this->__('Block succesfully saved'));
            if ($this->getRequest()->getParam('continue')) {
                return $this->_redirect(
                    '*/*/edit',
                    array(
                         'id' => $_block->getId(),
                        'continue_tab' => $this->getRequest()->getParam('continue_tab')
                    )
                );
            } else {
                return $this->_redirect('*/*/list');
            }
        }
    }

    protected function listAction()
    {
        $this->_title($this->__('Featured Products'))->_title($this->__('List Blocks'));
        $this->_initAction();
        $this->renderLayout();
    }

    protected function deleteAction()
    {
        $_block = Mage::getModel('awfeatured/blocks');
        if ($this->getRequest()->getParam('id')) {
            $_block->load($this->getRequest()->getParam('id'));
            if ($_block->getData() != array()) {
                $_block->delete();
                $this->_getSession()->addSuccess('Block has been successfully removed');
            } else {
                $this->_getSession()->addError('Can\'t load block by given ID');
            }
        } else {
            $this->_getSession()->addError('ID isn\'t specified');
        }

        return $this->_redirect('*/*/list');
    }

    protected function productsgridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('awfeatured/adminhtml_blocks_edit_tab_automation_productsgrid')
                ->toHtml()
        );
    }

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('awfeatured/adminhtml_blocks_edit_tab_automation_categoriesgrid')
                    ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function refreshcacheAction()
    {
        if ($this->getRequest()->getParam('configuration')) {
            Mage::getModel('awfeatured/representations_config')->refreshCache();
            $this->_getSession()->addSuccess($this->__('Configuration cache has been successfully refreshed'));
        }
        if ($this->getRequest()->getParam('thumbnails')) {
            Mage::helper('awfeatured/images')->removeThumbnails();
        }
        return $this->_redirect('adminhtml/system_config/edit', array('section' => 'awfeatured'));
    }

    public function getisformAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('awfeatured/adminhtml_blocks_edit_tab_automation_products_images')
                ->toHtml()
        );
    }

    public function setimageAction()
    {
        $_productId = $this->getRequest()->getParam('productId');
        $_blockId = $this->getRequest()->getParam('blockId');
        $_imageId = $this->getRequest()->getParam('imageId');
        $_product = Mage::getModel('catalog/product')->load($_productId);
        if ($_product->getData()) {
            $_block = Mage::getModel('awfeatured/blocks')->load($_blockId);
            if ($_block->getData()) {
                $_image = Mage::getModel('awfeatured/productimages')->loadBy($_blockId, $_productId);
                $_image->setData(
                    array(
                        'id' => $_image->getData('id'),
                        'block_id' => $_blockId,
                        'product_id' => $_productId,
                        'image_id' => $_imageId
                    )
                );
                if ($_imageId == -1 && $_image->getData('id')) {
                    $_image->delete();
                } else {
                    $_image->save();
                }
            }
        }
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'categoriesJson':
            case 'edit':
            case 'duplicate':
            case 'gettypeoptions':
            case 'index':
            case 'list':
            case 'productsgrid':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/awfeatured/list');
                break;
            case 'getisform':
            case 'setimage':
            case 'delete':
            case 'save':
            case 'new':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/awfeatured/new');
                break;
            case 'refreshcache':
                return true;
            default:
                return FALSE;
        }
    }

}
