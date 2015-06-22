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
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Customerattributes_Model_Source_Displayon
{
    const CREATE_ACCOUNT_PAGE_CODE   = 1;
    const CREATE_ACCOUNT_PAGE_LABEL  = 'Create Account Page';

    const CUSTOMER_ACCOUNT_PAGE_CODE  = 2;
    const CUSTOMER_ACCOUNT_PAGE_LABEL = 'Customer Account Page';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('aw_customerattributes');
        return array(
            array(
                'value' => self::CREATE_ACCOUNT_PAGE_CODE,
                'label' => $helper->__(self::CREATE_ACCOUNT_PAGE_LABEL),
            ),
            array(
                'value' => self::CUSTOMER_ACCOUNT_PAGE_CODE,
                'label' => $helper->__(self::CUSTOMER_ACCOUNT_PAGE_LABEL),
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('aw_customerattributes');
        return array(
            self::CREATE_ACCOUNT_PAGE_CODE   => $helper->__(self::CREATE_ACCOUNT_PAGE_LABEL),
            self::CUSTOMER_ACCOUNT_PAGE_CODE => $helper->__(self::CUSTOMER_ACCOUNT_PAGE_LABEL),
        );
    }
}