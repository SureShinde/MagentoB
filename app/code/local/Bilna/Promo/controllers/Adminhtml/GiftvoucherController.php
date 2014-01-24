<?php

class Bilna_Promo_Adminhtml_GiftvoucherController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('bilna/bilnapromo')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Gift Voucher'), Mage::helper('adminhtml')->__('Gift Voucher'));

		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

    public function applicantAction()
    {
    	$this->loadLayout();
    	$this->renderLayout();
    }
 
	/** 
     * Export order grid to CSV format 
     */ 
    public function exportCsvAction() 
    { 
        $fileName   = 'bilna_promo.csv'; 
        $grid       = $this->getLayout()->createBlock('Bilna_Promo_Block_Adminhtml_Giftvoucherapplicant_Grid'); 
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile()); 
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('bilnapromo/giftvoucher')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('giftvoucher_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('bilna/bilnapromo');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('Bilna_Promo_Block_Adminhtml_Giftvoucher_Edit'))
                    ->_addLeft($this->getLayout()->createBlock('Bilna_Promo_Block_Adminhtml_Giftvoucher_Edit_Tabs'));
            $version = substr(Mage::getVersion(), 0, 3);
            if (($version=='1.4' || $version=='1.5') && Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('banner')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        $imagedata = array();
        if (!empty($_FILES['banner']['name'])) {
            try {
                $ext = substr($_FILES['banner']['name'], strrpos($_FILES['banner']['name'], '.') + 1);
                $fname = 'File-' . time() . '.' . $ext;
                $uploader = new Varien_File_Uploader('banner');
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything

                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media').DS.'custom'.DS.'banners';

                $uploader->save($path, $fname);

                $imagedata['banner'] = 'custom/banners/'.$fname;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        if ($data = $this->getRequest()->getPost()) {
            if (!empty($imagedata['banner'])) {
                $data['banner'] = $imagedata['banner'];
            } else {
                if (isset($data['banner']['delete']) && $data['banner']['delete'] == 1) {
                    if ($data['banner']['value'] != '') {
                        $_helper = Mage::helper('banner');
                        $this->removeFile(Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($data['banner']['value']));
                    }
                    $data['banner'] = '';
                } else {
                    unset($data['banner']);
                }
            }
            $model = Mage::getModel('bilnapromo/giftvoucher');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('banner')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('banner')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('bilnapromo/giftvoucher')->load($this->getRequest()->getParam('id'));
                $_helper = Mage::helper('banner');
                $filePath = Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($model->getBanner());
                $model->delete();
                $this->removeFile($filePath);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function removeFile($file) {
        try {
            $io = new Varien_Io_File();
            $result = $io->rmdir($file, true);
        } catch (Exception $e) {

        }
    }
}