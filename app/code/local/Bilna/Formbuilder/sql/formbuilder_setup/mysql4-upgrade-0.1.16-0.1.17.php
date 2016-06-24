<?php
/**
 * Description of mysql4-upgrade-0.1.16-0.1.17
 *
 * @author Bilna Development Team <development@bilna.com>
 */

$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE `bilna_formbuilder_input` CHANGE COLUMN `value` `value` TEXT NULL DEFAULT NULL ;
	");
$installer->endSetup();
