<?php
class Bilna_Ccp_Model_Ccpmodel extends Mage_Core_Model_Abstract {

    const BATCH_SIZE = '200';
    const LAST_RANKING = '999999999';

    protected function _construct() {
        $this->_init('ccp/ccpmodel');
    }

    private function connDbWrite() {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    private function connDbRead() {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }
    
    // output: array { [0] => array {'name' => "ABCD 2 IN 1", 'product_id' => "31905", 'stock_qty' => "0.0000" }
    public function getProductInventories() {
        $read = $this->connDbRead();

        $select = $read->select()
            ->from(array('main_table' => 'catalog_product_flat_1'),
                array('main_table.name', 'main_table.entity_id as product_id'))
            ->join(
                array('stock' => Mage::getConfig()->getTablePrefix()."cataloginventory_stock_item")
                , 'stock.product_id = main_table.entity_id'
                , array('stock.qty as stock_qty')
                )
            ;
        $product_stock = $read->fetchAll($select);
        return $product_stock;
    }

    // output: array { [0] => array { 'product_id' => "31905", 'sales' => "6741500.00000000" }
    public function getProductsSales() {
        $configValues = Mage::getStoreConfig('bilna_ccp/ccp'); 
        $read = $this->connDbRead();

        $select = $read->select()
            ->from(array('main_table' => 'sales_flat_order_item'), array('product_id'))
            ->columns('sum(qty_ordered*price) as sales')
            ->where($configValues['product_bundle'] ? '1=1' : 'product_type != ?', 'bundle')
            ->group('product_id')
            ;
        $product_sales = $read->fetchAll($select);
        return $product_sales;
    }

    public function setProductScoringDataTable($product_stock, $product_sales) {
        // do the VM routines here
        $arr_sales_rank = $this->setRankings('sales', $product_sales);
        $arr_inv_rank = $this->setRankings('stock_qty', $product_stock);
        $configValues = Mage::getStoreConfig('bilna_ccp/ccp');
        $percentage_item = $configValues['percentage_itemsold']/100;
        $percentage_inventory = $configValues['percentage_inventory']/100;

        $write = $this->connDbWrite();
        $write->delete("bilna_ccp_product_scoring");

        $data = array();
        foreach ($product_stock as $key => $value) {

            $product_id = $value['product_id'];
            $sales=isset($product_sales[$key]['sales']) ? (int)$product_sales[$key]['sales'] : 0;

            $sales_rank = isset($arr_sales_rank[$product_id]) ? (int)$arr_sales_rank[$product_id] : Bilna_Ccp_Model_Ccpmodel::LAST_RANKING;
            $stock=isset($value['stock_qty']) ? $value['stock_qty'] : 0;
            $stock_rank = isset($arr_inv_rank[$product_id]) ? (int)$arr_inv_rank[$product_id] : Bilna_Ccp_Model_Ccpmodel::LAST_RANKING;
            $score = $percentage_item*$sales_rank + $percentage_inventory*$stock_rank;

            $data[] = "('".$product_id."', '".$sales."', '".$sales_rank."', '".$stock."', '".$stock_rank."', '".$score."', '')";
        }

        $sql="INSERT INTO bilna_ccp_product_scoring VALUES ";
        foreach ($data as $key => $value) {
            $sql.=$value;

            if(($key+1)%Bilna_Ccp_Model_Ccpmodel::BATCH_SIZE==0) {
                $write->query($sql);
                $sql="INSERT INTO bilna_ccp_product_scoring VALUES ";
            } else {
                $sql.=", ";
            }
        }
        Mage::log("All Products Scoring has been updated.");
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

        $data = array();
        foreach ($categoriesArray as $key => $category_id) {
            $productsArray = $this->getCatalogProductFromCategory($category_id);
            if(sizeof($productsArray) > 0) {
                $arrScore = array();
                foreach ($productsArray as $key => $product_id) {
                    $score = $this->getProductScore($product_id);
                    $arrScore[$product_id] = sizeof($score) > 0 && isset($score[0]['score']) ? (int)$score[0]['score'] : Bilna_Ccp_Model_Ccpmodel::LAST_RANKING;
                }
                $productRankingArray = $this->calculateRank($arrScore, "sort");

                foreach ($productRankingArray as $product_id => $position) {
                    $data[] = "UPDATE catalog_category_product SET position = '".$position."' WHERE category_id = '".$category_id."' AND product_id = '".$product_id."'; ";
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
        Mage::log("All Products Position has been updated.");
    }

    // get all category IDs to loop
    public function getAllCategories() {
        $list_category = array('2946');
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
        $result = $connection->fetchAll($select); 
        return $result;
    }
}