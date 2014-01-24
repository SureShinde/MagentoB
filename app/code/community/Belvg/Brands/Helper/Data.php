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
 * Brand main helper
 */
class Belvg_Brands_Helper_Data extends Belvg_Brands_Helper_Config
{

    /**
     * Get default image width
     *
     * @param string $type
     * @param int $store
     * @return int
     * @throws Exception
     */
    public function getLogoWidth($type, $store = '')
    {
        return $this->getSize('width', $type, $store);
    }

    /**
     * Get default image height
     *
     * @param string $type
     * @param int $store
     * @return int
     * @throws Exception
     */
    public function getLogoHeight($type, $store = '')
    {
        return $this->getSize('height', $type, $store);
    }

    /**
     * Getter for existing image dimension
     *
     * @param sting $size_type
     * @param string $type
     * @param int $store
     * @return int
     * @throws Exception
     */
    protected function getSize($size_type, $type, $store)
    {
        $method = 'get' . $type . ucfirst(strtolower($size_type));
        if (method_exists($this, $method)) {
            return $this->$method($store);
        } else {
            throw new Exception($this->__('Image %s with type "%s" does not exist', $size_type, $type));
        }
    }

    /**
     * Set layout for brands list
     *
     * @param Mage_Core_Controller_Varien_Action $action
     * @return false
     */
    public function renderListLayout(Mage_Core_Controller_Varien_Action $action)
    {
        $this->renderLayout($action, $this->getListLayout());
    }

    /**
     * Set layout for brand view page
     *
     * @param Mage_Core_Controller_Varien_Action $action
     * @return false
     */
    public function renderViewLayout(Mage_Core_Controller_Varien_Action $action)
    {
        $this->renderLayout($action, $this->getViewLayout());
    }

    /**
     * Set layout by layout code
     *
     * @param Mage_Core_Controller_Varien_Action $action
     * @param string $layout
     * @return void
     */
    protected function renderLayout(Mage_Core_Controller_Varien_Action $action, $layout)
    {
        $layout = Mage::getModel('page/config')
                ->getPageLayout($layout);
        $template = $layout->getTemplate();

        $action->loadLayout()
                ->getLayout()
                ->getBlock('root')
                ->setTemplate($template);
        $action->renderLayout();
    }

    /**
     * Get version of the Mage_EAV module
     *
     * @return string
     */
    public function getEavVersion()
    {
        return Mage::getConfig()->getModuleConfig("Mage_Eav")->version;
    }

    /**
     * Compare version of the Mage_EAV module to the swlwcted version
     *
     * @param string $version
     * @param string $sign
     * @return boolean
     */
    public function compareEavVersion($version, $sign = '>')
    {
        return (bool)(version_compare($this->getEavVersion(), $version, $sign) === TRUE);
    }

}