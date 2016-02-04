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


class AW_Onsale_Adminhtml_RuleController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Dirty rules notice message
     *
     * @var string
     */
    protected $_dirtyRulesNoticeMessage;

    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('promo/catalog')
                ->_addBreadcrumb(
                        Mage::helper('onsale')->__('Promotions'), Mage::helper('onsale')->__('Promotions')
        );
        return $this;
    }

    public function indexAction()
    {

        $this->_title($this->__('Promotions'))->_title($this->__('On Sale Label Rules'));

        $dirtyRules = Mage::getModel('onsale/flag')->loadSelf();
        if ($dirtyRules->getState()) {
            Mage::getSingleton('adminhtml/session')->addNotice($this->getDirtyRulesNoticeMessage());
        }

        $this->_initAction()
                ->_addBreadcrumb(
                        Mage::helper('onsale')->__('Catalog'), Mage::helper('onsale')->__('Catalog')
                )
                ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('On Sale Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('onsale/rule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('onsale')->__('This rule no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getRuleId() ? $model->getName() : $this->__('New Rule'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('current_onsale_rule', $model);

        $this->_initAction()->getLayout()->getBlock('onsale_adminhtml_edit')
                ->setData('action', $this->getUrl('*/onsale_rule/save'));

        $breadcrumb = $id ? Mage::helper('onsale')->__('Edit Rule') : Mage::helper('onsale')->__('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb)->renderLayout();
    }

    public function saveAction()
    {
        
        $request = Mage::app()->getRequest();
        
        if ($this->getRequest()->getPost()) {

            try {
                $model = Mage::getModel('onsale/rule');
                Mage::dispatchEvent(
                        'adminhtml_controller_catalogrule_prepare_save', array('request' => $this->getRequest())
                );
                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, array('from_date', 'to_date'));

                $data = $this->_filterPicture($data, 'category_page_image');
                $data = $this->_filterPicture($data, 'product_page_image');

                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('onsale')->__('Wrong rule specified.'));
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

                $autoApply = false;
                if (!empty($data['auto_apply'])) {
                    $autoApply = true;
                    unset($data['auto_apply']);
                }

                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('onsale')->__('The rule has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);
                if ($autoApply) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    Mage::getModel('onsale/flag')->loadSelf()
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
                        Mage::helper('onsale')->__('An error occurred while saving the rule data. Please review the log and try again.')
                );
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _filterPicture($data, $fieldName)
    {

        if (isset($_FILES[$fieldName]['name']) and (file_exists($_FILES[$fieldName]['tmp_name']))) {
            try {
                $uploader = new Varien_File_Uploader($fieldName);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything
                //$uploader->setAllowRenameFiles(false);
                $uploader->setAllowRenameFiles(true);

                // setAllowRenameFiles(true) -> move your file in a folder the magento way
                // setAllowRenameFiles(true) -> move your file directly in the $path folder
                $uploader->setFilesDispersion(false);

                $path = 'onsale/upload/';
                $fileName = $_FILES[$fieldName]['name'];

                $uploader->save(Mage::getBaseDir('media') . DS . $path, $fileName);

                $data[$fieldName] = $path . $fileName;
            } catch (Exception $e) {

                $data[$fieldName] = '';
                $this->_getSession()->addError(
                        Mage::helper('onsale')->__('Disallowed file type.')
                );
                $this->_getSession()->addError(
                        Mage::helper('onsale')->__($e->getMessage())
                );
            }
        } else {
            if (isset($data[$fieldName]['delete']) && $data[$fieldName]['delete'] == 1) {
                $data[$fieldName] = '';
            } else {
                unset($data[$fieldName]);
            }
        }
        return $data;
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('onsale/rule');
                $model->load($id);
                $model->delete();
                Mage::getModel('onsale/flag')->loadSelf()
                        ->setState(1)
                        ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('onsale')->__('The rule has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                        Mage::helper('onsale')->__('An error occurred while deleting the rule. Please review the log and try again.')
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('onsale')->__('Unable to find a rule to delete.')
        );
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('onsale/rule'))
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

    public function chooserAction()
    {
        switch ($this->getRequest()->getParam('attribute')) {
            case 'sku':
                $type = 'adminhtml/promo_widget_chooser_sku';
                break;

            case 'categories':
                $type = 'adminhtml/promo_widget_chooser_categories';
                break;
        }
        if (!empty($type)) {
            $block = $this->getLayout()->createBlock($type);
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }

    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('onsale/rule'))
                ->setPrefix('actions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Action_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Apply all active catalog onsale label rules
     */
    public function applyRulesAction()
    {
        $errorMessage = Mage::helper('onsale')->__('Unable to apply rules.');
        try {
            Mage::getModel('onsale/rule')->applyAll();
            Mage::getModel('onsale/flag')->loadSelf()
                    ->setState(0)
                    ->save();
            $this->_getSession()->addSuccess(Mage::helper('onsale')->__('The rules have been applied.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($errorMessage . ' ' . $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($errorMessage);
        }
        $this->_redirect('*/*');
    }

    /**
     * @deprecated since 1.5.0.0
     */
    public function addToAlersAction()
    {
        
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/onsale');
    }

    /**
     * Set dirty rules notice message
     *
     * @param string $dirtyRulesNoticeMessage
     */
    public function setDirtyRulesNoticeMessage($dirtyRulesNoticeMessage)
    {
        $this->_dirtyRulesNoticeMessage = $dirtyRulesNoticeMessage;
    }

    /**
     * Get dirty rules notice message
     *
     * @return string
     */
    public function getDirtyRulesNoticeMessage()
    {
        $defaultMessage = Mage::helper('onsale')->__('There are rules that have been changed but were not applied. Please, click Apply Rules in order to see immediate effect in the catalog.');
        return $this->_dirtyRulesNoticeMessage ? $this->_dirtyRulesNoticeMessage : $defaultMessage;
    }

}
