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
 * Brands image processor
 */
class Belvg_Brands_Helper_Image extends Mage_Core_Helper_Abstract
{

    /**
     * Current model
     *
     * @var Belvg_Brands_Model_Brands_Image
     */
    protected $_model;

    /**
     * Object Storage Classname
     *
     * @var string
     */
    protected $_allowed_class_name = 'Belvg_Brands_Model_Brands';

    /**
     * Image Processor Classname
     *
     * @var string
     */
    protected $_model_path = 'brands/brands_image';

    /**
     * Scheduled for resize image
     *
     * @var bool
     */
    protected $_scheduleResize = FALSE;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $_placeholder;

    /**
     * Used brand object
     *
     * @var Belvg_Brands_Model_Brands
     */
    protected $_object;

    /**
     * Return Image URL
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $model = $this->_getModel();

            $model->setBaseFile($this->getObject());

            if ($model->isCached()) {
                return $model->getUrl();
            } else {
                if ($this->_scheduleResize) {
                    $model->resize();
                }

                $url = $model->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }

        return $url;
    }


    /**
     * Define object, set base image path
     *
     * @param Belvg_Brands_Model_Brands $object
     * @return Belvg_Brands_Helper_Image
     * @throws Expression
     */
    public function init($object)
    {
        if ($object instanceof $this->_allowed_class_name) {
            $this->_reset();
            $this->setObject($object);
            $this->_getModel()->setBaseFile($object);
        } else {
            throw new Expression($this->__('Object sould be instateated from %s', $this->_allowed_class_name));
        }

        return $this;
    }

    /**
     * Set current object
     *
     * @param Belvg_Brands_Model_Brands $object
     * @return Belvg_Brands_Helper_Image
     */
    protected function setObject($object)
    {
        $this->_object = $object;
        return $this;
    }

    /**
     * Get curent brand object
     *
     * @return Belvg_Brands_Model_Brands
     */
    protected function getObject()
    {
        return $this->_object;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be NULL - in this case, lacking dimension will be calculated.
     *
     * @see Belvg_Brands_Model_Brands_Image
     * @param int $width
     * @param int $height
     * @return Belvg_Brands_Helper_Image
     */
    public function resize($width = NULL, $height = NULL)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->_scheduleResize = TRUE;
        return $this;
    }

    /**
     * Reset all previous data
     *
     * @return Belvg_Brands_Helper_Image
     */
    protected function _reset()
    {
        $this->_model = NULL;
        $this->_scheduleResize = FALSE;
        $this->_placeholder = FALSE;
        return $this;
    }

    /**
     * Get current Image model
     *
     * @return Belvg_Brands_Model_Brands_Image
     */
    protected function _getModel()
    {
        if (is_null($this->_model)) {
            $this->_model = Mage::getModel($this->_model_path);
        }

        return $this->_model;
    }

    /**
     * Get Placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        if (!$this->_placeholder) {
            $this->_placeholder = 'images/catalog/product/placeholder/image.jpg';
        }

        return $this->_placeholder;
    }

}
