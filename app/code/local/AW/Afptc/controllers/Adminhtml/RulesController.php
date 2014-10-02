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
 * @package    AW_Afptc
 * @version    1.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Afptc_Adminhtml_RulesController extends Mage_Adminhtml_Controller_Action
{

    protected function displayTitle($data = null, $root = 'Add Free Product To Cart')
    {
        if (!version_compare(Mage::getVersion(), '1.4', '<')) {
            if ($data) {
                if (!is_array($data)) {
                    $data = array($data);
                }
                $this->_title($this->__($root));
                foreach ($data as $title) {
                    $this->_title($this->__($title));
                }
            } else {
                $this->_title($this->__('Rules'))->_title($root);
            }
        }
        return $this;
    }

    public function indexAction()
    {
        $this
                ->displayTitle('Rules')
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $rule = $this->_loadRule();
        
        $rule->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('awafptc_rule', $rule);
        if ($rule->getId()) {
            $this->displayTitle('Edit Rule');
        } else {
            $this->displayTitle('New Rule');
        }

        $this
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->renderLayout();
    }
   
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('awafptc/rule'))
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

    protected function _loadRule()
    {
        return Mage::getModel('awafptc/rule')->load((int) $this->getRequest()->getParam('id', 0));
    }
    
    public function productGridAction()
    {        
        $this->getResponse()->setBody($this->getLayout()
                ->createBlock('awafptc/adminhtml_rules_product_grid')
                ->setCheckedValues((array) $this->getRequest()->getParam('checkedValues', array()))
                ->toHtml());
    }

    public function saveAction()
    {       
        $request = new Varien_Object($this->_filterDateTime(
                $this->getRequest()->getParams(), array('start_date', 'end_date'))); 
         
        $this->_prepareDates($request)->_prepareConditions($request);
         
        try {        
            $rule = Mage::getModel('awafptc/rule')
                    ->load($request->getId())->addData($request->getData())
                    ->loadPost($request->getData())
                    ->save();  
            
            if(!$rule->getProductId()) {
                Mage::getSingleton('adminhtml/session')->addNotice($this->__('No action product specified'));
            }
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule successfully saved'));
        } catch (Exception $e) {           
            Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage())
                    ->setFormActionData($request);
            return $this->_redirect('*/*/edit', array('id' => $request->getId(), 'tab' => $request->getTab()));
        }
        if ($request->getBack()) {
            return $this->_redirect('*/*/edit', array('id' => $rule->getId(), 'tab' => $request->getTab()));
        }
        return $this->_redirect('*/*/');
    }
    
    protected function _prepareDates(Varien_Object $request)
    {
        if ($request->getStartDate()) {
            try {
                $request->setStartDate(Mage::getModel('core/date')->gmtDate(null, $request->getStartDate()));
            } catch (Exception $e) {
                $request->setStartDate(Mage::getModel('core/date')->gmtDate());
            }
        } else {
            $request->setStartDate(Mage::getModel('core/date')->gmtDate());
        }
        if ($request->getEndDate()) {
            try {
                $request->setEndDate(Mage::getModel('core/date')->gmtDate(null, $request->getEndDate()));
            } catch (Exception $e) {
                $request->setEndDate(null);
            }
        } else {
            $request->setEndDate(null);
        }
        
        return $this;
    }
    
    protected function _prepareConditions(Varien_Object $request)
    {
        $data = $request->getData();
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
            $request->setData($data);
        }
        
        return $this;
    }
 
    public function deleteAction()
    {
        try {
            $request = $this->getRequest()->getParams();

            if (!isset($request['id'])) {
                throw new Mage_Core_Exception($this->__('Incorrect rule id'));
            }

            Mage::getModel('awafptc/rule')->setId($request['id'])->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule successfully deleted'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        try {
            $ruleIds = $this->getRequest()->getParam('rules');

            if (!is_array($ruleIds)) {
                throw new Mage_Core_Exception($this->__('Invalid rule ids'));
            }

            foreach ($ruleIds as $rule) {
                Mage::getSingleton('awafptc/rule')->setId($rule)->delete();
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%d auction(s) have been successfully deleted', count($ruleIds)));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }

    //protected function _isAllowed()
    //{
    //    return Mage::getSingleton('admin/session')->isAllowed('promo/awauction/auctions');
    //}

}