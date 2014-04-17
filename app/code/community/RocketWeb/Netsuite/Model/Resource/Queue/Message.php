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
class RocketWeb_Netsuite_Model_Resource_Queue_Message extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct()
	{
		$this->_init('rocketweb_netsuite/queue_message', 'message_id');
	}

    public function loadByBody($message,$body) {
        $adapter = $this->_getReadAdapter();
        $bind    = array('body' => $body);
        $select  = $adapter->select()
            ->from($this->getMainTable(), array('message_id'))
            ->where('body = :body');

        $messageId = $adapter->fetchOne($select, $bind);
        if ($messageId) {
            $this->load($message, $messageId);
        } else {
            $message->setData(array());
        }

        return $this;
    }


}