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
 * @package    AW_Collpur
 * @version    1.0.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Collpur_Block_Links extends Mage_Core_Block_Template
{

    public function addDealsLink()
    {  
        if ($this->helper('collpur')->extensionEnabled('AW_Collpur'))
        {
          if(Mage::getStoreConfig('collpur/general/enable_toplinks')) {
            $parentBlock = $this->getParentBlock();
            $text = $this->__('Deals');
//             $parentBlock->addLink($text, 'deals/', $text, true, array(), 25, null, 'class="top-link-deals"');
          }
        }
        return $this;
    }
}