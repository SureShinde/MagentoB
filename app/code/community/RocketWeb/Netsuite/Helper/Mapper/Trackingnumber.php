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

class RocketWeb_Netsuite_Helper_Mapper_Trackingnumber extends RocketWeb_Netsuite_Helper_Mapper {
    public function getMagentoFormat($trackData, RecordRef $shipMethod) {
        $magentoTracking = Mage::getModel('sales/order_shipment_track');
        $carrierCode = $this->getMagentoCarrierCodeFromNetsuiteInternalId($shipMethod->internalId);
        $magentoTracking->setNumber($trackData['number'])
            ->setCarrierCode($carrierCode)
            ->setTitle($trackData['description']);

        return $magentoTracking;
    }

    protected function getMagentoCarrierCodeFromNetsuiteInternalId($internalNetsuiteId) {
        $carrierCode = Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/default_tracking_code_carrier');
        $carrierCodeMap = unserialize(Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/tracking_mapping'));
        foreach($carrierCodeMap as $carrierCodeMapItem) {
            if($carrierCodeMapItem['internal_netsuite_id'] == $internalNetsuiteId) {
                return $carrierCodeMapItem['carrier_type'];
            }
        }
        return $carrierCode;
    }

    public function getNormalizedTrackingNumberData(ItemFulfillment $netsuiteShipment) {
        //Net Suite will store packages in different data structures based on the shipping carrier.
        //This method will normalize the tracking numbers in a single array
        $trackingNumbers = array();

        if(isset($netsuiteShipment->packageList) && is_array($netsuiteShipment->packageList->package)) {
            foreach($netsuiteShipment->packageList->package as $netsuitePackage) {
                if(!empty($netsuitePackage->packageTrackingNumber)) {
                    $trackingNumbers[] = array('number'=>$netsuitePackage->packageTrackingNumber,'description'=>$netsuitePackage->packageDescr);
                }
            }
        }
        if(isset($netsuiteShipment->packageFedExList) && is_array($netsuiteShipment->packageFedExList->packageFedEx)) {
            foreach($netsuiteShipment->packageFedExList->packageFedEx as $netsuitePackage) {
                if(!empty($netsuitePackage->packageTrackingNumberFedEx)) {
                    $trackingNumbers[] = array('number'=>$netsuitePackage->packageTrackingNumberFedEx,'description'=>'');
                }
            }
        }
        if(isset($netsuiteShipment->packageUpsList) && is_array($netsuiteShipment->packageUpsList->packageUps)) {
            foreach($netsuiteShipment->packageUpsList->packageUps as $netsuitePackage) {
                if(!empty($netsuitePackage->packageTrackingNumberUps)) {
                    $trackingNumbers[] = array('number'=>$netsuitePackage->packageTrackingNumberUps,'description'=>$netsuitePackage->packageDescrUps);
                }
            }
        }
        if(isset($netsuiteShipment->packageUspsList) && is_array($netsuiteShipment->packageUspsList->packageUsps)) {
            foreach($netsuiteShipment->packageUspsList->packageUsps as $netsuitePackage) {
                if(!empty($netsuitePackage->packageTrackingNumberUsps)) {
                    $trackingNumbers[] = array('number'=>$netsuitePackage->packageTrackingNumberUsps,'description'=>$netsuitePackage->packageDescrUsps);
                }
            }
        }

        return $trackingNumbers;

    }

}