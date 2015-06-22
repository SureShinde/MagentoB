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
 * EAV attribute url backend
 */
class Belvg_Brands_Model_Entity_Attribute_Backend_Url extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    /**
     * Save url key for the entity. This needs to be done in the afterSave
     * method so the object's ID is known.
     *
     * @param Belvg_Brands_Model_Brands $object
     * @return Belvg_Brands_Model_Entity_Attribute_Backend_Url
     * @throws Exception
     */
    public function afterSave($object)
    {
        parent::afterSave($object);
        $attr_code = $this->getAttribute()->getAttributeCode();
        try {

            $url_key = trim($object->getData($attr_code));

            if (!$url_key) {
                $url_key = $object->getTitle();
            }

            $url_key = Mage::getModel('catalog/product_url')->formatUrlKey($url_key);

            if (empty($url_key)) {
                $url_key = sprintf('brand-%s', $object->getId());
            }

            $object->setData($attr_code, $url_key);
            $object->getResource()->saveAttribute($object, $attr_code);
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

}
