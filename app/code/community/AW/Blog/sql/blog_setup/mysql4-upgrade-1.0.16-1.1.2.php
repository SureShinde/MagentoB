<?php
$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('blog/lcat')} ADD `layout` CHAR( 50 ) NOT NULL DEFAULT 'two_column.phtml' AFTER `sort_order` ;

CREATE TABLE `aw_blog_layout` (
  `layout_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_name` char(100) NOT NULL,
  `layout_desc` varchar(200) NOT NULL,
  PRIMARY KEY (`layout_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
");

$installer->endSetup();