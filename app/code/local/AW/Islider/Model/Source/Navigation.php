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

class AW_Islider_Model_Source_Navigation extends AW_Islider_Model_Source_Abstract {
    const HIDDEN = 0;
    const SHOWED = 1;
    const AUTO_HIDE = 2;

    const HIDDEN_LABEL = 'Hidden';
    const SHOWED_LABEL = 'Always showed';
    const AUTO_HIDE_LABEL = 'Auto hide';

    public function toOptionArray() {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::HIDDEN, 'label' => $_helper->__(self::HIDDEN_LABEL)),
            array('value' => self::SHOWED, 'label' => $_helper->__(self::SHOWED_LABEL)),
            array('value' => self::AUTO_HIDE, 'label' => $_helper->__(self::AUTO_HIDE_LABEL))
        );
    }
}
