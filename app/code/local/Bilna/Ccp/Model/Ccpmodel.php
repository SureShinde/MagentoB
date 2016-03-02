<?php
/**
 * Description of Bilna_Ccp_Model_Ccpmodel
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Ccp_Model_Ccpmodel  extends Mage_Core_Model_Abstract {

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('ccp/ccpmodel');
    }

    /**
	* Getting list of defined product list to be used
	*/
	public function getMatchedProductsList($param = array()) {

		$configValues = Mage::getStoreConfig('bilna_ccp/ccp');

		$resource = new Mage_Core_Model_Resource();  
		$read = $resource->getConnection('core_read');  

		$formatted_orderstatus = '"'.implode('","', explode(",", $configValues['order_status'])).'"';

		$select = $read->select()
		               ->from(array('main_table' => 'catalog_product_flat_1'),
		               			array('main_table.name'))
		               ->joinLeft(
			                array('stock' => Mage::getConfig()->getTablePrefix()."cataloginventory_stock_item")
			                , 'stock.product_id = main_table.entity_id'
			                , array('stock.is_in_stock', 'stock.qty as stock_qty')
			            )
		               ->joinLeft(
						   array('sales_item' => Mage::getConfig()->getTablePrefix()."sales_flat_order_item")
						   , 'sales_item.product_id = main_table.entity_id '
						   , array('product_id')
						)
		               ->joinLeft(
						   array('sales_order' => Mage::getConfig()->getTablePrefix()."sales_flat_order")
						   , 'sales_item.order_id = sales_order.entity_id'
						   , array('status')
						)
		               ->columns('sum(sales_item.qty_ordered*sales_item.price) as revenue')
		               ->where('sales_order.status IN ('.$formatted_orderstatus.')')
		               ->where($configValues['product_bundle'] ? '1=1' : 'sales_item.product_type != ?', 'bundle')
		               ->where('sales_order.created_at BETWEEN (NOW() - INTERVAL '.$configValues['max_days'].' DAY) AND NOW() ')
		               ->group('sales_item.product_id')
		            ;

		// Mage::log((string)$select);

		return $read->fetchAll($select);  


     	// $collections = $this->getCollection();        
      //   $collections->getSelect()->reset(Zend_Db_Select::COLUMNS)->joinLeft(
      //           array('stock' => Mage::getConfig()->getTablePrefix()."cataloginventory_stock_item")
      //           , 'stock.product_id = main_table.entity_id'
      //           , array('main_table.name', 'is_in_stock')
      //       );        
      //   $collections->getSelect()->joinLeft(
      //           array('sales_item' => Mage::getConfig()->getTablePrefix()."sales_flat_order_item")
      //           , 'sales_item.product_id = main_table.entity_id '
      //           , array('product_id')
      //       );
      //   $collections->getSelect()->joinLeft(
      //           array('sales_order' => Mage::getConfig()->getTablePrefix()."sales_flat_order")
      //           , 'sales_item.order_id = sales_order.entity_id'
      //           , array('status')
      //       );
      //   $collections->getSelect()->columns('stock.qty as stock_qty, sum(sales_item.qty_ordered*sales_item.price) as revenue');
      //   $collections->getSelect()->columns(
      //       array(
      //           'categories' => new Zend_Db_Expr("(select concat(';',(GROUP_CONCAT(category.category_id SEPARATOR ';')),';') from catalog_category_product_index category where category.product_id = main_table.entity_id and store_id=1)")
      //       ));

      //   $collections->addFieldToFilter('sales_order.status', array('in' => array('complete', 'processing', 'processing_cod', 'shipping_cod', 'holded')));
      //   $collections->addFieldToFilter('sales_item.product_type', array('neq' => 'bundle'));
      //   $collections->addFieldToFilter('sales_order.created_at', array(
      //                                       'from'     => strtotime('-30 day', time()),
      //                                       'to'       => time(),
      //                                       'datetime' => true
      //                                   )
      //                               );
      //   $collections->getSelect()->group('sales_item.product_id');

      //   return $collections->getData();
    }

    public function setRankings($field_to_compare, $arr_source = array()) {
    	
    	$arrRank = array();
		if(sizeof($arr_source) > 0 ) {
			foreach ($arr_source as $key => $value) {
				// we use product id as key for unique mapping
				$arrRank[$value['product_id']] = $value[$field_to_compare];
			}

			$arrRank = $this->calcRank($arrRank);
		}
    	
    	return $arrRank;
	}

	public function calcRank($arr_source=array()) {
		//create a copy and sort
		$arr_sorted = $arr_source;
		sort($arr_sorted);

		//reverses key and values
		$arr_sorted = array_flip($arr_sorted);

		//create result by using keys from sorted values + 1
		foreach($arr_source as $key => $val)
		    $arr_result[$key] = $arr_sorted[$val]+1;

		return $arr_result;
	}

}
