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
class RocketWeb_Netsuite_Model_Process_Export_Customer_Delete extends RocketWeb_Netsuite_Model_Process_Export_Abstract {
	public function process(RocketWeb_Netsuite_Model_Queue_Message $message) {
		if(!$message || !$message->getEntityId()) {
			throw new Exception("Message not initialized");
		}
		
		$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
		
		$recordRef = new RecordRef();
		$recordRef->type = RecordType::customer;
		$recordRef->externalId = $message->getEntityId();
		
		$request = new DeleteRequest();
		$request->baseRef = $recordRef;
		$response = $netsuiteService->delete($request);
		
		return $response;
	}
}