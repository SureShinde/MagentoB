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
 * brands moel collection
 */
class Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Collection_Abstract
{

    /**
     * Init collection
     *
     * @retur void
     */
    protected function _construct()
    {
        $this->_init('brands/brands');
    }

    /**
     * Add Store ID filter
     *
     * @param int|array $store
     * @return Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection
     */
    public function addStoreFilter($store = NULL)
    {
        if (!is_null($store)) {
            $this->getSelect()->where('e.store_id IN (?)', $store);
        }

        return $this;
    }

    /**
     * Set Store scope for collection
     *
     * @param mixed $store
     * @return Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection
     */
    public function setStore($store)
    {
        parent::setStore($store);
        return $this;
    }
}