<?php
require_once "Mage/Customer/controllers/AccountController.php";  

//$helper = Mage::helper('socialcommerce');

class Moxy_CustomCustomer_Customer_AccountController extends Mage_Customer_AccountController{


	public function editPostAction()
	{
		$username = $this->getRequest()->getPost('username');
		$usernameAvailable = Mage::helper('socialcommerce')->checkUsernameAvailable($username);
		
		if (! $usernameAvailable) {
				$session = $this->_getSession();
				$message = $this->__($username . ' already used by someone else. Please choose another username');	
				$session->addError($message);
				$session->setCustomerFormData($this->getRequest()->getPost());
				$this->_redirect('*/*/edit');	
		} else {
			
			parent::editPostAction();
			$customer = $this->_getSession()->getCustomer();
			$profiler = Mage::getModel('socialcommerce/profile')->load($customer->getId(), 'customer_id'); 
			$profiler->setUsername($this->getRequest()->getPost('username'));
			$profiler->save();
		}
	}


	public function createPostAction()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();

		$username = $this->getRequest()->getPost('username');
		$usernameAvailable = Mage::helper('socialcommerce')->checkUsernameAvailable($username);
		if (! $usernameAvailable) {
			$message = $this->__($username . ' already used by someone else. Please choose another username');	
			$session->addError($message);
			$session->setCustomerFormData($this->getRequest()->getPost());
			
			$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
			$this->_redirectError($errUrl);
		} else {		
			parent::createPostAction();
			$profile = Mage::getModel('socialcommerce/profile');

			# Assign data
			$customer = $this->_getSession()->getCustomer();
			$profile->setCustomerId($customer->getId());
			$profile->setStatus(1);
			$profile->setWishlist(1);
			$profile->setTemporary(0);
			$profile->setUsername($username);
			#
			$profile->save();
		}

	} 
}
				
