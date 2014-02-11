<?php
/**
* @copyright Bilna.
*/
$this->startSetup();

$this->run("
    ALTER TABLE am_landing_page ADD `banner` VARCHAR(50) DEFAULT NULL;
    ALTER TABLE am_landing_page ADD `background` VARCHAR(50) DEFAULT NULL;
    ALTER TABLE am_landing_page ADD `rack` VARCHAR(50) DEFAULT NULL;
");

$this->endSetup();