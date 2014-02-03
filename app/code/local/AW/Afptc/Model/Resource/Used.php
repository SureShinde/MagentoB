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


class AW_Afptc_Model_Resource_Used extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('awafptc/used', 'item_id');
    }
    
    public function markAsDeleted($rule, $quote)
    {        
      return  $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), 
                array('rule_id' => $rule, 'quote_id' => $quote, 'is_removed' => 1), 
                array('is_removed' => 1));
    }
    
    public function markedAsDeleted($rule, $quote)
    {
        return (bool) $this->_getReadAdapter()->fetchOne($this->_getReadAdapter()->select()
                                ->from($this->getMainTable(), array('is_removed'))
                                ->where("rule_id = ?", $rule)
                                ->where("quote_id = ?", $quote));
    }   
}