<?php
use Mage_Customer_Model_Session as MageSession;

class Bilna_Customer_Model_Api2_Login_Rest extends Bilna_Customer_Model_Api2_Login
{

    protected function _retrieve()
    {
        $session = new MageSession();
        $email = $this->getRequest()->getParam('email');
        $password = $this->getRequest()->getParam('password');
//         $type = $this->getRequest()->getParam('type');
        
        Mage::app()->getStore()->setWebsiteId(1);
        
//         if($type == "web"){
            if (! empty($email) && ! empty($password)) {
                try {
                    $session->login($email, $password);
                    
                    return $session->getCustomer()->getData();
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $this->_critical("This account is not confirmed");
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $this->_critical($e->getMessage());
                            break;
                        default:
                            $this->_critical($e->getMessage());
                    }
                } catch (Exception $e) {
                    $this->_critical($e->getMessage());
                }
            } else {
                $this->_critical("Login and password are required.");
            }
//         }
    }
}