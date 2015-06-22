<?php
namespace Frontend\Widgets;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FlashMessageWidget
 *
 * @author mariovalentino
 */
class FlashMessageWidget extends BaseWidget{
    //put your code here
    public function getContent($return = FALSE){
        $widgetHTML = $this->setView('flash_message');
        
        if($widgetHTML)
            return $widgetHTML;
        
        echo $widgetHTML;
    }
}
