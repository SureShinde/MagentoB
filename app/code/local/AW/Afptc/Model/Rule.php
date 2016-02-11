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


class AW_Afptc_Model_Rule extends Mage_Rule_Model_Rule
{
    protected $_activeRules;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('awafptc/rule');
    } 
    
    public function getConditionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_combine');
    }
    
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }        
        return $this;
    }
    
    public function getActiveRules(array $params)
    {
        if (!$this->_activeRules || isset($params['force'])) {
            if (!isset($params['store'])) {
                throw new Mage_Core_Exception("Store is not defined");
            }
            if (!isset($params['group'])) {
                throw new Mage_Core_Exception("Customer group is not defined");
            }
            if(!isset($params['website'])) {
                 throw new Mage_Core_Exception("Website is not defined");
            }

            $this->_activeRules = $this->getCollection()
                    ->addStatusFilter()
                    ->addHasProductFilter()
                    ->joinProductWebsite((int) $params['website'])
                    ->joinProductStock((int) $params['website'])
                    ->joinProductStatus((int) $params['store'])
                    ->addTimeLimitFilter()                 
                    ->addStoreFilter((int) $params['store'])
                    ->addGroupFilter((int) $params['group'])
                    ->addPriorityOrder();
         
        }

        return $this->_activeRules;
    }
}