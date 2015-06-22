<?php
namespace Frontend\Widgets;
/**
 * Description of SearchWidget
 *
 * @author bilna development.
 */
class CategorySelectWidget extends BaseWidget{
    
    public function getContent($return = FALSE) {
        $hashName       = $this->type.'/category/select';
        $hashKey        = 'category/select';
        $isGenerated        = isset($this->params['isGenerated']) ? TRUE : FALSE;
        
        $widgetHTML     = '';
        $responseData   = $this->getRedisData($hashName, $hashKey);
        if($responseData && !$isGenerated){
            $widgetHTML = $responseData;
        }else{
            $categoryModel  = new CategoryModel();
            $result         = $categoryModel->getMegaMenu();
            
            if ($result) {
                $data['category']        = $result['megamenu'];
                $widgetHTML              = $this->setView('category/select', $data);
                $this->setRedisData($hashName, $hashKey, $widgetHTML);
            }
        }
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
}
