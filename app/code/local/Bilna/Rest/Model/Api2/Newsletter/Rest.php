<?php
/**
 * Description of Bilna_Rest_Model_Api2_Newsletter_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Newsletter_Rest extends Bilna_Rest_Model_Api2_Newsletter {
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
}
