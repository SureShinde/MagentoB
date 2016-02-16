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
class RocketWeb_Netsuite_Model_Process_Export_Order_Place extends RocketWeb_Netsuite_Model_Process_Export_Abstract {

    /**
     * Submits the order to Netsuite
     *
     * @param RocketWeb_Netsuite_Model_Queue_Message $message
     * @throws Exception
     */
    public function process(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array ()) {

        if(!$message || !$message->getEntityId()) {
            throw new Exception("Message not initialized");
        }

        $magentoOrder = Mage::getModel('sales/order')->load($message->getEntityId());
        if(!$magentoOrder || !$magentoOrder->getId()) {
            throw new Exception("Cannot load order with id #{$message->getEntityId()} from Magento!");
        }

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        $netsuiteOrder = Mage::helper('rocketweb_netsuite/mapper_order')->getNetsuiteFormat($magentoOrder);

        Mage::dispatchEvent('netsuite_new_order_send_before',array('magento_order'=>$magentoOrder,'netsuite_order'=>$netsuiteOrder));

        $request = new AddRequest();
        $request->record = $netsuiteOrder;
        $response = $netsuiteService->add($request);

        if($response->writeResponse->status->isSuccess) {
            $netsuiteId = $response->writeResponse->baseRef->internalId;
            $magentoOrder->setNetsuiteInternalId($netsuiteId);
            $magentoOrder->getResource()->save($magentoOrder);

        }
        else {
            throw new Exception((string) print_r($response->writeResponse->status->statusDetail,true));
        }
    }



}