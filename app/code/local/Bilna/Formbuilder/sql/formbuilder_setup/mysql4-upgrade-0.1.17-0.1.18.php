<?php
/**
 * Description of mysql4-upgrade-0.1.16-0.1.17
 *
 * @author Bilna Development Team <development@bilna.com>
 */
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE bilna_formbuilder_form 
	CHANGE COLUMN `active_from` `active_from` DATETIME NULL ,
	CHANGE COLUMN `active_to` `active_to` DATETIME NULL ;
	");
$installer->endSetup();