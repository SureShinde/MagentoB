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


class AW_Featured_Model_Representations_Slider_Layouts extends AW_Featured_Model_Source_Abstract
{
    const LAYOUT_HORIZONTAL = 'horizontal';
    const LAYOUT_VERTICAL = 'vertical';

    public function toOptionArray()
    {
        $_helper = Mage::helper('awfeatured');
        return array(
            array('value' => self::LAYOUT_HORIZONTAL, 'label' => $_helper->__('Horizontal')),
            array('value' => self::LAYOUT_VERTICAL, 'label' => $_helper->__('Vertical'))
        );
    }
}
