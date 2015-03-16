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
 * Video Resource Model
 */
class AW_Advancedreports_Model_Mysql4_Option extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_init('advancedreports/option', 'option_id');
    }

    /**
     * Clear all custom options for the report for the administrator
     *
     * @param string $reportId
     * @param integer $admin_id
     * @return AW_Advancedreports_Model_Mysql4_Option
     */
    public function clearReportOptions($reportId, $admin_id)
    {
        $condition = 
            $this->_getWriteAdapter()->quoteInto('report_id = ?', $reportId).
            ' AND '.
            $this->_getWriteAdapter()->quoteInto('admin_id = ?', $admin_id);

        $table = Mage::getSingleton('core/resource')->getTableName('advancedreports/option');
        $this->_getWriteAdapter()->delete($table, $condition);
        return $this;
    }

    /**
     * Load an object
     *
     * @param   Mage_Core_Model_Abstract $object
     * @param   mixed $value
     * @param   string $field field to load by (defaults to model id)
     * @return  Mage_Core_Model_Mysql4_Abstract
     */
    public function load3params(Mage_Core_Model_Abstract $object, $reportId, $adminId, $path )
    {
        $read = $this->_getReadAdapter();
        if ($read && $reportId && $adminId) {

           $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.report_id=?', $reportId)
            ->where($this->getMainTable().'.admin_id=?', $adminId)
            ->where($this->getMainTable().'.path=?', $path)
            ;
            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }

//        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

}