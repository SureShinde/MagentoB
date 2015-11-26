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
    const QUEUE_TASK_CATALOG_PRODUCT_DETAIL = 'SOLR_CATALOG_PRODUCT_DETAIL';
    const QUEUE_TASK_CATALOG_PRODUCT_SALES = 'SOLR_CATALOG_PRODUCT_SALES';
    
    protected $_modelProduct;
    protected $_helperProduct;
    protected $_apiProduct;

    protected $_logPath = 'Bilna_Worker_Solr_GenerateProduct';

    public function run() {
        $this->_start();
        
        /**
         * @mode collect|process
         * @type images|config|bundle|detail
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
            if ($type == 'sales') {
                $productId = $row['product_id'];
                $total = $row['total'];
                $queueData = array (
                    'product_id' => $productId,
                    'total' => $total,
                );
                $this->_queueSvc->publish($this->_queueTask, json_encode($queueData));
            }
            else {
                $productId = $row['entity_id'];
                $this->_queueSvc->publish($this->_queueTask, $productId);
            }
            
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
        elseif ($type == 'detail') {
            $callback = function($msg) {
                $productId = $msg->body;
                $this->_logProgress("#{$productId} Received from queue");
                $product = $this->_helperProduct->getProduct($productId, self::DEFAULT_STORE_ID);
                $productDetail = $this->_apiProduct->workerGetProductDetail($product);
                $this->setQueryProductDetail($productId, $productDetail);
                $this->_logProgress("#{$productId} Inserted to database");
            };
        }
        elseif ($type == 'sales') {
            $callback = function($msg) {
                $data = json_decode($msg->body, true);
                $productId = $data['product_id'];
                $total = $data['total'];
                $this->_logProgress("#{$productId} Received from queue");
                
                if ($this->setQueryProductSales($productId, $total)) {
                    $this->_logProgress("#{$productId} Updated to database");
                }
                else {
                    $this->_logProgress("#{$productId} failed to updated");
                }
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
        elseif ($type == 'detail') {
            $this->_queueTask = self::QUEUE_TASK_CATALOG_PRODUCT_DETAIL;
        }
        elseif ($type == 'sales') {
            $this->_queueTask = self::QUEUE_TASK_CATALOG_PRODUCT_SALES;
        }
        else {
            $this->_critical('Invalid type.');
        }
    }

    private function getQueryProducts($type) {
        if ($type == 'sales') {
            $sql = "SELECT `sales_item`.`product_id`, SUM(`sales_item`.`row_total`) AS `total` ";
            $sql .= "FROM `sales_flat_order_item` AS `sales_item` ";
            $sql .= "INNER JOIN `catalog_product_flat_1` AS `prod` ON `sales_item`.`product_id` = `prod`.`entity_id` "; 
            $sql .= "WHERE `sales_item`.`updated_at` BETWEEN (NOW()-INTERVAL {$this->getSalesInterval()} DAY) AND NOW() ";
            $sql .= "GROUP BY `sales_item`.`product_id` ";
        }
        else {
            $sql = "SELECT `entity_id` FROM `catalog_product_flat_1` ";

            if ($type == 'config') {
                $sql .= "WHERE `type_id` = 'configurable' ";
            }
            elseif ($type == 'bundle') {
                $sql .= "WHERE `type_id` = 'bundle' ";
            }

            $sql .= "ORDER BY `entity_id` ";
        }
        
        return $this->_dbRead->query($sql);
    }
    
    private function getSalesInterval() {
        //- default interval 30 days
        $interval = ($this->getArg('interval')) ? (int) $this->getArg('interval') : 30;
        
        return $interval;
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
    
    private function setQueryProductDetail($entityId, $detail) {
        $sql = "INSERT INTO `api_product_flat_1` (`entity_id`, `detailed_info`) VALUES (:entity_id, :detail) ON DUPLICATE KEY UPDATE `detailed_info` = :detail ";
        $binds = array (
            'entity_id' => $entityId,
            'detail' => json_encode($detail),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductSales($entityId, $total) {
        $sql = "UPDATE `api_product_flat_1` SET `sales` = :total WHERE `entity_id` = :entity_id LIMIT 1 ";
        $binds = array (
            'total' => $total,
            'entity_id' => $entityId,
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProduct();
$worker->run();
