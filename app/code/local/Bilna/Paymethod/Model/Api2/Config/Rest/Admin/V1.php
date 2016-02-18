<?php

/**
 * API2 class for paymethod (admin)
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Paymethod_Model_Api2_Config_Rest_Admin_V1 extends Bilna_Paymethod_Model_Api2_Config_Rest
{
	protected function _retrieve()
    {
        $keyConfig = $this->getRequest()->getParam('id');

        try{

        	$config = Mage::getStoreConfig('payment/' . $keyConfig);

        } catch (Mage_Core_Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('config' => $config);

    }
}