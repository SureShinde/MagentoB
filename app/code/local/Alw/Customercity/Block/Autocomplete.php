<?php
/**
 * @category   Alw
 * @package    Alw_Customercity
 */

class Alw_Customercity_Block_Autocomplete extends Mage_Core_Block_Abstract
{
    protected $_suggestData = null;

	protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
	protected function _toHtml(){
    	$html = '';
        $suggestData = $this->getSuggestData();
        if (!($count = count($suggestData))) {
            return $html;
        }

        $count--;
		$arr =array();
      // $html = '<ul>';
        foreach ($suggestData as $index => $item) {
            // if ($index == 0) {
                // $item['row_class'] .= ' first';
            // }
            // if ($index == $count) {
                // $item['row_class'] .= ' last';
            // }
            // $html .=  "<li title=\"".$item['city']."||".$item['state']."\" class=\"".$item['row_class']."\">"
                // .$item['city'].", <i>".$item['state']."</i></li>";
				$city = str_replace(array("\n", "\r"), '', $item['city']);
				$state = str_replace(array("\n", "\r"), '', $item['state']);
				$arr[] = $city.", ".$state;
				//$arr = array("Jakarta Timur"=>"DKI Jakarta","Bekasi"=>"Jawa Barat");
        }


        //$html.= '</ul>';
		
			echo json_encode($arr);
      //  return $html;
    }
    
	public function getSuggestData(){

			$city = $this->getRequest()->getParam('query');

				$collection = Mage::getModel('customercity/customercity')->getCollection();
				$collection->addFieldToSelect('city');
				$collection->addFieldToSelect('state');
				//$collection->addOrder('city','ASC');

				$collection->getSelect()->where("city like '%$city%'");
				$data = array();
				$counter = 0;
				foreach ($collection as $city) {
								$_data = array(
									'city' => $city->getCity(),
									'state' => $city->getState(),
									'row_class' => (++$counter)%2?'odd':'even'
								);
								$data[] = $_data;
								$this->_suggestData = $data;
								
				}
								return $this->_suggestData;	

	      //  }
    }
	
	
}