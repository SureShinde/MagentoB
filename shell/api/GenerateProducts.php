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
        $this->logProgress('START');
        $this->init();
        $products = $this->getProducts();
        
        $success = 0;
        $failed = 0;
        $x = 1;
        
        while ($row = $products->fetch()) {
            $productId = $row['entity_id'];
            $productApi = Mage::getModel('bilna_rest/api2_product_rest_admin_v1');
            $product = $productApi->retrieve($productId, self::DEFAULT_STORE_ID);
            
            if ($this->processRedisInsert($product)) {
            //if ($this->processQueryInsert($product)) {
                $this->logProgress(sprintf("%d. Insert Product #%d success.", $x, $productId));
                $success++;
            }
            else {
                $this->logProgress(sprintf("%d. Insert Product #%d failed.", $x, $productId));
                $failed++;
            }
            
            $x++;
            unset ($row);
        }
        
        $stop = date($this->formatDate);
        $this->logProgress('Affected rows: ' . $success);
        $this->logProgress('Failed: ' . $failed);
        $this->logProgress('STOP');
        $this->logProgress(sprintf("Start at %s and stop at %s", $start, $stop));
    }
    
    protected function getProducts() {
        $sql = "SELECT `entity_id` FROM `catalog_product_flat_1` ";
        $query = $this->read->query($sql);
        
        return $query;
    }
    
    protected function processRedisInsert($product) {
        $key = 'PRODUCT';
        $hashKey = $product['entity_id'];
        
        try {
            $this->redisHelper->saveCache($key, $hashKey, $product);
            
            return true;
        }
        catch (Exception $ex) {
            $this->logProgress($ex->getMessage());
            
            return false;
        }
    }

    protected function processQueryInsert($product) {
        $sql = "INSERT INTO " . $this->tblPrefix . self::DEFAULT_STORE_ID . " (`entity_id`, `detailed_info`, `group_price`, `tier_price`, `attribute_config`, `attribute_bundle`, `review`, `images`) ";
        $sql .= "VALUES (:entity_id, :detailed_info, :group_price, :tier_price, :attribute_config, :attribute_bundle, :review, :images)";
        //$sql .= $this->collectQueryInsert($product);
        
        try {
            $this->write->query($sql, $this->getBindsQueryInsert($product));
            
            return true;
        }
        catch (Exception $ex) {
            $this->logProgress($ex->getMessage());
            
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


    protected function collectQueryInsert($product) {
        $entityId = $product['entity_id'] ? $product['entity_id'] : '';
        $detailedInfo = $product['detailed_info'] ? json_encode($product['detailed_info']) : '';
        $groupPrice = $product['group_price'] ? json_encode($product['group_price']) : '';
        $tierPrice = $product['tier_price'] ? json_encode($product['tier_price']) : '';
        $attributeConfig = $product['attribute_config'] ? json_encode($product['attribute_config']) : '';
        $attributeBundle = $product['attribute_bundle'] ? json_encode($product['attribute_bundle']) : '';
        $review = $product['review'] ? json_encode($product['review']) : '';
        $images = $product['images'] ? json_encode($product['images']) : '';
                
        $sql = sprintf(
            "(%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $entityId, $detailedInfo, $groupPrice, $tierPrice, $attributeConfig, $attributeBundle, $review, $images
        );
        
        return $sql;
    }

    protected function getLimit() {
        if ($this->getArg('limit')) {
            return (int) $this->getArg('limit');
        }
        
        return self::DEFAULT_LIMIT;
    }

    protected function critical($message) {
        $this->logProgress($message);
        $this->logProgress('Stop Process because error.');
        exit(1);
    }

    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
}

$shell = new GenerateProducts();
$shell->run();
