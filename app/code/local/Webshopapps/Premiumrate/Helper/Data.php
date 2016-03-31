<?php
/**
 * Magento Webshopapps Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipping MatrixRates
 *
 * @category   Webshopapps
 * @package    Webshopapps_Premiumrate
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
*/

class Webshopapps_Premiumrate_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	
	public function processZipcode($readAdaptor, $customerPostcode,&$twoPhaseFiltering,
		&$zipString, &$shortMatchPostcode, &$longMatchPostcode ) {
			
        $debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Premiumrate');
		//$zipRangeSet = Mage::getStoreConfig("carriers/premiumrate/zip_range"); //TODO sort out for backward compatability
		//$ukFiltering = Mage::getStoreConfig("carriers/premiumrate/uk_postcode"); //TODO sort out for backward compatability
        $postcodeFilter = Mage::getStoreConfig("carriers/premiumrate/postcode_filter");       
        Mage::helper('wsalogger/log')->postDebug('premiumrate','Postcode Filter',$postcodeFilter,$debug);	
        
		$customerPostcode = trim($customerPostcode);
		$twoPhaseFiltering = false;
		if ($postcodeFilter == 'numeric' && is_numeric($customerPostcode)) {			
			$zipString = ' AND '.$customerPostcode.' BETWEEN dest_zip AND dest_zip_to )';
			
		} else if ($postcodeFilter == 'uk' && strlen($customerPostcode)>4) {
			$twoPhaseFiltering = true;
			$longPostcode=substr_replace($customerPostcode,"",-3);
			$longMatchPostcode = trim($longPostcode);
			$shortMatchPostcode = preg_replace('/\d/','', $longMatchPostcode);
			$shortMatchPostcode = $readAdaptor->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0)", $shortMatchPostcode);
		}  else if ($postcodeFilter == 'uk_numeric') {
			if(is_numeric($customerPostcode)){
				$zipString = ' AND '.$customerPostcode.' BETWEEN dest_zip AND dest_zip_to )';
			} else {
				$twoPhaseFiltering = true;
				$longPostcode=substr_replace($customerPostcode,"",-3);
				$longMatchPostcode = trim($longPostcode);
				$shortMatchPostcode = preg_replace('/\d/','', $longMatchPostcode);
				$shortMatchPostcode = $readAdaptor->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0)", $shortMatchPostcode);
			}
		} else if ($postcodeFilter == 'canada') { 
			// first search complete postcode
			// then search exact match on first 3 chars
			// then search range
			$shortPart = substr($customerPostcode,0,3);
			if (strlen($shortPart) < 3 || !is_numeric($shortPart[1]) || !ctype_alpha($shortPart[2])) {
				$zipString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $customerPostcode);
			} else {
				$suffix = strtoupper($shortPart[2]);
				$zipFromRegExp='^'.$shortPart[0].'[0-'.$shortPart[1].'][A-'.$suffix.']$';
				$zipToRegExp='^'.$shortPart[0].'['.$shortPart[1].'-9]['.$suffix.'-Z]$';
				$shortMatchPostcode = $readAdaptor->quoteInto(" AND dest_zip REGEXP ?", $zipFromRegExp).$readAdaptor->quoteInto(" AND dest_zip_to REGEXP ? )",$zipToRegExp );
				$longMatchPostcode = $customerPostcode;
				$twoPhaseFiltering = true;
			}
		} else if ($postcodeFilter == 'can_numeric') { 
			if (is_numeric($customerPostcode)){
				$zipString = ' AND '.$customerPostcode.' BETWEEN dest_zip AND dest_zip_to )';
			} else {
				// first search complete postcode
				// then search exact match on first 3 chars
				// then search range
				$shortPart = substr($customerPostcode,0,3);
				if (strlen($shortPart) < 3 || !is_numeric($shortPart[1]) || !ctype_alpha($shortPart[2])) {
					$zipString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $customerPostcode);
				} else {
					$suffix = strtoupper($shortPart[2]);
					$zipFromRegExp='^'.$shortPart[0].'[0-'.$shortPart[1].'][A-'.$suffix.']$';
					$zipToRegExp='^'.$shortPart[0].'['.$shortPart[1].'-9]['.$suffix.'-Z]$';
					$shortMatchPostcode = $readAdaptor->quoteInto(" AND dest_zip REGEXP ?", $zipFromRegExp).$readAdaptor->quoteInto(" AND dest_zip_to REGEXP ? )",$zipToRegExp );
					$longMatchPostcode = $customerPostcode;
					$twoPhaseFiltering = true;
				}
			} 
		} else {
			 $zipString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $customerPostcode);
		}
		
		if ($debug) {
        	Mage::helper('wsalogger/log')->postDebug('premiumrate','Postcode Range Search String',$zipString);	
        	if ($twoPhaseFiltering) {
        		Mage::helper('wsalogger/log')->postDebug('premiumrate','Postcode 2 Phase Search String','short match:'.$shortMatchPostcode.
        			', long match:'.$longMatchPostcode);	
        	}
    	}
				
	}

	public function checkImportedItemsAvailability($request)
	{
		$contain_local = 0;
		$contain_import = 0;
		$status = array();

		$total_volweight = 0;
		$configurableQty = 0;

		$all_items = $request->getAllItems();

		foreach($all_items as $item) 
		{
			$product = Mage::getModel('catalog/product')->load( $item->getProductId() );
			$currentQty = $item->getQty();

			if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				$configurableQty = $currentQty;
				continue;
			} 
			elseif ($configurableQty > 0) 
			{
				$currentQty = $configurableQty;
				$configurableQty = 0;
			}

			$parentQty = 1;

			if ($item->getParentItem()!=null) 
			{
				if ($item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) 
				{
					$parentQty = $item->getParentItem()->getQty();
				}
			}

			$qty = $currentQty * $parentQty;
			$total_volweight += ($product->getVolumeWeight() * $qty);

            if ( is_null($product->getIsImport()) || $product->getIsImport() == 0 )
            {
            	$contain_local = 1;
            	$status['local_items']['product_ids'][] = $item->getProductId();

            	// initialize ( if not set yet )
            	if (!(isset($status['local_items']['qty']) && isset($status['local_items']['weight']) && isset($status['local_items']['price']) && isset($status['local_items']['volweight'])))
            	{
            		$status['local_items']['qty'] = 0;
            		$status['local_items']['weight'] = 0;
            		$status['local_items']['price'] = 0;
            		$status['local_items']['volweight'] = 0;
            	}

            	$status['local_items']['qty'] += $item->getQty();
        		$status['local_items']['weight'] += ( $item->getQty() * $item->getWeight() );
        		$status['local_items']['price'] += ( $item->getQty() * $item->getPrice() );
        		$status['local_items']['volweight'] += ( $product->getVolumeWeight() * $item->getQty() );
            }
            else
            {
            	$contain_import = 1;
            	$status['import_items']['product_ids'][] = $item->getProductId();

            	// initialize ( if not set yet )
            	if (!(isset($status['import_items']['qty']) && isset($status['import_items']['weight']) && isset($status['import_items']['price']) && isset($status['import_items']['volweight'])))
            	{
            		$status['import_items']['qty'] = 0;
            		$status['import_items']['weight'] = 0;
            		$status['import_items']['price'] = 0;
            		$status['import_items']['volweight'] = 0;
            	}

            	$status['import_items']['qty'] += $item->getQty();
        		$status['import_items']['weight']+= ( $item->getQty() * $item->getWeight() );
        		$status['import_items']['price'] += ( $item->getQty() * $item->getPrice() );
        		$status['import_items']['volweight'] += ( $product->getVolumeWeight() * $item->getQty() );
            }

			$product=Mage::getModel('catalog/product')->load( $item->getProductId() );
			
		}

        if ($contain_local == 1 && $contain_import == 1)
        	$status['item_status'] = Webshopapps_Premiumrate_Model_Carrier_Premiumrate::ITEMS_MIXED;
        else
        {
        	if ($contain_local == 1)
        		$status['item_status'] = Webshopapps_Premiumrate_Model_Carrier_Premiumrate::ITEMS_LOCAL;
        	else
        	if ($contain_import == 1)
        		$status['item_status'] = Webshopapps_Premiumrate_Model_Carrier_Premiumrate::ITEMS_IMPORT;
        }

        return $status;
	}
	
}