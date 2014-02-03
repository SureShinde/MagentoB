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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Afptc_Model_Resource_State extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('awafptc/state', 'item_id');
    } 
    
    public function setState(array $data)
    {
        if (!isset($data['customer_id'])) {
            throw new Mage_Core_Exception('Error updating state customer id is not defined');
        }
        if (!isset($data['quote_id'])) {
            throw new Mage_Core_Exception('Error updating state quote id is not defined');
        }
        if (!isset($data['state'])) {
            throw new Mage_Core_Exception('Error updating state state info is not defined');
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), array(
            'customer_id=?' => $data['customer_id'],
            'quote_id=?' => $data['quote_id']
        ));
        $this->_getWriteAdapter()->insert($this->getMainTable(), $data);

        return $this;
    }
    
    public function getState(array $data)
    {
        if (!isset($data['customer_id'])) {
            throw new Mage_Core_Exception('Error updating state customer id is not defined');
        }
        if (!isset($data['quote_id'])) {
            throw new Mage_Core_Exception('Error updating state quote id is not defined');
        }

        return $this->_getReadAdapter()->fetchOne($this->_getReadAdapter()->select()
                        ->from($this->getMainTable(), array('state'))
                        ->where("customer_id = ?", $data['customer_id'])
                        ->where("quote_id = ?", $data['quote_id']));
    }
}