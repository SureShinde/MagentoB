<?php
/**
 * Description of Bilna_Rest_Model_Api2_Newsletter_Rest
 *
 * @path    app/code/local/Bilna/Rest/Model/Api2/Newsletter/Rest.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Newsletter_Rest extends Bilna_Rest_Model_Api2_Newsletter {
    protected function _validate($data) {
        /* @var $validator Bilna_Rest_Model_Api2_Validator_Newsletter */
        $validator = Mage::getModel('bilna_rest/api2_validator_newsletter');

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
    
    protected function _subscribe($customerId, $email, $ownerId) {
        $modelNewsletterSubscriber = Mage::getModel('newsletter/subscriber');
        $subscriber = $modelNewsletterSubscriber->loadByEmail($email);
        $subscriberId = $subscriber->getId();
        
        if (!$subscriberId) {
            $modelNewsletterSubscriber->setSubscriberConfirmCode($modelNewsletterSubscriber->randomSequence());
        }
        
        $isConfirmNeed = (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $isSubscribeOwnEmail = $customerId && $ownerId == $customerId;
        $subscriberStatus = $subscriber->getStatus();
        
        if (!$subscriberId || $subscriberStatus == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED || $subscriberStatus == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
            if ($isConfirmNeed === true) {
                //- if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                
                if ($isOwnSubscribes == true) {
                    $modelNewsletterSubscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                }
                else {
                    $modelNewsletterSubscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
                }
            }
            else {
                $modelNewsletterSubscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
            }
            
            $modelNewsletterSubscriber->setSubscriberEmail($email);
        }
        
        if ($isSubscribeOwnEmail) {
            $modelNewsletterSubscriber->setStoreId($this->_getStore()->getId());
            $modelNewsletterSubscriber->setCustomerId($customerId);
        }
        else {
            $modelNewsletterSubscriber->setStoreId($this->_getStore()->getId());
            $modelNewsletterSubscriber->setCustomerId(0);
        }

        $modelNewsletterSubscriber->setIsStatusChanged(true);
        
        try {
            $modelNewsletterSubscriber->save();
            
            if ($isConfirmNeed === true && $isOwnSubscribes === false) {
                $modelNewsletterSubscriber->sendConfirmationRequestEmail();
            }
            else {
                $modelNewsletterSubscriber->sendConfirmationSuccessEmail();
            }

            return $subscriber;
        }
        catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }
    }
    
    protected function getType($status = NULL)
    {
        if(empty($status)) {
            return $status;
        }
        switch ($status) {
            case 1:
                $type = 'subscribe';
                break;
            case 2:
                $type = 'not active';
                break;
            case 3:
                $type = 'unsubscribe';
                break;
            case 4:
                $type = 'unconfirmed';
                break;
        }
        
        return $type;
    }
}
