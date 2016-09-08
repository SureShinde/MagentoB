<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProductSales
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/GenerateProduct.php';

class Bilna_Worker_Solr_GenerateProductSales extends Bilna_Worker_Solr_GenerateProduct {
    protected $_tubeAllow = 'solr_catalog_product_sales';
    protected $_type = 'sales';
    
    protected function _collect() {
        $queryProducts = $this->_getQuery();
                
        while ($product = $queryProducts->fetch()) {
            $productId = $product['entity_id'];
            $productQueue = array (
                'entity_id' => $productId,
                'total' => $product['total'],
            );
            
            if ($this->_queuePut($productQueue)) {
                $this->_logProgress("{$productId} store to queue.");
            }
        }
    }
    
    protected function _getProduct($data) {
        $productId = $data['entity_id'];
        $productTotal = $data['total'];
        
        return array (
            'id' => $productId,
            'total' => $productTotal,
        );
    }
    
    protected function _processProductData($product) {
        return $product;
    }
    
    protected function _setQuery($product) {
        $sql = "INSERT INTO `{$this->_productDbTableSet}` (`entity_id`, `sales_price`) VALUES (:entity_id, :total) ON DUPLICATE KEY UPDATE `sales_price` = :total ";
        $binds = array (
            'entity_id' => $product['id'],
            'total' => $product['total'],
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProductSales();
$worker->run();
