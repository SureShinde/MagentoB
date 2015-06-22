 <?php
class Alw_Customercity_Model_Mysql4_Customercity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct() 
	{
        parent::_construct();
        $this->_init('customercity/customercity'); 
	}
	
	 public function addFilterByCity($city)
	{
        $this->addFieldToFilter('city', 'Jakarta Barat');
        return $this;
    }
}
		
	
