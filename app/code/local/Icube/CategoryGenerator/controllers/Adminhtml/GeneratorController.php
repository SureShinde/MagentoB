<?php

class Icube_CategoryGenerator_Adminhtml_GeneratorController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('promo/catalog')
                ->_addBreadcrumb(
                        Mage::helper('categorygenerator')->__('Promotions'), Mage::helper('categorygenerator')->__('Promotions')
        );
        return $this;
    }
    
    public function indexAction()
    {

        $this->_title($this->__('Promotions'))->_title($this->__('Category Generator'));

        $this->_initAction()
                ->_addBreadcrumb(
                        Mage::helper('categorygenerator')->__('Catalog'), Mage::helper('categorygenerator')->__('Catalog')
                )
                ->renderLayout();
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction()
    {
    	
    	$this->_title($this->__('Promotions'))->_title($this->__('Category Generator'));
    	
        $_formData = Mage::getModel('categorygenerator/generator')->load($this->getRequest()->getParam('id'));
        $this->_getSession()->setData(Icube_CategoryGenerator_Helper_Data::FORM_DATA_KEY, $_formData);

        
        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('categorygenerator/generator');
                
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('categorygenerator')->__('Category generator no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Category Generator'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('generator_conditions_fieldset');

        Mage::register('cat_generator_data', $model);

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();

        
    }
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('categorygenerator/generator'))
                ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
   
    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('categorygenerator/adminhtml_generator_edit_tab_categories_categoriesgrid')
                    ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
    
     public function saveAction()
     {
        $request = Mage::app()->getRequest();
        
        if ($this->getRequest()->getPost()) {

            try {
                $model = Mage::getModel('categorygenerator/generator');
                Mage::dispatchEvent(
                        'adminhtml_controller_catalogrule_prepare_save', array('request' => $this->getRequest())
                );
                $data = $this->getRequest()->getPost();
                

                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('categorygenerator')->__('Wrong generator specified.'));
                    }
                }

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                if (empty($data['category_ids'])) {
	                $this->_getSession()->setPageData($data);
	                $this->_getSession()->addError(
                    Mage::helper('categorygenerator')->__('Please select a category.'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                
                $_categories = Mage::helper('categorygenerator')->prepareArray($data['category_ids']);
		        $data['category_data'] = array('categories' => $_categories);
		        unset($data['category_ids']);
                
                $autoApply = false;
                if (!empty($data['auto_apply'])) {
                    $autoApply = true;
                    unset($data['auto_apply']);
                }
                
                
                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('categorygenerator')->__('The generator has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);
                if ($autoApply) {
                    $this->getRequest()->setParam('id', $model->getId());
                    $this->_forward('applyGenerator');
                } else {
                    Mage::getModel('categorygenerator/flag')->loadSelf()
                            ->setState(1)
                            ->save();
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                }
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                        Mage::helper('categorygenerator')->__('An error occurred while saving the data. Please review the log and try again.')
                );
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function applyGeneratorAction()
    {
        $errorMessage = Mage::helper('categorygenerator')->__('Unable to apply generator.');
        try {
            Mage::getModel('categorygenerator/generator')->applyAll();
            Mage::getModel('categorygenerator/flag')->loadSelf()
                    ->setState(0)
                    ->save();
            $this->_getSession()->addSuccess(Mage::helper('categorygenerator')->__('The generator has been applied.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($errorMessage . ' ' . $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($errorMessage);
        }
        $this->_redirect('*/*');
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('categorygenerator/generator');
                $model->load($id);
                $model->delete();
                Mage::getModel('categorygenerator/flag')->loadSelf()
                        ->setState(1)
                        ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('categorygenerator')->__('The generator has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                        Mage::helper('categorygenerator')->__('An error occurred while deleting the generator. Please review the log and try again.')
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('categorygenerator')->__('Unable to find a generator to delete.')
        );
        $this->_redirect('*/*/');
    }
    
}