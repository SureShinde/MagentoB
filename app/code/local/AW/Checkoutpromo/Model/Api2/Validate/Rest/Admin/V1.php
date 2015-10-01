<?php

/**
 * API2 class for checkout promo (admin)
 *
 * @category   AW
 * @package    AW_Checkoutpromo
 * @author     Development Team <development@bilna.com>
 */
class AW_Checkoutpromo_Model_Api2_Validate_Rest_Admin_V1 extends AW_Checkoutpromo_Model_Api2_Validate_Rest
{
    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $storeId = 1;

        try{
            $quote = $this->_getQuote($quoteId, $storeId);
            $customer = $this->_getCustomer($customerId);
            $customerGroupId = $customer->getCustomerGroupId();

            $validator = Mage::getModel('checkoutpromo/validator')
                    ->init($customer->getWebsiteId(), $customerGroupId);

            if (count($quote->getAllItems())) {
                $v = $validator->process($quote);
                $_appliedBlockIds = $v->appliedBlockIds;
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        
        return array('applied_block_id' => $_appliedBlockIds);
    }

}