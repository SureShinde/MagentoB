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

class AW_Islider_Model_Source_Images_Type extends AW_Islider_Model_Source_Abstract {
    const FILE = 1;
    const REMOTEURL = 2;

    const FILE_LABEL = 'File';
    const REMOTEURL_LABEL = 'Remote URL';

    public function toOptionArray() {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::FILE, 'label' => $_helper->__(self::FILE_LABEL)),
            array('value' => self::REMOTEURL, 'label' => $_helper->__(self::REMOTEURL_LABEL))
        );
    }
}
