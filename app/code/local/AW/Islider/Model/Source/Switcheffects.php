<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Model_Source_Switcheffects extends AW_Islider_Model_Source_Abstract {
    const SE_FADE_APPEAR = 'fade-appear';
    const SE_SIMPLE_SLIDER = 'simple-slider';
    const SE_BLIND_UPDOWN = 'blind-up-down';
    const SE_SLIDE_UPDOWN = 'slide-up-down';
    const SE_JALOUSIE = 'jalousie';
    const SE_RANDOM = 'random';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::SE_SIMPLE_SLIDER, 'label' => $_helper->__('Simple Slider')),
            array('value' => self::SE_FADE_APPEAR, 'label' => $_helper->__('Fade / Appear')),
            array('value' => self::SE_BLIND_UPDOWN, 'label' => $_helper->__('Blind Up / Blind Down')),
            array('value' => self::SE_SLIDE_UPDOWN, 'label' => $_helper->__('Slide Up / Slide Down')),
            array('value' => self::SE_JALOUSIE, 'label' => $_helper->__('Jalousie')),
            array('value' => self::SE_RANDOM, 'label' => $_helper->__('Random effect for each switch'))
        );
    }
}
