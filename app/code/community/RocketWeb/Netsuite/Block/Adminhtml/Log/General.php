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
class RocketWeb_Netsuite_Block_Adminhtml_Log_General extends Mage_Core_Block_Template {
	public function getNumLines() {
		return Mage::registry('general_log_num_lines');
	}
	
	public function getLogLines() {
		$logFileName = RocketWeb_Netsuite_Helper_Data::LOG_FILE_NAME;
		$linesToDisplay = $this->getNumLines();
		
		if (!$open_file = @fopen(Mage::getBaseDir('log').DS.$logFileName,'r')) {
			$this->setError("Cannot open log file. Make sure Magento logging is enabled.");
		}
		else {
			$pointer = -2;	// Ignore new line characters at the end of the file
			$char = '';
			$beginning_of_file = false;
			$lines = array();
			
			for ($i=1;$i<=$linesToDisplay;$i++) {
				if ($beginning_of_file == true) {
					continue;
				}
				while ($char != "\n") {
					if(fseek($open_file,$pointer,SEEK_END) < 0) {
						$beginning_of_file = true;
						rewind($open_file);
						break;
					}
					$pointer--;
					fseek($open_file,$pointer,SEEK_END);
					$char = fgetc($open_file);
				}
				array_push($lines,fgets($open_file));
				$char = '';
			}
		}
		fclose($open_file);
		
		return array_reverse($lines);
	}
	
	protected $_errors = array();
	
	public function getErrors() {
		return $this->_errors;
	}
	public function setError($error) {
		$this->_errors[] = $error;
	}
}