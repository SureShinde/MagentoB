<?php

/**
 * API2 class for paymethod (admin)
 *
 * @category   Bilna
 * @package    Custom AW_Afptc 
 * @author     Development Team <development@bilna.com>
 */
class AW_Afptc_Model_Api2_Observer_Rest_Admin_V1 extends AW_Afptc_Model_Api2_Observer_Rest
{
	/**
	 * Default store Id (for install)
	 */
	const DISTRO_STORE_ID       = 1;
	
	/**
	 * Default store code (for install)
	 *
	 */
	const DISTRO_STORE_CODE     = 'default';
	
	protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');

        try{

        	$quote = $this->_getQuote($quoteId);
        	$customerId = $quote->getCustomerId();
        	
        	$customerGroup = 0;
        	if($customerId != null)
        	{
        		$customer = Mage::getModel('customer/customer')->load($customerId);
        		$customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId()); 
        	}
        	
        	if($quote->hasItems())
        	{
        		$rules = Mage::getModel('awafptc/rule')->getActiveRules(array(
        				'store' => self::DISTRO_STORE_ID,
        				'group' => $customerGroup,
        				'website' => 1
        		));
        		$activeRules = array();
        		
        	}

        } catch (Mage_Core_Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('config' => $config);

    }
}