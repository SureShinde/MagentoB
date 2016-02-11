<?php
class Bilna_Promo_Block_Giftvoucher extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
	
    public function _getBaseUrl() {
        return Mage::getBaseUrl();
    }
    
    public function _getMediaUrl() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
    }
    
    public static function _getActivePromo() {
		$data["submitDate"] = date("Y-m-d H:i:s");
		
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "select * from bilna_promo_giftvoucher 
        			WHERE	start_date <= '".$data["submitDate"]."' and 
        					end_date >= '".$data["submitDate"]."' and 
        					status = '1'
        			ORDER BY priority DESC limit 1";
        $result = $read->fetchAll($sql);

        if(empty($result)) return false;
        
        return $result[0]["banner"];
    }
}