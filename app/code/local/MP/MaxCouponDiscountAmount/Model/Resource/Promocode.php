<?php

/**
 * @category    MP
 * @package     MP_MaxCouponDiscountAmount
 * @copyright   MagePhobia (http://www.magephobia.com)
 */
class MP_MaxCouponDiscountAmount_Model_Resource_Promocode extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('salesrule/rule', 'rule_id');
    }

    public function getCouponMaxDiscountAmount($couponCode)
    {
        $resource = Mage::getSingleton('core/resource');
        $adapterRead = $resource->getConnection('core_read');
        $tableName = array(
            'r' => $resource->getTableName('salesrule')
        );
        $columns = array(
            'max_discount_amount' => 'max_discount_amount',
        );
        $select = $adapterRead->select()
            ->from($tableName, $columns)
            ->join(
                array('sc' => $resource->getTableName('salesrule/coupon')),
                'r.rule_id = sc.rule_id',
                array()
            )
            ->where('sc.code = ?', $couponCode);

        $resultSelect = $adapterRead->fetchAll($select);

        if ((isset($resultSelect[0])) && (isset($resultSelect[0]['max_discount_amount']))
            && ($resultSelect[0]['max_discount_amount'] > 0)) {
            return $resultSelect[0]['max_discount_amount'];
        } else {
            return false;
        }
    }
}