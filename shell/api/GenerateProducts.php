<?php
/**
 * Description of GenerateProducts
 *
 * @project Logan
 * @author Bilna Development Team <development@bilna.com>
 */

ini_set('memory_limit', '-1');

require_once dirname(__FILE__) . '/../abstract.php';

class GenerateProducts extends Mage_Shell_Abstract {
    const DEFAULT_STORE_ID = 1;
    const DEFAULT_LIMIT = 100;
    const REDIS_PRODUCT_KEY = 'COLLECT_PRODUCT';

    protected $read = null;
    protected $write = null;
    protected $tblPrefix = 'api_product_flat_';
    
    protected $formatDate = 'd-M-Y H:i:s';
    
    protected $redisHelper;

    protected function init() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
        
        $this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->redisHelper = Mage::helper('bilna_rest/redis');
    }

    public function run() {
        $start = date($this->formatDate);
        $this->logProgress('<<-START->>');
        $this->init();
        
        if ($this->getArg('collect')) {
            $this->collectProductToRedis();
        }
        else {
            $productId = $this->getProductId();

            if (!$productId) {
                $this->critical('Product not found.');
            }

            $productApi = Mage::getModel('bilna_rest/api2_product_rest_admin_v1');
            $product = $productApi->retrieve($productId, self::DEFAULT_STORE_ID);

            if (!$product) {
                $this->critical("Couldn't retrieve product from API.");
            }

            if ($this->processQueryInsert($product)) {
                $this->logProgress("Insert Product #{$productId} success.");
            }
            else {
                $this->logProgress("Insert Product #{$productId} failed.");
            }
        }
        
        $stop = date($this->formatDate);
        $this->logProgress(sprintf("Start at %s and stop at %s", $start, $stop));
        $this->logProgress($this->getInterval($start, $stop));
        $this->logProgress('<<-STOP->>');
    }
    
    protected function getProductId() {
        return (int) $this->getProductIdFromQueue();
    }
    
    protected function getProductIdFromQueue() {
        //- soon
        $productIds = $this->redisHelper->getCacheAll(self::REDIS_PRODUCT_KEY);
        $productId = 0;
        
        if ($productIds) {
            foreach ($productIds as $k => $v) {
                $productId = $k;
                $this->redisHelper->removeCache(self::REDIS_PRODUCT_KEY, $k);
                break;
            }
        }
        
        return $productId;
    }
    
    protected function processQueryInsert($product) {
        $sql = "INSERT INTO " . $this->tblPrefix . self::DEFAULT_STORE_ID . " (`entity_id`, `detailed_info`, `group_price`, `tier_price`, `attribute_config`, `attribute_bundle`, `review`, `images`) ";
        $sql .= "VALUES (:entity_id, :detailed_info, :group_price, :tier_price, :attribute_config, :attribute_bundle, :review, :images)";
        $binds = $this->getBindsQueryInsert($product);
        
        try {
            $this->write->beginTransaction();
            $this->write->query($sql, $binds);
            $this->write->commit();
            
            return true;
        }
        catch (Exception $ex) {
            $this->logProgress($ex->getMessage());
            $this->write->rollback();
            
            return false;
        }
    }
    
    protected function getBindsQueryInsert($product) {
        $entityId = $product['entity_id'] ? $product['entity_id'] : '';
        $detailedInfo = $product['detailed_info'] ? json_encode($product['detailed_info']) : '';
        $groupPrice = $product['group_price'] ? json_encode($product['group_price']) : '';
        $tierPrice = $product['tier_price'] ? json_encode($product['tier_price']) : '';
        $attributeConfig = $product['attribute_config'] ? json_encode($product['attribute_config']) : '';
        $attributeBundle = $product['attribute_bundle'] ? json_encode($product['attribute_bundle']) : '';
        $review = $product['review'] ? json_encode($product['review']) : '';
        $images = $product['images'] ? json_encode($product['images']) : '';
        
        $binds = array (
            'entity_id' => $entityId,
            'detailed_info' => $detailedInfo,
            'group_price' => $groupPrice,
            'tier_price' => $tierPrice,
            'attribute_config' => $attributeConfig,
            'attribute_bundle' => $attributeBundle,
            'review' => $review,
            'images' => $images,
        );
        
        return $binds;
    }
    
    protected function getInterval($start, $stop) {
        $diff = abs(strtotime($stop) - strtotime($start));
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
        $minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
        $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60));
        
        return sprintf("%d years, %d months, %d days, %d hours, %d minutes, %d seconds", $years, $months, $days, $hours, $minutes, $seconds);
    }
    
    protected function critical($message) {
        $this->logProgress($message);
        $this->logProgress('STOP Process.');
        exit(1);
    }

    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
    
    protected function collectProductToRedis() {
        $sql = "SELECT `entity_id` FROM `catalog_product_flat_1` ";
        $query = $this->read->query($sql);
        
        while ($product = $query->fetch()) {
            $this->redisHelper->saveCache(self::REDIS_PRODUCT_KEY, $product['entity_id'], 'ready');
        }
    }
}

$shell = new GenerateProducts();
$shell->run();
