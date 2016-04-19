<?php
/**
 * Description of Bilna_Worker_Order_VeritransCharge
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/Order.php';

class Bilna_Worker_Order_VeritransCharge extends Bilna_Worker_Order_Order {
    protected $_tubeAllow = 'vt_charge';
    protected $_tblVtCharge = 'veritrans_api_log';

    protected function _process() {
        try {
            $this->_queueSvc->watch($this->_tubeAllow);
            $this->_queueSvc->ignore($this->_tubeIgnore);

            while ($job = $this->_queueSvc->reserve()) {
                $charge = $this->_parseData($job->getData());
                $orderNo = $charge['order_no'];
                $this->_logProgress("#{$orderNo} Received from queue");
                
                if ($this->_setQuery($charge)) {
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$orderNo} Insert to DB => success");
                }
                else {
                    $this->_queueSvc->bury($job);
                    $this->_logProgress("#{$orderNo} Insert to DB => failed");
                }
            }
        }
        catch (Exception $ex) {
            $this->_queueSvc->bury($job);
            $this->_critical($ex->getMessage());
        }
    }
    
    protected function _setQuery($charge) {
        $sql = "INSERT INTO `{$this->_tblVtCharge}` (`order_no`, `request`, `response`, `type`) VALUES (:order_no, :request, :response, :type) ";
        $binds = array (
            'order_no' => $charge['order_no'],
            'request' => $this->_prepareData($charge['request']),
            'response' => $this->_prepareData($charge['response']),
            'type' => $charge['type'],
        );
        
        return $this->_dbWrite->query($sql, $binds);
    }
}

$worker = new Bilna_Worker_Order_VeritransCharge();
$worker->run();
