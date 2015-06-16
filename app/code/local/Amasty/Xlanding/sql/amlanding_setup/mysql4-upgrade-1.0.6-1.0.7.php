<?php
/**
* @copyright Bilna.2013
*/
$this->startSetup();

$this->run("
    INSERT INTO core_config_data VALUES (NULL, 'default', 0, 'bilna_module/amlanding/imageurl', 'amasty');
");

$this->endSetup();