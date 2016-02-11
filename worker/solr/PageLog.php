<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProduct
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Worker_Solr_PageLog extends Bilna_Worker_Abstract {
    const QUEUE_TASK_PRODUCT_LOG = 'LOG_PAGE';

    protected $_type;
    protected $_pageLogTable = 'log_page';
    protected $_logPath = 'Bilna_Worker_Solr_PageLog';

    public function run() {
        $this->_start();

        $this->_queueTask = self::QUEUE_TASK_PRODUCT_LOG;
        $this->_queueSvc->connect($this->_queueTask);

        $callback = function($msg) {
            $msgBody = json_decode($msg->body, true);
            
            $userSession = $msgBody['user_session'];
            $productId = implode('|', $msgBody['product_id']);
            $categoryId = $msgBody['category_id'];
            $pageUrl = $msgBody['page_url'];
            $pageReferer = $msgBody['page_referer'];
            $pageType = $msgBody['page_type'];

            $this->_logProgress("#{$userSession} Received from queue");

            if ($this->_getType() == 'db') {
                $this->_setQueryPageLog($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageType);
                $this->_logProgress("#{$userSession} Inserted to database");
            }
            elseif ($this->_getType() == 'file') {
                $this->_writePageLog($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageType);
                $this->_logProgress("#{$userSession} Stored to file");
            }
            else {
                $this->_critical('Invalid type.');
            }
        };

        $this->_queueSvc->channel->basic_consume($this->_queueTask, '', false, true, false, false, $callback);
        
        while (count($this->_queueSvc->channel->callbacks)) {
            $this->_queueSvc->channel->wait();
        }

        $this->_stop();
    }

    protected function _setQueryPageLog($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageType) {
        try {
            $sql = "INSERT INTO `{$this->_pageLogTable}` (`user_session`, `product_id`, `category_id`, `page_url`, `page_referer`, `page_type`, `created_at`) ";
            $sql .= "VALUES (:user_session, :product_id, :category_id, :page_url, :page_referer, :page_type, NOW()) ";
            $binds = array (
                'user_session' => $userSession,
                'product_id' => $productId,
                'category_id' => $categoryId,
                'page_url' => $pageUrl,
                'page_referer' => $pageReferer,
                'page_type' => $pageType,
            );
            
            return $this->_dbWrite->query($sql, $binds);
        }
        catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }
    }

    protected function _writePageLog($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageType) {
        try {
            $data = array ($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageType);
            $dataText = implode(',', $data);

            $logPath = Mage::getBaseDir('log');
            $logFile = "{$logPath}/log_page.log";

            if (file_exists($logFile)) {
                $handle = fopen($logFile, 'a');
            }
            else {
                $handle = fopen($logFile, 'w'); 
            }
            
            fwrite($handle, "{$dataText}\n");
            fclose($handle);

            return true;
        }
        catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }
    }

    protected function _getType() {
        if ($this->_type) {
            return $this->_type;
        }
        else {
            if ($type = $this->getArg('type')) {
                if (in_array($type, array ('db', 'file'))) {
                    $this->_type = $type;

                    return $type;
                }
            }

            $this->_type = 'db';
        }

        return $this->_type;
    }
}

$worker = new Bilna_Worker_Solr_PageLog();
$worker->run();
