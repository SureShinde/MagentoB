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


class AW_Advancedreports_Helper_Additional extends AW_Advancedreports_Helper_Abstract
{
    const REGISTRY_PATH = 'aw_advancedreports_additional';

    /**
     * Returns reports factory class
     *
     * @return AW_Advancedreports_Model_Additional_Reports
     */
    public function getReports()
    {
        if (!Mage::registry(self::REGISTRY_PATH)) {
            Mage::register(self::REGISTRY_PATH, Mage::getModel('advancedreports/additional_reports'));
        }
        return Mage::registry(self::REGISTRY_PATH);
    }

    /**
     * Item name
     *
     * @param $name
     * @return AW_Advancedreports_Model_Additional_Item
     */
    protected function _getItemByName($name)
    {
        $reports = $this->getReports()->getReports();
        if (is_array($reports) || $reports instanceof Traversable) {
            foreach ($this->getReports()->getReports() as $report) {
                if ($report->getName() == $name) {
                    return $report;
                }
            }
        }
        return new AW_Advancedreports_Model_Additional_Item();
    }

    public function getVersionCheck($item)
    {
        if (is_string($item)) {
            return version_compare($this->_helper()->getVersion(), $this->_getItemByName($item)->getRequiredVersion(), '>=');
        } elseif ($item instanceof AW_Advancedreports_Model_Additional_Item) {
            return version_compare($this->_helper()->getVersion(), $item->getRequiredVersion(), '>=');
        }
    }
}
