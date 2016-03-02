<?php
require_once dirname(__FILE__) . '/../abstract.php';

class Ccp extends Mage_Shell_Abstract {
    
    public function run() {

        $productModel = Mage::getModel('ccp/ccpmodel');
        $productsList = $productModel->getMatchedProductsList();

        $arr_sales_rank = $productModel->setRankings('revenue', $productsList);
        $arr_inv_rank = $productModel->setRankings('stock_qty', $productsList);

        $configValues = Mage::getStoreConfig('bilna_ccp/ccp');
        $percentage_item = $configValues['percentage_itemsold']/100;
        $percentage_inventory = $configValues['percentage_inventory']/100;

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->delete("bilna_ccp_product_scoring");

        foreach ($productsList as $item) {
            $score = $percentage_item*$arr_sales_rank[$item['product_id']] + $percentage_inventory*$arr_inv_rank[$item['product_id']];

            try {
                $write->insert(
                        "bilna_ccp_product_scoring", 
                        array('product_id' => $item['product_id'],
                                'sales' => $item['revenue'],
                                'sales_rank' => $arr_sales_rank[$item['product_id']],
                                'inventory' => $item['stock_qty'],
                                'inventory_rank' => $arr_inv_rank[$item['product_id']],
                                'score' => $score,
                                'created_at' => now()
                        )
                );
                Mage::log("CCP was successfully updated.");
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }        
    }    
}

$shell = new Ccp();
$shell->run();
exit;