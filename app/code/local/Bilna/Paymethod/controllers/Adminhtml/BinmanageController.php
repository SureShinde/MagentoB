<?php
/**
 * Description of Bilna_Paymethod_Adminhtml_BinmanageController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Adminhtml_BinmanageController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        $this->_title($this->__('Bin Management'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna');
        $this->_addContent($this->getLayout()->createBlock('paymethod/adminhtml_binmanage'));
        $this->renderLayout();
    }
    
    /**
     * Grid with Ajax Request
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('paymethod/adminhtml_binmanage_grid')->toHtml()
        );
    }
    
    public function exportCsvAction() {
        $filename = sprintf("Credit_Card_Report_%s.csv", date('Ymd', Mage::getModel('core/date')->timestamp(time())));
        $grid = $this->getLayout()->createBlock('Bilna_Paymethod_Block_Adminhtml_Binmanage_Grid');
        $this->_prepareDownloadResponse($filename, $grid->getCsvFile()); 
    }
    
    public function newAction() {
        $this->_forward('edit');
    }
    
    public function editAction() {
        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('paymethod/binmanage');
        
        if ($id) {
            $model->load((int) $id);
            
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            }
            else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('paymethod/binmanage')->__('Example does not exist'));
                $this->_redirect('*/*/');
            }
        }
        
        Mage::register('binmanage_data', $model);
 
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }
    
    public function saveAction() {
        $helper = Mage::helper('paymethod/binmanage');
        
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('paymethod/binmanage');
            $id = $this->getRequest()->getParam('id');
            
            if ($id) {
                $model->load($id);
            }
            
            $model->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            
            try {
                if ($id) {
                    $model->setId($id);
                }
                
                $model->save();
 
                if (!$model->getId()) {
                    Mage::throwException($helper->__('Error saving Bin Information'));
                }
 
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Bin Information was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
 
                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array ('id' => $model->getId()));
                }
                else {
                    $this->_redirect('*/*/');
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array ('id' => $model->getId()));
                }
                else {
                    $this->_redirect('*/*/');
                }
            }
 
            return;
        }
        
        Mage::getSingleton('adminhtml/session')->addError($helper->__('No data found to save'));
        $this->_redirect('*/*/');
    }
    
    public function deleteAction() {
        $helper = Mage::helper('paymethod/binmanage');
        
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('paymethod/binmanage');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The Bin Information has been deleted.'));
                $this->_redirect('*/*/');
                
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array ('id' => $this->getRequest()->getParam('id')));
                
                return;
            }
        }
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the example to delete.'));
        $this->_redirect('*/*/');
    }
}
