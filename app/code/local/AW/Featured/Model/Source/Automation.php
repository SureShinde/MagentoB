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


class AW_Featured_Model_Source_Automation extends AW_Featured_Model_Source_Abstract
{
    const NONE = 0;
    const NONE_LABEL = 'None';
    const RANDOM = 1;
    const RANDOM_LABEL = 'Random Products';
    const TOPSELLERS = 2;
    const TOPSELLERS_LABEL = 'Top Sellers';
    const TOPRATED = 3;
    const TOPRATED_LABEL = 'Top Rated';
    const MOSTREVIEWED = 4;
    const MOSTREVIEWED_LABEL = 'Most Reviewed';
    const RECENTLYADDED = 5;
    const RECENTLYADDED_LABEL = 'Recently Added';
    const CURRENTCATEGORY = 6;
    const CURRENTCATEGORY_LABEL = 'Current Category Products';

    public function toOptionArray()
    {
        $_helper = Mage::helper('awfeatured');
        return array(
            array('value' => self::NONE, 'label' => $_helper->__(self::NONE_LABEL)),
            array('value' => self::RANDOM, 'label' => $_helper->__(self::RANDOM_LABEL)),
            array('value' => self::TOPSELLERS, 'label' => $_helper->__(self::TOPSELLERS_LABEL)),
            array('value' => self::TOPRATED, 'label' => $_helper->__(self::TOPRATED_LABEL)),
            array('value' => self::MOSTREVIEWED, 'label' => $_helper->__(self::MOSTREVIEWED_LABEL)),
            array('value' => self::RECENTLYADDED, 'label' => $_helper->__(self::RECENTLYADDED_LABEL)),
            array('value' => self::CURRENTCATEGORY, 'label' => $_helper->__(self::CURRENTCATEGORY_LABEL))
        );
    }
}
