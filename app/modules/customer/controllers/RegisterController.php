<?php

namespace Frontend\Customer\Controllers;


class RegisterController extends \Frontend\Controllers\CoreController {
    protected $_css = array (
        'bilna/customer',
        'thirdparty/datepicker',
        'thirdparty/jquery-ui-datepicker',
    );
    protected $_js = array (
        //'bilna/login',
    );
    
    public function IndexAction(){
        $this->view->viewInModule = true;
    }
} 