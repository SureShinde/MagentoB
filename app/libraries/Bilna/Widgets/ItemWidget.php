<?php
namespace Frontend\Widgets;
/**
 * Description of ProductListWidget
 *
 * @author mariovalentino
 */
class ItemWidget extends BaseWidget{
    
    public function getContent($return = FALSE) {
        $itemWidgetHTML = NULL;
        $requestItem    = FALSE;
        $itemIndexPost  = array();
        
        $productID      = $this->params['productID'];
        $itemID         = $this->params['itemID'];
        $groupPrice     = $this->params['customerGroupPrice'];
        $layout         = $this->params['layout'];
        
        $productPost['id'] = $productID;
        $productResult     = (new ProductModel())->getProduct($productPost);
        
        if($productResult && !empty($groupPrice)){
            if( $productResult['type'] == BilnaStatic::$productType['SIMPLE'] ){
                if( empty($itemID) ){
                    $itemID = $productResult['itemId'];
                }
                
                $itemGetPost['conditions'] = [ ['field' => 'id'       , 'filter' => '=', 'value' => $itemID],
                                               ['field' => 'productId', 'filter' => '=', 'value' => $productID],
                                               ];
                $itemGetResult             = (new ItemModel())->getItems($itemGetPost);

                if($itemGetResult){
                    $itemIndexPost['id']        = $itemID;
                    $itemIndexPost['groupId']   = $groupPrice;
                    
                    $itemIndexResult                       = (new ItemModel())->getItemWidgetIndexed($itemIndexPost);
                    
                    
                    $itemIndexResult['selectedWarehouse']  = BilnaItem::getDefaultWarehouse( NULL, 0, $itemIndexResult['stock'] );
                    $itemIndexResult['selectedPrice']      = BilnaItem::getDefaultItemWidgetPrice($itemIndexResult['priceList'], $itemIndexResult['selectedWarehouse']['minQty']);
                    $itemIndexResult['layout']             = $layout;
                    $itemIndexResult['groupPrice']         = $groupPrice;
                    $itemIndexResult['displayImage']       = $this->url->getProductUrl('base').$itemIndexResult['productId'].'/'.$itemIndexResult['baseImage'];

                    $hashName   = 'product/item/widget';
                    $hashKey    = $productID.'-'.$itemID.'-'.$groupPrice.'-'.$layout;

                    $result       = $this->getRedisData($hashName, $hashKey);
                    if($result){
                        $itemWidgetHTML = $result;
                    } else {
                        $itemWidgetHTML = $this->setView('item/item', array('item' => $itemIndexResult));
                        $this->setRedisData($hashName, $hashKey, $itemWidgetHTML);
                    }
                }
            }else if( $productResult['type'] == BilnaStatic::$productType['CONFIGURABLE'] ){
                
            }
        }
        
        if($return)
            return $itemWidgetHTML;
        
        echo $itemWidgetHTML;
    }
}
