<?php
/**
 * Description of Bilna_Rest_Model_Api2_Newsletter_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Rest/Model/Api2/Newsletter/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Newsletter_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Newsletter_Rest {
    protected function _retrieve() {
        $customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
        
        $data = [];
        
        if($customer->getId()) {
            $status = $subscriber->getStatus();
            
            $data = array (
                'customer_id' => $subscriber->getCustomerId(),
                'email' => $subscriber->getEmail(),
                'type' => $this->getType($status),
            );
        }
        
        return $data;
    }

    protected function _create(array $filteredData) {
        //- validasi request data
        $this->_validate($filteredData);
        
        try {
            $customerId = $filteredData['customer_id'];
            $email = (string) $filteredData['email'];
            $type = $filteredData['type'];

            if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && $customerId == 0) {
                $this->_critical('Sorry, but administrator denied subscription for guests. Please <a href="' . Mage::helper('customer')->getRegisterUrl() . '">register</a>.');
            }

            $ownerId = Mage::getModel('customer/customer')
                ->setWebsiteId($this->_getStore()->getWebsiteId())
                ->loadByEmail($email)
                ->getId();
            
            if ($customerId) {
                if ($ownerId !== null && $ownerId == $customerId) {
                    $this->_critical('This email address is already assigned to another user.');
                }
            }
            
            if ($type == 'subscribe') {
                //$ownerId->setIsSubscribed(TRUE);
                $customer = Mage::getModel('customer/customer')->load($ownerId);
                Mage::getModel('newsletter/subscriber')->subscribeCustomer($customer);
                
                return $this->_subscribe($customerId, $email, $ownerId);
            }
            elseif ($type == 'confirmation') {
                //- soon
                $this->_critical('still working.');
            }
            elseif ($type == 'unsubscribe') {
                //- soon
                $this->_critical('still working.');
            }
            else {
                $this->_critical('Unsupported type.');
            }
        }
        catch (Mage_Core_Exception $ex) {
            $this->_critical($ex->getMessage());
        }
        catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }
    }
}
