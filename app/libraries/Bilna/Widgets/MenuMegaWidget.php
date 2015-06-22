<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */

class MenuMegaWidget extends BaseWidget {
    /**
     * getting content widget.
     */
    public function getContent($return = FALSE) {
        $loadFrom       = $this->params['load'];
        $hashName       = $this->type.'/'.CategoryModel::$getMegamenuURL;
        $hashKey        = $loadFrom.'-'.CategoryModel::$getMegamenuURL;
        $widgetHTML     = '';
        $responseData   = FALSE;//$this->getRedisData($hashName, $hashKey);
        if($responseData){
            $widgetHTML = $responseData;
        }else{
            $categoryModel  = new CategoryModel();
            $result         = $categoryModel->getMegaMenu();

            if ($result) {
                $resultData = $result;
                $resultData['megamenu']  = $this->_recursiveMegamenu($resultData['megamenu']);
                $data['widget_megamenu'] = $resultData['megamenu'];
                $data['load']            = $loadFrom;
                
                $widgetHTML              = $this->setView('menu/mega', $data);
                $this->setRedisData($hashName, $hashKey, $widgetHTML);
            }
        }
        
        if($return)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
    
    private function _recursiveMegamenu(&$megamenu){
        foreach ($megamenu as $key => &$value){
            $value['slug'] = BilnaUtil::deleteSlashOrHTML($value['path']);
            $value['path'] = BilnaUtil::replaceUri($value['path'], $value['id'], 'c');
            
            if(isset($value['categories'])){
                $this->_recursiveMegamenu($value['categories']);
            }
        }
        
        return $megamenu;
    }
}
