<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */
class CategoryDescWidget extends BaseWidget{

    
    /**
     * getting content widget.
     */
    public function getContent($return = FALSE){
        $hashName       = $this->type.'/'.'category/desc';
        $hashKey        = $this->params['data']['id'];
        $isGenerated    = isset($this->params['isGenerated']) ? TRUE : FALSE;
        
        $responseData   = $this->getRedisData($hashName, $hashKey);
        $widgetHTML = '';
        if($responseData && !$isGenerated){
            $widgetHTML = $responseData;
        }else{
            $data['name'] = $this->params['data']['name'];
            $data['desc'] = $this->params['data']['description'];

            $widgetHTML   = $this->setView('category/description', $data);
            $this->setRedisData($hashName, $hashKey, $widgetHTML);
        }
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
}
