<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */
class SolrFilterWidget extends BaseWidget{

    /**
     * getting content widget.
     */
    public function getContent($return = FALSE){
        
        $name               = $this->params['data']['name'];
        $id                 = $this->params['data']['id'];
        $filter_data        = $this->params['filter_data'];
        $customerGroupPrice = $this->params['customerGroupPrice'];
        $url                = $this->params['url'];
        $type               = $this->params['type'];
        
        $facetData = $filter_data;
        if($type == 'category'){
            $facetData['category'] = $name;
        }elseif($type == 'vendor'){
            $facetData['vendor'] = $name;
        }elseif($type == 'brand'){
            $facetData['brand'] = $name;
        }
        
        $widgetHTML = '';
        $filter     = array();
        
        $facetAPI              = (new SearchModel())->getFacetsList($facetData);
        $facetAPI['facetFields']['brand_attr']['Fap fap'] = 0 ;
        $facetAPI['facetFields']['bedding_type_attr']['Bondage'] = 0 ;
        
        $facetAPI['facetFields']['price_attr'] = array(0, 50000, 100000, 150000);
        if($facetAPI){
            $filter            = $facetAPI['facetFields'];
            foreach($filter as $key => $value){
                if(strpos($key, "attr") === FALSE || preg_match("/^$type/", 'attr')){
                    unset($filter[$key]);
                    continue;
                }
            }
            
            
            $priceFilter = $filter['price_attr'];
            $rangeMin    = min($priceFilter);
            $rangeMax    = max($priceFilter);
            unset($filter['price_attr']);
            
            foreach($filter as $attr => $dataFilter){
                foreach($dataFilter as $label => $dataValue){
                    unset($filter[$attr][$label]);
                    $filter[$attr][$label]['total'] = $dataValue;
                    
                    if(isset($filter_data[$attr])){
                        $position = count($filter_data[$attr]);
                        if(in_array($label, $filter_data[$attr])){
                            $filter_data = $filter_data;
                        }else{
                            $filter_data[$attr][$position] = $label;
                        }
                        
                        $filter[$attr][$label]['url'] = $filter_data;
                        unset($filter_data[$attr][$position]);
                    }else{
                        $notExistFilter[$attr]        = array($label);
                        $position                     = count($notExistFilter[$attr]);
                        $mergeFilterData              = array();
                        $mergeFilterData              = array_merge($mergeFilterData, $notExistFilter, $filter_data);
                        if($priceFilter){
                            array_merge($mergeFilterData, $priceFilter);
                        }
                        unset($notExistFilter);
                        $filter[$attr][$label]['url'] = $mergeFilterData;
                        unset($mergeFilterData[$attr][$position]);
                    }
                }
            }
        }
        
        $bilnaUtil = new BilnaUtil();
        foreach($filter as $key => $value){
            foreach($value as $label => $data){
                $urlFilter = '';
                foreach($data['url'] as $label2 => $value2){
                    sort($value2);
                    $urlFilter .= $bilnaUtil->getFilterForm($label2).'='.join(",",$value2).'&';
                    $filter[$key][$label]['url'] = substr($urlFilter,0,-1)  ;
                }
            }
        }
        
        
        if(isset($filter_data['price_attr'])){
            $priceArray = explode("-",$filter_data['price_attr'][0]);
            $min        = $priceArray[0];
            $max        = $priceArray[1];
        }else{
            $min        = $rangeMin;
            $max        = $rangeMax;
        }
        $filter_price = array('min' => $min, 'max' => $max);
        $range_price  = array('min' => $rangeMin, 'max' => $rangeMax);
        
        
        $data['base_url']     = $url;
        $data['filter_menu']  = $filter;
        $data['type']         = $type;
        $data['range_price']  = $range_price;
        $data['filter_price'] = $filter_price;
        $data['filter_data']  = $filter_data;
        
        
        $widgetHTML = $this->setView('solr/filter', $data);
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
}
