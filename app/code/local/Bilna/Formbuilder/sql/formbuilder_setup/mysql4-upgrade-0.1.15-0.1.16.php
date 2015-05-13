<?php
/**
 * Description of mysql4-upgrade-0.1.15-0.1.16
 *
 * @author Bilna Development Team <development@bilna.com>
 */

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `bilna_formbuilder_input` MODIFY `value` varchar(256) DEFAULT NULL;
    ALTER TABLE `bilna_formbuilder_form` ADD COLUMN `product_promo` TINYINT NULL DEFAULT '0' AFTER `email_share_apps`;
");
$installer->endSetup();
