<?php
$installer = $this;
$installer->startSetup();
$installer->run("    
    CREATE TABLE IF NOT EXISTS `bin_code` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` int(11) NOT NULL,
        `platform` varchar(64) NOT NULL,
        `issuer` varchar(32) NOT NULL,
        `name` varchar(128) NOT NULL,
        PRIMARY KEY (`id`)
    );

    INSERT INTO bin_code VALUES
        (null, '410504', 'Visa', 'BNI', 'BNI Biru'),
        (null, '410505', 'Visa', 'BNI', 'BNI Gold'),
        (null, '426097', 'Visa', 'BNI', 'BNI BJB Platinum'),
        (null, '426098', 'Visa', 'BNI', 'BNI BJB Gold'),
        (null, '436583', 'Visa', 'BNI', 'BNI Platinum Corporate'),
        (null, '439062', 'Visa', 'BNI', 'BNI Gold Corporate'),
        (null, '451249', 'Visa', 'BNI', 'BNI Platinum'),
        (null, '457512', 'Visa', 'BNI', 'BNI Infinite'),
        (null, '466573', 'Visa', 'BNI', 'BNI GARUDA SIGNATURE'),
        (null, '466574', 'Visa', 'BNI', 'BNI GARUDA PLATINUM'),
        (null, '471293', 'Visa', 'BNI', 'BNI Ferrari'),
        (null, '518446', 'MasterCard', 'BNI', 'BNI BIC Platinum'),
        (null, '519893', 'MasterCard', 'BNI', 'BNI Platinum'),
        (null, '522028', 'MasterCard', 'BNI', 'BNI BIC Biru'),
        (null, '522505', 'MasterCard', 'BNI', 'BNI Galeries Lafayette'),
        (null, '522787', 'MasterCard', 'BNI', 'BNI Citilink Gold'),
        (null, '523026', 'MasterCard', 'BNI', 'BNI Matrix Biru'),
        (null, '524069', 'MasterCard', 'BNI', 'BNI Matrix Gold'),
        (null, '524125', 'MasterCard', 'BNI', 'BNI Titanium'),
        (null, '524495', 'MasterCard', 'BNI', 'BNI Citilink Titanium'),
        (null, '524609', 'MasterCard', 'BNI', 'BNI Lotte Platinum'),
        (null, '526422', 'MasterCard', 'BNI', 'BNI Silver'),
        (null, '526423', 'MasterCard', 'BNI', 'BNI'),
        (null, '531857', 'MasterCard', 'BNI', 'BNI BIC Gold'),
        (null, '532668', 'MasterCard', 'BNI', 'BNI Emerald'),
        (null, '537176', 'MasterCard', 'BNI', 'BNI Gold'),
        (null, '542640', 'MasterCard', 'BNI', 'BNI Gold'),
        (null, '548415', 'MasterCard', 'BNI', 'BNI Lotte Gold'),
        (null, '548988', 'MasterCard', 'BNI', 'BNI Biru');

");
$installer->endSetup();
