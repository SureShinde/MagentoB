<?php
namespace Frontend\Widgets;
/**
 * Description of ProductListWidget
 *
 * @author mariovalentino
 */
class ProductWidget extends BaseWidget{
    public function getContent($return = FALSE) {
        $productWidgetHTML  = NULL;
        $productData        = array();
        
        if(isset($this->params['productID'])) 
            $productID = $this->params['productID'];
        
        if(isset($this->params['itemID'])) 
            $itemID = $this->params['itemID'];
        
        if(isset($this->params['customerGroupPrice'])) 
            $groupPrice = $this->params['customerGroupPrice'];
        
        if(isset($this->params['layout'])) 
            $layout = $this->params['layout'];
        
        // setting default value for groupPrice and layout.
        if(empty($groupPrice)) 
            $groupPrice = $this->session->get(BilnaStatic::$sessionGroupPrice);
        
        if(empty($layout)) 
            $layout = BilnaStatic::$productWidgetLayout;
        
        
        $hashName      = 'product/widget/'.$layout;
        $hashKey       = $productID.'-'.$groupPrice;
        
        $request['id'] = $productID;
        $product       = (new ProductModel())->getProduct($request);
        
        
        
        $result = FALSE;//$this->getRedisData($hashName, $hashKey);
        if($result){
            $productWidgetHTML = $result;
            
        }else{
            if( $product['type'] == BilnaStatic::$productType['SIMPLE'] ){
                if( empty($itemID) ) 
                    $itemID = $product['itemId'];
                
                if($itemID) 
                    $hashKey = $productID.'-'.$itemID.'-'.$groupPrice;

                $result = FALSE;//$this->getRedisData($hashName, $hashKey);
                if($result){
                    $productWidgetHTML = $result;
                    
                }else{
                    $itemData    = $this->_getItemData($itemID, $groupPrice);
                    if($itemData){
                        $productData                        = BilnaItem::buildProductData($product, $itemData['price']);
                        $productData['rating']              = $itemData['item']['rating'];
                        $productData['notifyStock']         = $itemData['item']['selectedWarehouse']['notifyStock'] == 1 ? TRUE : FALSE;
                        $productData['isInStock']           = $itemData['item']['selectedWarehouse']['isInStock'] == 1 ? TRUE : FALSE;
                        $productData['selectedWarehouse']   = $itemData['item']['selectedWarehouse'];
                    }
                }

            }else if( $product['type'] == BilnaStatic::$productType['CONFIGURABLE'] ){
                $productChild = (new ProductModel())->getProductChild($request);
                if($productChild){
                    $listItem     = array();
                    $listInStock  = array();
                    foreach($productChild['childs'] as $key => $value){
                        $itemData = $this->_getItemData($value['itemId'], $groupPrice);
                        if(!$itemData)
                            continue;

                        array_push($listInStock, $itemData['item']['selectedWarehouse']['isInStock']);
                        array_push($listItem, $itemData);
                    }
                    
                    
                    
                    if(count($listItem)){
                        $getPriceRating = $this->_getPriceAndRating($listItem);
                        if($getPriceRating){
                        //    echo '<pre>';print_r($getPriceRating);echo '</pre>';
                            $productData                        = BilnaItem::buildProductData($product, $getPriceRating['price']);
                            $productData['rating']              = $getPriceRating['rating'];
                            $productData['notifyStock']         = FALSE;
                            $productData['isInStock']           = in_array(1, $listInStock) ? TRUE : FALSE;
                            $productData['selectedWarehouse']   = '';
                        }
                    }
                }
            }
            
            //echo '<pre>';print_r($productData);echo '</pre>';
            
            $productWidgetHTML = $this->setView('product/'.$layout, array('product' => $productData));
            $this->setRedisData($hashName, $hashKey, $productWidgetHTML);
        }
        
        if($return)
            return $productWidgetHTML;
        
        echo $productWidgetHTML;
    }
    
    
    private function _getItemData($itemID, $groupPrice){
        $itemDataPost['id']      = $itemID;
        $itemDataPost['groupId'] = $groupPrice;

        $itemData = (new ItemModel())->getItemIndexed($itemDataPost);
        //echo '<pre>';print_r($itemData);echo '</pre>';
        if(!$itemData)
            return FALSE;
        
        $selectedWarehouse  = BilnaItem::getDefaultWarehouse(NULL, 0, $itemData['stocks']);
        $qty                = $selectedWarehouse['qty'];
        $warehouseID        = $selectedWarehouse['warehouseId'];
        $price              = BilnaItem::getDefaultItemDetailPrice($qty, $warehouseID, $itemData);
        
        $itemData['selectedWarehouse'] = $selectedWarehouse;
        return ['price' => $price, 'item' => $itemData];        
    }
    
    
    private function _getPriceAndRating($listItem){
        $price  = ['finalPrice'     => 0,
                   'originalPrice'  => 0,
                   'minPrice'       => 0,
                   'maxPrice'       => 0,
                   'discount'       => 0,
                  ];
        $rating = 0;
        
        if(count($listItem) > 0 & is_array($listItem)){
        
            $listFinalPrice = array();
            $rating         = 0;
            $discount       = 0;
            $listDiscount   = array();
            $i              = 0;
            foreach($listItem as $key => $data){
                foreach($data as $key2 => $data2){
                    if($key2 == 'price'){
                        array_push($listFinalPrice, $data2['finalPrice']);
                        array_push($listDiscount, $data2['discount']);
                    }

                    if($key2 == 'item'){
                        if($data2['rating'] != 0){
                            $rating += $data2['rating'];
                            $i++;
                        }
                    }
                }
            }

            //echo $i.'-'.$j;
            
            $rating     = $rating/$i;
            $discount   = max($listDiscount);
                    
            //echo $rating.'-'.$discount;
                    
            $minPrice   = min($listFinalPrice);
            $maxPrice   = max($listFinalPrice);

            $price['minPrice'] = $minPrice;
            $price['maxPrice'] = $maxPrice;
            $price['discount'] = $discount;
            
        }
        
        return ['price' => $price, 'rating' => $rating];
    }
}
