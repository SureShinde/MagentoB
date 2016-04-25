<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProductDetail
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/GenerateProduct.php';

class Bilna_Worker_Solr_GenerateProductDetail extends Bilna_Worker_Solr_GenerateProduct {
    protected $_tubeAllow = 'solr_catalog_product_detail';
    protected $_type = 'detail';

    protected function _processProductData($product) {
        return $this->_productApi->workerGetProductDetail($product);
    }
    
    protected function _setQuery($product) {
        $sql = "INSERT INTO `{$this->_productDbTableSet}` (`entity_id`, `detailed_info`) VALUES (:entity_id, :detail) ON DUPLICATE KEY UPDATE `detailed_info` = :detail ";
        $binds = array (
            'entity_id' => $product['id'],
            'detail' => $product['data'],
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProductDetail();
$worker->run();
