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
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

/**
 * Setup Block
 */
class AW_Advancedreports_Block_Adminhtml_Setup extends Mage_Adminhtml_Block_Abstract
{
    const DATA_KEY_SECURE_CHECK = 'aw_ar_secure_check';
    const DATA_KEY_REPORT_TITLE = 'aw_ar_report_title';
    const DATA_KEY_REPORT_ROUTE = 'aw_ar_report_route';

    /**
     * Retrives Setup Instance
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return $this->getData('setup');
    }

    /**
     * Retrives Setup Url
     *
     * @return string
     */
    public function getSetupUrl()
    {       
        return $this->getUrl('advancedreports_admin/setup/edit', array(
                                'report_id'=> $this->getSetup()->getReportId(),
                                'sc'       => base64_encode(Mage::registry(self::DATA_KEY_SECURE_CHECK)),
                                'title'       => base64_encode(Mage::registry(self::DATA_KEY_REPORT_TITLE)),
                                'route'       => base64_encode(Mage::registry(self::DATA_KEY_REPORT_ROUTE)),
                            ));
    }

}