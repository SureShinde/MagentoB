<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProductBundle
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/GenerateProduct.php';

class Bilna_Worker_Solr_GenerateProductBundle extends Bilna_Worker_Solr_GenerateProduct {
    protected $_tubeAllow = 'solr_catalog_product_bundle';
    protected $_type = 'bundle';
    
    protected function _processProductData($product) {
        return $this->_productApi->workerGetProductBundle($product);
    }
    
    protected function _setQuery($product) {
        $sql = "INSERT INTO `{$this->_productDbTableSet}` (`entity_id`, `attribute_bundle`) VALUES (:entity_id, :bundle) ON DUPLICATE KEY UPDATE `attribute_bundle` = :bundle ";
        $binds = array (
            'entity_id' => $product['id'],
            'bundle' => $product['data'],
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProductBundle();
$worker->run();
