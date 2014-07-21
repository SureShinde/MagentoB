<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */

class RocketWeb_Netsuite_Helper_Data extends Mage_Core_Helper_Data {

	protected $_netsuiteService = null;
	
	const LOG_FILE_NAME = 'netsuite.log';
	
	public function isEnabled() {
		return Mage::getStoreConfig('rocketweb_netsuite/general/enabled');
	}

    public function loadNetsuiteNamespace() {
        require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'NetSuiteService.php');
        require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'SoapClientWithTimeout.php');
    }

    public function getNonNullNetsuiteObjectProperties($netsuiteObject) {
        $vars = get_object_vars($netsuiteObject);
        $retVars = array();
        foreach($vars as $key=>$value) {
            if(!is_null($value)) $retVars[$key] = $value;
        }
        return $retVars;
    }

    //current_run_mode is set in shell/netsuite/netsuiteCron.php
    protected function getCurrentRunMode() {
        if(Mage::registry('current_run_mode')) return Mage::registry('current_run_mode');
        else return 'default';
    }

    protected function getSystemConfigPathForRunMode($runMode) {
        switch($runMode) {
            case 'import': $configPath ='connection_import';break;
            case 'export': $configPath ='connection_export';break;
            case 'stock': $configPath ='connection_stock';break;
            default: $configPath='general';
        }

        //if a separate connection is not defined, use the general one
        if(Mage::getStoreConfig('rocketweb_netsuite/'.$configPath.'/same')==0) {
            return $configPath;
        }
        else {
            return 'general';
        }
    }

    /**
     * @param null $connectionData
     * @return NetSuiteService|null
     */
    public function getNetsuiteService($connectionData = null) {
		/*
		 * The bad practice of using and rewriting data in some global variable is caused by how the Netsuite PHP toolkit is built. In the interest of not
		 * changing code in the toolkit we take this approach.
		 */
		$this->loadNetsuiteNamespace();

        $runMode = $this->getCurrentRunMode();
		global $nsendpoint,$nshost,$nsemail,$nspassword,$nsrole,$nsaccount;
		
		if(is_null($this->_netsuiteService[$runMode])) {
			
			if(is_null($connectionData)) {
                $configPath = $this->getSystemConfigPathForRunMode($runMode);
				$nshost = Mage::getStoreConfig('rocketweb_netsuite/general/host');
				$nsendpoint = Mage::getStoreConfig('rocketweb_netsuite/general/end_point');
				$nsaccount = Mage::getStoreConfig('rocketweb_netsuite/general/account_id');
				$nsemail = Mage::getStoreConfig("rocketweb_netsuite/{$configPath}/email");
				$nspassword = Mage::getStoreConfig("rocketweb_netsuite/{$configPath}/password");
				$nsrole = Mage::getStoreConfig("rocketweb_netsuite/{$configPath}/role_id");
			}
			else {
				$nshost = $connectionData['host'];
				$nsendpoint = $connectionData['end_point'];
				$nsaccount = $connectionData['account_id'];
				$nsemail = $connectionData['email'];
				$nspassword = $connectionData['password'];
				$nsrole = $connectionData['role_id'];
			}
			
			
			require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'NetSuiteService.php');
			$this->_netsuiteService[$runMode] = new NetSuiteService();

            $preferences = new Preferences();
            $preferences->ignoreReadOnlyFields = true;
            $this->_netsuiteService[$runMode]->addHeader('preferences',$preferences);
		}
		return $this->_netsuiteService[$runMode];
	}
	
	public function getServerTime() {
        static $serverTime = null;

        if(!$serverTime) {
            $this->loadNetsuiteNamespace();
            $getServerTimeRequest = new GetServerTimeRequest();
            $getServerTimeResult = $this->getNetsuiteService()->getServerTime($getServerTimeRequest);
            if($getServerTimeResult->getServerTimeResult->status->isSuccess) {
                $serverTime = $getServerTimeResult->getServerTimeResult->serverTime;
            }
            else {
                throw new Exception((string) print_r($getServerTimeResult->getServerTimeResult->status->statusDetail,true));
            }
        }

        return $serverTime;

    }
	
    public function log($message, $filename = '') {
        $filename = empty ($filename) ? self::LOG_FILE_NAME : $filename;
        Mage::log($message, Zend_Log::DEBUG, $filename);
    }

    public function getNetsuiteShippingMethodInternalId($magentoShippingMethodCode) {
        $shippingMapping = unserialize(Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/netsuite_mapping'));
        foreach($shippingMapping as $shippingMappingElement) {
            if($shippingMappingElement['shipping_method'] == $magentoShippingMethodCode) {
                return $shippingMappingElement['internal_netsuite_id'];
            }
        }

        return Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/nesuite_default_shipping_id');
    }

    public function getNetsuitePaymentMethodInternalId(Mage_Sales_Model_Order_Payment $magentoPaymentObject) {
        $paymentMapping = unserialize(Mage::getStoreConfig('rocketweb_netsuite/payment_methods/netsuite_mapping'));
        foreach($paymentMapping as $paymentMappingElement) {
            if($paymentMappingElement['payment_method'] == $magentoPaymentObject->getMethod()) {
                if($paymentMappingElement['payment_cc'] == '') {
                    return $paymentMappingElement['internal_netsuite_id'];
                }
                else {
                    if($magentoPaymentObject->getCcType() == $paymentMappingElement['payment_cc']) {
                        return $paymentMappingElement['internal_netsuite_id'];
                    }
                }
            }
        }
        return null;
    }

    public function getNetsuiteLocationForStockDeduction() {
        return Mage::getStoreConfig('rocketweb_netsuite/stock/order_location');
    }

    public function convertNetsuiteDateToSqlFormat($netsuiteDateString) {
        return date('Y-m-d H:i:s',strtotime($netsuiteDateString));
    }

    public function getImportDir() {
        return Mage::getBaseDir('media') . DS . 'import/';
    }

    protected $cachedLists = array();
    public function getListValue($listInternalId,$listItemInternalId) {
        if(!isset($this->cachedLists[$listInternalId])) {
            $getListRequest = new GetListRequest();
            $getListRequest->baseRef = new RecordRef();
            $getListRequest->baseRef->internalId = $listInternalId;
            $getListRequest->baseRef->type = RecordType::customList;

            $response = $this->getNetsuiteService()->getList($getListRequest);
            if($response->readResponseList->readResponse[0]->status->isSuccess == 1) {
                foreach($response->readResponseList->readResponse[0]->record->customValueList->customValue as $listValue) {
                    $this->cachedLists[$listInternalId][$listValue->valueId] = $listValue->value;
                }
            }
            else {
                throw new Exception((string) print_r($response->readResponseList->readResponse[0]->status->statusDetail,true));
            }
        }

        return $this->cachedLists[$listInternalId][$listItemInternalId];
    }

    protected $cachedRecordLists = array();
    public function getRecordListItem($searchClassName,$nameField,$listItemInternalId) {
        if(!isset($this->cachedRecordLists[$searchClassName])) {
            $request = new SearchRequest();
            $request->searchRecord = new $searchClassName;
            $response = $this->getNetsuiteService()->search($request);

            if($response->searchResult->status->isSuccess) {
                foreach($response->searchResult->recordList->record as $record) {
                    $this->cachedRecordLists[$searchClassName][$record->internalId] = $record->{$nameField};
                }
            }
            else {
                throw new Exception((string) print_r($response->searchResult->status->statusDetail));
            }
        }

        return $this->cachedRecordLists[$searchClassName][$listItemInternalId];
    }

    /*
     * This method is necessary as we cannot start a product search and inside it, do another (i.e. vendor) search
     * We will just search all standard lists and cache them before searching the products
     */
    public function cacheStandardLists() {
        $fieldMap = Mage::getModel('rocketweb_netsuite/config')->getConfigVarMapProductColumns('field_map',null,'products');
        foreach($fieldMap as $field) {
            if($field['netsuite_field_type'] == RocketWeb_Netsuite_Model_Product_Map_Value::FIELD_TYPE_RECORD) {
                $request = new SearchRequest();
                $request->searchRecord = new $field['netsuite_field_search_class_name'];
                $response = $this->getNetsuiteService()->search($request);

                if($response->searchResult->status->isSuccess) {
                    foreach($response->searchResult->recordList->record as $record) {
                        $this->cachedRecordLists[$field['netsuite_field_search_class_name']][$record->internalId] = $record->{$field['netsuite_field_name_field']};
                    }
                }
                else {
                    throw new Exception((string) print_r($response->searchResult->status->statusDetail));
                }
            }
        }
    }

    public function getProductDataDiff(Mage_Catalog_Model_Product $product) {
        $ignoredKeys = array('netsuite_last_import_date');

        $origData = $product->getOrigData();

        $differences = array();
        foreach($origData as $origKey=>$origValue) {
            if(in_array($origKey,$ignoredKeys)) {
                continue;
            }
            if($product->getData($origKey) != $origValue) {
                $differences[] = array('key'=>$origKey,'old'=>$origValue,'new'=>$product->getData($origKey));
            }
        }

        return $differences;
    }

    //Priorities help managing the order the items in the queue are processed, i.e. to make sure a configurable's simples are processed first
    public function getRecordPriority2($record) {
        if($record instanceof InventoryItem) {
            if($record->matrixType == ItemMatrixType::_parent) {
                return 1;
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }

    public function getRecordPriority($path) {
        if($path == 'order_fullfilment') {
            return 30;
        }
        elseif($path == 'order') {
            return 20;
        }
        elseif($path == 'cashsale' || $path == 'invoice') {
            return 10;
        }
        elseif($path == 'creditmemo') {
            return 40;
        }
        elseif($path == 'inventoryitem') {
            return 50;
        }
        else {
            return 0;
        }
    }

    public function getInvoiceTypeInNetsuite() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/invoices_netsuite_type');
    }

}