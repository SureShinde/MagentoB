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

class AW_Islider_Adminhtml_ImageController extends Mage_Adminhtml_Controller_Action {
    protected function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('awislider/adminhtml_slider_edit_tabs_images')->toHtml());
    }

    protected function ajaxformAction() {
        if($this->getRequest()->getParam('id')) {
            //loading image
            $_image = Mage::getModel('awislider/image')->load($this->getRequest()->getParam('id'));
            if($_image->getData()) {
                Mage::helper('awislider')->setFormDataImage($_image);
            } else {
                $this->_getSession()->addError('Couldn\'t load image');
            }
        }
        $_block = $this->getLayout()->createBlock('awislider/adminhtml_slider_edit_tabs_images_container');
        $_block->setData('image_id', $this->getRequest()->getParam('id'));
        $_block->setData('image_pid', $this->getRequest()->getParam('pid'));
        $this->getResponse()->setBody($_block->toHtml());
    }

    protected function saveimageAction() {
        $_result = array();
        $_request = $this->getRequest();

        $_data = array();
        $_errors = array();
        if($_request->getParam('image_type') == AW_Islider_Model_Source_Images_Type::FILE) {
            $_data['type'] = AW_Islider_Model_Source_Images_Type::FILE;
            //File uploading
            if(isset($_FILES['image_file']['name']) && $_FILES['image_file']['name']) {
                if(Mage::helper('awislider/files')->isAllowedImage($_FILES['image_file']['type'])) {
                    if($_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
                        try {
                            $uploader = new Varien_File_Uploader('image_file');
                            $uploader
                                ->setAllowedExtensions(null)
                                ->setAllowCreateFolders(TRUE)
                                ->setFilesDispersion(FALSE);
                            $_filename = $_request->getParam('image_id') ? $_request->getParam('image_id') : $_request->getParam('image_tmp_id');
                            $_filename .= '.tmp';
                            
                            $result = $uploader->save(Mage::helper('awislider/files')->getPath(), $_filename);
                            $_data['location'] = $_FILES['image_file']['name'];
                        } catch (Exception $ex) {
                            $_errors[] = $ex->getMessage();
                        }
                    }
                } else {
                    $_errors[] = $this->__('Unallowed file type');
                }
            }
        } else {
            $_data['type'] = AW_Islider_Model_Source_Images_Type::REMOTEURL;
            $_data['location'] = $_request->getParam('image_remote');
        }

        $_data['id'] = $_request->getParam('id');
        $_data['pid'] = $_request->getParam('image_pid');
        $_data['title'] = $_request->getParam('image_title');
        $_data['is_active'] = $_request->getParam('image_is_active');
        $_data['active_from'] = $_request->getParam('image_from');
        $_data['active_to'] = $_request->getParam('image_to');
        $_data['sort_order'] = $_request->getParam('image_sort_order');
        $_data['url'] = $_request->getParam('image_url');
        $_data['new_window'] = $_request->getParam('image_in_new_window');
        $_data['nofollow'] = $_request->getParam('image_nofollow');
        
        $active_from = Mage::app()->getLocale()->date($_data['active_from'],null,null,false);
        $active_to = Mage::app()->getLocale()->date($_data['active_to'],null,null,false);

        if($_data['active_from'] != NULL ) {
            $_data['active_from'] = $active_from->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }
        if($_data['active_to'] != NULL) {
            $_data['active_to'] = $active_to->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }
        
        // Additional checks
        $_datesCheck = true;
        if($_data['active_from'] && $active_from->toValue() < 1) {
            $_errors[] = $this->__('Wrong value for \'Date From\' field');
            $_datesCheck = false;
        }
        if($_data['active_to'] && $active_to->toValue() < 1) {
            $_errors[] = $this->__('Wrong value for \'Date To\' field');
            $_datesCheck = false;
        }
        if($_data['active_from'] && $_data['active_to'] && $_datesCheck && $active_to->toValue() < $active_from->toValue())
            $_errors[] = $this->__('Value of \'Date To\' should be equal or greater than value of \'Date From\' field');
        if(filter_var($_data['sort_order'], FILTER_VALIDATE_INT) === false)
            $_errors[] = $this->__('Sort order should be integer');

        $_image = Mage::getModel('awislider/image')->load($_request->getParam('id'));
        if(!isset($_data['location']) && !$_image->getData('location'))
            $_errors[] = $this->__('Please, specify image file');

        if(!$_errors) {
            // If file were uploaded
            if($_data['type'] == AW_Islider_Model_Source_Images_Type::FILE && isset($_data['location'])) {
                if($_image->getData('location')) {
                    //removing old file
                    @unlink(Mage::helper('awislider/files')->getPath().'100x100_'.$_image->getData('location'));
                    if($_image->getData('type') == AW_Islider_Model_Source_Images_Type::FILE)
                        @unlink(Mage::helper('awislider/files')->getPath().$_image->getData('location'));
                }
                if(file_exists(Mage::helper('awislider/files')->getPath().$_data['location']))
                    $_data['location'] = uniqid().'.'.Mage::helper('awislider/files')->getExtension($_data['location']);
                @rename (Mage::helper('awislider/files')->getPath().$_filename,
                    Mage::helper('awislider/files')->getPath().$_data['location']);
            }
            // If file type changed from uploaded to remote
            if(isset($_data['location']) && $_data['type'] == AW_Islider_Model_Source_Images_Type::REMOTEURL && $_image->getData('type') == AW_Islider_Model_Source_Images_Type::FILE) {
                //removing old preview and old file
                @unlink(Mage::helper('awislider/files')->getPath().'100x100_'.$_image->getData('location'));
                @unlink(Mage::helper('awislider/files')->getPath().$_image->getData('location'));
            }
            // if slider image with remote url just updated
            if(isset($_data['location']) && $_data['type'] == AW_Islider_Model_Source_Images_Type::REMOTEURL && $_image->getData('type') == AW_Islider_Model_Source_Images_Type::REMOTEURL) {
                //removing old preview
                @unlink(Mage::helper('awislider/files')->getPath().'100x100_'.$_image->getData('location'));
            }

            $_image->setData($_data);
            $_image->save();
        } else {
            $_messagesBlock = Mage::getSingleton('core/layout')->getMessagesBlock();
            foreach($_errors as $error) {
                $_messagesBlock->addMessage(Mage::getModel('core/message')->error($error));
            }
            $_errors = $_messagesBlock->getGroupedHtml();
        }

        $_result = array(
            's' => $_errors ? false : true,
            'errors' => $_errors
        );

        $_responseBlock = $this->getLayout()->createBlock('adminhtml/template')
            ->setTemplate('aw_islider/images/ajaxresponse.phtml');
        $_responseBlock->setData('resp_object', Zend_Json::encode($_result));
        
        $response = Mage::app()->getResponse();
        $response->setBody($_responseBlock->toHtml());
    }

    protected function removeAction() {
        $_image = Mage::getModel('awislider/image')->load($this->getRequest()->getParam('id'));
        if($_image->getData()) {
            $_image->removePreviewImage()
                ->removeSelfImage();
            $_image->delete();
            $this->_getSession()->addSuccess($this->__('Image has been successfully deleted'));
        }
        $this->_redirect('awislider_admin/adminhtml_slider/edit', array('id' => $this->getRequest()->getParam('pid'),
            'continue_tab' => $this->getRequest()->getParam('continue_tab')));
    }

    protected function _isAllowed() {
        switch($this->getRequest()->getActionName()) {
            case 'saveimage':
            case 'remove':
                return Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new');
                break;
            case 'grid':
            case 'ajaxform':
                return Mage::getSingleton('admin/session')->isAllowed('cms/awislider/list');
                break;
            default:
                return false;
        }
    }
}