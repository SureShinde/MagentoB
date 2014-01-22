<?php
/**
 *
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @version    1.1.1
 * @copyright  Copyright (c) 2010-2012 PT Bilna. (http://www.bilna.com)
 * 
 */


$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('wrappinggiftevent/manage')}
    ADD wrapping_startdate DATE null 
    AFTER wrapping_desc;

    ALTER TABLE {$this->getTable('wrappinggiftevent/manage')}
    ADD wrapping_enddate DATE null 
    AFTER wrapping_startdate;
");

$installer->endSetup();