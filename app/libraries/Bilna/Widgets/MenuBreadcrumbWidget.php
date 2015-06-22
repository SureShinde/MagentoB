<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */
class MenuBreadcrumbWidget extends BaseWidget{

    /**
     * getting content widget.
     */
    public function getContent($return = FALSE){
        $list         = array();
        $hashName     = $this->type.'/'.$this->params['type'].'/breadcrumb';
        $hashKey      = $this->params['data']['id'];
        
        $widgetHTML   = '';
        $responseData = $this->getRedisData($hashName, $hashKey);
        if($responseData){
            $widgetHTML = $responseData;
        }else{ 
            if($this->params['type'] == 'category'){
                
                $level  = 0;

                $categoryAPI             = new CategoryModel();
                $dataQueryCategory['id'] = $this->params['data']['id'];
                $queryCategory           = $categoryAPI->getCategory($dataQueryCategory);
                if($queryCategory == FALSE)
                    return NULL;

                $list[$level]['name'] = $queryCategory['name'];
                $list[$level]['path'] = BilnaUtil::replaceUri($queryCategory['path'], $queryCategory['id'], 'c');

                if($queryCategory['parentId'] != 0){
                    $dataQueryCategory['id'] = $queryCategory['parentId'];
                    $queryCategory           = (new CategoryModel())->getCategory($dataQueryCategory);
                    $level += 1;
                    $list[$level]['name'] = $queryCategory['name'];
                    $list[$level]['path'] = BilnaUtil::replaceUri($queryCategory['path'], $queryCategory['id'], 'c');

                    if($queryCategory['parentId'] != 0){
                        $dataQueryCategory['id'] = $queryCategory['parentId'];
                        $queryCategory           = (new CategoryModel())->getCategory($dataQueryCategory);
                        $level += 1;
                        $list[$level]['name'] = $queryCategory['name'];
                        $list[$level]['path'] = BilnaUtil::replaceUri($queryCategory['path'], $queryCategory['id'], 'c');
                    }
                }
                krsort($list);

            }elseif($this->params['type'] == 'brand'){
                $level  = 0;

                $list[$level]['name'] = BilnaUtil::getMessage('Brands');
                $list[$level]['path'] = 'brands';
                $level++;
                
                $brandAPI               = new BrandModel();
                $dataQueryBrand['id']   = $this->params['data']['id'];
                $queryBrand             = $brandAPI->getBrand($dataQueryBrand);
                if($queryBrand === FALSE)
                    return NULL;


                $list[$level]['name'] = $queryBrand['name'];
                $list[$level]['path'] = $queryBrand['path'];
                
            }elseif($this->params['type'] == 'vendor'){
                $level = 0;
                
                $list[$level]['name'] = BilnaUtil::getMessage('Vendors');
                $list[$level]['path'] = 'vendors';
                $level++;
                
                $vendorAPI              = new VendorModel();
                $dataQueryVendor['id']  = $this->params['data']['id'];
                $queryVendor            = $vendorAPI->getVendor($dataQueryVendor);
                if($queryVendor === FALSE)
                    return NULL;
                
                $list[$level]['name'] = $queryVendor['name'];
                $list[$level]['path'] = $queryVendor['path'];  
            }

            $widgetHTML = $this->setView('menu/breadcrumb', array('widget_breadcrumb' => $list));
            $this->setRedisData($hashName, $hashKey, $widgetHTML);
        }
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
    
}
