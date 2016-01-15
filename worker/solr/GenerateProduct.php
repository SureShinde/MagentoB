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

    const PRODUCT_IMAGE_SIZE_THUMBNAIL = 60;
    const PRODUCT_IMAGE_SIZE_HORIZONTAL = 80;
    const PRODUCT_IMAGE_SIZE_VERTICAL = 150;
    const PRODUCT_IMAGE_SIZE_DETAIL = 360;
    
    protected $_modelProduct;
    protected $_helperProduct;
    protected $_apiProduct;
    protected $_apiProductTable = 'api_product_flat_1';
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
            elseif ($mode == 'truncate') {
                if (!$this->truncate()) {
                    $this->_critical('Truncate table failed.');
                }
                
                $this->_logProgress('Truncate table success.');
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
                
        while ($product = $queryProducts->fetch()) {
            if ($type == 'images') {
                $productId = $product['entity_id'];
                $productImagesAttribute = $this->getProductImagesAttribute($productId);
                $productImages = $this->getProductImages($productId, $productImagesAttribute);
                
                $queueData = array (
                    'product_id' => $productId,
                    'product_images' => $productImages,
                );
                $this->_queueSvc->publish($this->_queueTask, json_encode($queueData));
                unset ($queueData);
            }
            elseif ($type == 'sales') {
                $productId = $product['product_id'];
                $total = $product['total'];
                
                $queueData = array (
                    'product_id' => $productId,
                    'total' => $total,
                );
                $this->_queueSvc->publish($this->_queueTask, json_encode($queueData));
            }
            else {
                $productId = $product['entity_id'];
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
                $product = json_decode($msg->body, true);
                $productId = $product['product_id'];
                $productImages = $product['product_images'];
                $this->_logProgress("#{$productId} Received from queue");
                
                $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog/product";
                $imageHelper = Mage::helper('bilna_rest/product_image');
                $productImagesNew = array ();
                
                if ($productImages) {
                    foreach ($productImages as $productImage) {
                        $url = $productImage['url'];
                        $productImageUrl = $imageUrl . $url;
                        
                        //- image thumbnail
                        $imageHelper->init($url);
                        $imageHelper->resize(self::PRODUCT_IMAGE_SIZE_THUMBNAIL);
                        $productImageThumbnail = $imageHelper->__toString();
                        
                        //- image horizontal
                        $imageHelper->init($url);
                        $imageHelper->resize(self::PRODUCT_IMAGE_SIZE_HORIZONTAL);
                        $productImageHorizontal = $imageHelper->__toString();
                        
                        //- image vertical
                        $imageHelper->init($url);
                        $imageHelper->resize(self::PRODUCT_IMAGE_SIZE_VERTICAL);
                        $productImageVertical = $imageHelper->__toString();
                        
                        //- image detail
                        $imageHelper->init($url);
                        $imageHelper->resize(self::PRODUCT_IMAGE_SIZE_DETAIL);
                        $productImageDetail = $imageHelper->__toString();
                        
                        $productImagesNew[] = array (
                            'id' => $productImage['id'],
                            'label' => $productImage['label'],
                            'position' => $productImage['position'],
                            'exclude' => $productImage['exclude'],
                            'url' => $productImageUrl,
                            'types' => $productImage['types'],
                            'resize' => array (
                                'base' => $productImageUrl,
                                'thumbnail' => $productImageThumbnail, //- 72px
                                'horizontal' => $productImageHorizontal, //- 110px
                                'vertical' => $productImageVertical, //- 151px
                                'detail' => $productImageDetail, //- 225px
                            ),
                        );
                    }
                }
                
                $this->setQueryProductImages($productId, $productImagesNew);
                $this->_logProgress("#{$productId} Inserted to database");
            };
        }
        elseif ($type == 'config') {
            $callback = function($msg) {
                $productId = $msg->body;
                $this->_logProgress("#{$productId} Received from queue");
                $product = $this->_helperProduct->getProduct($productId, self::DEFAULT_STORE_ID);
                $productConfig = $this->_apiProduct->workerGetProductConfig($product);
                $this->setQueryProductConfig($productId, $productConfig);
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
    
    private function truncate() {
        $sql = "TRUNCATE TABLE {$this->_apiProductTable} ";
        
        return $this->_dbRead->query($sql);
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

    private function setQueryProductImages($productId, $images) {
        $sql = "INSERT INTO `{$this->_apiProductTable}` (`entity_id`, `images`) VALUES (:entity_id, :images) ON DUPLICATE KEY UPDATE `images` = :images ";
        $binds = array (
            'entity_id' => $productId,
            'images' => json_encode($images),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductConfig($productId, $config) {
        $sql = "INSERT INTO `{$this->_apiProductTable}` (`entity_id`, `attribute_config`) VALUES (:entity_id, :config) ON DUPLICATE KEY UPDATE `attribute_config` = :config ";
        $binds = array (
            'entity_id' => $productId,
            'config' => json_encode($config),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductBundle($productId, $bundle) {
        $sql = "INSERT INTO `{$this->_apiProductTable}` (`entity_id`, `attribute_bundle`) VALUES (:entity_id, :bundle) ON DUPLICATE KEY UPDATE `attribute_bundle` = :bundle ";
        $binds = array (
            'entity_id' => $productId,
            'bundle' => json_encode($bundle),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductDetail($productId, $detail) {
        $sql = "INSERT INTO `{$this->_apiProductTable}` (`entity_id`, `detailed_info`) VALUES (:entity_id, :detail) ON DUPLICATE KEY UPDATE `detailed_info` = :detail ";
        $binds = array (
            'entity_id' => $productId,
            'detail' => json_encode($detail),
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
    
    private function setQueryProductSales($productId, $total) {
        $sql = "UPDATE `{$this->_apiProductTable}` SET `sales_price` = :total WHERE `entity_id` = :entity_id LIMIT 1 ";
        $binds = array (
            'total' => $total,
            'entity_id' => $productId,
        );
        
        return $this->_dbWrite->query($sql, $binds);
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
            $sql = "SELECT `entity_id` ";
            $sql .= "FROM `catalog_product_flat_1` ";

            if ($type == 'config') {
                $sql .= "WHERE `type_id` = 'configurable' ";
            }
            elseif ($type == 'bundle') {
                $sql .= "WHERE `type_id` = 'bundle' ";
            }
            else {
                $sql .= "WHERE 1 = 1 ";
            }
            
            if ($this->getArg('id')) {
                $sql .= "AND `entity_id` = {$this->getArg('id')} ";
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
    
    private function getProductImagesAttribute($productId) {
        $sql = "SELECT `attribute_id`, `value` ";
        $sql .= "FROM `catalog_product_entity_varchar` ";
        $sql .= "WHERE `attribute_id` IN (85,86,87) AND `store_id` IN ('0','1') AND `entity_id` = {$productId} ";
        $query = $this->_dbRead->query($sql);
        $result = array ();
        
        while ($attributeImage = $query->fetch()) {
            switch ($attributeImage['attribute_id']) {
                case '85':
                    $type = 'image';
                    break;
                case '86':
                    $type = 'small_image';
                    break;
                case '87':
                    $type = 'thumbnail';
                    break;
            }
            
            $result[$type] = $attributeImage['value'];
        }
        
        return $result;
    }

    private function getProductImages($productId, $productImagesAttribute) {
        $sql = "SELECT `cpemg`.`value_id` AS `id`, `cpemgv`.`label`, `cpemgv`.`position`, `cpemgv`.`disabled` AS `exclude`, `cpemg`.`value` AS `url` ";
        $sql .= "FROM `catalog_product_entity_media_gallery` AS `cpemg` ";
        $sql .= "INNER JOIN `catalog_product_entity_media_gallery_value` AS `cpemgv` ON `cpemg`.`value_id` = `cpemgv`.`value_id` ";
        $sql .= "WHERE `cpemgv`.`disabled` = 0 AND `cpemgv`.`store_id` IN ('0','1') AND `cpemg`.`entity_id` = {$productId} ";
        $query = $this->_dbRead->query($sql);
        $result = array ();
        
        while ($productImage = $query->fetch()) {
            $types = array ();
            
            //- get default image
            if ($productImagesAttribute['image'] == $productImage['url']) {
                $types[] = 'image';
            }
            
            //- get default small_image
            if ($productImagesAttribute['small_image'] == $productImage['url']) {
                $types[] = 'small_image';
            }
            
            //- get default thumbnail
            if ($productImagesAttribute['thumbnail'] == $productImage['url']) {
                $types[] = 'thumbnail';
            }
            
            $result[] = array (
                'id' => $productImage['id'],
                'label' => $productImage['label'],
                'position' => $productImage['position'],
                'exclude' => $productImage['exclude'],
                'url' => $productImage['url'],
                'types' => $types,
            );
        }
        
        return $result;
    }
}

$worker = new Bilna_Worker_Solr_GenerateProduct();
$worker->run();
