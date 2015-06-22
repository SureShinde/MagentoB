<?php
namespace Frontend\Widgets;
/**
 * Description of StaticAreaWidget
 *
 * @author mariovalentino
 */

class StaticAreaWidget extends BaseWidget {
    /**
     * override get content widget.
     */
    public function getContent($return = FALSE) {
        $column = NULL;
        $staticAreaWidgetHTML = '';
        $load                 = isset($this->params['load']) ?  $this->params['load'] : null;
        unset($this->params['load']);
        foreach($this->params as $key => $value){
            $column = $key;
            break;
        }
        $hashName             = $this->type.'/'.StaticAreaModel::$getStaticAreaIndexed;
        $hashKey              = $load ? $load.'-'.$this->params[$column] : $this->params[$column];
        
        $result  = $this->getRedisData($hashName, $hashKey);
        
        if($result){
            $staticAreaWidgetHTML = $result;
        }else{
            $staticAreaModel = new StaticAreaModel();
            $result          = $staticAreaModel->getStaticAreaIndexed(array ($column => $this->params[$column]));
            //print_r($result);
            if ($result !== false && $result['status']) {
                # getting view instance;
                $type       = $result['type'];
                $staticId   = $result['id'];

                $data['type']     = $type; 
                $data['content']  = $result;
                $data['staticId'] = $staticId;
                $data['path']     = $this->url->getMediaUrl();
                $data['load']     = $load;
                
                $staticAreaWidgetHTML = $this->setView('static_area/'.$data['type'], $data);
                $this->setRedisData($hashName, $hashKey, $staticAreaWidgetHTML);
            }
        }
        
        if($return)
            return $staticAreaWidgetHTML;
        
        echo $staticAreaWidgetHTML;
    }
    
    /**
     * setting content widget
     */
    public function setContent(){
        $staticAreaModel = new StaticAreaModel();
        $result          = $staticAreaModel->getStaticAreaIndexed(array ('identifier' => $this->params['name']));

        if ($result !== false) {
            # getting view instance;
            $type       = $result['type'];
            $content    = $result['contents']; 
            $staticId   = $result['id'];

            $data['type']     = $type; 
            $data['content']  = $result;
            $data['staticId'] = $staticId;
            $data['path']     = $this->url->getMediaUrl();
            $data['load']     = $load;

            $widgetHTML = $this->setView('static_area/'.$data['type'], $data);
            $this->setRedisData($hashName, $hashKey, $widgetHTML);
        }
    }
}
