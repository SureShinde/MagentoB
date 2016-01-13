<?php 
require_once '../app/Mage.php';
Mage::app();

$sendFromEmailAddress = "indra.kurniawan@bilna.com";
$sendFromName = 'Tickets Bilna'; 
$sendToEmailAddress = array("indra.kurniawan@bilna.com", "taufik.r@bilna.com");

$write = Mage::getSingleton('core/resource')->getConnection('core_read');
 
$sql = "(SELECT categorySub.VALUE AS Category,FORMAT(sub3.mbd, 1) AS 'Margin before Disc (%)',FORMAT(sub3.mard, 1) AS 'Margin after Disc (%)',COUNT( DISTINCT  sfoi.order_id) AS 'Total Number of Orders',sub1.toto AS 'Total Number of Orders (excluding cancelled)',FORMAT((SUM(sfoi.row_total)), 0) AS 'Revenue before Disc',FORMAT((SUM(sfoi.row_total) - SUM(IF (sfo.discount_amount < 0,sfoi.discount_amount,0))),0) AS 'Revenue after Disc',FORMAT(sub1.totrevb / sub1.toto, 0) AS 'Average Basket Size',FORMAT(sub1.totrevb, 0) AS 'Revenue before Disc (excluding Cancelled)',FORMAT(sub1.totreva, 0) AS 'Revenue after Disc (excluding Cancelled)',FORMAT((sub1.totreva / 1.1), 0) AS 'Revenue Nett after tax',FORMAT(sub1.totreva / 14000, 0) AS 'USD (14.0K)' FROM sales_flat_order_item sfoi LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN (SELECT   DISTINCT cpe.entity_id,eaov.VALUE VALUE  FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei.VALUE= eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND  eao.attribute_id = 292 WHERE cpe.sku IS NOT  NULL  AND  cpei.VALUE IS NOT  NULL ) categorySub ON sfoi.product_id = categorySub.entity_id LEFT JOIN (SELECT categorySub.VALUE AS category,COUNT( DISTINCT  sfoi.order_id) AS toto,ROUND((SUM(sfoi.row_total)), 0) AS totrevb,ROUND((SUM(sfoi.row_total) - SUM(IF (sfo.discount_amount < 0,sfoi.discount_amount,0))),0) AS totreva FROM sales_flat_order_item sfoi LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN (SELECT   DISTINCT cpe.entity_id,eaov.VALUE VALUE  FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei.VALUE= eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND  eao.attribute_id = 292 WHERE cpe.sku IS NOT  NULL ) categorySub ON sfoi.product_id = categorySub.entity_id WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete')GROUP  BY categorySub.VALUE) sub1 ON categorySub.VALUE= sub1.category LEFT JOIN ((SELECT categorySub.VALUE AS category,100 * (1 - subb1.cost / SUM(sfoi.row_total)) AS mbd,100 * (SUM(sfoi.row_total -IF (sfo.discount_amount < 0,sfoi.discount_amount,0)) - subb1.cost) / SUM(sfoi.row_total -IF (sfo.discount_amount < 0,sfoi.discount_amount,0)) AS mard,100 * (SUM(sfoi.row_total - sfoi.discount_amount) - subb1.cost) / SUM(sfoi.row_total - sfoi.discount_amount) AS mad FROM sales_flat_order_item sfoi LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN (SELECT   DISTINCT cpe.entity_id,eaov.VALUE VALUE  FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei.VALUE= eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND  eao.attribute_id = 292 WHERE cpe.sku IS NOT  NULL ) categorySub ON sfoi.product_id = categorySub.entity_id LEFT JOIN (SELECT categorySub.VALUE AS category,SUM(sfoi.qty_ordered * cped.VALUE) AS cost FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND  cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id INNER JOIN (SELECT   DISTINCT cpe.entity_id,eaov.VALUE VALUE FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int AS cpei ON cpe.entity_id = cpei.entity_id INNER JOIN eav_attribute_option AS eao ON cpei.VALUE= eao.option_id INNER JOIN eav_attribute_option_value AS eaov ON eao.option_id = eaov.option_id AND  eao.attribute_id = 292 WHERE cpe.sku IS NOT  NULL ) categorySub ON sfoi.product_id = categorySub.entity_id WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete') AND  sfoi.product_type = 'simple'GROUP  BY category) subb1 ON categorySub.VALUE= subb1.category WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete') AND  sfoi.item_id NOT IN (SELECT sfoi.item_id FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND  cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete') AND  sfoi.product_type = 'simple' AND  sfoi.qty_ordered * cped.VALUE IS  NULL )GROUP  BY categorySub.VALUE)) sub3 ON categorySub.VALUE= sub3.category WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR))GROUP  BY categorySub.VALUE ) UNION(SELECT 'Grand Total',FORMAT(sub4.mbd, 1),FORMAT(sub4.mard, 1),COUNT(sfo.entity_id) AS 'Total number of orders',sub2.toto,FORMAT((SUM(sfo.grand_total) - SUM(sfo.discount_amount) - SUM(sfo.shipping_amount)),0) AS 'Revenue before discount',FORMAT((SUM(sfo.grand_total) - SUM(IF (sfo.discount_amount < 0,sfo.discount_amount,0)) - SUM(sfo.shipping_amount)),0) AS 'Revenue after discount',FORMAT(sub2.totrevb / sub2.toto, 0) AS 'Average basket size',FORMAT(sub2.totrevb, 0),FORMAT(sub2.totreva, 0),FORMAT((sub2.totreva / 1.1), 0),FORMAT(sub2.totreva / 14000, 0) FROM sales_flat_order sfo LEFT JOIN (SELECT COUNT(sfo.entity_id) AS toto,ROUND((SUM(sfo.grand_total) - SUM(sfo.discount_amount) - SUM(sfo.shipping_amount)),0) AS totrevb,ROUND((SUM(sfo.grand_total) - SUM(IF (sfo.discount_amount < 0,sfo.discount_amount,0)) - SUM(sfo.shipping_amount)),0) AS totreva FROM sales_flat_order sfo WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete')) sub2 ON TRUE LEFT JOIN (SELECT 100 * (1 - subb1.cost / SUM(sfoi.row_total)) AS mbd,100 * (SUM(sfoi.row_total -IF (sfo.discount_amount < 0,sfoi.discount_amount,0)) - subb1.cost) / SUM(sfoi.row_total -IF (sfo.discount_amount < 0,sfoi.discount_amount,0)) AS mard,100 * (SUM(sfoi.row_total -IF (sfo.discount_amount < 0,sfoi.discount_amount,0)) - subb1.cost) / SUM(sfoi.row_total -IF (sfo.discount_amount < 0,sfoi.discount_amount,0)) - 100 * (SUM(sfoi.row_total - sfoi.discount_amount) - subb1.cost) / SUM(sfoi.row_total - sfoi.discount_amount) AS mad FROM sales_flat_order_item sfoi LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id LEFT JOIN (SELECT SUM(sfoi.qty_ordered * cped.VALUE) AS cost FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND  cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete') AND  sfoi.product_type = 'simple') subb1 ON TRUE WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete') AND  sfoi.item_id NOT IN (SELECT sfoi.item_id FROM sales_flat_order_item sfoi LEFT JOIN catalog_product_entity cpe ON sfoi.sku = cpe.sku LEFT JOIN catalog_product_entity_decimal cped ON cpe.entity_id = cped.entity_id AND  cped.attribute_id = 79 LEFT JOIN sales_flat_order sfo ON sfoi.order_id = sfo.entity_id WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)) AND  sfo.state IN ('new','processing','complete') AND  sfoi.product_type = 'simple' AND  sfoi.qty_ordered * cped.VALUE IS  NULL )) sub4 ON TRUE WHERE DATE(ADDDATE(sfo.created_at,INTERVAL 7 HOUR)) = DATE(ADDDATE(NOW(), INTERVAL 2 HOUR)))";
 
