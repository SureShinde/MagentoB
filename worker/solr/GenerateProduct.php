<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProduct
 *
 * @author Bilna Development Team <development@bilna.com>
 */

ini_set('memory_limit', '2046M');

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Worker_Solr_GenerateProduct extends Bilna_Worker_Abstract {
    protected $_productHelper;
    protected $_productApi;
    
    protected $_tubeIgnore = 'default';
    protected $_tubeAllow = 'solr_catalog_product_detail';
    protected $_productDbTableGet = 'catalog_product_flat_1';
    protected $_productDbTableSet = 'api_product_flat_1';
    protected $_salesOrderItemTable = 'sales_flat_order_item';
    protected $_mode = 'process';
    protected $_type = 'detail';

    public function run() {
        $this->_start();
        $this->_getMode();
        $this->_getType();
        
        if ($this->_mode == 'collect') {
            $this->_collect();
        }
        elseif ($this->_mode == 'process') {
            $this->_process();
        }
        else {
            $this->_critical('Invalid mode.');
        }
        
        $this->_stop();
    }
    
    protected function _start() {
        $this->_logProgress('START');
        $this->_dbConnect();
        $this->_queueConnect();
        $this->_productModule();
    }
    
    protected function _stop() {
        $this->_logProgress('STOP');
    }
    
    protected function _getMode() {
        if ($mode = $this->getArg('mode')) {
            $this->_mode = $mode;
        }
        
        return $this->_mode;
    }
    
    protected function _getType() {
        return $this->_type;
    }

    protected function _collect() {
        $queryProducts = $this->_getQuery();
                
        while ($product = $queryProducts->fetch()) {
            $productId = $product['entity_id'];
            
            if ($this->_queuePut($productId)) {
                $this->_logProgress("{$productId} store to queue.");
            }
        }
    }
    
    protected function _getQuery() {
        if ($this->_type == 'sales') {
            $sql = "SELECT `sales_item`.`product_id` AS `entity_id`, SUM(`sales_item`.`row_total`) AS `total` ";
            $sql .= "FROM `{$this->_salesOrderItemTable}` AS `sales_item` ";
            $sql .= "INNER JOIN `catalog_product_flat_1` AS `prod` ON `sales_item`.`product_id` = `prod`.`entity_id` "; 
            $sql .= "WHERE `sales_item`.`updated_at` BETWEEN (NOW()-INTERVAL {$this->_getProductSalesInterval()} DAY) AND NOW() ";
            $sql .= "GROUP BY `sales_item`.`product_id` ";
        }
        else {
            $sql = "SELECT `entity_id` ";
            $sql .= "FROM `{$this->_productDbTableGet}` ";

            if ($this->_type == 'config') {
                $sql .= "WHERE `type_id` = 'configurable' ";
            }
            elseif ($this->_type == 'bundle') {
                $sql .= "WHERE `type_id` = 'bundle' ";
            }
            else {
                $sql .= "WHERE 1 = 1 ";
            }
            
            if ($id = $this->getArg('id')) {
                $sql .= "AND `entity_id` = {$id} ";
            }
            
            $sql .= "ORDER BY `entity_id` ";
        }
        
        if ($limit = $this->getArg('limit')) {
            $sql .= "LIMIT {$limit} ";
        }
        
        return $this->_dbRead->query($sql);
    }
    
    protected function _getProductSalesInterval() {
        //- default interval 30 days
        $interval = ($this->getArg('interval')) ? (int) $this->getArg('interval') : 30;
        
        return $interval;
    }

    protected function _queuePut($data) {
        try {
            $this->_queueSvc->useTube($this->_getTube())->put($this->_prepareData($data));
            
            return true;
        }
        catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }
    }
    
    protected function _getTube() {
        return $this->_tubeAllow;
    }

    protected function _process() {
        try {
            $this->_queueSvc->watch($this->_getTube());
            $this->_queueSvc->ignore($this->_tubeIgnore);
            $x = 1;

            while ($job = $this->_queueSvc->reserve()) {
                $data = $this->_parseData($job->getData());
                $productId = $this->_getProductId($data);
                $this->_logProgress("#{$productId} Received from queue");
                $product = $this->_getProduct($data);
                
                if ($this->_setQuery($product)) {
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$productId} Insert to DB => success");
                }
                else {
                    $this->_queueSvc->bury($job);
                    $this->_logProgress("#{$productId} Insert to DB => failed");
                }
                
                unset ($data);
                unset ($product);
                
                if ($x == 1000) {
                    $this->_logProgress("Flush memory (5s)......");
                    $this->_flushMemory();
                    $x = 1;
                }
                
                $x++;
            }
        }
        catch (Exception $ex) {
            $this->_queueSvc->bury($job);
            $this->_critical($ex->getMessage());
        }
    }
    
    protected function _getProductId($data) {
        if (is_array($data) || is_object($data)) {
            return $data['entity_id'];
        }
        
        return $data;
    }

    protected function _getProduct($data) {
        $product = $this->_productHelper->getProduct($data, self::DEFAULT_STORE_ID);
        $productData = $this->_prepareData($this->_processProductData($product));
        
        return array (
            'id' => $product->getId(),
            'data' => $productData,
        );
    }

    protected function _processProductData($product) {
        return $this->_productApi->workerGetProductDetail($product);
    }

    protected function _productModule() {
        $this->_productHelper = Mage::helper('catalog/product');
        $this->_productApi = Mage::getModel('bilna_rest/api2_product_rest_admin_v1');
    }

    protected function _critical($message) {
        $this->_logProgress($message);
        exit(1);
    }
    
    protected function _flushMemory() {
        ob_start();
        ob_end_clean();
        flush();
        sleep(5);
        ob_end_flush();
    }
}
