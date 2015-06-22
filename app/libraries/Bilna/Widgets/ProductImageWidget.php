<?php
namespace Frontend\Widgets;
/**
 * Description of ProductImageWidget
 *
 * @author mariovalentino
 */
class ProductImageWidget extends BaseWidget{
    
    /**
     * getting product image widget.
     */
    public function getContent($return = FALSE){
        
        $redisHash  = $this->type.'/product/image';
        $redisKey   = $this->params['id'];
        $widgetHTML = '';
        $responseData = $this->getRedisData($redisHash, $redisKey);
        
        
        //if($responseData){
        //    $widgetHTML = $responseData;
        //}else{
            $dataQueryProductImage['id']     = $this->params['id'];
            $productAPI                      = new ProductModel();
            $queryProductImage               = $productAPI->getProductImages($dataQueryProductImage);
            
            $queryProductImage['small_path'] = $this->url->getProductUrl('small');
            $queryProductImage['large_path'] = $this->url->getProductUrl('large');
            
            $data['widget_image'] = $queryProductImage;
            $widgetHTML           = $this->setView('product_image', $data);
            $this->setRedisData($redisHash, $redisKey, $widgetHTML);
        //}
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
}
