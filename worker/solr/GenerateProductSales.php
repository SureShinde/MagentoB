<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProductSales
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/GenerateProduct.php';

class Bilna_Worker_Solr_GenerateProductSales extends Bilna_Worker_Solr_GenerateProduct
{
    protected $_tubeAllow = 'solr_catalog_product_sales';
    protected $_type = 'sales';

    protected function _collect()
    {
        $queryProducts = $this->_getQuery();

        while ($product = $queryProducts->fetch()) {
            $productId = $product['entity_id'];
            $productQueue = [
                'entity_id' => $productId,
                'total' => $product['total'],
                'in_stock' => $product['in_stock'],
            ];

            if ($this->_queuePut($productQueue)) {
                $this->_logProgress("{$productId} store to queue.");
            }
        }
    }

    protected function _getProduct($data)
    {
        return [
            'id' => $data['entity_id'],
            'total' => $data['total'],
            'in_stock' => $data['in_stock'],
        ];
    }

    protected function _processProductData($product)
    {
        return $product;
    }

    protected function _setQuery($product)
    {
        $sql = "INSERT INTO `{$this->_productDbTableSet}` (`entity_id`, `sales_price`, `in_stock`) VALUES (:entity_id, :total, :in_stock) ON DUPLICATE KEY UPDATE `sales_price` = :total, `in_stock` = :in_stock ";
        $binds = [
            'entity_id' => $product['id'],
            'total' => $product['total'],
            'in_stock' => $product['in_stock'] > 0 ? 1 : 0
        ];

        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProductSales();
$worker->run();
