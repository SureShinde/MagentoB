<?php
/**
 * Description of Bilna_Rest_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Helper_Product_Generate extends Mage_Core_Helper_Abstract
{
    const BATCH_SIZE = 100;

    private $targetTable = 'api_product_flat_1';
    private $typeConfig = 'configurable';
    private $typeBundle = 'bundle';
    private $logFilename = 'generate_product_solr.log';

    private $dbRead;
    private $dbWrite;
    private $productModel;
    private $dateModel;
    private $imageHelper;
    private $imageUrl;
    private $imageSizes;

    public function process()
    {
        try {
            $this->initialize();

            // get product count
            $productCount = $this->getProductCount();
            $this->log("Found: {$productCount}.");

            // get base data
            $baseQuery = $this->getBaseQuery();

            // process in batches
            $finished = 0;
            $batch = [];
            while ($data = $baseQuery->fetch()) {
                $batch[] = $data;
                if (count($batch) < self::BATCH_SIZE) continue;

                // batch is full
                $this->processBatch($batch);
                $finished += self::BATCH_SIZE;
                $batch = [];
                $this->log("Progress: $finished/$productCount.");
            }

            // last batch
            $this->processBatch($batch);
            $finished += count($batch);
            $batch = [];
            $this->log("Finished: $finished.");
        } catch (Exception $e) {
            $this->log("Error: {$e->getMessage()}");
        }
    }

    private function initialize()
    {
        $this->dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->productModel = Mage::getModel('bilna_rest/api2_product_rest_admin_v1');
        $this->dateModel = Mage::getModel('core/date');
        $this->imageHelper = Mage::helper('bilna_rest/product_image');
        $this->imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
        $this->imageSizes = Mage::getStoreConfig('generate/images') ?: [
            'thumbnail' => 75,
            'horizontal' => 150,
            'vertical' => 150,
            'detail' => 265
        ];
    }

    private function processBatch($batch)
    {
        $productData = $this->getProductData($batch);
        $imageData = $this->getImageData($batch);

        // collect query components and data binder
        $c = 0;
        $query = [];
        $bind = [];
        foreach ($batch as $product) {

            $data = [
                "entity_id_$c" => $product['entity_id'],
                "detailed_info_$c" => $this->buildDetailedInfo($product),
                "attribute_config_$c" => $this->buildAttributeConfig($product, $productData),
                "attribute_bundle_$c" => $this->buildAttributeBundle($product, $productData),
                "images_$c" => $this->buildImages($product, $imageData),
                "sales_price_$c" => $product['sales_price'],
                "in_stock_$c" => $product['in_stock']
            ];

            $keysWithColon = array_map(function ($k) { return ":$k"; }, array_keys($data));
            $query[] = '(' . implode(', ', $keysWithColon) . ', NOW())'; // NOW() as updated_at
            $bind = array_merge($bind, $data);

            $c++;
        }

        // build the full query then execute
        $query = implode(', ', $query);
        $query = "INSERT INTO {$this->targetTable}
            (entity_id, detailed_info, attribute_config, attribute_bundle, images, sales_price, in_stock, updated_at)
            VALUES $query
            ON DUPLICATE KEY UPDATE
                entity_id = VALUES(entity_id),
                detailed_info = VALUES(detailed_info),
                attribute_config = VALUES(attribute_config),
                attribute_bundle = VALUES(attribute_bundle),
                images = VALUES(images),
                sales_price = VALUES(sales_price),
                in_stock = VALUES(in_stock),
                updated_at = VALUES(updated_at)";
        $this->dbWrite->query($query, $bind);

    }

    private function buildDetailedInfo($product)
    {
        $info = [
            'description' => $product['description'],
            'additional' => NULL, // always null
            'how_to_use' => $product['how_to_use'],
            'nutrition_fact' => $product['nutrition_fact'],
            'size_chart' => $product['size_chart'],
            'more_detail' => $product['more_detail'],
            'additional_info' => NULL // always null
        ];
        return json_encode($info);
    }

    private function buildAttributeConfig($product, $productData)
    {
        if ($product['type_id'] !== $this->typeConfig) return NULL;

        $productId = $product['entity_id'];
        if (empty($productData[$productId])) return NULL;

        $result = $this->productModel->workerGetProductConfig($productData[$productId]);
        return $result ? json_encode($result) : NULL;
    }

    private function buildAttributeBundle($product, $productData)
    {
        if ($product['type_id'] !== $this->typeBundle) return NULL;

        $productId = $product['entity_id'];
        if (empty($productData[$productId])) return NULL;

        $result = $this->productModel->workerGetProductBundle($productData[$productId]);
        return $result ? json_encode($result) : NULL;
    }

    private function buildImages($product, $imageData)
    {
        $productId = $product['entity_id'];
        if (empty($imageData[$productId])) return NULL;

        $result = [];
        foreach ($imageData[$productId] as $image) {
            $url = $image['url'];
            $resize = [
                'base' => $this->imageUrl . $url
            ];

            // generate images
            foreach ($this->imageSizes as $key => $size) {
                if ($key === 'vertical') continue;

                $this->imageHelper->init($url);
                $this->imageHelper->resize($size);
                $resize[$key] = $this->imageHelper->__toString();
            }
            $resize['vertical'] = $resize['horizontal'];

            // get default images
            $types = [];
            if ($product['image'] == $url) {
                $types[] = 'image';
            }
            if ($product['small_image'] == $url) {
                $types[] = 'small_image';
            }
            if ($product['thumbnail'] == $url) {
                $types[] = 'thumbnail';
            }

            $result[] = [
                'id' => $image['id'],
                'label' => $image['label'],
                'position' => $image['position'],
                'exclude' => $image['exclude'],
                'url' => $resize['base'],
                'types' => $types,
                'resize' => $resize
            ];
        }

        return json_encode($result);
    }

    private function getProductCount()
    {
        $query = $this->dbRead->query('SELECT COUNT(1) AS count FROM catalog_product_flat_1');
        $result = $query->fetch();
        return isset($result['count']) ? $result['count'] : 0;
    }

    private function getBaseQuery()
    {
        return $this->dbRead->query(
            "SELECT
                cpf.entity_id,
                cpf.type_id,
                cpev_d.value AS description,
                cpev_htu.value AS how_to_use,
                cpev_nf.value AS nutrition_fact,
                cpev_sc.value AS size_chart,
                cpev_md.value AS more_detail,
                cpev_i.value AS image,
                cpev_si.value AS small_image,
                cpev_t.value AS thumbnail,
                IFNULL(gsfoi.total, 0) AS sales_price,
                IFNULL(ciss.stock_status, 0) AS in_stock
            FROM catalog_product_flat_1 AS cpf
            LEFT JOIN catalog_product_entity_text AS cpev_d
                ON cpf.entity_id = cpev_d.entity_id AND cpev_d.attribute_id = '72'
            LEFT JOIN catalog_product_entity_text AS cpev_htu
                ON cpf.entity_id = cpev_htu.entity_id AND cpev_htu.attribute_id = '286'
            LEFT JOIN catalog_product_entity_text AS cpev_nf
                ON cpf.entity_id = cpev_nf.entity_id AND cpev_nf.attribute_id = '287'
            LEFT JOIN catalog_product_entity_text AS cpev_sc
                ON cpf.entity_id = cpev_sc.entity_id AND cpev_sc.attribute_id = '289'
            LEFT JOIN catalog_product_entity_text AS cpev_md
                ON cpf.entity_id = cpev_md.entity_id AND cpev_md.attribute_id = '182'
            LEFT JOIN catalog_product_entity_varchar AS cpev_i
                ON cpf.entity_id = cpev_i.entity_id AND cpev_i.attribute_id = '85'
            LEFT JOIN catalog_product_entity_varchar AS cpev_si
                ON cpf.entity_id = cpev_si.entity_id AND cpev_si.attribute_id = '86'
            LEFT JOIN catalog_product_entity_varchar AS cpev_t
                ON cpf.entity_id = cpev_t.entity_id AND cpev_t.attribute_id = '87'
            LEFT JOIN cataloginventory_stock_status AS ciss
                ON cpf.entity_id = ciss.product_id
            LEFT JOIN (
                    SELECT
                        sfoi.product_id,
                        SUM(sfoi.row_total) AS total
                    FROM sales_flat_order_item AS sfoi
                    WHERE sfoi.created_at BETWEEN (NOW() - INTERVAL 30 DAY) AND NOW()
                    GROUP BY sfoi.product_id
                ) AS gsfoi
                ON cpf.entity_id = gsfoi.product_id
            ORDER BY cpf.type_id DESC, cpf.entity_id"
        );
    }

    private function getProductData($batch)
    {
        $configBundleProducts = array_filter($batch, function ($product) {
            return $product['type_id'] === $this->typeConfig || $product['type_id'] === $this->typeBundle;
        });
        if (empty($configBundleProducts)) return [];

        $productIds = array_column($configBundleProducts, 'entity_id');
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $productIds));

        // build ID-to-product map
        $result = [];
        foreach ($collection as $product) {
            $result[$product->getId()] = $product;
        }

        return $result;
    }

    private function getImageData($batch)
    {
        $productIds = array_column($batch, 'entity_id');
        $productSqlFilter = "('" . implode("', '", $productIds) . "')";

        // fetch images
        $query = $this->dbRead->query(
            "SELECT
                cpemg.entity_id,
                cpemg.value_id AS id,
                cpemgv.label,
                cpemgv.position,
                cpemgv.disabled AS exclude,
                cpemg.value AS url
            FROM catalog_product_entity_media_gallery AS cpemg
            INNER JOIN catalog_product_entity_media_gallery_value AS cpemgv
                ON cpemg.value_id = cpemgv.value_id
            WHERE
                cpemgv.disabled = 0 AND
                cpemgv.store_id IN ('0', '1') AND
                cpemg.entity_id IN $productSqlFilter
            GROUP BY cpemg.value_id"
        );

        // group image data by product ID
        $result = [];
        while ($image = $query->fetch()) {
            $productId = $image['entity_id'];
            if (!isset($result[$productId])) {
                $result[$productId] = [$image];
            } else {
                $result[$productId][] = $image;
            }
        }

        return $result;
    }

    private function log($message)
    {
        Mage::log($message, null, $this->logFilename);
        echo $this->dateModel->date('Y-m-d H:i:s - ') . $message . "\n";
    }
}
