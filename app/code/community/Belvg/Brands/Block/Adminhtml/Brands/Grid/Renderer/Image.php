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
 * Show image brands in grid
 */
class Belvg_Brands_Block_Adminhtml_Brands_Grid_Renderer_Image
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    const IMG_WIDTH = 70;

    public function render(Varien_Object $row)
    {
        $brand_id = $row->getId();
        $store_id = (int) $this->getRequest()->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID);

        $_brand = Mage::getModel('brands/brands')
                ->setStoreId($store_id)
                ->load($brand_id);

        $image = Mage::helper('brands/image')->init($_brand)->resize(self::IMG_WIDTH);

        if ($image) {
            return '<img src="' . $image . '" width="70" height="70" />';
        } else {
            return NULL;
        }
    }

}
