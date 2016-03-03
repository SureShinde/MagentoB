<?php
class Bilna_Ccp_Model_Ccpmodel extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('ccp/ccpmodel');
    }
    
    // output: array { [0] => array {'name' => "ABCD 2 IN 1", 'product_id' => "31905", 'stock_qty' => "0.0000" }
    public function getProductInventories($param = array()) {
        $resource = new Mage_Core_Model_Resource();  
        $read = $resource->getConnection('core_read');  

        $select = $read->select()
            ->from(array('main_table' => 'catalog_product_flat_1'),
                array('main_table.name', 'main_table.entity_id as product_id'))
            ->joinLeft(
                array('stock' => Mage::getConfig()->getTablePrefix()."cataloginventory_stock_item")
                , 'stock.product_id = main_table.entity_id'
                , array('stock.qty as stock_qty')
                )
            ;
        Mage::log((string)$select);
        $product_stock = $read->fetchAll($select);
        return $product_stock;
    }

    // output: array { [0] => array {'name' => "ABCD 2 IN 1", 'product_id' => "31905", 'stock_qty' => "0.0000", 'sales' => "6741500.00000000" }
    public function getProductsSales($product_stock) {
        $configValues = Mage::getStoreConfig('bilna_ccp/ccp'); 
        $resource = new Mage_Core_Model_Resource();  
        $read = $resource->getConnection('core_read'); 

        $productQtyArray = array();
        foreach ($product_stock as $key => $value) {
            $select = $read->select()
                ->from(array('main_table' => 'sales_flat_order_item'), array())
                ->columns('sum(qty_ordered*price) as sales')
                ->where($configValues['product_bundle'] ? '1=1' : 'product_type != ?', 'bundle')
                ->where('product_id=?', $value['product_id'])
                ->group('product_id')
                ;
            Mage::log((string)$select);
            $product_sales = $read->fetchAll($select);
            // output: array { [0] => array {'sales' => "6741500.00000000" } }

            $product_stock[$key]['sales'] = sizeof($product_sales) > 0 && $product_sales[0]['sales'] ? (int)$product_sales[0]['sales'] : 99999;
        }
        return $product_stock;
    }

    public function setProductScoringDataTable($product_stock) {
        // do the VM routines here
        $arr_sales_rank = $this->setRankings('sales', $product_stock);
        $arr_inv_rank = $this->setRankings('stock_qty', $product_stock);
        $configValues = Mage::getStoreConfig('bilna_ccp/ccp');
        $percentage_item = $configValues['percentage_itemsold']/100;
        $percentage_inventory = $configValues['percentage_inventory']/100;

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->delete("bilna_ccp_product_scoring");

        foreach ($product_stock as $key => $value) {
            $product_id = $value['product_id'];
            $score = $percentage_item*$arr_sales_rank[$product_id] + $percentage_inventory*$arr_inv_rank[$product_id];

            try {
                $write->insert(
                        "bilna_ccp_product_scoring", 
                        array('product_id' => $product_id,
                                'sales' => (int)$value['sales'],
                                'sales_rank' => $arr_sales_rank[$product_id],
                                'inventory' => $value['stock_qty'],
                                'inventory_rank' => $arr_inv_rank[$product_id],
                                'score' => $score
                        )
                );
                Mage::log('Product ID '.$product_id.' ('.$arr_sales_rank[$product_id].'|'.$arr_inv_rank[$product_id].') has been updated with score of '.$score);
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /* input array format: 
    {
      [0] => {'name' => "Pampers Active Baby Pants Diapers M 56",'is_in_stock' => "1",'stock_qty' => "306.0000",'product_id' => "268",'status' => "processing",'sales' => "1188000.00000000", },
      [1] => {'name' => "Sebamed Baby Care Cream 100ml",'is_in_stock' => "1",'stock_qty' => "1.0000",'product_id' => "320",'status' => "processing",'sales' => "107000.00000000",  },
      [2] => {'name' => "LG Smartphone L1 II Black",'is_in_stock' => "1",'stock_qty' => "-9.0000",'product_id' => "52508",'status' => "processing",'sales' => "733000.00000000",  }
    }
    */
    public function setRankings($field_to_compare, $productArray) {     
        $result = array();
        if(sizeof($productArray) > 0 ) {
            foreach ($productArray as $key => $value) {
                // we use product id as key for unique mapping
                $result[$value['product_id']] = (int)$value[$field_to_compare];
            }
            $result = $this->calculateRank($result);
        }       
        return $result;
    }

    // input array format: {[268] => "306.0000", [320] => "1.0000", [52508] => "-9.0000"}
    // output depends on sorting: {[268] => "1", [320] => "2", [52508] => "3"}
    public function calculateRank($array, $sorting="rsort") {
        $arr_sorted = $array;

        switch ($sorting) {
            case 'sort':
                sort($arr_sorted);
                break;            
            default:
                rsort($arr_sorted);
                break;
        }
        
        $arr_sorted = array_flip($arr_sorted);
        foreach($array as $key => $val)
            $arr_result[$key] = $arr_sorted[$val]+1;
        return $arr_result;
    }

    public function setProductPosition() {
        $productRankingArray = array();
        $categoriesArray = $this->getAllCategories();

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($categoriesArray as $key => $category_id) {
            $productsArray = $this->getCatalogProductFromCategory($category_id);
            if(sizeof($productsArray) > 0) {
                $arrScore = array();
                foreach ($productsArray as $key => $product_id) {
                    $score = $this->getProductScore($product_id);
                    $arrScore[$product_id] = sizeof($score) > 0 && isset($score[0]['score']) ? (int)$score[0]['score'] : 99999;
                }
                $productRankingArray = $this->calculateRank($arrScore, "sort");

                foreach ($productRankingArray as $product_id => $position) {
                    try {
                        $write->update(
                            "catalog_category_product",
                            array("position" => $position),
                            array("category_id=".$category_id, "product_id=".$product_id)
                        );
                        $write->commit();
                        Mage::log('Product ID '.$product_id.' from Category ID '.$category_id.' was updated to position '.$position);
                    } catch(Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }

    // get all category IDs to loop
    public function getAllCategories() {
        $list_category = array('2887');
        return $list_category;
    }

    // get products' IDs from CCP Table
    // returns format array {[0] =>"309", [1] => "310" }
    public function getCatalogProductFromCategory($category_id) {
        $return = array();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
                ->from('catalog_category_product', array('product_id'))
                ->where('category_id=?', $category_id)
                ;
        Mage::log((string)$select);
        $result = $connection->fetchAll($select); 
        foreach ($result as $key => $value) {
            $return[] = $value['product_id'];
        }
        return $return;
    }

    // get product score from table bilna_ccp_product_scoring table
    // returns format array {[0] => array { 'score' => "1.00" } }
    public function getProductScore($product_id) {
        $return = array();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
                ->from('bilna_ccp_product_scoring', array('score'))
                ->where('product_id=?', $product_id)
                ;
        Mage::log((string)$select);
        $result = $connection->fetchAll($select); 
        return $result;
    }
}