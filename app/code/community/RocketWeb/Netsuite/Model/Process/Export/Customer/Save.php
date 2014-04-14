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
class RocketWeb_Netsuite_Model_Process_Export_Customer_Save extends RocketWeb_Netsuite_Model_Process_Export_Abstract {

    /**
     * @param RocketWeb_Netsuite_Model_Queue_Message $message
     * @throws Exception
     */
    public function process(RocketWeb_Netsuite_Model_Queue_Message $message) {

		if(!$message || !$message->getEntityId()) {
			throw new Exception("Message not initialized");
		}
		
		$magentoCustomer = Mage::getModel('customer/customer')->load($message->getEntityId());
		if(!$magentoCustomer || !$magentoCustomer->getId()) {
			throw new Exception("Cannot load customer with id #{$message->getEntityId()} from Magento!");
		}
		
		$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
		$customerExists = Mage::helper('rocketweb_netsuite/mapper_customer')->findNetsuiteCustomer('email', Mage::helper('rocketweb_netsuite/mapper_customer')->getExternalId($magentoCustomer));
		$netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getNetsuiteFormat($magentoCustomer);

        Mage::dispatchEvent('netsuite_customer_send_before',array('netsuite_customer'=>$netsuiteCustomer));
		
		if(!$customerExists) {
			$request = new AddRequest();
			$request->record = $netsuiteCustomer;
			$response = $netsuiteService->add($request);
		}
		else {
			$request = new UpdateRequest();
            $netsuiteCustomer->internalId = $customerExists;
            unset($netsuiteCustomer->entityId);
			$request->record = $netsuiteCustomer;
			$response = $netsuiteService->update($request);
		}
		
		if($response->writeResponse->status->isSuccess) {
			$netsuiteId = $response->writeResponse->baseRef->internalId;
			$magentoCustomer->setNetsuiteInternalId($netsuiteId);
			$magentoCustomer->getResource()->saveAttribute($magentoCustomer, 'netsuite_internal_id');
		}
		else {
			if($response->writeResponse->status->statusDetail[0]->code == 'DUP_ENTITY') {
				$request = new UpdateRequest();
                unset($netsuiteCustomer->entityId);
				$request->record = $netsuiteCustomer;
				$response = $netsuiteService->update($request);
			}
			else {
				throw new Exception((string) print_r($response->writeResponse->status->statusDetail,true));
			}
		}
	}
}