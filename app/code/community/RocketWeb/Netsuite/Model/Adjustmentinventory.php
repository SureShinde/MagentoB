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
class RocketWeb_Netsuite_Model_Adjustmentinventory extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('rocketweb_netsuite/adjustmentinventory');
    }

    public function loadByNetsuiteId($netsuiteId) {
        $this->_getResource()->loadByNetsuiteId($this, $netsuiteId);
        return $this;
    }

    public function setQuantitiesFromInventoryList(InventoryAdjustmentInventoryList $list) {
        $quantities = '';
        foreach($list->inventory as $item) {
            $quantities[$item->item->name]=$item->adjustQtyBy;
        }
        $this->setData('quantities',serialize($quantities));
    }

    public function getQuantities() {
        return unserialize($this->getData('quantities'));
    }

    public function getQtyForSku($sku) {
        $quantites = $this->getQuantities();
        foreach($quantites as $itemSku=>$qty) {
            if($itemSku == $sku) {
                return $qty;
            }
        }
        return 0;
    }
}