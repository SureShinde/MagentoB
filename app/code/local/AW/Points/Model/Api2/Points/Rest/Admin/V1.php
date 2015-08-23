<?php

/**
 * API2 class for points (AW) (admin)
 *
 * @category   AW
 * @package    AW_Points (custom)
 * @author     Development Team <development@bilna.com>
 */
class AW_Points_Model_Api2_Points_Rest_Admin_V1 extends AW_Points_Model_Api2_Points_Rest
{

	protected function _retrieve()
    {
        $customerId = $this->getRequest()->getParam('id');
        
        try{
        	/** @var $customer Mage_Customer_Model_Customer */
			$customer = $this->_getCustomer($customerId);
			$_summaryForCustomer = Mage::getModel('points/summary')->loadByCustomer($customer);

			$moneyForPoints = Mage::getModel('points/rate')
                ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                ->exchange($_summaryForCustomer->getPoints());
            $_moneyForPoints = Mage::app()->getStore()->convertPrice($moneyForPoints, true);

            $isAvailableToRedeem = Mage::helper('points')->isAvailableToRedeem($_summaryForCustomer->getPoints());

            $isAvailable = 
            	$_summaryForCustomer->getPoints()
            	&& $_moneyForPoints
            	&& $isAvailableToRedeem
            	&& $customer->getId();

            $canUseWithCoupon = Mage::helper('points/config')->getCanUseWithCoupon();

		} catch (Mage_Core_Exception $e) {
			$this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
		}

		return array(
			'isAvailable' => $isAvailable,
			'canUseWithCoupon' => $canUseWithCoupon,
			'moneyForPoints' => $_moneyForPoints
		);
    }

}