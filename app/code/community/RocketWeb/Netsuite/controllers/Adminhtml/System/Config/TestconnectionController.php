<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Adminhtml_System_Config_TestconnectionController extends Mage_Adminhtml_Controller_Action {
	public function connectAction() {
		$connectionData = array();
		$connectionData['host'] = $this->getRequest()->getParam('host');
		$connectionData['end_point'] = $this->getRequest()->getParam('end_point');
		$connectionData['account_id'] = $this->getRequest()->getParam('account_id');
		$connectionData['email'] = $this->getRequest()->getParam('email');
		$connectionData['password'] = $this->getRequest()->getParam('password');
		$connectionData['role_id'] = $this->getRequest()->getParam('role_id');
		
		$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService($connectionData);
		$netsuiteService->setSearchPreferences(false, 1);
        $getServerTimeRequest = new GetServerTimeRequest();
		
		try {
			$response = $netsuiteService->getServerTime($getServerTimeRequest);
		}
		catch(Exception $ex) {
			$result['status'] = 'error';
			$result['message'] = $ex->getMessage();
			echo json_encode($result);
			return;	
		}

        if(is_null($response)) {
            $result['status'] = 'error';
            $result['message'] = 'Cannot connect';
        }
        else {
            $result['status'] = 'success';
        }

        echo json_encode($result);
        return;


    }
}