<?php 
require_once '../app/Mage.php';
Mage::app();


$helper = Mage::helper('sendreport');

$senderEmail = Mage::getStoreConfig('bilna_sendreport/sendreport/sender_email');
$senderName = Mage::getStoreConfig('bilna_sendreport/sendreport/sender_name');
$receiverEmail = explode(',', Mage::getStoreConfig('bilna_sendreport/sendreport/receiver_email'));
$action = Mage::getStoreConfig('bilna_sendreport/sendreport/action');

$sql = "( SELECT categorySub.VALUE AS Category, COUNT(DISTINCT sfoi.order_id) AS 'Total Number of Orders', sub1.toto AS 'Total Number of Orders (excluding cancelled)', FORMAT(sub1.totrevb / sub1.toto, 0) AS 'Average Basket Size', FORMAT(SUM(sfoi.row_total), 0) AS 'Revenue before Disc (IDR)', FORMAT(SUM(sfoi.row_total) / 13500, 0) AS 'Revenue before Disc (USD)', FORMAT(sub1.totrevb, 0) AS 'Revenue before Disc (excluding Cancelled - IDR)', FORMAT(sub1.totrevb / 13500, 0) AS 'Revenue before Disc (excluding Cancelled - USD)', FORMAT(sub1.totreva, 0) AS 'Revenue after Disc (excluding Cancelled - IDR)', FORMAT(sub1.totreva / 13500, 0) AS 'Revenue after Disc (excluding Cancelled - USD)', FORMAT(sub3.mbd, 2) AS 'Margin before Disc (%)', FORMAT(100 * ((sub1.totrevb - sub1.totreva) / sub1.totrevb), 2) AS 'Disc (%)' FROM sales_flat_order_item sfoi INNER JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN ( SELECT DISTINCT cpe.entity_id, eaov. VALUE FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei. VALUE = eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND eao.attribute_id = 292 WHERE cpe.sku IS NOT NULL AND cpei.VALUE IS NOT NULL ) categorySub ON sfoi.product_id = categorySub.entity_id INNER JOIN ( SELECT categorySub. VALUE AS category, COUNT(DISTINCT sfoi.order_id) AS toto, ROUND((SUM(sfoi.row_total)), 0) AS totrevb, ROUND( ( SUM(sfoi.row_total) - SUM( IF ( sfo.discount_amount < 0, sfoi.discount_amount, 0 ) ) ), 0 ) AS totreva FROM sales_flat_order_item sfoi INNER JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN ( SELECT DISTINCT cpe.entity_id, eaov. VALUE FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei. VALUE = eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND eao.attribute_id = 292 WHERE cpe.sku IS NOT NULL AND cpei.VALUE IS NOT NULL ) categorySub ON sfoi.product_id = categorySub.entity_id WHERE  DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) GROUP BY categorySub.VALUE ) sub1 ON categorySub.VALUE   = sub1.category INNER JOIN ( ( SELECT categorySub. VALUE AS category, 100 * (   1 - (subb1.cost / SUM(sfoi.row_total)) ) AS mbd FROM sales_flat_order_item sfoi INNER JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN ( SELECT DISTINCT cpe.entity_id, eaov. VALUE FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei. VALUE = eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND eao.attribute_id = 292 WHERE cpe.sku IS NOT NULL AND cpei.VALUE IS NOT NULL ) categorySub ON sfoi.product_id = categorySub.entity_id INNER JOIN ( SELECT categorySub.VALUE AS category, SUM(IF(sfoi.parent_item_id IS NOT NULL AND sfoi.row_total = 0, 0,(sfoi.qty_ordered * cped.VALUE))) AS cost FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN ( SELECT DISTINCT cpe.entity_id, eaov. VALUE FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei. VALUE = eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND eao.attribute_id = 292 WHERE cpe.sku IS NOT NULL AND cpei.VALUE IS NOT NULL ) categorySub ON sfoi.product_id = categorySub.entity_id WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) GROUP BY category ) subb1 ON categorySub.VALUE = subb1.category WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) AND sfoi.item_id NOT IN ( SELECT sfoi.item_id FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND cped.attribute_id = 79 INNER JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) AND (sfoi.qty_ordered * cped.VALUE IS NULL OR 0) ) GROUP BY categorySub.VALUE ) ) sub3 ON categorySub.VALUE = sub3.category WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) GROUP BY categorySub.VALUE ) UNION ( SELECT 'Grand Total', COUNT(sfo.entity_id), sub2.toto, FORMAT(sub2.totrevb / sub2.toto, 0), FORMAT(SUM(sfo.subtotal),0), FORMAT(SUM(sfo.subtotal) / 13500,0), FORMAT(sub2.totrevb, 0), FORMAT(sub2.totrevb / 13500, 0), FORMAT(sub2.totreva, 0), FORMAT(sub2.totreva / 13500, 0), FORMAT(sub4.mbd, 2), FORMAT(100 * ((sub2.totrevb - sub2.totreva) / sub2.totrevb), 2) FROM sales_flat_order sfo INNER JOIN ( SELECT COUNT(sfo.entity_id) AS toto, ROUND(SUM(sfo.subtotal),0) AS totrevb, ROUND(SUM(sfo.grand_total) - SUM(sfo.shipping_amount),0) AS totreva FROM sales_flat_order sfo WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) ) sub2 ON TRUE INNER JOIN ( SELECT 100 * ( 1 - subb1.cost / SUM(sfoi.row_total) ) AS mbd FROM sales_flat_order_item sfoi LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id LEFT JOIN ( SELECT SUM(IF(sfoi.parent_item_id IS NOT NULL AND sfoi.row_total = 0, 0,(sfoi.qty_ordered * cped.VALUE))) AS cost FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) ) subb1 ON TRUE WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) AND sfoi.item_id NOT IN ( SELECT sfoi.item_id FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) AND sfo.state IN ( 'new', 'processing', 'complete' ) AND (sfoi.qty_ordered * cped.VALUE IS NULL OR 0) ) ) sub4 ON TRUE WHERE DATE( ADDDATE( sfo.created_at, INTERVAL 7 HOUR ) ) = DATE( ADDDATE(NOW(), INTERVAL 2 HOUR) ) )";
$sql = strtolower($sql);

$str_arr = $helper->getInbetweenStrings("select", 'from', $sql);
$str_arr[0] = $str_arr[0].',';
$str_arr = $helper->getInbetweenStrings('as', ',', $str_arr[0]);
$message = "<table border='1'><tr>";
foreach ($str_arr as $value) {
    $message .= '<td>'.trim(str_replace("'","",$value)).'</td>';
}
$message .= "</tr>";

$readresult = $helper->getData($sql);
while ($row = $readresult->fetch() ) {
	$message .= '<tr>';
	foreach ($str_arr as $value) {
	    $message .= '<td>'.$row[trim(str_replace("'","",$value))].'</td>';
	}
	$message .= '</tr>';
}
$message .= "</table>";

if($action=='send'){
	$mail = new Zend_Mail();
	$mail->setBodyHtml($message);
	$mail->setFrom($senderEmail, $senderName);
	$mail->addTo($receiverEmail, '');
	$mail->setSubject('Magento Hourly Report - '.date('D, d M Y H:i', strtotime('+7 hours')));
	$mail->send();
}else{
	echo $message;
}