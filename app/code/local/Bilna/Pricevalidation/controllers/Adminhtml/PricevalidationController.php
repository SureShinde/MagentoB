<?php

class Bilna_Pricevalidation_Adminhtml_PricevalidationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Bilna'))->_title($this->__('Bilna Price Validation'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna/bilna');
        //$this->_addContent($this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation'));
        $this->renderLayout();
    }

    public function editAction() {
        $id     = $this->getRequest()->getParam('profile_id');
        $model  = Mage::getModel('bilna_pricevalidation/profile')->load($id)->factory();

        if ($model->getProfileId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('profile_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('bilna/pricevalidation');

            //$this->_addBreadcrumb(Mage::helper('bilna_pricevalidation')->__('SingleFeed Profile Manager'), Mage::helper('adminhtml')->__('SingleFeed Profile Manager'));
            //$this->_addBreadcrumb(Mage::helper('bilna_pricevalidation')->__('New Profile'), Mage::helper('adminhtml')->__('New Profile'));

            $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true)
                ->setCanLoadRulesJs(true);

            $this->_addContent($this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit'))
                ->_addLeft($this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit_tabs'));
//            $this->_addLeft($this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit_tabs'));
//            $this->_addContent($this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit'));
//            $this->_addContent($this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit'))
//                ->_addLeft($this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bilna_pricevalidation')->__('Profile does not exist'));
            $this->_redirect('*/*/index');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    //Initialize action
    //set the breadcrumbs and the active menu
    //@return Mage_Adminhtml_Controller_Action
    protected function _initAction()
    {
        //$this->loadLayout();
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            //->_setActiveMenu('bilna/bilna_formbuilder_formbuilder')
            //->_title($this->__('Bilna'))->_title($this->__('Formbuilder'))
            //->_addBreadcrumb($this->__('Bilna'), $this->__('Bilna'))
            //->_addBreadcrumb($this->__('Formbuilder'), $this->__('Formbuilder'));

        //return $this;
    }

    public function saveAction() {
        if ($postData = $this->getRequest()->getPost()) {

            try {
                $model = Mage::getModel('bilna_pricevalidation/profile');

                if (($id = $this->getRequest()->getParam('profile_id'))) {
                    $model->load($id);
                }
                if (!isset($postData['columns_post'])) {
                    $postData['columns_post'] = array();
                }
                if (isset($postData['conditions'])) {
                    $postData['conditions_post'] = $data['conditions'];
                    unset($postData['conditions']);
                }
                /*if (isset($postData['options']['reindex'])) {
                    $postData['options']['reindex'] = array_flip($data['options']['reindex']);
                }*/
                if (isset($postData['options']['refresh'])) {
                    $postData['options']['refresh'] = array_flip($data['options']['refresh']);
                }
                $model->addData($postData);
                $model = $model->factory();

                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                for($i = 0; $i < count($postData['columns_post']['field']); $i++) {
                    if(empty($postData['columns_post']['alias'][$i])) {
                        Mage::getSingleton('adminhtml/session')->addError('Column alias cannot empty !');
                        $this->_redirect('*/*/edit', array('profile_id' => $this->getRequest()->getParam('profile_id')));
                        return;
                    }
                }

                $model->setId($this->getRequest()->getParam('profile_id'))
                        ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Profile was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if (($invokeStatus = $this->getRequest()->getParam('start'))) {
                    $model->load($this->getRequest()->getParam('profile_id'));
                    $dataRun = $model->getData();

                    $cleanDir = '';

                    if(!empty($dataRun['base_dir'])){
                        $baseDir = explode('/', $dataRun['base_dir']);
                        if($baseDir[count($baseDir)-1] != '/') {
                            $baseDir[] = '/';
                        }
                        $cleanDir = implode('/', $baseDir);
                    }

                    if(!file_exists(Mage::getConfig()->getVarDir().'/pricevalidation/import/'.$cleanDir.$dataRun['filename'])) {
                        Mage::getSingleton('adminhtml/session')->addError('File not found !');
                        $this->_redirect('*/*/edit', array('profile_id' => $this->getRequest()->getParam('profile_id')));
                        return;
                    }
                    else {
                        require_once(Mage::getBaseDir('lib') . '/PHPExcel/PHPExcel/IOFactory.php');

                        $objReader = new PHPExcel_Reader_CSV();
                        $objReader->setInputEncoding('CP1252');
                        $objReader->setDelimiter(';');
                        $objReader->setEnclosure('');
                        $objReader->setLineEnding("\r\n");
                        $objReader->setSheetIndex(0);
                        $objPHPExcel = $objReader->load(Mage::getConfig()->getVarDir().'/pricevalidation/import/'.$cleanDir.$dataRun['filename']);
                        $worksheet = $objPHPExcel->getActiveSheet();

                        $columnsKeyToBeProcessed = array();
                        $cleanData = array();
                        $fieldList = array();
                        $originalHeader = '';
                        $errors = array();

                        foreach($worksheet->getRowIterator() as $key=>$row) {
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

                            if($key == 1){ // Get Header
                                foreach ($cellIterator as $cell) {
                                    if (!is_null($cell)) {
                                        $originalHeader = $cell->getValue();
                                        $dataCsv = explode(';',$cell->getValue());
                                        foreach($dataCsv as $keyColumn=>$RowColumnData) {
                                            foreach($postData['columns_post']['alias'] as $keyMatch=>$rowAlias) {
                                                if($rowAlias == $RowColumnData){
                                                    $columnsKeyToBeProcessed[$postData['columns_post']['field'][$keyMatch]] = $keyColumn;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if($key == 1){
                                $errors[] = $originalHeader.'Error Description';
                            }

                            if($key > 1) { // Skip price validation on header
                                $cellIterator = $row->getCellIterator();
                                foreach ($cellIterator as $cell) {
                                    if (!is_null($cell)) {
                                        $error = '';
                                        $dataCsv = explode(';',$cell->getValue());
                                        foreach($columnsKeyToBeProcessed as $keyDataColumn=>$columnKey) {
                                            $cleanData[$key-2][$keyDataColumn] = $dataCsv[$columnKey];
                                            $fieldList[] = $keyDataColumn;
                                        }

                                        if(in_array('price', $fieldList) && in_array('cost', $fieldList) && in_array('special_price', $fieldList)) {
                                            if(($cleanData[$key-2]['price'] - $cleanData[$key-2]['cost']) < 0) {
                                                $error .= 'Price - Cost results in negative value! ';
                                            }
                                            if(($cleanData[$key-2]['special_price'] - $cleanData[$key-2]['cost']) < 0) {
                                                $error .= 'Special Price - Cost result in negative value!';
                                            }
                                        }
                                        elseif(in_array('price', $fieldList) && in_array('cost', $fieldList)) {
                                            if(($cleanData[$key-2]['price'] - $cleanData[$key-2]['cost']) < 0) {
                                                $error .= 'Price - Cost results in negative value! ';
                                            }
                                        }
                                        elseif(in_array('special_price', $fieldList) && in_array('cost', $fieldList)) {
                                            if(($cleanData[$key-2]['special_price'] - $cleanData[$key-2]['cost']) < 0) {
                                                $error .= 'Special Price - Cost result in negative value!';
                                            }
                                        }

                                        if(!empty($error)) {
                                            $errors[] = $cell->getValue().';'.$error;
                                        }
                                    }
                                }
                            }
                        }
                        if(count($errors) > 1){
                            $objPHPExcelExport = new PHPExcel();
                            $objPHPExcelExport->setActiveSheetIndex(0);

                            $rowExport = 1;
                            foreach($errors as $rowError) {
                                $objPHPExcelExport->getActiveSheet()->setCellValue('A'.$rowExport, $rowError);
                                $rowExport++;
                            }

                            $errFileName = explode('.', $dataRun['filename']);
                            $extension = $errFileName[count($errFileName)-1];
                            $errFileName[count($errFileName)-1] = '_error';
                            $exportFilename = implode('', $errFileName).'.'.$extension;

                            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcelExport, 'CSV')->setDelimiter(';')
                                //->setEnclosure('"')
                                //->setLineEnding("\r\n")
                                ->setSheetIndex(0)
                                //->setSheetIndex(0)
                                ->save(str_replace('.php', '.csv', Mage::getConfig()->getVarDir().'/pricevalidation/import/'.$cleanDir.$exportFilename));
                        }

                        //$model->pending($invokeStatus)->save();
                        //Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('bilna_pricevalidation')->__('Profile started successfully'));
                    }
                }

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('profile_id' => $model->getProfileId()));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find profile to save'));
        $this->_redirect('*/*/');
    }

    //Check currently called action by permissions for current user
    //@return bool
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('bilna/bilna_pricevalidation_pricevalidation');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_grid')->toHtml()
        );
    }

    public function uploadAction()
    {
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('csv','txt','*'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $target = Mage::getConfig()->getVarDir('pricevalidation/import');
            Mage::getConfig()->createDirIfNotExists($target);
            $result = $uploader->save($target);

            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}