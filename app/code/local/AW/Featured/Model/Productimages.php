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


class AW_Featured_Model_Productimages extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('awfeatured/productimages');
    }
    
    public function loadBy($_blockId = null, $_productId = null, $_imageId = null)
    {
        $_collection = $this->getCollection();
        if ($_blockId != null) {
            $_collection->addBlockFilter($_blockId);
        }
        if ($_productId != null) {
            $_collection->addProductFilter($_productId);
        }
        if ($_imageId != null) {
            $_collection->addImageFilter($_imageId);
        }
        foreach ($_collection as $_image) {
            return $_image;
        }
        return $this;
    }
}
