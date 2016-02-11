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


class AW_Featured_Helper_Onsale extends Mage_Core_Helper_Abstract
{
    private $_isOnsale = null;
    
    public function isOnsale()
    {
        if ($this->_isOnsale === null) {
            $_modules = (array) Mage::getConfig()->getNode('modules')->children();
            $this->_isOnsale = false;
            if (array_key_exists('AW_Onsale', $_modules)
                && 'true' == (string) $_modules['AW_Onsale']->active
                && !(bool) Mage::getStoreConfig('advanced/modules_disable_output/AW_Onsale')) {
                $this->_isOnsale = true;
            }
        }
        return $this->_isOnsale;
    }
    
    public function startOnsale($_product, $wh)
    {
        return '<div class="onsale-category-container-list" style="width:' . $wh . 'px;height:' . $wh . 'px;">'
            . Mage::helper('onsale')->getCategoryLabelHtml($_product)
        ;
    }
    
    public function endOnsale()
    {
        return '</div>';
    }
}
