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
class RocketWeb_Netsuite_Model_Resource_Queue_Message_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct()
	{
		parent::_construct();
		$this->_init('rocketweb_netsuite/queue_message');
	}
	
	
	protected function _initSelect()
	{
		parent::_initSelect();
		$this->getSelect()->columns('LEFT(body,LOCATE(\'|\',body)-1) as action');
		$this->getSelect()->columns('RIGHT(body,CHAR_LENGTH(body) - LOCATE(\'|\',body)) as item_id');
		
		return $this;
	}
}