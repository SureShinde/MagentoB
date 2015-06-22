<?php
namespace Frontend\Widgets;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductListWidget
 *
 * @author mariovalentino
 */
class ItemListWidget extends BaseWidget{
    
    public function getContent($return = FALSE){
        $itemWidgetHTML = '';
        $itemList       = $this->params['item'];
        //$groupPrice     = $this->params['customerGroupPrice'];
        $layout         = $this->params['layout'];
        
        
        foreach($itemList as $key => $data){
            $itemWidgetPost['productID']          = $data['productID'];
            $itemWidgetPost['itemID']             = $data['itemID'];
            $itemWidgetPost['customerGroupPrice'] = $data['groupPrice'];
            $itemWidgetPost['layout']             = $layout;

            $itemWidget = new ItemWidget($itemWidgetPost);
            $itemHTML   = $itemWidget->getContent(TRUE);

            if($itemHTML){
                $itemWidgetHTML .= $itemHTML;
            }
        }
        
        if($itemWidgetHTML == ''){
            $this->setView('item/noitem');
        }
        
        if($return)
            return $itemWidgetHTML;
        
        echo $itemWidgetHTML;
    }   
}
