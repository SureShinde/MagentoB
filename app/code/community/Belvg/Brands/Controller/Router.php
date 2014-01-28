<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * Brands separate controller action
 */
class Belvg_Brands_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{

    /**
     * Inject new route into the list of routes
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();

        $brand = new Belvg_Brands_Controller_Router();
        $front->addRouter('brand', $brand);
    }

    /**
     * Compare current path with the route rules
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {

        if (!Mage::app()->isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                    ->setRedirect(Mage::getUrl('install'))
                    ->sendResponse();
            return FALSE;
        }

        if (!Mage::helper('brands')->isActive()) {
            return FALSE;
        }

        $route = Mage::helper('brands')->getRoute();

        $identifier = $request->getPathInfo();

        if (substr(str_replace("/", "", $identifier), 0, strlen($route)) == $route) {
            if (str_replace("/", "", $identifier) == $route) {
                $request->setModuleName('brands')
                        ->setControllerName('index')
                        ->setActionName('index');
                return TRUE;
            }

            $suffix = Mage::helper('catalog/product')->getProductUrlSuffix();
            $identifier = substr_replace($request->getPathInfo(), '', 0, strlen("/" . $route . "/"));
            $identifier = str_replace($suffix, '', $identifier);

            $brands = Mage::getModel('brands/brands');
            $brand_id = $brands->checkIdentifier($identifier, Mage::app()->getStore()->getId());
            if (!$brand_id) {
                return FALSE;
            }

            $request->setModuleName('brands')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam('id', $brand_id);

            return TRUE;
        } else {
            return FALSE;
        }
    }

}
