<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Model_Representations_Config
{
    const CACHE_KEY = 'awfeatured_rpr_config';
    const CACHE_TIME = 600;

    private $_config = null;

    public function getConfig()
    {
        if (is_null($this->_config)) {
            $this->_loadRepresentationsConfig();
        }
        return $this->_config;
    }

    public function getRepresentation($key)
    {
        $_r = $this->getRepresentations();
        if (isset($_r[$key])) {
            return $_r[$key];
        }
        return NULL;
    }

    /**
     * Covers Magento bug in 1.3.2.4
     * 
     * @param Varien_Simplexml_Element $element
     * @return array
     */
    protected function _varienSimpleXMLElementAsArray($element)
    {
        if (Mage::helper('awfeatured')->checkVersion('1.4')) {
            return $element->asArray();
        }

        $result = array();
        foreach ($element as $key => $value) {
            if (get_class($value) == 'Varien_Simplexml_Element' && count($value) > 0) {
                $result[$key] = $this->_varienSimpleXMLElementAsArray($value);
            } else {
                $result[$key] = (string) $value;
            }
        }
        return $result;
    }

    public function getRepresentations()
    {
        $repr = array();
        foreach ($this->getConfig()->getNode() as $key => $options) {
            $repr[$key] = new Varien_Object($this->_varienSimpleXMLElementAsArray($options));
        }
        return $repr;
    }

    protected function _loadRepresentationsConfig($forceCache = false)
    {
        $cacheLoad = $this->_loadCache();
        if ($cacheLoad && !$forceCache) {
            return $this;
        }
        $this->_config = $this->loadFromXmlFiles();
        $this->_saveCache();
        return $this;
    }

    public function loadFromXmlFiles()
    {
        $path = Mage::getConfig()->getOptions()->getCodeDir() . DS . 'local' . DS
            . 'AW' . DS . 'Featured' . DS . 'etc' . DS.'representations';
        $files = glob($path . DS . '*.xml');
        $rprConfig = new Varien_Simplexml_Config();
        foreach ($files as $file) {
            if (!$rprConfig->getNode()) {
                $rprConfig = new Varien_Simplexml_Config($file);
            } else {
                $tmpConfig = new Varien_Simplexml_Config($file);
                $rprConfig->extend($tmpConfig);
            }
        }
        $this->_config = $rprConfig;
        return $this->_config;
    }

    protected function _prepareConfigCache()
    {
        if (!$this->_config) {
            $this->_config = new Varien_Simplexml_Config();
            $this->_config->setCacheChecksum(NULL);
        } else {
            $this->_config->setCacheChecksum($this->_config->getXmlString());
        }
        $this->_config->setCache(Mage::app()->getCache())
            ->setCacheId(self::CACHE_KEY)
            ->setCacheLifetime(self::CACHE_TIME)
        ;
        return $this->_config;
    }

    protected function _saveCache()
    {
        return $this->_prepareConfigCache()->saveCache();
    }

    protected function _loadCache()
    {
        return $this->_prepareConfigCache()->loadCache();
    }
    
    public function refreshCache()
    {
        $this->_loadRepresentationsConfig(true);
        $this->_saveCache();
        return $this;
    }
}
