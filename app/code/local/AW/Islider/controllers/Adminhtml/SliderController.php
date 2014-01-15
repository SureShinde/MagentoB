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
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Adminhtml_SliderController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        return $this->loadLayout()->_setActiveMenu('cms/awislider');
    }

    /**
     * Returns true when admin session contain error messages
     */
    private function _hasErrors() {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function indexAction() {
        return $this->_redirect('*/*/list');
    }

    protected function newAction() {
        Mage::helper('awislider')->setFormData(array());
        return $this->_redirect('*/*/edit');
    }
    
    protected function listAction() {
        $this->_initAction()->_setTitle($this->__('List Sliders'));
        $this->renderLayout();
    }
    
    protected function editAction() {
        if($this->getRequest()->getParam('id'))
            $this->_getSession()->addNotice($this->__('Use images with the same width and height for best results'));
        $this->_initAction()->_setTitle($this->__('Slider'));
        if(!$this->getRequest()->getParam('fswe') || !Mage::helper('awislider')->getFormData($this->getRequest()->getParam('id'))) {
            $_formData = Mage::getModel('awislider/slider')->load($this->getRequest()->getParam('id'));
            if($_formData->getData()) {
                Mage::helper('awislider')->setFormData($_formData);
            }
            if(!$_formData->getData() && $this->getRequest()->getParam('id')) {
                $this->_getSession()->addError($this->__('Couldn\'t load slider block by given ID'));
                return $this->_redirect('*/*/list');
            }
        }
        $this->_addContent($this->getLayout()->createBlock('awislider/adminhtml_slider_edit'))
            ->_addLeft($this->getLayout()->createBlock('awislider/adminhtml_slider_edit_tabs'));
        $this->renderLayout();
    }
    
    protected function saveAction() {
        $_data = array();
        $_request = $this->getRequest();
        if($_request->getParam('name')) {
            $_data['name'] = $_request->getParam('name');
            if($_request->getParam('block_id')) {
                $_data['block_id'] = $_request->getParam('block_id');
                if(preg_match('/^[a-zA-Z0-9-_]*$/', $_data['block_id'])) {
                    $_data['block_id'] = $_request->getParam('block_id');
                    $_data['is_active'] = $_request->getParam('block_is_active');
                    if(in_array($_request->getParam('autoposition'), array_keys(Mage::getModel('awislider/source_autoposition')->toShortOptionArray()))) {
                        $_data['autoposition'] = $_request->getParam('autoposition');
                        if(!($_request->getParam('store') === null) && $_request->getParam('store') != array()) {
                            $_data['store'] = $_request->getParam('store');
                            $_blocksCollection = Mage::getModel('awislider/slider')->getCollection()
                                ->addIdFilter($_request->getParam('id'), true)
                                ->addBlockIdFilter($_data['block_id']);
                            if(!in_array(0, $_data['store']))
                                $_blocksCollection->addStoreFilter($_data['store']);
                            if(!$_blocksCollection->getSize()) {
                                if(in_array($_request->getParam('switch_effect'), array_keys(Mage::getModel('awislider/source_switcheffects')->toShortOptionArray()))) {
                                    $_data['switch_effect'] = $_request->getParam('switch_effect');
                                    $_data['nav_autohide'] = $_request->getParam('nav_autohide');
                                    $_options = array(
                                        'options' => array(
                                            'min_range' => 1
                                        )
                                    );
                                    if($_request->getParam('width') == '' || ($_request->getParam('width') != '' && filter_var($_request->getParam('width'), FILTER_VALIDATE_INT, $_options))) {
                                        $_data['width'] = $_request->getParam('width');
                                        if($_request->getParam('height') == '' || ($_request->getParam('height') != '' && filter_var($_request->getParam('height'), FILTER_VALIDATE_INT, $_options))) {
                                            $_data['height'] = $_request->getParam('height');
                                            $_options['options']['min_range'] = 0;
                                            if(filter_var($_request->getParam('animation_speed'), FILTER_VALIDATE_INT, $_options) !== false) {
                                                $_data['animation_speed'] = $_request->getParam('animation_speed');
                                                if(filter_var($_request->getParam('first_timeout'), FILTER_VALIDATE_INT, $_options) !== false) {
                                                    $_data['first_timeout'] = $_request->getParam('first_timeout');
                                                    $_data['id'] = $_request->getParam('id');
                                                } else {
                                                    $this->_getSession()->addError($this->__('\'First frame timeout\' field value should be integer and equals or greater than 0'));
                                                }
                                            } else {
                                                $this->_getSession()->addError($this->__('\'Animation Speed\' field value should be integer and equals or greater than 0'));
                                            }
                                        } else {
                                            $this->_getSession()->addError($this->__('\'Height\' field value should be integer and equals or greater than 1'));
                                        }
                                    } else {
                                        $this->_getSession()->addError($this->__('\'Width\' field value should be integer and equals or greater than 1'));
                                    }
                                } else {
                                    $this->_getSession()->addError($this->__('Wrong value specified for \'Switch effect\' field'));
                                }
                            } else {
                                $this->_getSession()->addError($this->__('Block with the same \'Block ID\' already exists in selected store views'));
                            }
                        } else {
                            $this->_getSession()->addError($this->__('Store isn\'t specified'));
                        }
                    } else {
                        $this->_getSession()->addError($this->__('Wrong value specified for \'Automatic layout position\' field'));
                    }
                } else {
                    $this->_getSession()->addError($this->__('The following symbols are allowed to be used in the \'Block ID\' field: a-z 0-9 - _'));
                }
            } else {
                $this->_getSession()->addError($this->__('\'Block ID\' field can\'t be empty'));
            }
        } else {
            $this->_getSession()->addError($this->__('\'Name\' field can\'t be empty'));
        }

        if($this->_hasErrors()) {
            Mage::helper('awislider')->setFormData($_request->getParams());
            return $this->_redirect('*/*/edit', array('id' => $_request->getParam('id'), 'fswe' => 1));
        } else {
            $_block = Mage::getModel('awislider/slider')->load($_data['id']);
            $_block->setData($_data);
            $_block->save();
            
            $this->_getSession()->addSuccess($this->__('Block has been succesfully saved'));
            if($this->getRequest()->getParam('continue'))
                return $this->_redirect('*/*/edit', array('id' => $_block->getId(),
                    'continue_tab' => $this->getRequest()->getParam('continue_tab')));
            else
                return $this->_redirect('*/*/list');
        }
    }

    protected function deleteAction() {
        $_slider = Mage::getModel('awislider/slider')->load($this->getRequest()->getParam('id'));
        if($_slider->getData()) {
            foreach($_slider->getImagesCollection() as $image) {
                $image->removePreviewImage()
                    ->removeSelfImage();
            }
            $_slider->delete();
            $this->_getSession()->addSuccess($this->__('Slider block has been successfully deleted'));
        }
        return $this->_redirect('*/*/list');
    }

    protected function massactionstatusAction() {
        if($this->getRequest()->getParam('awislider')) {
            $newStatus = $this->getRequest()->getParam('status');
            if(Mage::getModel('awislider/source_status')->getOption($newStatus)) {
                $cnt = 0;
                foreach($this->getRequest()->getParam('awislider') as $sliderId) {
                    $_slider = Mage::getModel('awislider/slider')->load($sliderId);
                    if($_slider->getData()) {
                        $_slider->setData('is_active', $newStatus);
                        $_slider->save();
                        $cnt++;
                    }
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', $cnt));
            }
        }
        return $this->_redirect('*/*/list');
    }

    protected function _isAllowed() {
        switch($this->getRequest()->getActionName()) {
            case 'delete':
            case 'new':
            case 'save':
            case 'massactionstatus':
                return Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new');
                break;
            case 'edit':
            case 'index':
            case 'list':
                return Mage::getSingleton('admin/session')->isAllowed('cms/awislider/list');
                break;
            default:
                return false;
        }
    }

    /**
     * Set title of page
     */
    protected function _setTitle($action)
    {
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Image Slider'))->_title($this->__($action));
        }
        return $this;
    }
}
