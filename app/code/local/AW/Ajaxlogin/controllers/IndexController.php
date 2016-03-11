<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxlogin
 * @version    1.0.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * 
 */
class AW_Ajaxlogin_IndexController extends Mage_Core_Controller_Front_Action {
    
    /**
     * 
     */
    private $__responseObject = null;
    
    
    /**
     * 
     */
    protected function __prepareAction() {
        if ( $this->getRequest()->isPost() ) {
            if ( $this->getRequest()->getPost('__forceReloading') ) {
                $this->__forceReloading();
            }
            if ( $this->getRequest()->getPost('__forceUpdating') ) {
                $this->__forceUpdating();
            }
            
            if ( $this->__relatedToCustomerAccountRoutine($this->__fetchLocation()) ) {
                if ( $this->getRequest()->getActionName() != 'recoveryPost' ) {
                    $this->__forceReloading();
                }
            }
            if ( $this->__relatedToCheckoutRoutine($this->__fetchLocation()) ) {
                if ( $this->getRequest()->getActionName() != 'recoveryPost' ) {
                    $this->__forceLanding( $this->__fetchLocation() );
                    $this->__forceReloading();
                }
            }
            if ( $this->__relatedToShoppingCartRoutine($this->__fetchLocation()) ) {
                if ( $this->getRequest()->getActionName() != 'logoutPost' ) {
                    $this->__forceLanding( $this->__fetchLocation() );
                }
                if ( $this->getRequest()->getActionName() != 'recoveryPost' ) {
                    $this->__forceReloading();
                }
            }
        }
        else {
            $this->_redirect('');
        }
    }
    
    
    /**
     * 
     */
    public function loginPostAction() {
        $this->__prepareAction();
        
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            return $this->__sendResponse(
                array(
                    'success'      => 0,
                    'errorMessage' => 'Already logged in'
                )
            );
        }
        
