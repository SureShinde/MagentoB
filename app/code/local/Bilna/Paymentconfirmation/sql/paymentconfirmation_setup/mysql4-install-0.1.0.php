<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    DELETE FROM cms_block WHERE identifier = 'above-header';
    INSERT INTO cms_block set content = '<div class=\"space-above-header\">
    <div class=\"container-above-header\">
    <ul class=\"keypoint\">
    <li><a href=\"{{config path=\'web/unsecure/base_url\'}}shipping-policy/\"><em class=\"fa fa-truck\"></em> Pengiriman Gratis</a></li>
    <li><a href=\"{{config path=\'web/unsecure/base_url\'}}return-policy/\"><em class=\"fa fa-money\"></em> Perlindungan Pembeli</a></li>
    <li><a href=\"{{config path=\'web/unsecure/base_url\'}}faq/\"><em class=\"fa fa-reply-all\"></em> Pusat Bantuan</a></li>
    <li><a href=\"{{config path=\'web/unsecure/base_url\'}}orderdetail/\"><em class=\"fa fa-file-text-o\"></em> Cek Pesanan</a></li>
    <li><a href=\"{{config path=\'web/unsecure/base_url\'}}konfirmasipembayaran/\"><em class=\"fa fa-file-text-o\"></em> Konfirmasi Pembayaran</a></li>
    </ul>
    <div class=\"customer-link\">{{block type=\"staticarea/block\" id=\"banner-corner\"}}</div>
    </div>
    </div>',title='above-header',creation_time=NOW(),update_time=NOW(),is_active=1,identifier = 'above-header';
    DROP TABLE IF EXISTS `bilna_payment_confirmation`;
    CREATE TABLE `bilna_payment_confirmation` (
    `id` int(13) NOT NULL AUTO_INCREMENT,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `order_id` varchar(50) NOT NULL,
    `email` varchar(50) DEFAULT NULL,
    `nominal` double DEFAULT NULL,
    `dest_bank` varchar(50) NOT NULL,
    `transfer_date` date DEFAULT NULL,
    `source_bank` varchar(50) NOT NULL,
    `source_acc_number` varchar(50) NOT NULL,
    `source_acc_name` varchar(50) NOT NULL,
    `comment` text,
    `status` int(1) DEFAULT '0',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1
");
$installer->endSetup();

