<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProductImages
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/GenerateProduct.php';

class Bilna_Worker_Solr_GenerateProductImages extends Bilna_Worker_Solr_GenerateProduct {
    protected $_tubeAllow = 'solr_catalog_product_images';
    protected $_type = 'images';
    
    protected $_productImageSizeThumbnail = 75;
    protected $_productImageSizeHorizotal = 150;
    protected $_productImageSizeVertical = 150;
    protected $_productImageSizeDetail = 265;
    
    protected function _collect() {
        $queryProducts = $this->_getQuery();
                
        while ($product = $queryProducts->fetch()) {
            $productId = $product['entity_id'];
            $productImagesAttribute = $this->_getProductImagesAttribute($productId);
            $productImages = $this->_getProductImages($productId, $productImagesAttribute);
            $productQueue = array (
                'entity_id' => $productId,
                'images' => $productImages,
            );
            
            if ($this->_queuePut($productQueue)) {
                $this->_logProgress("{$productId} store to queue.");
            }
        }
    }
    
    protected function _getProductImagesAttribute($productId) {
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
    
    protected function _getProductImages($productId, $productImagesAttribute) {
        $sql = "SELECT `cpemg`.`value_id` AS `id`, `cpemgv`.`label`, `cpemgv`.`position`, `cpemgv`.`disabled` AS `exclude`, `cpemg`.`value` AS `url` ";
        $sql .= "FROM `catalog_product_entity_media_gallery` AS `cpemg` ";
        $sql .= "INNER JOIN `catalog_product_entity_media_gallery_value` AS `cpemgv` ON `cpemg`.`value_id` = `cpemgv`.`value_id` ";
        $sql .= "WHERE `cpemgv`.`disabled` = 0 AND `cpemgv`.`store_id` IN ('0','1') AND `cpemg`.`entity_id` = {$productId} ";
        $sql .= "GROUP BY `cpemg`.`value_id` ";
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
    
    protected function _getProduct($data) {
        $productId = $data['entity_id'];
        $productImages = $data['images'];

        $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog/product";
        $imageHelper = Mage::helper('bilna_rest/product_image');
        $productImagesResize = array ();

        if ($productImages) {
            foreach ($productImages as $productImage) {
                $url = $productImage['url'];
                $productImageUrl = $imageUrl . $url;

                //- image thumbnail
                $imageHelper->init($url);
                $imageHelper->resize($this->_productImageSizeThumbnail);
                $productImageThumbnail = $imageHelper->__toString();

                //- image horizontal
                $imageHelper->init($url);
                $imageHelper->resize($this->_productImageSizeHorizotal);
                $productImageHorizontal = $imageHelper->__toString();

                //- image vertical
                //$imageHelper->init($url);
                //$imageHelper->resize($this->_productImageSizeVertical);
                //$productImageVertical = $imageHelper->__toString();

                //- image detail
                $imageHelper->init($url);
                $imageHelper->resize($this->_productImageSizeDetail);
                $productImageDetail = $imageHelper->__toString();

                $productImagesResize[] = array (
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
        
        return array (
            'id' => $productId,
            'images' => $this->_prepareData($productImagesResize),
        );
    }
    
    protected function _processProductData($product) {
        return $this->_productApi->workerGetProductImages($product);
    }
    
    protected function _setQuery($product) {
        $sql = "INSERT INTO `{$this->_productDbTableSet}` (`entity_id`, `images`) VALUES (:entity_id, :images) ON DUPLICATE KEY UPDATE `images` = :images ";
        $binds = array (
            'entity_id' => $product['id'],
            'images' => $product['images'],
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Solr_GenerateProductImages();
$worker->run();
