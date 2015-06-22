<?php
/**
 * Description of BilnaStatic
 *
 * @author mariovalentino
 */
namespace Bilna\Libraries;

class BilnaStatic {
    public static $registered   = ['yes' => 1, 'no' => 2, 'guest' => 3];
    public static $method       = ['web' => 1, 'facebook' => 2, 'twitter' => 3, 'google' => 4];
    
    public static $sessionLoginUser = 'SESSION_LOGIN_USER';
    public static $sessionLoginGuest = 'SESSION_LOGIN_GUEST';
    public static $sessionBasketGuest = 'SESSION_BASKET_GUEST';
    public static $sessionLoginUserRedis = 'session/login/user';
    public static $sessionLoginGuestRedis = 'session/login/guest';
    public static $sessionBasketUserRedis = 'session/basket/user';
    public static $sessionBasketGuestRedis = 'session/basket/guest';
    public static $sessionCartVoucher = 'SESSION_CART_VOUCHER';
    
    public static $sessionLanguage = 'SESSION_LANG';
    public static $sessionGroupPrice = 'SESSION_GROUP_PRICE';
    public static $sessionGroupWebsite = 'SESSION_GROUP_WEBSITE';
    
    public static $userCookie = 'bilna_login_cookie';
    public static $cartCookie = 'bilna_cart_cookie';
    
    public static $api      = 'api';
    public static $widget   = 'widget';
    public static $page     = 'page';
    public static $session  = 'session';
    
    public static $web      = 'web';
    public static $facebook = 'facebook';
    public static $twitter  = 'twitter';
    public static $google   = 'google';
    
    public static $groupGeneral = 'GENERAL';
    public static $groupGuest   = 'NOT_LOGGED_IN';
    public static $groupBaby    = 'BABY_CUSTOMER';
    public static $groupHome    = 'HOME_CUSTOMER';
    public static $groupCredit  = 'BCREDIT_CUSTOMER';
    
    
    public static $update_profile    = 'update_profile';
    public static $update_password   = 'update_password';
    public static $update_newsletter = 'update_newsletter';
    
    public static $newsletter   = 'newsletter';
    public static $website      = 'website';
    public static $price        = 'price';
    
    public static $customerOrder = 'customer-order';
    
    public static $userLogin = 'session/user/login';
    
    public static $guestCartName       = 'session/cart/guest';
    public static $userCartName        = 'session/cart/user';
    
    public static $userCartVoucher     = 'user-voucher';
    
    public static $voucherOK           = 'OK';
    public static $voucherWarning      = 'Warning';
    public static $voucherFail         = 'Fail';
    public static $voucherPending      = 'Pending';
    
    
    public static $messageTypeCredential = 'credential';
    public static $messageTypePayment    = 'payment';
    public static $messageTypeShipping   = 'shipping';
    
	//STATIC DEFAULT PRODUCT WIDGET
    public static $productWidgetLayout = 1;
    public static $productWidgetQty = 1;
    
    public static $type_address = ['residence' => 1, 'apartment' => 2, 'office' => 3];
    
    
    public static $BILNA_GIFT_VOUCHER    = 'bilna-gift-voucher';
    public static $VERITRANS_CREDIT_CARD = 'veritrans-credit-card';
    public static $BILNA_ATM_TRANSFER    = 'bilna-atm-transfer';
    public static $BILNA_COD             = 'bilna-cod';
    public static $SPRINT_KLIKBCA        = 'sprint-klikbca';
    public static $SPRINT_KLIKPAY        = 'sprint-klikpay';
    public static $BILNA_CREDIT          = 'bilna-credit';
    
    public static $input_gift_voucher    = 'code';
    public static $input_sprint_klikpay  = 'userid';
    public static $input_veritrans_cc    = 'card-number';
    public static $input_bilna_credit    = 'point';
    
    public static $customerTypeVerified     = 'verified';
    public static $customerTypeGuest        = 'guest';
    public static $customerTypeNotVerified  = 'unverified';
    
    //- Customer Credentials Type
    public static $customerCredentialsType = array (
        array (
            'type' => 1,
            'code' => 'login',
            'name' => 'Login',
        ),
        array (
            'type' => 2,
            'code' => 'facebook',
            'name' => 'Facebook',
        ),
        array (
            'type' => 3,
            'code' => 'twitter',
            'name' => 'Twitter',
        ),
        array (
            'type' => 4,
            'code' => 'google-plus',
            'name' => 'Google +',
        ),
    );
    /**
     * default message
     */
    const IS_NOT_AJAX = 'just for ajax request';
    const IS_NOT_POST = 'just for post request';
    
    public static $socialStatus = ['NOK' => 'NOK', 'OK' => 'OK', 'PENDING' => 'PENDING', 'NEED_INFO' => 'NEED_INFO'];
    
    public static $productType  = ['SIMPLE' => 'simple', 'CONFIGURABLE' => 'configurable'];
}