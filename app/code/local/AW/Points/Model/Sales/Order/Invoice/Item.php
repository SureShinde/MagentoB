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
 * @package    AW_Points
 * @version    1.6.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Points_Model_Sales_Order_Invoice_Item extends Mage_Sales_Model_Order_Invoice_Item {
    public function getOrderItem() {
        if (is_null($this->_orderItem)) {
            if ($this->getInvoice()) {
                $this->_orderItem = $this->getInvoice()->getOrder()->getItemById($this->getOrderItemId());
            }
            else {
                $this->_orderItem = Mage::getModel('sales/order_item')->load($this->getOrderItemId());
            }
        }
        
        return $this->_orderItem;
    }
}
