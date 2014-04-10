<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


class Brim_PageCache_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @param SimpleXMLElement $xml
     * @param array $options
     * @return SimpleXMLElement
     */
    public function addOptionsToXml(SimpleXMLElement $xml, array $options) {

        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $childXml = $xml->addChild($key);
                $this->addOptionsToXml($childXml, $value);
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $xml;
    }

    /**
     *
     *
     * @param array $headers
     * @param Zend_Controller_Response_Http $response
     * @return Zend_Controller_Response_Http
     */
    public function copyCachedHeadersToResponse($headers = array(), Zend_Controller_Response_Http $response) {
        foreach ($headers as $header) {
            if (strcasecmp($header['name'], 'status') === 0) {
                $response->setHeader($header['name'], $header['value']);
                if (($responseCode = (int) $header['value']) > 0) {
                    // response code must be parsed from the status header as the cached header
                    // stores a 200 in http_response_code even when the page was a 404
                    $response->setHttpResponseCode($responseCode);
                }
            } else if (strcasecmp($header['name'], 'location') === 0
                || strcasecmp($header['name'], 'content-type') === 0) {
                $response->setHeader($header['name'], $header['value']);
            }
        }

        return $response;
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    public function enablePageCache() {

        $layout = Mage::app()->getLayout();

        if (!($rootBlock = $layout->getBlock('root'))) {
            $rootBlock = $layout->createBlock('core/template', 'root');

            $controllerAction = Mage::app()->getFrontController()->getAction();

            $controllerAction->loadLayout('brim_pagecache_default');
        }

        return $rootBlock;
    }

    /**
     * Filters out complex values from a data array.
     *
     * @param $data
     * @return mixed
     */
    public function filterComplexValues($data) {
        foreach ($data as $i => $v) {
            if (is_object($v) || is_array($v)) {
                unset($data[$i]);
            }
        }
        return $data;
    }

    /**
     * Detects html content by looking at the response headers.
     *
     * @param $response
     * @return bool
     */
    public function isHTML($response) {

        $result = false;
        foreach ($response->getHeaders() as $header) {
            if (strcasecmp($header['name'], 'content-type') === 0 && strpos($header['value'], 'html') !== false) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Strips a uri of params that should be ignored for the purposes of the cache key.  Appends session defined
     * session variables to the uri.
     *
     * @param $uri
     * @param null $ignoreParams
     * @return string
     */
    public static function normalizeUri($uri, $ignoreParams=null, $sessionVars=null) {

        if ($ignoreParams === null) {
            $ignoreParams = Mage::getStoreConfig('brim_pagecache/conditions/ignore_params');

            if (is_array($ignoreParams)) {
                foreach ($ignoreParams as $k => $v) {
                    if (isset($v['parameter'])) {
                        $ignoreParams[$k] = $v['parameter'];
                    }
                }
            }
        }

        if (is_string($ignoreParams)) {
            // need for the l1 cache.  params are comma separated.
            $ignoreParams = explode(',', $ignoreParams);
        }

        if (strpos($uri, '?') !== false) {
            // Parse out params from url
            list($path, $query) = explode('?', $uri);
            $newUri = $path;
            parse_str($query, $params);
        } else {
            // no params
            $newUri = $uri;
            $params = array();
        }

        // Injects cookie/session vars to the uri params
        if ($sessionVars === null) {
            $sessionVars = Mage::getSingleton('core/cookie')
                ->get(Brim_PageCache_Model_Config::SESSION_VARS_COOKIE_NAME);
        }
        if ($sessionVars != '') {
            try {
                $params = array_merge($params, Zend_Json::decode($sessionVars));
            } catch (Zend_Json_Exception $e) {
                // ignore if session vars are not json encoded.
            }
        }

        // remove params set to be ignored
        foreach ($ignoreParams as $ignoreParam) {
            if (isset($params[$ignoreParam])) {
                unset($params[$ignoreParam]);
            }
        }

        // Append what remains of the params
        if (count($params) > 0) {
            $newUri .= '?' . http_build_query($params);
        }

        return $newUri;
    }

    /**
     * Gets a request object with the original request uri.  Certain extension have modify the original request causing
     * incorrect result.  ie. different request being treated as one.
     *
     * @param null $request
     * @return Mage_Core_Controller_Request_Http|null
     */
    public static function getRequest($request=null) {
        if (is_null($request)) {
            if (!($request = Mage::registry('brim_pagecache_original_request'))) {
                $request = Mage::app()->getRequest()->getOriginalRequest();
            }
        }

        return $request;
    }
}
