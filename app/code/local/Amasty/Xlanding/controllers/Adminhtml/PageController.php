<?php

/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Xlanding_Adminhtml_PageController extends Mage_Adminhtml_Controller_Action {

    protected $_title = 'Landing Page';
    protected $_modelName = 'page';

    protected function _setActiveMenu($menuPath) {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Catalog'))->_title($this->__($this->_title));
        return $this;
    }

    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/amlanding/' . $this->_modelName . 's');
        $this->_addContent($this->getLayout()->createBlock('amlanding/adminhtml_' . $this->_modelName));
        $this->renderLayout();
    }

    public function newAction() {
        $this->editAction();
    }

    public function optionsAction() {
        $result = '<input id="attr_value" name="attr_value[]" value="" class="input-text" type="text" />';

        $code = $this->getRequest()->getParam('code');
        if (!$code) {
            $this->getResponse()->setBody($result);
            return;
        }

        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
        if (!$attribute) {
            $this->getResponse()->setBody($result);
            return;
        }

        if (!in_array($attribute->getFrontendInput(), array('select', 'multiselect'))) {
            $this->getResponse()->setBody($result);
            return;
        }

        $options = $attribute->getFrontend()->getSelectOptions();
        //array_shift($options);  

        $result = '<select id="attr_value" name="attr_value[]" class="select">';
        foreach ($options as $option) {
            $result .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $result .= '</select>';

        $this->getResponse()->setBody($result);
    }

    public function editAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $model = Mage::getModel('amlanding/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlanding')->__('Record does not exist'));
            $this->_redirect('*/*/');
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        } else {
            $this->prepareForEdit($model);
        }

        Mage::register('amlanding_' . $this->_modelName, $model);

        $this->loadLayout();

        $this->_setActiveMenu('catalog/amlanding/' . $this->_modelName . 's');
        $this->_title($this->__('Edit'));

        $this->_addContent($this->getLayout()->createBlock('amlanding/adminhtml_' . $this->_modelName . '_edit'));
        $this->_addLeft($this->getLayout()->createBlock('amlanding/adminhtml_' . $this->_modelName . '_edit_tabs'));

        $this->renderLayout();
    }

    public function saveAction() {

        if ($data = $this->getRequest()->getPost()) {

            //init model and set data
            $model = Mage::getModel('amlanding/page');

            if ($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
            }
			
			//image
				$imagedata = array ();
				$path = Mage::getBaseDir('media') . DS . Mage::getStoreConfig('bilna_module/amlanding/imageurl');
				//Zend_Debug::Dump($_FILES['banner']['name']);exit;
				
				//banner
				if (!empty ($_FILES['banner']['name'])) {
					try {
						$ext = substr($_FILES['banner']['name'], strrpos($_FILES['banner']['name'], '.') + 1);
						$fname = 'File-' . time() . '-banner.' . $ext;
						$uploader = new Varien_File_Uploader('banner');
						$uploader->setAllowedExtensions(array ('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything
						$uploader->setAllowRenameFiles(true);
						$uploader->setFilesDispersion(false);				
						$uploader->save($path, $fname);

						$imagedata['banner'] = Mage::getStoreConfig('bilna_module/amlanding/imageurl') . '/' . $fname;
					}
					catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						
						return;
					}
				}
				
				//background
				if (!empty ($_FILES['background']['name'])) {
					try {
						$ext = substr($_FILES['background']['name'], strrpos($_FILES['background']['name'], '.') + 1);
						$fname = 'File-' . time() . '-background.' . $ext;
						$uploader = new Varien_File_Uploader('background');
						$uploader->setAllowedExtensions(array ('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything
						$uploader->setAllowRenameFiles(true);
						$uploader->setFilesDispersion(false);				
						$uploader->save($path, $fname);

						$imagedata['background'] = Mage::getStoreConfig('bilna_module/amlanding/imageurl') . '/' . $fname;
					}
					catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						
						return;
					}
				}
				
				//rack
				if (!empty ($_FILES['rack']['name'])) {
					try {
						$ext = substr($_FILES['rack']['name'], strrpos($_FILES['rack']['name'], '.') + 1);
						$fname = 'File-' . time() . '-rack.' . $ext;
						$uploader = new Varien_File_Uploader('rack');
						$uploader->setAllowedExtensions(array ('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything
						$uploader->setAllowRenameFiles(true);
						$uploader->setFilesDispersion(false);				
						$uploader->save($path, $fname);

						$imagedata['rack'] = Mage::getStoreConfig('bilna_module/amlanding/imageurl') . '/' . $fname;
					}
					catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						
						return;
					}
				}
				
				if (!empty($imagedata['banner'])) {
					$data['banner'] = $imagedata['banner'];
				} else {
					if (isset($data['banner']['delete']) && $data['banner']['delete'] == 1) {
						if ($data['banner']['value'] != '') {
							$_helper = Mage::helper('amlanding');
							$this->removeFile(Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($data['banner']['value']));
						}
						$data['banner'] = '';
					} else {
						unset($data['banner']);
					}
				}
				
				if (!empty($imagedata['background'])) {
					$data['background'] = $imagedata['background'];
				} else {
					if (isset($data['background']['delete']) && $data['background']['delete'] == 1) {
						if ($data['background']['value'] != '') {
							$_helper = Mage::helper('amlanding');
							$this->removeFile(Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($data['background']['value']));
						}
						$data['background'] = '';
						//$data['background'] = $_FILES['background']['name'];
					} else {
						unset($data['background']);
					}
				}
				
				if (!empty($imagedata['rack'])) {
					$data['rack'] = $imagedata['rack'];
				} else {
					if (isset($data['rack']['delete']) && $data['rack']['delete'] == 1) {
						if ($data['rack']['value'] != '') {
							$_helper = Mage::helper('amlanding');
							$this->removeFile(Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($data['rack']['value']));
						}
						$data['rack'] = '';
						//$data['rack'] = $_FILES['rack']['name'];
					} else {
						unset($data['rack']);
					}
				}

				//save image end//
			//Zend_Debug::Dump($data); exit;
            $model->setData($data);
			
            $this->prepareForSave($model);


            //validating
            if (!$this->_validatePostData($data)) {
                $this->_redirect('*/*/edit', array('id' => $model->getPageId(), '_current' => true));
                return;
            }

            // try to save it
            try {
                // save the data
                $model->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('amlanding')->__('The page has been saved.'));
                
				
								
				// clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
				// check if 'Save and Continue'
                if ($this->getRequest()->getParam('continue')) {
					$this->_redirect('*/*/edit', array('id' => $model->getPageId(), '_current' => true));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
            	throw $e;die;
				$this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
            	throw $e;die;
				$this->_getSession()->addException($e, Mage::helper('amlanding')->__('An error occurred while saving the page.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $model = Mage::getModel('amlanding/' . $this->_modelName)->load($id);
		$_helper = Mage::helper('amlanding');

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__($this->_title . ' has been successfully deleted'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlanding')->__('Please select records'));
            $this->_redirect('*/*/');
            return;
        }
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amlanding/' . $this->_modelName)->load($id);
                $model->delete();
                // TODO remove files
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were successfully deleted', count($ids)
                    )
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function massActivateAction() {
        return $this->_modifyStatus(1);
    }

    public function massInactivateAction() {
        return $this->_modifyStatus(0);
    }

    protected function _modifyStatus($status) {
        $ids = $this->getRequest()->getParam('pages');
        if ($ids && is_array($ids)) {
            try {
                Mage::getModel('amlanding/' . $this->_modelName)->massChangeStatus($ids, $status);
                $message = $this->__('Total of %d record(s) have been updated.', count($ids));
                $this->_getSession()->addSuccess($message);
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select page(s).'));
        }

        return $this->_redirect('*/*');
    }

    /**
     * Prepare model
     * @param Amasty_Xlanding_Model_Page $model
     * @return boolean
     */
    public function prepareForSave($model) {
        $attributeCodes = $model->getData('attr_code');
        $attributeValues = $model->getData('attr_value');

        $validArray = array();

        foreach ($attributeValues as $index => $value) {
            if (isset($attributeCodes[$index]) && $attributeCodes[$index] != '') {
                if (!isset($validArray[$attributeCodes[$index]])) {
                    $validArray[$attributeCodes[$index]] = array();
                }
                if ($value != '') {
                    $validArray[$attributeCodes[$index]][] = $value;
                }
            }
        }
        $model->setData('attributes', serialize($validArray));

        return true;
    }

    public function prepareForEdit($model) {
        $fields = array('stores', 'cust_groups', 'cats');
        foreach ($fields as $f) {
            $val = $model->getData($f);
            if (!is_array($val)) {
                $model->setData($f, explode(',', $val));
            }
        }

        //$model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        return true;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool     Return FALSE if someone item is invalid
     */
    protected function _validatePostData($data) {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            /** @var $validatorCustomLayout Mage_Adminhtml_Model_LayoutUpdate_Validator */
            $validatorCustomLayout = Mage::getModel('adminhtml/layoutUpdate_validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->_getSession()->addError($message);
            }
        }
        return $errorNo;
    }

    protected function _title($text = null, $resetIfExists = true) {
        if (Mage::helper('ambase')->isVersionLessThan(1, 4)) {
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }
	
	protected function removeFile($file) {
        try {
            $io = new Varien_Io_File();
            $result = $io->rmdir($file, true);
        } catch (Exception $e) {

        }
    }

}