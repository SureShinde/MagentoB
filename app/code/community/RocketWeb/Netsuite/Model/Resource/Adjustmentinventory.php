<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Model_Resource_Adjustmentinventory extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct()
	{
		$this->_init('rocketweb_netsuite/adjustmentinventory', 'id');
	}

    public function loadByNetsuiteId(RocketWeb_Netsuite_Model_Adjustmentinventory $inventory, $netsuiteId)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array('internal_netsuite_id' => $netsuiteId);
        $select  = $adapter->select()
            ->from( Mage::getSingleton('core/resource')->getTableName('netsuite_adjustment_inventory'), array('id'))
            ->where('internal_netsuite_id = :internal_netsuite_id');

        $inventoryId = $adapter->fetchOne($select, $bind);
        if ($inventoryId) {
            $this->load($inventory, $inventoryId);
        } else {
            $inventory->setData(array());
        }

        return $this;
    }
}