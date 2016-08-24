<?php

/**
 * API2 class for point (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Point_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Point_Rest
{
    protected function _retrieve()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $quoteId = $this->getRequest()->getParam('quote_id');

        $quote = $this->_getQuote($quoteId);
        $customer = $this->_getCustomer($customerId);

        $points = $this->getPoints($quote, $customer);
        $money = $this->getMoney($points, $customer);
        $infoPage = $this->getInfoPage();

        return [
            'points' => $points,
            'money' => $money,
            'info_page' => $infoPage['general']['info_page']
        ];
    }
}