<?php
class Bilna_Ccp_Model_Ccpmodel extends Mage_Core_Model_Abstract {

    const BATCH_SIZE     = '200';
    const MAX_RANKING   = '999999';

    protected function _construct() {
        $this->_init('ccp/ccpmodel');
    }

    private function connDbWrite() {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    private function connDbRead() {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    // returning an array with attribute codes from magento
    private function getAttributeIDs() {
        $return = array();
        $read = $this->connDbRead();
        $select = $read->select()
            ->from(
                Mage::getConfig()->getTablePrefix()."eav_attribute"
                , array('attribute_code', 'attribute_id')
                )
            ->where('attribute_code IN (?) ', array('visibility','status'))
            ;
        $query = $read->query($select);
        while ($attributes = $query->fetch()) {
            $return[$attributes['attribute_code']] = $attributes['attribute_id'];
        }
        return $return;
    }

    // validation values for store config
    public function getStoreConfigValues() {
        $configValues = Mage::getStoreConfig('bilna_ccp/ccp');
        $configValues['product_bundle']         = isset($configValues['product_bundle']) && !empty($configValues['product_bundle']) ? $configValues['product_bundle'] : 0;
        $configValues['max_days']               = isset($configValues['max_days']) && !empty($configValues['max_days']) ? (int)$configValues['max_days'] : 90;
        $configValues['percentage_itemsold']    = isset($configValues['percentage_itemsold']) && !empty($configValues['percentage_itemsold']) ? (int)$configValues['percentage_itemsold'] : 70;
        $configValues['percentage_inventory']   = isset($configValues['percentage_inventory']) && !empty($configValues['percentage_inventory']) ? (int)$configValues['percentage_inventory'] : 30;
        return $configValues;
    }
    
    // output: array { [0] => array {'name' => "ABCD 2 IN 1", 'product_id' => "31905", 'stock_qty' => "0.0000" }
    public function getProductInventories() {
        $attributes = $this->getAttributeIDs();
        $read = $this->connDbRead();
        $select = $read->select()
            ->from(
                array('stock' => Mage::getConfig()->getTablePrefix().Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item'))
                , array('stock.product_id', 'stock.qty as stock_qty', 'stock.is_in_stock')
                )
            ->joinLeft(
                array('product_entity' => Mage::getConfig()->getTablePrefix()."catalog_product_entity_int")
                , 'product_entity.entity_id = stock.product_id AND product_entity.attribute_id = '.$attributes['status']
                , array('product_entity.value as product_status')
                )
            ->joinLeft(
                array('product_visible' => Mage::getConfig()->getTablePrefix()."catalog_product_entity_int")
                , 'product_visible.entity_id = stock.product_id AND product_visible.attribute_id = '.$attributes['visibility']
                , array('product_visible.value as product_visibility')
                )
            ;
        $product_stock = $read->fetchAll($select);
        return $product_stock;
    }

    // output: array { [0] => array { 'product_id' => "31905", 'sales' => "6741500.00000000" }
    public function getProductsSales() {
        $configValues = $this->getStoreConfigValues();
        $read = $this->connDbRead();

        $select = $read->select()
            ->from(array('main_table' => Mage::getSingleton('core/resource')->getTableName('sales/order_item') ), array('product_id'))
            ->columns('sum(qty_ordered*price) as sales')
            ->where($configValues['product_bundle'] ? '1=1' : 'product_type != ?', 'bundle')
            ->where('created_at >= NOW() - INTERVAL ? DAY', $configValues['max_days'])
            ->group('product_id')
            ;
        $product_sales = $read->fetchAll($select);
        return $product_sales;
    }

    public function setProductScoringDataTable($product_stock, $product_sales) {
        // do the VM routines here
        $arr_sales_rank = $this->setRankings('sales', $product_sales);
        $arr_inv_rank = $this->setRankings('stock_qty', $product_stock);
        $configValues = $this->getStoreConfigValues();
        $percentage_item = $configValues['percentage_itemsold']/100;
        $percentage_inventory = $configValues['percentage_inventory']/100;

        // combine and use one final array only
        $final_array = $this->refactorArray($product_stock, $product_sales);

        $write = $this->connDbWrite();
        $scoring_table = Mage::getSingleton('core/resource')->getTableName('ccp/ccpmodel');
        $write->delete($scoring_table);

        $data = array();
        $final_array_length = sizeof($final_array);
        foreach ($final_array as $product_id => $value) {
            $sales=isset($value['sales']) && !empty($value['sales']) ? (int)$value['sales'] : 0;
            $stock=isset($value['stock_qty']) ? (int)$value['stock_qty'] : 0;
            $sales_rank = $this->standardizeRanking($arr_sales_rank, $product_id, $value, $final_array_length);
            $stock_rank = $this->standardizeRanking($arr_inv_rank, $product_id, $value, $final_array_length);
            $score = $percentage_item*$sales_rank + $percentage_inventory*$stock_rank;

            $data[] = "('".$product_id."', '".$sales."', '".$sales_rank."', '".$stock."', '".$stock_rank."', '".$score."', '')";
        }

        $sql="INSERT INTO ".$scoring_table." VALUES ";
        foreach ($data as $key => $value) {
            $sql.=$value;

            if(($key+1)%Bilna_Ccp_Model_Ccpmodel::BATCH_SIZE==0) {
                $write->query($sql);
                $sql="INSERT INTO ".$scoring_table." VALUES ";
            } else {
                $sql.=", ";
            }
        }
        if(sizeof($data)%Bilna_Ccp_Model_Ccpmodel::BATCH_SIZE>0) {
            $sql = substr($sql, 0, -2);     // remove comma from the string
            $write->query($sql);
        }

        Mage::log("All Products Scoring has been updated.");
    }

    // input product_stock: array { [0] => array {'name' => "ABCD 2 IN 1", 'product_id' => "31905", 'stock_qty' => "0.0000" }
    // input product_sales: array { [0] => array { 'product_id' => "31905", 'sales' => "6741500.00000000" }
    // output             : array ( [31905] => array('stock_qty' = '0.0000', 'sales' => '6741500.00000000') )
    public function refactorArray($product_stock, $product_sales) {
        $return = array();
        foreach ($product_stock as $key => $value) {
            $return[$value['product_id']]['stock_qty']          = $value['stock_qty'];
            $return[$value['product_id']]['product_status']     = $value['product_status'];
            $return[$value['product_id']]['is_in_stock']        = $value['is_in_stock'];
            $return[$value['product_id']]['product_visibility'] = $value['product_visibility'];
        }
        foreach ($product_sales as $key => $value) {
            $return[$value['product_id']]['sales'] = $value['sales'];
        }
        return $return;
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
                $result[$value['product_id']] = $value[$field_to_compare];
            }
            $result = $this->calculateRank($result);
        }
        return $result;
    }

    // input array format: {[268] => "306.0000", [320] => "1.0000", [52508] => "-9.0000"}
    // output depends on sorting: {[268] => "1", [320] => "2", [52508] => "3"}
    public function calculateRank($array, $reverse=true) {
        $arr_sorted = $array;

        if($reverse) 
            rsort($arr_sorted);
        else
            sort($arr_sorted);

        $arr_sorted = array_flip($arr_sorted);
        foreach($array as $key => $val)
            $arr_result[$key] = $arr_sorted[$val]+1;
        return $arr_result;
    }

    // input example: (100, array ( [31905] => array('stock_qty' = '0.0000', 'sales' => '6741500.00000000', 'product_status' => '1', 'is_in_stock' => '0', 'product_visibility' => '1')) )
    // output depends on filtering: 999999999
    public function standardizeRanking($ranking, $productId, $productData, $lastRanking) {
        if($productData['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED 
                && $productData['is_in_stock'] == Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK
                && $productData['product_visibility'] != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
            ) {
            return Bilna_Ccp_Model_Ccpmodel::MAX_RANKING;
        }        
        if (!isset($ranking[$productId]) || !isset($productData['is_in_stock'])) {
            return $lastRanking;
        }
        return (int)$ranking[$productId];
    }

    public function setProductPosition() {
        $productRankingArray = array();
        $categoriesArray = $this->getAllCategories();

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $data = array();
        foreach ($categoriesArray as $key => $category_id) {
            $productsArray = $this->getCatalogProductFromCategory($category_id);
            if(sizeof($productsArray) > 0) {
                $arrScore = array();
                foreach ($productsArray as $key => $product_id) {
                    $score = $this->getProductScore($product_id);
                    $arrScore[$product_id] = sizeof($score) > 0 && isset($score[0]['score']) ? (int)$score[0]['score'] : Bilna_Ccp_Model_Ccpmodel::MAX_RANKING;
                }
                $productRankingArray = $this->calculateRank($arrScore, false);

                foreach ($productRankingArray as $product_id => $position) {
                    $data[] = "UPDATE ".Mage::getSingleton('core/resource')->getTableName('catalog/category_product')." SET position = '".$position."' WHERE category_id = '".$category_id."' AND product_id = '".$product_id."'; ";
                }
            }
        }

        $sql="";
        foreach ($data as $key => $value) {
            $sql.=$value;
            if(($key+1)%Bilna_Ccp_Model_Ccpmodel::BATCH_SIZE==0) {
                $write->query($sql);
                $sql="";
            }
        }
        if(sizeof($data)%Bilna_Ccp_Model_Ccpmodel::BATCH_SIZE>0) {
            $write->query($sql);
        }

        Mage::log("All Products Position has been updated.");
    }

    // get all category IDs to loop
    public function getAllCategories() {
        $list_category = array('2946', '3013');
        return $list_category;
    }

    // get products' IDs from CCP Table
    // returns format array {[0] =>"309", [1] => "310" }
    public function getCatalogProductFromCategory($category_id) {
        $return = array();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
                ->from(Mage::getSingleton('core/resource')->getTableName('catalog/category_product'), array('product_id'))
                ->where('category_id=?', $category_id)
                ;
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
                ->from(Mage::getSingleton('core/resource')->getTableName('ccp/ccpmodel'), array('score'))
                ->where('product_id=?', $product_id)
                ;
        $result = $connection->fetchAll($select); 
        return $result;
    }
}
