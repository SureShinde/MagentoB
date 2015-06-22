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

class AW_Islider_Model_Source_Autoposition extends AW_Islider_Model_Source_Abstract {
    const NONE = 0;
    const NONE_LABEL = 'None';
    const LEFTCOLUMN = 1;
    const LEFTCOLUMN_LABEL = 'Left Column';
    const RIGHTCOLUMN = 2;
    const RIGHTCOLUMN_LABEL = 'Right Column';
    const BEFORECONTENT = 3;
    const BEFORECONTENT_LABEL = 'Before Content';
    
    public function toOptionArray() {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::NONE, 'label' => $_helper->__(self::NONE_LABEL)),
            array('value' => self::LEFTCOLUMN, 'label' => $_helper->__(self::LEFTCOLUMN_LABEL)),
            array('value' => self::RIGHTCOLUMN, 'label' => $_helper->__(self::RIGHTCOLUMN_LABEL)),
            array('value' => self::BEFORECONTENT, 'label' => $_helper->__(self::BEFORECONTENT_LABEL)),
        );
    }
}
