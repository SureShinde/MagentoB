<?php
/**
/**
 * Email to a Friend Product Controller
 *
 * @category    Mage
 * @package     Mage_Sedfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 require_once("Mage/Sendfriend/controllers/ProductController.php");
class Alw_Sendfriend_ProductController extends Mage_Sendfriend_ProductController
{
    /**
     * Predispatch: check is enable module
     * If allow only for customer - redirect to login page
     *
     * @return Mage_Sendfriend_ProductController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        /* @var $helper Mage_Sendfriend_Helper_Data */
        $helper = Mage::helper('sendfriend');
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        if (!$helper->isEnabled()) {
            $this->norouteAction();
            return $this;
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate($this)) {
			$emailsession = 'Please sign in or sign up first to email the product to your friend';
			Mage::getSingleton('core/session')->setEmailsession($emailsession);
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl(Mage::getUrl('*/*/send', array(
                    '_current' => true
                )));
                Mage::getSingleton('catalog/session')
                    ->setSendfriendFormData($this->getRequest()->getPost());
            }
        }

        return $this;
    }
}
