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


class AW_Customerattributes_Model_Source_Validation
{
    const NONE_CODE           = 'none';
    const NONE_LABEL          = 'None';

    const ALPHANUMERIC_CODE   = 'alphanumeric';
    const ALPHANUMERIC_LABEL  = 'Alphanumeric';

    const NUMERIC_CODE        = 'numeric';
    const NUMERIC_LABEL       = 'Numeric';

    const ALPHA_CODE          = 'alpha';
    const ALPHA_LABEL         = 'Alpha';

    const URL_CODE            = 'url';
    const URL_LABEL           = 'Url';

    const EMAIL_CODE          = 'email';
    const EMAIL_LABEL         = 'Email';

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
                'value' => self::NONE_CODE,
                'label' => $helper->__(self::NONE_LABEL),
            ),
            array(
                'value' => self::ALPHANUMERIC_CODE,
                'label' => $helper->__(self::ALPHANUMERIC_LABEL),
            ),
            array(
                'value' => self::NUMERIC_CODE,
                'label' => $helper->__(self::NUMERIC_LABEL),
            ),
            array(
                'value' => self::ALPHA_CODE,
                'label' => $helper->__(self::ALPHA_LABEL),
            ),
            array(
                'value' => self::URL_CODE,
                'label' => $helper->__(self::URL_LABEL),
            ),
            array(
                'value' => self::EMAIL_CODE,
                'label' => $helper->__(self::EMAIL_LABEL),
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
            self::NONE_CODE         => $helper->__(self::NONE_LABEL),
            self::ALPHANUMERIC_CODE => $helper->__(self::ALPHANUMERIC_LABEL),
            self::NUMERIC_CODE      => $helper->__(self::NUMERIC_LABEL),
            self::ALPHA_CODE        => $helper->__(self::ALPHA_LABEL),
            self::URL_CODE          => $helper->__(self::URL_LABEL),
            self::EMAIL_CODE        => $helper->__(self::EMAIL_LABEL),
        );
    }
}