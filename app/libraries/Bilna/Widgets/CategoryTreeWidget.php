<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */
class CategoryTreeWidget extends BaseWidget{

    /**
     * getting content widget.
     */
    public function getContent($return = FALSE){
        $name           = $this->params['data']['name'];
        $id             = $this->params['data']['id'];
        $isGenerated    = isset($this->params['isGenerated']) ? TRUE : FALSE;
        
        $hashName       = $this->type.'/category/tree';
        $hashKey        = $id;        
        
        $responseData = $this->getRedisData($hashName, $hashKey);
        $widgetHTML   = '';
        if($responseData && !$isGenerated){
            $widgetHTML = $responseData;
        }else{
            $data['categoryId'] = $id;
            $categoryAPI        = (new CategoryModel())->getCategoryParentChild($data);
            $categoryActive     = NULL;
            $categoryParent     = $this->_recursiveTreeWidget($categoryAPI, $name, $categoryActive);
            
            $data['current_menu'] = $categoryParent['categories'];
            $data['current_name'] = $name;
            $data['parent_name']  = $categoryActive;
            
            
            //echo '<pre>';print_r($data);echo '</pre>';
            
            $widgetHTML = $this->setView('category/tree', $data);
            $this->setRedisData($hashName, $hashKey, $widgetHTML);
        }
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
    
    private function _recursiveTreeWidget(&$category, $name, &$activename){
        foreach($category as $key => &$value){
            if(isset($value['id']))
                $value['path']  = BilnaUtil::replaceUri($value['path'], $value['id'], 'c');
            if($value['name'] == $name){
                $activename = $value['name'];
            }
            
            if(isset($value['categories'])){
                $this->_recursiveTreeWidget($value['categories'], $name, $activename);
            }
        }
        return $category;
    }
}
