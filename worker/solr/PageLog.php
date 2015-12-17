<?php
/**
 * Description of Bilna_Worker_Solr_GenerateProduct
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Worker_Solr_PageLog extends Bilna_Worker_Abstract {
    const QUEUE_TASK_PRODUCT_LOG = 'LOG_PAGE';

    protected $_pageLogTable = 'log_page';
    protected $_logPath = 'Bilna_Worker_Solr_PageLog';

    public function run() {
        $this->_start();

        $this->_queueTask = self::QUEUE_TASK_PRODUCT_LOG;
        $this->_queueSvc->connect($this->_queueTask);

        $callback = function($msg) {
            $msgBody = json_decode($msg->body, true);
            
            $userSession = $msgBody['user_session'];
            $productId = implode(',', $msgBody['product_id']);
            $categoryId = $msgBody['category_id'];
            $pageUrl = $msgBody['page_url'];
            $pageReferer = $msgBody['page_referer'];
            $pageType = $msgBody['page_type'];

            $this->_logProgress("#{$userSession} Received from queue");
            $this->_setQueryPageLog($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageype);
            $this->_logProgress("#{$userSession} Inserted to database");
        };

        $this->_queueSvc->channel->basic_consume($this->_queueTask, '', false, true, false, false, $callback);
        
        while (count($this->_queueSvc->channel->callbacks)) {
            $this->_queueSvc->channel->wait();
        }

        $this->_stop();
    }

    protected function _setQueryPageLog($userSession, $productId, $categoryId, $pageUrl, $pageReferer, $pageType) {
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
}

$worker = new Bilna_Worker_Solr_PageLog();
$worker->run();
