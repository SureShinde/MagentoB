<?php

$installer = $this;
$installer->startSetup();

$query = "
    DROP TABLE IF EXISTS {$this->getTable('send_report')};

    CREATE TABLE {$this->getTable('send_report')} (
        `send_report_id` INT(11) unsigned NOT NULL auto_increment,
        `send_report_code` VARCHAR(255) NOT NULL,
        `sender_email` VARCHAR(255) NOT NULL,
        `sender_name` VARCHAR(255) NULL,
        `receiver_email` VARCHAR(255) NOT NULL,
        `receiver_name` VARCHAR(255) NULL,
        `version` VARCHAR(4) NOT NULL DEFAULT 'test',
        `status` SMALLINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`send_report_id`),
        CONSTRAINT UNQ_ReportCode UNIQUE (send_report_code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

    INSERT INTO {$this->getTable('send_report')} VALUES(
        NULL,
        'sales_by_category_report',
        'indra.kurniawan@bilna.com',
        'Tickets Bilna',
        'indra.kurniawan@bilna.com, taufik.r@bilna.com, uke.m@bilna.com',
        NULL,
        'prod',
        1
    );
";

$installer->run($query);

$installer->endSetup();
