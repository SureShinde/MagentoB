<?php

abstract class Bilna_Customer_Model_Api2_Resetpassword_Rest extends Bilna_Customer_Model_Api2_Resetpassword
{

    protected function _create(array $data)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(1)
            ->loadByEmail($data["email"]);

        if ($customer->getId()) {
            try {
                $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                $customer->sendPasswordResetConfirmationEmail();
            } catch (Exception $exception) {
                $this->_error($exception->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
        }else{
            $this->_error("No email exist!", Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
}
