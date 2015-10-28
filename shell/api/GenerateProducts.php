<?php
/**
 * Description of GenerateProducts
 *
 * @project Logan
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class GenerateProducts extends Mage_Shell_Abstract {
    const DEFAULT_STORE_ID = 1;
    const DEFAULT_LIMIT = 100;

    protected $read = null;
    protected $write = null;
    protected $tblPrefix = 'api_products_flat_';

    protected function init() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
        
        $this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->write = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function run() {
        $this->init();
        $products = $this->getProducts();
        
        if (!$products) {
            $this->critical('Product not found.');
        }
        
        $x = 0;
        $limit = $this->getLimit();
        $inserts = array ();
        
        foreach ($products as $product) {
            if ($x == $limit) {
                $this->critical('Proses dimari');
            }
            
            $productApi = Mage::getModel('bilna_rest/api2_product_rest_admin_v1');
            $_product = $productApi->retrieve($product->getId(), self::DEFAULT_STORE_ID);
            
            $inserts[] = $this->collectQueryInsert($_product);
            $x++;
        }
        
        echo "setop dimari";
        exit;
        
//        echo json_encode($inserts) . "\n";
//        exit;
    }
    
    protected function getProducts() {
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addStoreFilter();
        //$products->addAttributeToFilter('entity_id', 1718);
        //$products->addAttributeToFilter('entity_id', 18849);
        $products->getSelect()->limit(5);
        
        if ($products->getSize() > 0) {
            return $products;
        }
        
        return false;
    }
    
    protected function collectQueryInsert($product) {
        $binds = array (
            'entity_id' => $product['entity_id'],
            'attribute_set_id' => $product['attribute_set_id'],
            'type_id' => $product['type_id'],
            'sku' => $product['sku'],
            'name' => $product['name'],
            'meta_title' => $product['meta_title'],
            'meta_description' => $product['meta_description'],
            'url_key' => $product['url_key'],
            'custom_design' => $product['custom_design'],
            'page_layout' => $product['page_layout'],
            'options_container' => $product['options_container'],
            'country_of_manufacture' => $product['country_of_manufacture'],
            'msrp_enabled' => $product['msrp_enabled'],
            'msrp_display_actual_price_type' => $product['msrp_display_actual_price_type'],
            'gift_message_available' => $product['gift_message_available'],
            'weight_contents' => $product['weight_contents'],
            'milk_stage' => $product['milk_stage'],
            'type' => $product['type'],
            'supplier_name_neccessity' => $product['supplier_name_neccessity'],
            'promo' => $product['promo'],
            'aw_os_product_display' => $product['aw_os_product_display'],
            'aw_os_product_position' => $product['aw_os_product_position'],
            'aw_os_product_text' => $product['aw_os_product_text'],
            'aw_os_category_display' => $product['aw_os_category_display'],
            'aw_os_category_position' => $product['aw_os_category_position'],
            'aw_os_category_text' => $product['aw_os_category_text'],
            'aw_os_product_image_path' => $product['aw_os_product_image_path'],
            'aw_os_category_image_path' => $product['aw_os_category_image_path'],
            'netsuite_internal_id' => $product['netsuite_internal_id'],
            'warranty_covers' => $product['warranty_covers'],
            'price' => $product['price'],
            'special_price' => $product['special_price'],
            'cost' => $product['cost'],
            'weight' => $product['weight'],
            'msrp' => $product['msrp'],
            'detailed_info' => $product['detailed_info'],
            'short_description' => $product['short_description'],
            'meta_keyword' => $product['meta_keyword'],
            'custom_layout_update' => $product['custom_layout_update'],
            'ingredients' => $product['ingredients'],
            'service_center' => $product['service_center'],
            'manufacturer' => $product['manufacturer'],
            'status' => $product['status'],
            'visibility' => $product['visibility'],
            'enable_googlecheckout' => $product['enable_googlecheckout'],
            'tax_class_id' => $product['tax_class_id'],
            'brand' => $product['brand'],
            'enable_zoom_plugin' => $product['enable_zoom_plugin'],
            'milk_flavor' => $product['milk_flavor'],
            'consigment' => $product['consigment'],
            'confirm_image' => $product['confirm_image'],
            'category_id_asc' => $product['category_id_asc'],
            'brands' => $product['brands'],
            'product_master' => $product['product_master'],
            'expected_cost' => $product['expected_cost'],
            'event_cost' => $product['event_cost'],
            'confirm_desc' => $product['confirm_desc'],
            'partnership_type' => $product['partnership_type'],
            'product_category' => $product['product_category'],
            'product_subcategory' => $product['product_subcategory'],
            'ship_by' => $product['ship_by'],
            'sold_by' => $product['sold_by'],
            'exclusive_product' => $product['exclusive_product'],
            'warranty_available' => $product['warranty_available'],
            'warranty_period' => $product['warranty_period'],
            'warranty_provider' => $product['warranty_provider'],
            'special_from_date' => $product['special_from_date'],
            'special_to_date' => $product['special_to_date'],
            'news_from_date' => $product['news_from_date'],
            'news_to_date' => $product['news_to_date'],
            'custom_design_from' => $product['custom_design_from'],
            'custom_design_to' => $product['custom_design_to'],
            'last_netsuite_stock_update' => $product['last_netsuite_stock_update'],
            'netsuite_last_import_date' => $product['netsuite_last_import_date'],
            'event_start_date' => $product['event_start_date'],
            'event_end_date' => $product['event_end_date'],
            'group_price' => $product['group_price'],
            'tier_price' => $product['tier_price'],
        );
        
        echo json_encode($binds);exit;
        
        return $result;
    }

    protected function processQueryInsert($product) {
        $sql = sprintf("INSERT INTO `%s%d` VALUES ", $this->tblPrefix, self::DEFAULT_STORE_ID);
        
        return $sql;
    }

    protected function getLimit() {
        if ($this->getArg('limit')) {
            return (int) $this->getArg('limit');
        }
        
        return self::DEFAULT_LIMIT;
    }

    protected function critical($message) {
        echo $message . "\n";
        exit;
    }

    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
}

$shell = new GenerateProducts();
$shell->run();
