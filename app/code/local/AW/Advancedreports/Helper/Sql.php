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

class AW_Advancedreports_Helper_Sql extends AW_Advancedreports_Helper_Abstract
{
    /**
     * Table names cache
     * @var array
     */
    protected $_tables = array();

    /**
     * Retrives name of table in DB
     * @param string $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        if (!isset($this->_tables[$tableName])) {
            $this->_tables[$tableName] = Mage::getSingleton('core/resource')->getTableName($tableName);
        }
        return $this->_tables[$tableName];
    }


    /**
     * Retrives filter string
     * @return string
     */
    public function getProcessStates()
    {
        $states = explode( ",", $this->_helper()->confProcessOrders() );
        $is_first = true;
        $filter = "";
        foreach ($states as $state)
        {
            if (!$is_first)
            {
            $filter .= " OR ";
            }
            $filter .= "val.value = '".$state."'";
            $is_first = false;
        }
        return "(".$filter.")";
    }



}