        if ($this->getRequest()->isPost()) {
            $login['username'] = $this->getRequest()->getPost('email');
            $login['password'] = $this->getRequest()->getPost('password');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                    
                    $this
                        ->__addToResponse( array('success' => 1) )
                        ->__addToResponse( array('customer_information' => $this->__getCustomerData()) )
                        ->__setResponseLanding( Mage::helper('ajaxlogin/data')->getConfigLanding(AW_Ajaxlogin_Helper_Data::XML_CONFIG_PATH_LOGINFORM_LOGIN_LANDING) )
                        ->__sendResponse()
                    ;
                }
                catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $__value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $__value);
                        break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                        break;
                        default:
                            $message = $e->getMessage();
                    }
                    
                    $this->__sendResponse(
                        array(
                            'success'      => 0,
                            'errorMessage' => $message
                        )
                    );
                    $session->setUsername($login['username']);
                }
                catch (Exception $e) {
                    $this->__sendResponse(
                        array(
                            'success'      => 0,
                            'errorMessage' => 'Unknown error'
                        )
                    );
                }
            }
            else {
                $this->__sendResponse(
                    array(
                        'success'      => 0,
                        'errorMessage' => 'Login and password are required.'
                    )
                );
            }
        }
        
        return $this;
    }
    
    
    /**
     * 
     */
    public function loginWithNetworkAction() {
        $this->__prepareAction();
        
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            return $this->__sendResponse(
                array(
                    'success'      => 0,
                    'errorMessage' => 'Already logged in'
                )
            );
        }
        
        if ($this->getRequest()->isPost()) {
            $__networkName = $this->getRequest()->getPost('network');
            
            $__network = null;
            foreach ( Mage::helper('ajaxlogin/data')->getNetworks() as $__networkInfo ) {
                if ( $__networkInfo->getName() == $__networkName ) {
                    $__network = $__networkInfo;
                    break;
                }
            }
            
            if ( $__network ) {
                if ( $__network->getModel() ) {
                    try {
                        $__model = Mage::getModel($__network->getModel());
                        if ( $__model ) {
                            $__model->setRequest($this->getRequest());
                            if ( $__model->login() ) {
                                $this
                                    ->__addToResponse( array('success' => 1) )
                                    ->__addToResponse( array('customer_information' => $this->__getCustomerData()) )
                                    ->__setResponseLanding( Mage::helper('ajaxlogin/data')->getConfigLanding(AW_Ajaxlogin_Helper_Data::XML_CONFIG_PATH_LOGINFORM_LOGIN_LANDING) )
                                    ->__sendResponse()
                                ;
                            }
                            else {
                                $this
                                    ->__addToResponse( array('success' => 0) )
                                    ->__addToResponse( array('notRegistered' => 1) )
                                    ->__sendResponse()
                                ;
                            }
                        }
                        else {
                            $this->__sendResponse(
                                array(
                                    'success'      => 0,
                                    'errorMessage' => 'Unknown error'
                                )
                            );
                        }
                    }
                    catch ( Exception $__E ) {
                        $this->__sendResponse(
                            array(
                                'success'      => 0,
                                'errorMessage' => $__E->getMessage()
                            )
                        );
                    }
                }
            }
        }
        
        return $this;
    }
    
    
    /**
     * 
     */
    public function logoutPostAction() {
        $this->__prepareAction();
        
        $this->_getSession()->logout()->setBeforeAuthUrl(Mage::getUrl());
        $this
            ->__addToResponse( array('success' => 1) )
            ->__setResponseLanding( Mage::helper('ajaxlogin/data')->getConfigLanding(AW_Ajaxlogin_Helper_Data::XML_CONFIG_PATH_LOGINFORM_LOGOUT_LANDING) )
            ->__sendResponse()
        ;
        
        return $this;
    }
    
    
    /**
     * 
     */
    public function registerPostAction() {
        $this->__prepareAction();
        
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            return $this->__sendResponse(
                array(
                    'success'      => 0,
                    'errorMessage' => 'Already logged in'
                )
            );
        }
        
        $session->setEscapeMessages(true);
        if ($this->getRequest()->isPost()) {
            $errors = array();
            
            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }
            
            /* Attempt to safely load a customer form model */
            if ( method_exists($customer, 'getEntityType') ) {
                /* This indicates Magento CE 1.4.2.0 and above + EE */
                $customerForm = Mage::getModel('customer/form');
            }
            else {
                $customerForm = null;
            }
            
            if ( $customerForm ) {
                /**
                 * CE 1.4.2.x and above, EE
                 */
                
                $customerForm
                    ->setFormCode('customer_account_create')
                    ->setEntity($customer)
                ;
                $customerData = $customerForm->extractData($this->getRequest());
            }
            else {
                /**
                 * 1.4.1.1
                 * There wasn't any form model that time in Magento
                 */
                $data = $this->_filterPostData($this->getRequest()->getPost());
                
                foreach (Mage::getConfig()->getFieldset('customer_account') as $code=>$node) {
                    if ($node->is('create') && isset($data[$code])) {
                        if ($code == 'email') {
                            $data[$code] = trim($data[$code]);
                        }
                        $customer->setData($code, $data[$code]);
                    }
                }
            }
            
            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }
            
            $customer->getGroupId();
            
            if ($this->getRequest()->getPost('create_address')) {
                $address = Mage::getModel('customer/address');
                $addressForm = Mage::getModel('customer/form');
                $addressForm
                    ->setFormCode('customer_register_address')
                    ->setEntity($address)
                ;
                
                $addressData   = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address
                        ->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false))
                    ;
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);
                    
                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                }
                else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }
            
            try {

                if ( $customerForm ) {
                    /**
                     * CE 1.4.2.x and above, EE
                     */
                    
                    $customerErrors = $customerForm->validateData($customerData);
                    if ($customerErrors !== true) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                    else {
                        $customerForm->compactData($customerData);
                        $customer->setPassword($this->getRequest()->getPost('password'));
                        $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                        $customerErrors = $customer->validate();
                        if (is_array($customerErrors)) {
                            $errors = array_merge($customerErrors, $errors);
                        }
                    }
                }
                else {
                    /**
                     * CE 1.4.1.1
                     */
                    
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }
                
                $validationResult = count($errors) == 0;
                if (true === $validationResult) {

                    /* start : add username on login */
                    $username = $this->getRequest()->getPost('username');
                    if (!preg_match ('/^[a-zA-Z0-9_.-]+$/', $username)) {
                        $message = $this->__('Username ' .$username . ' contains invalid character. Only letters (a-z), numbers (0-9), periods (.), dashs (-), and underscores (_) are allowed');
                        return $this->__sendResponse(
                            array(
                                'success'      => 0,
                                'errorMessage' => $message
                            )
                        );
                    }                    
                    $usernameAvailable = Mage::helper('socialcommerce')->checkUsernameAvailable($username);
                    if (! $usernameAvailable) {
                        $message = $this->__('Username ' .$username . ' already used by someone else. Please choose another username');  
                        return $this->__sendResponse(
                            array(
                                'success'      => 0,
                                'errorMessage' => $message
                            )
                        );
                    } else {
                        $customer->save();
                        $profile = Mage::getModel('socialcommerce/profile');

                        # Assign data
                        $profile->setCustomerId($customer->getId());
                        $profile->setStatus(1);
                        $profile->setWishlist(1);
                        $profile->setTemporary(0);
                        $profile->setUsername($username);
                        #
                        $profile->save();
                    }
                    /* end : add username on login */

                    
                    
                    Mage::dispatchEvent(
                        'customer_register_success',
                        array(
                            'account_controller' => $this,
                            'customer'           => $customer
                        )
                    );
                    
                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                        $this->__addToResponse( array('loggedIn' => 0) );
                        $__message =
                              $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName()) . "\n"
                            . $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()))
                        ;
                        $this->__addToResponse( array('successMessage' => $__message) );
                        $this->__cancelReloading();
                    }
                    else {
                        $session->setCustomerAsLoggedIn($customer);
                        $this->__addToResponse( array('loggedIn' => 1) );
                    }
                    
                    $this
                        ->__addToResponse( array('success' => 1) )
                        ->__addToResponse( array('customer_information' => $this->__getCustomerData()) )
                        ->__setResponseLanding( Mage::helper('ajaxlogin/data')->getConfigLanding(AW_Ajaxlogin_Helper_Data::XML_CONFIG_PATH_REGISTERFORM_LANDING) )
                        ->__sendResponse()
                    ;
                }
                else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if ( is_array($errors) ) {
                        $this->__addToResponse( array('errorMessage' => join('<br />', $errors)) );
                    }
                    else {
                        $this->__addToResponse( array('errorMessage' => 'Invalid customer data') );
                    }
                    $this->__sendResponse( array('success' => 0) );
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, try to request your new password and access your account.');
                    $session->setEscapeMessages(false);
                    $this->__addToResponse( array('frame' => 'recovery') );
                }
                else {
                    $message = $e->getMessage();
                }
                
                $this->__sendResponse(
                    array(
                        'success'      => 0,
                        'errorMessage' => $message
                    )
                );
            }
            catch (Exception $e) {
                $session
                    ->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'))
                ;
                $this->__sendResponse(
                    array(
                        'success'      => 0,
                        'errorMessage' => $this->__('Cannot save the customer.') . ': ' . $e->getMessage()
                    )
                );
            }
        }
        
        return $this;
    }
    
    
    /**
     * 
     */
    public function registerWithNetworkAction() {
        $__result = $this->registerPostAction();
        
        if ( $this->__getResponseObject()->getData('loggedIn') ) {
            $__networkName = $this->getRequest()->getPost('network');
            
            $__network = null;
            foreach ( Mage::helper('ajaxlogin/data')->getNetworks() as $__networkInfo ) {
                if ( $__networkInfo->getName() == $__networkName ) {
                    $__network = $__networkInfo;
                    break;
                }
            }
            
            if ( $__network ) {
                if ( $__network->getModel() ) {
                    try {
                        $__model = Mage::getModel($__network->getModel());
                        if ( $__model ) {
                            $__model->setRequest($this->getRequest());
                            $this
                                ->__addToResponse( array('customer_information' => $this->__getCustomerData()) )
                                ->__sendResponse()
                            ;
                        }
                    }
                    catch ( Exception $__E ) {
                        Mage::logException( $__E );
                    }
                }
            }
            
            
        }
        
        return $__result;
    }
    
    
    /**
     * 
     */
    public function recoveryPostAction() {
        $this->__prepareAction();
        
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->__sendResponse(
                    array(
                        'success'      => 0,
                        'errorMessage' => $this->__('Invalid email address.')
                    )
                );
            }
            
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email)
            ;
            if ($customer->getId()) {
                try {
                    if ( !method_exists($customer, 'changeResetPasswordLinkToken') ) {
                        /**
                         * CE 1.4.1.1, EE 1.11.x
                         */
                        
                        $newPassword = $customer->generatePassword();
                        $customer->changePassword($newPassword, false);
                        $customer->sendPasswordReminderEmail();
                    }
                    else {
                        /**
                         * CE 1.4.2.x and above, EE 1.12.0.x and above
                         */
                        
                        $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                        $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                        $customer->sendPasswordResetConfirmationEmail();
                    }
                }
                catch (Exception $__E) {
                    $this->__sendResponse(
                        array(
                            'success'      => 0,
                            'errorMessage' => $__E->getMessage()
                        )
                    );
                }
            }
            
            $this
                ->__addToResponse(
                    array(
                        'success'        => 1,
                        'successMessage' => Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email))
                    )
                )
                ->__setResponseLanding( Mage::helper('ajaxlogin/data')->getConfigLanding(AW_Ajaxlogin_Helper_Data::XML_CONFIG_PATH_RECOVERYFORM_LANDING) )
                ->__sendResponse()
            ;
        }
        else {
            $this->__sendResponse(
                array(
                    'success'      => 0,
                    'errorMessage' => $this->__('Please enter your email.')
                )
            );
        }
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __getResponseObject() {
        if ( !$this->__responseObject ) {
            $this->__responseObject = new Varien_Object();
        }
        
        return $this->__responseObject;
    }
    
    
    /**
     * 
     */
    protected function __sendResponse($dataToAdd = null) {
        if ( $dataToAdd ) {
            $this->__addToResponse($dataToAdd);
        }
        
        header('Content-Type: text/javascript');
        $this->getResponse()->setBody(
            Zend_Json_Encoder::encode($this->__getResponseObject()->getData())
        );
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __addToResponse($data) {
        if ( is_array($data) ) {
            foreach ( $data as $__key => $__value ) {
                $this->__getResponseObject()->setData($__key, $__value);
            }
        }
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __forceReloading() {
        $this->__addToResponse( array('__forceReloading' => 1) );
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __cancelReloading() {
        $this->__addToResponse( array('__cancelReloading' => 1) );
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __forceUpdating() {
        $this->__addToResponse( array('__forceUpdating' => 1) );
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __forceLanding($location) {
        $this->__addToResponse( array('__forceLanding' => $location) );
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __setResponseLanding($landing) {
        # Dependencies
        $__forceReloading  = $this->__getResponseObject()->getData('__forceReloading');
        $__cancelReloading = $this->__getResponseObject()->getData('__cancelReloading');
        $__forceUpdating   = $this->__getResponseObject()->getData('__forceUpdating');
        $__forceLanding    = $this->__getResponseObject()->getData('__forceLanding');
        
        # Current location
        $__location = $this->__fetchLocation();
        
        # Landing page
        $__landing = (string)$landing;
        if ( $__forceReloading ) {
            if ( !$__landing ) $__landing = $__location;
        }
        if ( $__forceLanding ) {
            $__landing = $__forceLanding;
        }
        if (strpos($__landing,'checkout/onepage') !== false) {
        	$__landing = str_replace("http","https", $__landing);
        }
        
        # Do landing
//         if ( ($__landing) and (!$__cancelReloading) and (($__landing != $__location) or ($__forceReloading)) ) {
            $this->__addToResponse( array('landing' => $__landing) );
//         }
        
        # Do updating
//         if ( (!$__landing) or ($__landing == $__location) or ($__forceUpdating) ) {
//             $this->getLayout()->getUpdate()->addHandle('default');
//             if ( Mage::getSingleton('customer/session')->isLoggedIn() ) {
//                 $this->getLayout()->getUpdate()->addHandle('customer_logged_in');
//             }
//             else {
//                 $this->getLayout()->getUpdate()->addHandle('customer_logged_out');
//             }
//             $this->loadLayoutUpdates();
//             $this->generateLayoutXml();
//             $this->generateLayoutBlocks();
            
//             $__updates = array();
            
//             $__block = $this->getLayout()->getBlock('header');
//             if ( $__block ) {
//                 $__updates['header_container'] = array(
//                     'selection'        => '.header-container',
//                     'inner'            => true,
//                     'update'           => $__block->toHtml(),
//                     'update_selection' => '.header-container'
//                 );
//             }
            
//             $__block = $this->getLayout()->getBlock('cart_sidebar');
//             if ( $__block ) {
//                 $__updates['cart_sidebar'] = array(
//                     'selection' => '.col-right .block-cart',
//                     'update'    => $__block->toHtml()
//                 );
//             }
            
//             $this->__addToResponse( array('pageUpdates' => $__updates) );
//         }
        
        return $this;
    }
    
    
    /**
     * 
     */
    protected function __fetchLocation() {
        $__location = $this->_getRefererUrl();
        if ( !$__location ) {
            $__location = $this->getRequest()->getPost('location');
        }
        if ( !$__location ) {
            $__location = $this->getRequest()->getParam('location');
        }
        
        return $__location;
    }
    
    
    /**
     * 
     */
    protected function __relatedToCustomerAccountRoutine($location) {
        $__customerAccountRoutines = array(
            Mage::getUrl('customer'),
//             Mage::getUrl('sales/order/history'),
            Mage::getUrl('sales/billing_agreement'),
            Mage::getUrl('sales/recurring_profile'),
            Mage::getUrl('review/customer'),
            Mage::getUrl('tag/customer'),
            Mage::getUrl('wishlist'),
            Mage::getUrl('oauth'),
            Mage::getUrl('newsletter/manage'),
            Mage::getUrl('downloadable/customer')
        );
        
        $__belong = false;
        foreach ( $__customerAccountRoutines as $__routine ) {
            if ( strpos($location, $__routine) !== false ) {
                $__belong = true;
                false;
            }
        }
        
        return $__belong;
    }
    
    
    /**
     * 
     */
    protected function __relatedToShoppingCartRoutine($location) {
        $__shoppingCartRoutine = Mage::getUrl('checkout/cart/');
        return ( strpos($location, $__shoppingCartRoutine) !== false ) ? true : false;
    }
    
    
    /**
     * 
     */
    protected function __relatedToCheckoutRoutine($location) {
        $__checkoutRoutine = Mage::getUrl('checkout/onepage/');
        return ( strpos($location, $__checkoutRoutine) !== false ) ? true : false;
    }
    
    
    /**
     * 
     */
    protected function __getCustomerData() {
        $__customerData = null;
        
        $__customer = $this->_getSession()->getCustomer();
        if ( $__customer ) {
            $__customerData = array(
                'firstname'  => $__customer->getData('firstname'),
                'lastname'   => $__customer->getData('lastname'),
                'email'      => $__customer->getData('email'),
                'gender'     => $__customer->getData('gender'),
                'taxvat'     => $__customer->getData('taxvat')
            );
            
            if ( $__customer->getPrimaryBillingAddress() ) {
                foreach  ( $__customer->getPrimaryBillingAddress()->getData() as $__key => $__value ) {
                    $__customerData[$__key] = $__value;
                }
            }
            
            if ( isset($__customerData['vat_id']) and ( $__customerData['vat_id'] ) ) {
                $__customerData['taxvat'] = $__customerData['vat_id'];
            }
        }
        
        return $__customerData;
    }
    
    
    /**
     * 
     */
    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }
    
    
    /**
     * Tribute to Magento 1.4.1.1
     */
    protected function _filterPostData($data) {
        return $this->_filterDates($data, array('dob'));
    }
}
