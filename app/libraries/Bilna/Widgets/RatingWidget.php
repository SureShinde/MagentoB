<?php
namespace Frontend\Widgets;
/**
 * Description of ProductReviewWidget
 *
 * @author mariovalentino
 */
class RatingWidget extends BaseWidget{
    /**
     * getting content widget
     */
    public function getContent($return = FALSE){
        $itemData   = $this->params['item'];
        $type       = $this->params['type'];
        $folder     = 'rating';
        
        
        $data['item']   = $itemData;
        $data['type']   = $type;
        
        switch($type){
            case 'product' : 
                $ratingForm['id']        = 1;
                $getProductRatingForm    = (new RatingModel())->getRatingForm($ratingForm);
                $data['additionalInput'] = $getProductRatingForm['inputs'];
                $widgetHTML = $this->setView($folder.'/'.$type, array('data' => $data));
                break;
            
            case 'vendor' :
                $ratingForm['id']        = 2;
                $getProductRatingForm    = (new RatingModel())->getRatingForm($ratingForm);
                $data['additionalInput'] = $getProductRatingForm['inputs'];
                $widgetHTML = $this->setView($folder.'/'.$type, array('data' => $data));
                break;
            
            case 'item' :
                $widgetHTML = $this->setView($folder.'/'.$type, array('data' => $data));
                break;
            
            default :
                $widgetHTML = '';
                break;
        }
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
        
    }
}
