<?php
class Alw_Customercity_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getRegionId($region_name)
	{
	
		$tableName = Mage::getSingleton('core/resource')->getTableName('directory_country_region'); 		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
		$query = "select region_id from ".$tableName." where default_name='".$region_name."'";
		$val= $write->query($query);	
	    return $val;
	
	}
}
