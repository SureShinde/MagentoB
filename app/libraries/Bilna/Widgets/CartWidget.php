<?php
namespace Frontend\Widgets;
/**
 * Description of CartWidget
 *
 * @author mariovalentino
 */
use Bilna\Libraries\BilnaStatic;
class CartWidget extends BaseWidget {
    protected $session;
    
    public function __construct($params = array ()) {
        parent::__construct($params);
        $this->session = $this->di->getSession();
    }
    
    public function getContent($return = false) {
        $basketData = false;
        
        
        if ($this->session->has(BilnaStatic::$sessionLoginUser)) {
            $redisSession = new BilnaRedis(BilnaStatic::$session);
            $customerId = $this->session->get(BilnaStatic::$sessionLoginUser);
            $basketData = $redisSession->getRedisData(BilnaStatic::$sessionBasketUserRedis, $customerId);
            unset ($redisSession);
        }
        else {
            if ($this->session->has(BilnaStatic::$sessionBasketGuest)) {
                $basketData = json_decode($this->session->get(BilnaStatic::$sessionBasketGuest), true);
            }
        }
        
        $widgetHTML = $this->setView('cart', array ('basketData' => $basketData));
        
        if ($return) {
            return $widgetHTML;
        }
        
        echo $widgetHTML;
    }
}
