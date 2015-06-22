<?php
/**
 * Description of LoginController
 *
 * @author bilna development.
 */
namespace Frontend\Controllers;

class LoginController extends CoreController{
    protected $_css = array (
        'bilna/customer',
        'thirdparty/datepicker',
        'thirdparty/jquery-ui-datepicker',
    );
    protected $_js = array (
        //'bilna/login',
    );
    
    protected $_recaptchaEnabled  = false;
    protected $_genderArr         = array ();
    
    /*
     * login default.
     */
    public function indexAction(){
        
    }
    
    /*
     * register.
     */
    public function registerAction(){
    
    }
    
    /*
     * verify register account.
     */
    public function verifyRegisterAction(){
        die('verifyRegister');
    }
    
    /*
     * forget password.
     */
    public function forgetAction(){
    
    }
    
    /*
     * verify forget password.
     */
    public function verifyForgetAction(){
        die('verifyForget');
    }
    
    /*
     * change password.
     */
    public function changePasswordAction(){
        $this->view->render('login','change-password');
    }
}
