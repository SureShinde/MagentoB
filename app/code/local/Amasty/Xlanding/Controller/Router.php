<?php

class Amasty_Xlanding_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();
        $front->addRouter('amlanding', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
     	if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        
        $identifier = trim($request->getPathInfo(), '/');
        
        if ($pos = strpos($identifier, '/')) {
        	$identifier = substr($identifier, 0, $pos);
        } else if ($pos = strpos($identifier, '?')) {
        	$identifier = substr($identifier, 0, $pos);
        }

        $condition = new Varien_Object(array(
            'identifier' => $identifier,
        ));
        
        
		/* @var $page Amasty_Xlanding_Model_Page */
        $page   = Mage::getModel('amlanding/page');
        $pageId = $page->checkIdentifier($identifier, Mage::app()->getStore()->getId());
        if (!$pageId) {
            return false;
        }
        
        
    	$pathInfo = $request->getPathInfo();
        // remove suffix if any
        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        if ($suffix && '/' != $suffix){
            $pathInfo = str_replace($suffix, '', $pathInfo);
        }
        //add trailing slash
        $pathInfo = trim($pathInfo, '/') . '/';
        
        
        list($cat, $params) = explode($identifier, $pathInfo, 2);

        $params = trim($params, '/');
        if ($params)
            $params = explode('/', $params);
        // remember for futire use in the helper
        if ($params){
            Mage::register('amshopby_current_params', $params);
        }

        $request->setModuleName('amlanding')
            ->setControllerName('page')
            ->setActionName('view')
            ->setParam('page_id', $pageId)
            ->setParam('am_landing', $identifier);
        return true;
    }
}
