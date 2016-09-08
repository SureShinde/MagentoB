<?php
/**
 * Description of Bilna_Worker_Order_Order
 *
 * @path    worker/order/Order.php
 * @author  Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Worker_Order_Order extends Bilna_Worker_Abstract {
    protected $_tubeIgnore = 'default';
    protected $_tubeAllow = 'order';
    
    public function run() {
        $this->_start();
        $this->_process();
        $this->_stop();
    }
    
    protected function _start() {
        $this->_logProgress('START');
        $this->_dbConnect();
        $this->_queueConnect();
    }
    
    protected function _process() {
        $this->_logProgress('PROCESS');
    }

    protected function _stop() {
        $this->_logProgress('STOP');
    }
    
    protected function _queuePut($data) {
        return $this->_queueSvc->useTube($this->_getTube())->put($this->_prepareData($data));
    }
    
    protected function _getTube() {
        return $this->_tubeAllow;
    }
}
