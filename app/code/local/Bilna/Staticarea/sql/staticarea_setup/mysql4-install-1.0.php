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
  `block_id` char(100) NOT NULL,
  `status_area` enum('1','0') NOT NULL DEFAULT '1',
  `type` char(100) NOT NULL,
  `storeview` char(250) NOT NULL,
  `area_createddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `area_updatedate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `bilna_staticarea_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staticarea_id` int(11) NOT NULL,
  `content` longtext,
  `status` enum('1','0') NOT NULL DEFAULT '1',
  `active_from` datetime DEFAULT NULL,
  `active_to` datetime DEFAULT NULL,
  `order` tinyint(3) NOT NULL,
  `url` varchar(250) NOT NULL,
  `url_action` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
");

$installer->endSetup();