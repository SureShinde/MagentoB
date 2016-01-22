<?php
class Bilna_SendReport_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getInbetweenStrings($start, $end, $str){
	    $matches = array();
	    $regex = "/$start(.*?)$end/s";
	    preg_match_all($regex, $str, $matches);
	    return $matches[1];
	}

    public function getCollectionByCode($code){
    	$sendreport = Mage::getModel('sendreport/salescategory');
	    $coll = $sendreport->getCollection()
				->addFieldToFilter('send_report_code', $code)
				->addFieldToFilter('status', 1)
				->setPageSize(1)
			    ->setCurPage(1);
		return $coll;
    }

    public function getData($sql){
    	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		return $read->query($sql);
    }
}