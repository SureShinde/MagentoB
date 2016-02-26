<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProductConfig
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/GenerateProduct.php';

class Bilna_Worker_Solr_GenerateProductConfig extends Bilna_Worker_Solr_GenerateProduct {
    protected $_tubeAllow = 'solr_catalog_product_config';
    protected $_type = 'config';
    
    protected function _processProductData($product) {
        return $this->_productApi->workerGetProductConfig($product);
    }
    
    protected function _setQuery($product) {
        $sql = "INSERT INTO `{$this->_productDbTableSet}` (`entity_id`, `attribute_config`) VALUES (:entity_id, :config) ON DUPLICATE KEY UPDATE `attribute_config` = :config ";
        $binds = array (
            'entity_id' => $product['id'],
            'config' => $product['data'],
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProductConfig();
$worker->run();
