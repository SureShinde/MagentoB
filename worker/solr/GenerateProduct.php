<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProduct
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Worker_Solr_GenerateProduct extends Bilna_Worker_Abstract {
    const QUEUE_TASK_CATALOG_PRODUCT_IMAGES = 'SOLR_CATALOG_PRODUCT_IMAGES';
    const QUEUE_TASK_CATALOG_PRODUCT_CONFIG = 'SOLR_CATALOG_PRODUCT_CONFIG';
    const QUEUE_TASK_CATALOG_PRODUCT_BUNDLE = 'SOLR_CATALOG_PRODUCT_BUNDLE';
    //const QUEUE_TASK_API_PRODUCT = 'API_PRODUCT_FLAT_1';
    
    protected $_modelProduct;
    protected $_helperProduct;
    protected $_apiProduct;

    protected $_logPath = 'Bilna_Worker_Solr_GenerateProduct';

    public function run() {
        $this->_start();
        
        /**
         * @mode collect|images|product_config|product_bundle
         */
        if ($mode = $this->getArg('mode')) {
            if ($mode == 'collect') {
                if ($type = $this->getArg('type')) {
                    $this->collect($type);
                }
                else {
                    $this->_critical('Please input type.');
                }
            }
            elseif ($mode == 'process') {
                if ($type = $this->getArg('type')) {
                    $this->process($type);
                }
                else {
                    $this->_critical('Please input type.');
                }
            }
            else {
                $this->_critical('Invalid mode.');
            }
        }
        else {
            $this->_critical('Please input mode.');
        }
        
        $this->_stop();
    }

    private function collect($type) {
        $this->setQueueTask($type);
        $this->_queueSvc->connect($this->_queueTask);
        $queryProducts = $this->getQueryProducts($type);
                
        while ($row = $queryProducts->fetch()) {
            $productId = $row['entity_id'];
            $this->_queueSvc->publish($this->_queueTask, $productId);
            $this->_logProgress('#' . $productId . ' store to queue');
        }
    }
    
    private function process($type) {
        $this->setQueueTask($type);
        $this->_helperProduct = Mage::helper('catalog/product');
        $this->_apiProduct = Mage::getModel('bilna_rest/api2_product_rest_admin_v1');
        $this->_queueSvc->connect($this->_queueTask);
        
        if ($type == 'images') {
            $callback = function($msg) {
                $productId = $msg->body;
                $this->_logProgress("#{$productId} Received from queue");
                $product = $this->_helperProduct->getProduct($productId, self::DEFAULT_STORE_ID);
                $productImages = $this->_apiProduct->workerGetProductImages($product);
                $this->setQueryProductImages($productId, $productImages);
                $this->_logProgress("#{$productId} Inserted to database");
            };
        }
        elseif ($type == 'config') {
            $callback = function($msg) {
                $productId = $msg->body;
                $this->_logProgress("#{$productId} Received from queue");
                $product = $this->_helperProduct->getProduct($productId, self::DEFAULT_STORE_ID);
                $productImages = $this->_apiProduct->workerGetProductImages($product);
                $this->setQueryProductImages($productId, $productImages);
                $this->_logProgress("#{$productId} Inserted to database");
            };
        }
        elseif ($type == 'bundle') {
            $callback = function($msg) {
                $productId = $msg->body;
                $this->_logProgress("#{$productId} Received from queue");
                $product = $this->_helperProduct->getProduct($productId, self::DEFAULT_STORE_ID);
                $productBundle = $this->_apiProduct->workerGetProductBundle($product);
                $this->setQueryProductBundle($productId, $productBundle);
                $this->_logProgress("#{$productId} Inserted to database");
            };
        }
        else {
            $this->_critical('Invalid type.');
        }
        
        $this->_queueSvc->channel->basic_consume($this->_queueTask, '', false, true, false, false, $callback);
        
        while (count($this->_queueSvc->channel->callbacks)) {
            $this->_queueSvc->channel->wait();
        }
    }
    
    private function setQueueTask($type) {
        if ($type == 'images') {
            $this->_queueTask = self::QUEUE_TASK_CATALOG_PRODUCT_IMAGES;
        }
        elseif ($type == 'config') {
            $this->_queueTask = self::QUEUE_TASK_CATALOG_PRODUCT_CONFIG;
        }
        elseif ($type == 'bundle') {
            $this->_queueTask = self::QUEUE_TASK_CATALOG_PRODUCT_BUNDLE;
        }
        else {
            $this->_critical('Invalid type.');
        }
    }

    private function getQueryProducts($type) {
        $sql = "SELECT `entity_id` FROM `catalog_product_flat_1` ";
        
        if ($type == 'config') {
            $sql .= "WHERE `type_id` = 'configurable' ";
        }
        elseif ($type == 'bundle') {
            $sql .= "WHERE `type_id` = 'bundle' ";
        }
        
        $sql .= "ORDER BY `entity_id` ";
        $query = $this->_dbRead->query($sql);
        
        return $query;
    }
    
    private function setQueryProductImages($entityId, $images) {
        $sql = "INSERT INTO `api_product_flat_1` (`entity_id`, `images`) VALUES (:entity_id, :images) ON DUPLICATE KEY UPDATE `images` = :images ";
        $binds = array (
            'entity_id' => $entityId,
            'images' => json_encode($images),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductConfig($entityId, $config) {
        $sql = "INSERT INTO `api_product_flat_1` (`entity_id`, `attribute_config`) VALUES (:entity_id, :config) ON DUPLICATE KEY UPDATE `attribute_bundle` = :config ";
        $binds = array (
            'entity_id' => $entityId,
            'config' => json_encode($config),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductBundle($entityId, $bundle) {
        $sql = "INSERT INTO `api_product_flat_1` (`entity_id`, `attribute_bundle`) VALUES (:entity_id, :bundle) ON DUPLICATE KEY UPDATE `attribute_bundle` = :bundle ";
        $binds = array (
            'entity_id' => $entityId,
            'bundle' => json_encode($bundle),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProduct();
$worker->run();
