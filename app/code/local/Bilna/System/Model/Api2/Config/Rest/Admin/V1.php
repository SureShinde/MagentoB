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
    static $STORE_ID = 1;

    protected function _retrieve()
    {
        try{
            $key = $this->getRequest()->getParam('id');
            $keyConfig = str_replace("-", "/", $key);
            $config = Mage::getStoreConfig($keyConfig, self::$STORE_ID);

            // Format config value
            switch ($keyConfig) {
                case 'bilna_expressshipping':
                    if (array_key_exists('orderlimit',$config)) {
                        if (array_key_exists('cut_off', $config['orderlimit'])) {
                            $expressShippingHelper = Mage::helper('bilna_expressshipping');
                            $config['orderlimit']['cut_off'] = $expressShippingHelper->getDisplayCutOffTime($config['orderlimit']['cut_off']);
                        }
                    }
                    break;
                case 'bilna_expressshipping/orderlimit':
                    if (array_key_exists('cut_off', $config)) {
                        $expressShippingHelper = Mage::helper('bilna_expressshipping');
                        $config['cut_off'] = $expressShippingHelper->getDisplayCutOffTime($config['orderlimit']['cut_off']);
                    }
                    break;
                case 'bilna_expressshipping/orderlimit/cut_off':
                    $expressShippingHelper = Mage::helper('bilna_expressshipping');
                    $config = $expressShippingHelper->getDisplayCutOffTime($config);
                    break;
                default:
                    break;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('config' => $config);

    }

}