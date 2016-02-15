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
class RocketWeb_Netsuite_Helper_Transform extends Mage_Core_Helper_Data {
	/*
	 * For some data, Netsuite is using enumarations. For example, setting a country requires respecting the standard defined in the Country class.
	 * Sadly, the conventions are just ad-hoc conventions even for things like countries where ISOs exist. This class provides utility function that try
	 * to convert Magento "enums" to Netsuite ones.
	 */
	
	
	//receives a ISO country code, transforms it in "Netsuite" format. US -> _unitedStates
	public function transformCountryCode($countryCode) {
        $countryCodes = Mage::getConfig()->getNode('rocketweb_netsuite/mappings/country_codes')->asArray();

        foreach($countryCodes as $code=>$name) {
            if($code == $countryCode) {
                return $name;
            }
        }
        return null;
	}

    public function orderStateMagentoToNetsuite($magentoOrderState) {
        $statusToStateMap = Mage::getSingleton('rocketweb_netsuite/config')->convertDefaultMapOrderstatuses();
        foreach($statusToStateMap as $statusToStateMapItem) {
            if($statusToStateMapItem['magento_status'] == $magentoOrderState) {
                return $statusToStateMapItem['netsuite_status'];
            }
        }
        return null;
    }

    public function netsuiteStatusToMagentoOrderState($netsuiteStatus) {
        $statusToStateMap = Mage::getSingleton('rocketweb_netsuite/config')->convertDefaultMapOrderstatuses();
        foreach($statusToStateMap as $statusToStateMapItem) {
            if($statusToStateMapItem['netsuite_status'] == $netsuiteStatus) {
                return $statusToStateMapItem['magento_status'];
            }
        }
        return null;
    }

    public function regionCodeToRegionId($regionCode,$countryCode) {
        return Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode)->getId();
    }

    public function regionCodeToRegionName($regionCode,$countryCode) {
        return Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode)->getName();
    }

    public function netsuiteCountryToCountryCode($netsuiteCountryName) {
        $countryCodes = Mage::getConfig()->getNode('rocketweb_netsuite/mappings/country_codes')->asArray();
        foreach($countryCodes as $code=>$name) {
            if($netsuiteCountryName == $name) {
                return $code;
            }
        }
        return null;
     }

    public function getFileExtensionFromNetSuiteMediaType($mediaType) {
        switch($mediaType) {
            case '_AUTOCAD': return 'cad';
            case '_BMPIMAGE': return 'bmp';
            case '_CONFIG': return 'cfg';
            case '_CSV': return 'csv';
            case '_EXCEL': return 'xls';
            case '_FLASH': return 'fla';
            case '_GIFIMAGE': return 'gif';
            case '_GZIP': return 'zip';
            case '_HTMLDOC': return 'html';
            case '_ICON': return 'ico';
            case '_IMAGE': return 'jpg';
            case '_JAVASCRIPT': return 'js';
            case '_JPGIMAGE': return 'jpg';
            case '_JSON': return 'json';
            case '_MESSAGERFC': return 'rfc';
            case '_MISCBINARY': return 'bin';
            case '_MISCTEXT': return 'txt';
            case '_MP3': return 'mp3';
            case '_MPEGMOVIE': return 'mpeg';
            case '_MSPROJECT': return 'msp';
            case '_PDF': return 'pdf';
            case '_PJPGIMAGE': return 'jpg';
            case '_PLAINTEXT': return 'txt';
            case '_PNGIMAGE': return 'png';
            case '_POSTSCRIPT': return 'ps';
            case '_POWERPOINT': return 'ppt';
            case '_QUICKTIME': return 'qt';
            case '_RTF': return 'rtf';
            case '_SMS': return 'sms';
            case '_STYLESHEET': return 'css';
            case '_TAR': return 'tar';
            case '_TARCOMP': return 'tar';
            case '_TIFFIMAGE': return 'tiff';
            case '_VISIO': return 'visio';
            case '_WEBAPPPAGE': return 'html';
            case '_WEBAPPSCRIPT': return 'html';
            case '_WORD': return 'doc';
            case '_XMLDOC': return 'xml';
            case '_ZIP': return 'zip';
        }
    }

    public function cybersourceDecisionToNetsuitePaymentEventResult($decision) {
        switch($decision) {
            case 'ACCEPT':
                return TransactionPaymentEventResult::_accept;
            case 'REJECT':
            default:
                return TransactionPaymentEventResult::_reject;
        }
    }

    public function avsCodeToNetsuiteAvsCode($avsCode) {
        $avsCode = strtoupper(trim($avsCode));

        switch($avsCode) {
            case 'Y':
                return AvsMatchCode::_y;
            case 'X':
                return AvsMatchCode::_x;
            case 'N':
            default:
                return AvsMatchCode::_n;
        }
    }

    //public function
}