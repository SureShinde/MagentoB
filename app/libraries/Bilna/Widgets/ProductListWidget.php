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
class ProductListWidget extends BaseWidget{
    
    public function getContent($return = FALSE){
        $productWidgetHTML = '';
        $productList       = $this->params['product'];
        $layout            = NULL;
        if(isset($this->params['layout'])){
            $layout = $this->params['layout'];
        }
        
        if($productList){
            foreach($productList as $key => $data){
                $productWidgetPost['productID'] = $data['id'];
                $productWidgetPost['layout']    = $layout;


                $productWidget = new ProductWidget($productWidgetPost);
                $productHTML   = $productWidget->getContent(TRUE);

                if($productHTML){
                    $productWidgetHTML .= $productHTML;
                }
            }
        }else{
            $productWidgetHTML = $this->setView('product/noproduct');
        }
        
        if($productWidgetHTML == ''){
            if($layout == 2){
                $productWidgetHTML .= $this->setView('product/noproduct2');
            }else{
                $productWidgetHTML .= $this->setView('product/noproduct1');
            }
        }
        
        if($return)
            return $productWidgetHTML;
        
        echo $productWidgetHTML;
    }   
}
