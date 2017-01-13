<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Ordercomplete_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Ordercomplete_Rest
{   
    protected function _retrieve()
    {
    	return $this->saveClientHistory(array(
            'client_id' => $this->getRequest()->getParam('client_id'),
            'order_id' => $this->getRequest()->getParam('order_id')
        ));
    }
}