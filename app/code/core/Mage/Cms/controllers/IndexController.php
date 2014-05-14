<?php
/**
 * Magento
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
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cms index controller
 *
 * @category   Mage
 * @package    Mage_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Cms_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Renders CMS Home page
     *
     * @param string $coreRoute
     */
    public function indexAction($coreRoute = null)
    {
        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultIndex');
        }
    }

    /**
     * Default index action (with 404 Not Found headers)
     * Used if default page don't configure or available
     *
     */
    public function defaultIndexAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Render CMS 404 Not found page
     *
     * @param string $coreRoute
     */
    public function noRouteAction($coreRoute = null)
    {
        
        $url = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($url,'?superdonkey') == false) {
	        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
	        $pattern = '/http:\/\/bilna.local\/([a-z0-9-_]*)?((\/[a-zA-Z0-9-_]*(\.html)?)*)?/';
	        preg_match($pattern, $url, $matches);
	        $storeview = $matches[1];
	        $mainUrl = $matches[2];
	        
            if($storeview == "perlengkapan_rumah"){
                $url = str_replace($storeview, "perlengkapan-rumah", $url);
            }else{
                $pattern = '/\/blog/';
                preg_match($pattern, $mainUrl, $matches);
                if(isset($matches[0]) && !empty($matches[0])){
                    $extra = str_replace($matches[0], "", $mainUrl);
            
                    $url = "http://www.bilna.com".$matches[0]."/".$storeview.$extra;
                }
            }
            $url = $url."?superdonkey";

            $httpCode = $this->checkUrl($url);
            if($httpCode == 404) {
                $pattern = '/\/blog/';
                preg_match($pattern, $mainUrl, $matches);
                if(isset($matches[0]) && !empty($matches[0])){
                    $extra = str_replace($matches[0], "", $mainUrl);
            
                    $url = "http://www.bilna.com".$matches[0].$extra;
                }else{
                    $url = str_replace($storeview."/", "", $url);
                }
            	$url = $url."?superdonkey";
            
                $httpCode = $this->checkUrl($url);
                if($httpCode == 404) {
                    $url = "http://www.bilna.com".$mainUrl."~superdonkey";
            
                    $httpCode = $this->checkUrl($url);
                    if($httpCode == 404) {
                        header("Location: http://www.bilna.com/");
                        die();
                    }
                }
            }
            $url = str_replace("?superdonkey", "", $url);

            if($url == $currentUrl){
            	$pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
            	if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            		$this->_forward('defaultNoRoute');
            	}
            }else{
            	header("Location: ".$url);
            	die();
            }
        }else{
        	$pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
        	if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
        		$this->_forward('defaultNoRoute');
        	}
        }

        /*$pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
        }*/
    }

    public function checkUrl($url){
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);
        
        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        curl_close($handle);
        
        return $httpCode;
    }
    /**
     * Default no route page action
     * Used if no route page don't configure or available
     *
     */
    public function defaultNoRouteAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Render Disable cookies page
     *
     */
    public function noCookiesAction()
    {
        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_COOKIES_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoCookies');;
        }
    }

    /**
     * Default no cookies page action
     * Used if no cookies page don't configure or available
     *
     */
    public function defaultNoCookiesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
