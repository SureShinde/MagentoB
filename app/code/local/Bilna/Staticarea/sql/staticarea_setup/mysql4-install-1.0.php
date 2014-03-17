<?php
/**
 *
 * @category   Bilna
 * @package    Bilna_Staticarea
 * @version    
 * @copyright  www.bilna.com
 * @license    
 */


$installer = $this;

$installer->startSetup();

$installer->run("	
CREATE TABLE `bilna_staticarea` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_name` char(100) NOT NULL,
  `block_id` int(11) NOT NULL,
  `status_area` enum('1','0') NOT NULL DEFAULT '1',
  `type` char(100) NOT NULL,
  `storeview` char(250) NOT NULL,
  `area_createddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `area_updatedate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

$installer->endSetup();