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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Clickcheckout
 * @version    1.1.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Clickcheckout_Block_Popup_Points extends AW_Points_Block_Checkout_Cart_Points
{
    public function getAppliedPoints(){
        $session = Mage::getSingleton('checkout/session');
        if($session->getData('use_points')){
        return $session->getData('points_amount');
        }else return 0;
    }

    public function getPoints()
    {
        if (is_null($this->getData('points')))
        {
            try
            {
                $pointsSummary = 0;

                /* Ponts amount for the rules */
                foreach ($this->_appliedRules as $rule)
                {
                    $pointsSummary += $rule->getPointsChange();
                }
                if($this->getAppliedPoints()){
                    $money = Mage::getModel('points/rate')
                                       ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                                       ->exchange($this->getAppliedPoints());
                $pointsSummary += Mage::getModel('points/rate')
                                    ->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS)
                                    ->exchange($this->_quote->getData('base_subtotal_with_discount')-$money);
                }else{
                $pointsSummary += Mage::getModel('points/rate')
                                        ->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS)
                                        ->exchange($this->_quote->getData('base_subtotal_with_discount'));
                }
                if (Mage::helper('points/config')->getMaximumPointsPerCustomer())
                {
                    $customersPoints = 0;

                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    if ($customer) $customersPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

                    if ($pointsSummary + $customersPoints > Mage::helper('points/config')->getMaximumPointsPerCustomer())
                    {
                        $pointsSummary = Mage::helper('points/config')->getMaximumPointsPerCustomer() - $customersPoints;
                    }
                }
                $this->setData('points', $pointsSummary);
            } catch (Exception $ex)
            { }
        }

        return $this->getData('points');
    }
}