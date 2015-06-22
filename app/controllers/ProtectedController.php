<?php

/*
 * @author Bilna Development <development@bilna.com> 
 */

namespace Frontend\Controllers;

use Phalcon\Flash\Direct,
    Phalcon\Flash\Session,
    Phalcon\Acl
;
use Frontend\Libraries\BackendValidator

//,Backend\Core\Libraries\Authentication
;

abstract class ProtectedController extends CoreController {

    public function initialize() {
        parent::initialize();
        //detect if already login, if yes then redirect to dashboard
        $page = $this->dispatcher->getModuleName() . "/" . $this->dispatcher->getControllerName() . "/" . $this->dispatcher->getActionName();
        if (in_array($page, (array) $this->config->auth->nonloginpage)) {
            #public areas, still do nothing
        } else {
            $this->view->page = $page;
            #areas with session
            $this->auth = $this->session->get('auth');
            //$this->auth['session_created_time']	= !isset($this->auth['session_created_time']) ? \time() : $this->auth['session_created_time'];
            $this->timeDiff = $this->session->get('lastAccessTime') != '' ? $_SERVER['REQUEST_TIME'] - $this->session->get('lastAccessTime') : $_SERVER['REQUEST_TIME'] - $this->auth['session_created_time'];
            $this->http = $this->common->isSecure() ? "https" : "http";
            $this->lastUrl = $this->dispatcher->getControllerName() != 'ajax' ? $this->http . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "";
            $this->session->set('lastUrl', $this->lastUrl);
            if ($this->timeDiff >= $this->config->maxloginsession) {
                $this->session->remove('auth');
                $this->session->remove('lastAccessTime');
                if ($this->dispatcher->getControllerName() == 'ajax') {
                    /* return response code 401 for jtable datalist if session expired
                      http://www.jtable.org/ApiReference/GeneralOptions#genopt-unAuthorizedRequestRedirectUrl
                     */
                    http_response_code(401);
                    exit;
                } else {
                    return $this->response->redirect('?sessionexpired=1');
                }
            }
            if (!$this->session->get('auth')) {
                $this->session->remove('lastAccessTime');
                $this->flash->error('Please login to continue..');
                return $this->response->redirect('?pleaselogin=1');
            } else {
                $this->session->set('lastAccessTime', $_SERVER['REQUEST_TIME']);
                $this->view->page = $page;
                $auth = $this->session->get('auth');
                $authentication = new Authentication();
                $this->view->users = $auth;
                #implement ACL if already login
                $acl = $authentication->getACL($auth['admin_role_id']);
                $resource = $this->dispatcher->getModuleName() . "/" . $this->dispatcher->getControllerName();
                $method = $this->view->method != '' ? $this->view->method : "other";
                #$this->common->debug($method,FALSE);
                #$this->common->debug($this->view->method,FALSE);
                #$this->common->debug($this->aclmethod,FALSE);
                #die();
                $this->view->title = !$this->view->title ? "Unauthorized Access" : $this->view->title;
                #echo $authentication->getAdminRoleNameById($auth['admin_role_id']).", $resource, $method <hr>";die();
                if ($acl->isAllowed($authentication->getAdminRoleNameById($auth['admin_role_id']), $resource, $method)) {
                    //allowed	    			
                } else {
                    return $this->view->pick("../../dashboard/views/dashboard/unauth");
                }
            }
        }
    }

    //public function microtime_float()
    //{
    //    list($usec, $sec) = explode(" ", microtime());
    //    return ((float)$usec + (float)$sec);
    //}
    //
	//public function route404Action()
    //{
    //	return $this->view->pick("route404");
    //}
}
