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


class AW_Featured_Model_Source_Autoposition extends AW_Featured_Model_Source_Abstract
{
    const NONE = 0;
    const NONE_LABEL = 'None';
    const LEFTCOLUMN = 1;
    const LEFTCOLUMN_LABEL = 'Left Column';
    const RIGHTCOLUMN = 2;
    const RIGHTCOLUMN_LABEL = 'Right Column';
    const BEFORECONTENT = 3;
    const BEFORECONTENT_LABEL = 'Before Content';
    
    public function toOptionArray()
    {
        $_helper = Mage::helper('awfeatured');
        return array(
            array('value' => self::NONE, 'label' => $_helper->__(self::NONE_LABEL)),
            array('value' => self::LEFTCOLUMN, 'label' => $_helper->__(self::LEFTCOLUMN_LABEL)),
            array('value' => self::RIGHTCOLUMN, 'label' => $_helper->__(self::RIGHTCOLUMN_LABEL)),
            array('value' => self::BEFORECONTENT, 'label' => $_helper->__(self::BEFORECONTENT_LABEL)),
        );
    }
}