$readresult=$write->query($sql);
$message = "
	<table border='1'>
		<tr>
			<td>Category</td>
			<td>Margin before Disc (%)</td>
			<td>Margin after Disc (%)</td>
			<td>Total Number of Orders</td>
			<td>Total Number of Orders (excluding cancelled)</td>
			<td>Revenue before Disc</td>
			<td>Revenue after Disc</td>
			<td>Average Basket Size</td>
			<td>Revenue before Disc (excluding Cancelled)</td>
			<td>Revenue after Disc (excluding Cancelled)</td>
			<td>Revenue Nett after tax</td>
			<td>USD (14.0K)</td>
		</tr>";
while ($row = $readresult->fetch() ) {
	$message .= "
		<tr>
			<td>".$row['Category']."</td>
			<td>".$row['Margin before Disc (%)']."</td>
			<td>".$row['Margin after Disc (%)']."</td>
			<td>".$row['Total Number of Orders']."</td>
			<td>".$row['Total Number of Orders (excluding cancelled)']."</td>
			<td>".$row['Revenue before Disc']."</td>
			<td>".$row['Revenue after Disc']."</td>
			<td>".$row['Average Basket Size']."</td>
			<td>".$row['Revenue before Disc (excluding Cancelled)']."</td>
			<td>".$row['Revenue after Disc (excluding Cancelled)']."</td>
			<td>".$row['Revenue Nett after tax']."</td>
			<td>".$row['USD (14.0K)']."</td>
		</tr>";
}
$message .= "</table>";
try
{
    $mail = new Zend_Mail();
	$mail->setBodyHtml($message);
	$mail->setFrom($sendFromEmailAddress, $sendFromName);
	$mail->addTo($sendToEmailAddress, '');
	$mail->setSubject('Category Sales Report From '.date("d M Y 00:00:00").' to '.date("d M Y H:i:s", strtotime('+7 hours')));
	$mail->send();
}
catch(Exception $e)
{
    echo $e; 
}