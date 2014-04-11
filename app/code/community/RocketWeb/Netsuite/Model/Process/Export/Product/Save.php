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
class RocketWeb_Netsuite_Model_Process_Export_Product_Save extends RocketWeb_Netsuite_Model_Process_Export_Abstract {
	public function process(RocketWeb_Netsuite_Model_Queue_Message $message) {
		if(!$message || !$message->getEntityId()) {
			throw new Exception("Message not initialized");
		}
		
		$magentoProduct = Mage::getModel('catalog/product')->load($message->getEntityId());
		if(!$magentoProduct || !$magentoProduct->getId()) {
			throw new Exception("Cannot load product with id #{$message->getEntityId()} from Magento!");
		}
		
		$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
		$productExists = Mage::helper('rocketweb_netsuite/mapper_product')->productExistsInNetsuite($magentoProduct);
		$netsuiteProduct = Mage::helper('rocketweb_netsuite/mapper_product')->getNetsuiteFormat($magentoProduct);
		
		if(!$productExists) {
			$request = new AddRequest();
			$request->record = $netsuiteProduct;
			$response = $netsuiteService->add($request);
		}
		else {
			$request = new UpdateRequest();
			$request->record = $netsuiteProduct;
			$response = $netsuiteService->update($request);
		}
		
		
		if($response->writeResponse->status->isSuccess) {
			$netsuiteId = $response->writeResponse->baseRef->internalId;
			$magentoProduct->setNetsuiteInternalId($netsuiteId);
			$magentoProduct->getResource()->saveAttribute($magentoProduct, 'netsuite_internal_id');
		}
		else {
			if($response->writeResponse->status->statusDetail[0]->code == 'DUP_ENTITY') {
				$request = new UpdateRequest();
				$request->record = $netsuiteProduct;
				$netsuiteService->update($request);
			}
			else {
				throw new Exception((string) print_r($response->writeResponse->status->statusDetail,true));
			}
		}

        $inventoryAdjustmentRequest = Mage::helper('rocketweb_netsuite/mapper_product')->getInventoryAdjustmentRequestForNewProduct($magentoProduct,$netsuiteId);
        if($inventoryAdjustmentRequest && $request instanceof AddRequest) {
            $response = $netsuiteService->add($inventoryAdjustmentRequest);
            if(!$response->writeResponse->status->isSuccess) {
                throw new Exception((string) print_r($response->writeResponse->status->statusDetail,true));
            }
        }
		
	}
}