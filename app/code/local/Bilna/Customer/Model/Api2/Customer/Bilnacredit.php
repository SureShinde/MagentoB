<?php
/**
 * Description of Bilnacredit
 *
 * @author Bilna Development Team <development@bilna.com>
 * 
 * @link http://mandagreen.com/showing-all-reviews-and-ratings-on-a-page-in-magento/
 * @link https://wiki.magento.com/display/m1wiki/Using+Magento+1.x+collections
 * @link http://devdocs.magento.com/guides/m1x/magefordev/mage-for-dev-8.html
 * 
 */

class Bilna_Customer_Model_Api2_Customer_Bilnacredit extends Mage_Api2_Model_Resource
{
    protected function getCreditBalance($customerId = null) 
    {
        /**
	 * Get the resource model
	 */
	$resource = Mage::getSingleton('core/resource');
	
	/**
	 * Retrieve the read connection
	 */
	$readConnection = $resource->getConnection('core_read');

	/**
	 * Retrieve our table name
	 */
	$table = $resource->getTableName('points/summary');
	
        $query = 'SELECT * FROM ' . $table . ' WHERE customer_id = '
			. (int)$customerId . ' LIMIT 1';
	
	/**
	 * Execute the query and store the result in $sku
	 */
	$collection = $readConnection->fetchAll($query);
        
        return $collection[0];
    }
    
    protected function getCreditHistory($summaryId = null)
    {
        /**
	 * Get the resource model
	 */
	$resource = Mage::getSingleton('core/resource');
	
	/**
	 * Retrieve the read connection
	 */
	$readConnection = $resource->getConnection('core_read');

	/**
	 * Retrieve our table name
	 */
	$table = $resource->getTableName('points/transaction');
	
        $query = 'SELECT * FROM ' . $table . ' WHERE summary_id = '
			. (int)$summaryId . ' ORDER BY id DESC';
	
	/**
	 * Execute the query and store the result in $collection
	 */
	$collection = $readConnection->fetchAll($query);
        
        return $collection;
    }
}
