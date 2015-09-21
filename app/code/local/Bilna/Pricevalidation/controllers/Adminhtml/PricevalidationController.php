<?php

class Bilna_Pricevalidation_Adminhtml_PricevalidationController extends Mage_Adminhtml_Controller_Action
{
    private $silver = 'silver reseller';
    private $platinum = 'platinum reseller';

    public function indexAction()
    {
        $this->_title($this->__('Bilna'))->_title($this->__('Bilna Price Validation'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna/bilna');
        $this->renderLayout();
    }

    public function editAction() {
        $id     = $this->getRequest()->getParam('profile_id');
        $model  = Mage::getModel('bilna_pricevalidation/profile')->load($id)->factory();
        $modelLog  = Mage::getModel('bilna_pricevalidation/log')->getCollection()->addFieldToFilter('profile_id', $id);

        if ($model->getProfileId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('profile_data', $model);
            Mage::register('profile_log_data', $modelLog);

            $this->loadLayout();
            $this->_setActiveMenu('bilna/pricevalidation');

            $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true)
                ->setCanLoadRulesJs(true);

            $this->_addContent($this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit'))
                ->_addLeft($this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_pricevalidation_edit_tabs'));

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
                    $started = date('Y-m-d H:i:s');

                    $runningStatus = array(
                        'run_status' => 'running',
                        'started_at' => $started
                    );
                    $model->addData($runningStatus);
                    $model->setId($this->getRequest()->getParam('profile_id'));
                    $model->save();

                    $model->load($this->getRequest()->getParam('profile_id'));
                    $dataRun = $model->getData();

                    $cleanDir = '';

                    if(!empty($dataRun['base_dir'])){
                        $baseDir = explode('/', $dataRun['base_dir']);
                        if(count($baseDir) > 1){
                            if($baseDir[count($baseDir)-1] != '/') {
                                $baseDir[] = '/';
                            }
                            $cleanDir = implode('/', $baseDir);
                        }
                        else{
                            $cleanDir = $baseDir[0].'/';
                        }
                    }

                    if(!file_exists(Mage::getConfig()->getBaseDir('base').'/files/pricevalidation/import/'.$cleanDir.$dataRun['filename'])) {
                        Mage::getSingleton('adminhtml/session')->addError('File not found !');
                        $this->_redirect('*/*/edit', array('profile_id' => $this->getRequest()->getParam('profile_id')));
                        return;
                    }
                    else {
                        $columnsKeyToBeProcessed = array();
                        $cleanData = array();
                        $fieldList = array();
                        $errors = array();
                        $custGroup = array();

                        $listGroups = Mage::getModel('customer/group')->getCollection();
                        //echo "<pre>";print_r($listGroups->getData());die;
                        foreach($listGroups->getData() as $listGroup) {
                            if(strtolower($listGroup['customer_group_code']) == $this->silver) {
                                $custGroup['silver'] = $listGroup['customer_group_id'];
                            }
                            if(strtolower($listGroup['customer_group_code']) == $this->platinum) {
                                $custGroup['platinum'] = $listGroup['customer_group_id'];
                            }
                        }

                        $file = Mage::getConfig()->getBaseDir('base').'/files/pricevalidation/import/'.$cleanDir.$dataRun['filename'];
                        $csv = new Varien_File_Csv();
                        $csvFile = $csv->getData($file);
                        for($i = 0; $i < count($csvFile); $i++) {
                            $readyForUpdateGP = 0;
                            if($i == 0){ // Get Header
                                if (!is_null($csvFile[$i])) {
                                    $dataCsv = explode(';',$csvFile[$i][0]);
                                    if($dataCsv[count($dataCsv)-1] == ''){
                                        unset($dataCsv[count($dataCsv)-1]);
                                    }
                                    foreach($dataCsv as $keyColumn=>$RowColumnData) {
                                        foreach($postData['columns_post']['alias'] as $keyMatch=>$rowAlias) {
                                            if($rowAlias == $RowColumnData){
                                                $columnsKeyToBeProcessed[$postData['columns_post']['field'][$keyMatch]] = $keyColumn;
                                            }
                                        }
                                    }
                                }
                            }

                            if($i == 0){
                                $errors[] = $csvFile[$i][0].';Error Description';
                            }

                            if($i > 0) { // Skip price validation on header
                                if (!is_null($csvFile[$i][0])) {
                                    $error = '';
                                    $dataCsv = explode(';',$csvFile[$i][0]);
                                    foreach($columnsKeyToBeProcessed as $keyDataColumn=>$columnKey) {
                                        if(($keyDataColumn == 'ignore_flag') && (empty($dataCsv[$columnKey]))) {
                                            $dataCsv[$columnKey] = 0;
                                        }
                                        if(isset($dataCsv[$columnKey]) && !empty($dataCsv[$columnKey])) {
                                            $cleanData[$i-1][$keyDataColumn] = $dataCsv[$columnKey];
                                            $fieldList[] = $keyDataColumn;
                                        }
                                    }
                                    $csvFile[$i][0] = implode(';', $dataCsv);

                                    $productId = Mage::getModel('catalog/product')->getIdBySku($cleanData[$i-1]['SKU']);

                                    if(!empty($productId)) {
                                        if(in_array('price', $fieldList)) {
                                            if(!empty($cleanData[$i-1]['price'])) {
                                                if((int)floatval($cleanData[$i-1]['price']) < 0) {
                                                    $error .= 'Price value cannot smaller than 0 ! ';
                                                }
                                            }
                                            else {
                                                $error .= 'Price cannot empty ! ';
                                            }
                                        }
                                        if(in_array('cost', $fieldList)) {
                                            if(!empty($cleanData[$i-1]['cost'])) {
                                                if((int)floatval($cleanData[$i-1]['cost']) < 0) {
                                                    $error .= 'Cost value cannot smaller than 0 ! ';
                                                }
                                            }
                                            else {
                                                $error .= 'Cost cannot empty ! ';
                                            }
                                        }
                                        if(in_array('special_price', $fieldList)) {
                                            if(!empty($cleanData[$i-1]['special_price'])) {
                                                if((int)floatval($cleanData[$i-1]['special_price']) < 0) {
                                                    $error .= 'Special Price cannot smaller than 0 ! ';
                                                }
                                            }
                                            else {
                                                $error .= 'Special Price cannot empty ! ';
                                            }
                                        }

                                        if(empty($error)) {
                                            if(in_array('ignore_flag', $fieldList) && ($cleanData[$i-1]['ignore_flag'] == 1)) {
                                                $productId = Mage::getModel('catalog/product')->getIdBySku($cleanData[$i-1]['SKU']);
                                                $updateProduct = Mage::getModel('catalog/product')->load($productId);
                                                if(in_array('price', $fieldList)) {
                                                    if(!empty($cleanData[$i-1]['price'])) {
                                                        if((int)floatval($cleanData[$i-1]['price']) >= 0) {
                                                            $price = (int)floatval($cleanData[$i-1]['price']);
                                                            $readyForUpdateGP++;
                                                            $updateProduct->setPrice($price)->save();
                                                        }
                                                        else {
                                                            $error .= 'Price value cannot smaller than 0 ! ';
                                                        }
                                                    }
                                                }
                                                if(in_array('cost', $fieldList)) {
                                                    if(!empty($cleanData[$i-1]['cost'])) {
                                                        if((int)floatval($cleanData[$i-1]['cost']) >= 0) {
                                                            $cost = (int)floatval($cleanData[$i-1]['cost']);
                                                            $readyForUpdateGP++;
                                                            $updateProduct->setCost($cost)->save();
                                                        }
                                                        else {
                                                            $error .= 'Cost value cannot smaller than 0 ! ';
                                                        }
                                                    }
                                                }
                                                if(in_array('special_price', $fieldList)) {
                                                    if(!empty($cleanData[$i-1]['special_price'])) {
                                                        if((int)floatval($cleanData[$i-1]['special_price']) >= 0) {
                                                            $specialPrice = (int)floatval($cleanData[$i-1]['special_price']);
                                                            $updateProduct->setSpecialPrice($specialPrice)->save();
                                                        }
                                                        else {
                                                            $error .= 'Special Price cannot smaller than 0 ! ';
                                                        }
                                                    }
                                                }
                                            }
                                            else {
                                                if(in_array('price', $fieldList) && in_array('cost', $fieldList) && in_array('special_price', $fieldList)) {
                                                    if(($cleanData[$i-1]['price'] - $cleanData[$i-1]['cost']) < 0) {
                                                        $error .= 'Price - Cost results in negative value! ';
                                                    }
                                                    if(($cleanData[$i-1]['special_price'] - $cleanData[$i-1]['cost']) < 0) {
                                                        $error .= 'Special Price - Cost result in negative value! ';
                                                    }
                                                }
                                                elseif(in_array('price', $fieldList) && in_array('cost', $fieldList)) {
                                                    if(($cleanData[$i-1]['price'] - $cleanData[$i-1]['cost']) < 0) {
                                                        $error .= 'Price - Cost results in negative value! ';
                                                    }
                                                }
                                                elseif(in_array('special_price', $fieldList) && in_array('cost', $fieldList)) {
                                                    if(($cleanData[$i-1]['special_price'] - $cleanData[$i-1]['cost']) < 0) {
                                                        $error .= 'Special Price - Cost result in negative value! ';
                                                    }
                                                }

                                                if(empty($error)) {
                                                    $productId = Mage::getModel('catalog/product')->getIdBySku($cleanData[$i-1]['SKU']);
                                                    $updateProduct = Mage::getModel('catalog/product')->load($productId);
                                                    if(in_array('price', $fieldList)) {
                                                        if(!empty($cleanData[$i-1]['price'])) {
                                                            $price = (int)floatval($cleanData[$i-1]['price']);
                                                            $readyForUpdateGP++;
                                                            $updateProduct->setPrice($price)->save();
                                                        }
                                                    }
                                                    if(in_array('cost', $fieldList)) {
                                                        if(!empty($cleanData[$i-1]['cost'])) {
                                                            $cost = (int)floatval($cleanData[$i-1]['cost']);
                                                            $readyForUpdateGP++;
                                                            $updateProduct->setCost($cost)->save();
                                                        }
                                                    }
                                                    if(in_array('special_price', $fieldList)) {
                                                        if(!empty($cleanData[$i-1]['special_price'])) {
                                                            $specialPrice = (int)floatval($cleanData[$i-1]['special_price']);
                                                            $updateProduct->setSpecialPrice($specialPrice)->save();
                                                        }
                                                    }
                                                }
                                            }
                                            /* Customer Group Price Section Start */
                                            if((empty($error) && ($readyForUpdateGP == 2))) {
                                                $grossMargin = $price - $cost;
                                                foreach ($updateProduct->getData('group_price') as $productData) {
                                                    if (in_array($productData['cust_group'], $custGroup)) {
                                                        if ($productData['cust_group'] == $custGroup['silver']) {
                                                            $updateGroupPrice = $grossMargin - ($grossMargin * 0.15); // 15%
                                                            $groupPriceUpdate[] = array(
                                                                'website_id' => 0,
                                                                'cust_group' => 2,
                                                                'price' => $updateGroupPrice
                                                            );
                                                        } elseif ($productData['cust_group'] == $custGroup['platinum']) {
                                                            $updateGroupPrice = $grossMargin - ($grossMargin * 0.75); // 75%
                                                            $groupPriceUpdate[] = array(
                                                                'website_id' => 0,
                                                                'cust_group' => 4,
                                                                'price' => $updateGroupPrice
                                                            );
                                                        }
                                                    } else {
                                                        $groupPriceUpdate[] = array(
                                                            'website_id' => $productData['website_id'],
                                                            'cust_group' => $productData['cust_group'],
                                                            'price' => $productData['price']
                                                        );
                                                    }
                                                }
                                                $updateProduct->setData('group_price', $groupPriceUpdate);
                                                $updateProduct->save();
                                            }
                                            /* Customer Group Price Section End */
                                        }
                                    }
                                    else {
                                        $error .= 'SKU error!';
                                    }
                                    if(!empty($error)) {
                                        $errors[] = $csvFile[$i][0].';'.$error;
                                    }
                                }
                            }
                            $totalRow = $i;
                        }

                        if(count($errors) > 1){
                            $errFileName = explode('.', $dataRun['filename']);
                            $extension = $errFileName[count($errFileName)-1];
                            $errFileName[count($errFileName)-1] = '_error_'.Mage::getModel('core/date')->date('Y-m-d_H:i:s');
                            $exportFilename = implode('', $errFileName).'.'.$extension;
                            $fileWrite = Mage::getConfig()->getBaseDir('base').'/files/pricevalidation/import/'.$cleanDir.$exportFilename;
                            $csvWrite = new Varien_File_Csv();
                            $csvExport = array();
                            $column = array();
                            //$csvExport = $a;
                            foreach($errors as $rowError) {
                                $column['A'] = $rowError;
                                $csvExport[] = $column;
                            }
                            $csvWrite->saveData($fileWrite, $csvExport);
                        }
                        $ended = date('Y-m-d H:i:s');

                        $postDataUpdate = array(
                            'run_status' => 'finished',
                            'finished_at' => $ended
                        );
                        $model->addData($postDataUpdate);
                        $model->setId($this->getRequest()->getParam('profile_id'))
                            ->save();

                        $dataLog = array(
                            'profile_id' => $this->getRequest()->getParam('profile_id'),
                            'started_at' => $started,
                            'finished_at' => $ended,
                            'rows_found' => $totalRow,
                            'rows_errors' => count($errors)-1,
                            'user_id' => Mage::getSingleton('admin/session')->getUser()->getData('user_id'),
                            'error_file' => isset($exportFilename) ? $exportFilename : '',
                            'base_dir' => $cleanDir,
                            'source_file' => $dataRun['filename']
                        );

                        $modelLog = Mage::getModel('bilna_pricevalidation/log');
                        $modelLog->addData($dataLog)->save();
                    }
                }

                if ($this->getRequest()->getParam('back')) {
                    Mage::getSingleton('core/session')->setSessionRun(true);
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

    public function gridlogAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bilna_pricevalidation/adminhtml_log_grid')->toHtml()
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

            $target = Mage::getConfig()->getBaseDir('base').'/files/pricevalidation/import';
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