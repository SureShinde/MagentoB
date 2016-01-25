<?php
class Bilna_SendReport_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getInbetweenStrings($start, $end, $str){
	    $matches = array();
	    $regex = "/$start(.*?)$end/s";
	    preg_match_all($regex, $str, $matches);
	    return $matches[1];
	}

    public function getData($sql){
    	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		return $read->query($sql);
    }
}