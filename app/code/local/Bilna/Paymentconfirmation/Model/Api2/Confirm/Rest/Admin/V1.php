<?php
/**
 * Description of Bilna_Paymentconfirmation_Model_Api2_Confirm_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Paymentconfirmation/Model/Api2/Confirm/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymentconfirmation_Model_Api2_Confirm_Rest_Admin_V1 extends Bilna_Paymentconfirmation_Model_Api2_Confirm_Rest {
    protected function _create(array $data) {
        $this->_validate($data);
        
        try {
            /* @var $validator Bilna_Paymentconfirmation_Model_Payment */
            $confirmModel = Mage::getModel('Paymentconfirmation/payment');
            $order = $confirmModel->isValidOrder($data['order_number']);
            
            if (!$order->getId()) {
                $this->_error('Order Number is not valid.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
            }
            
            if (!$this->_validateOrderEmail($order, $data['email'])) {
                $this->_error('Customer Email is not valid.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
            }
            
            if (!$this->_validateOrderTotal($order, $data['nominal'])) {
                $this->_error('Nominal is not valid.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
            }
            
            $fields = array (
                'order_id' => $data['order_number'],
                'email' => $data['email'],
                'nominal' => $data['nominal'],
                'dest_bank' => $data['bank_to'],
                'transfer_date' => $data['transfer_date'],
                'source_bank' => $data['bank_from'],
                'source_acc_name' => $data['name_from'],
                'comment' => $data['comment'],
                'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
            );
            $confirmModel->setData($fields);
            $confirmModel->save();
            
            if (!$confirmModel->getId()) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
            
            return $this->_getLocation($confirmModel);
        }
        catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
    }
    
    protected function _validate($data) {
        /* @var $validator Bilna_Paymentconfirmation_Model_Api2_Validator_Confirm */
        $validator = Mage::getModel('Paymentconfirmation/api2_validator_confirm');

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
    
    protected function _validateOrderEmail($order, $email) {
        if ($order->getCustomerEmail() != $email) {
            return false;
        }
        
        return true;
    }
    
    protected function _validateOrderTotal($order, $total) {
        if ($order->getGrandTotal() != $total) {
            return false;
        }
        
        return true;
    }
}
