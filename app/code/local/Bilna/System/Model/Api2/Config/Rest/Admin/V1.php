<?php

/**
 * API2 class for System Configuration (admin)
 *
 * @category   Bilna
 * @package    Bilna_System
 * @author     Development Team <development@bilna.com>
 */
class Bilna_System_Model_Api2_Config_Rest_Admin_V1 extends Bilna_System_Model_Api2_Config_Rest
{

	protected function _retrieve()
    {
        try{
        	$key = $this->getRequest()->getParam('id');
        	$keyConfig = str_replace("-", "/", $key);

        	$config = Mage::getStoreConfig($keyConfig);

        } catch (Mage_Core_Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('config' => $config);

    }

}