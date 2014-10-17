<?php
require_once dirname(__FILE__) . '/../abstract.php';
//require_once(dirname(__FILE__) . "/../../app/Mage.php");
class Url_Rewrite_Shell extends Mage_Shell_Abstract {
    public function run() {
        ini_set('display_errors', 1);

        $limit = $this->getLimit();
        $offset = $this->getOffset();

        $resource       = Mage::getSingleton('core/resource');
        $adapter        = $resource->getConnection('core_read');
        $tableName      = $resource->getTableName('core_url_rewrite');
        $select = $adapter->select()
            ->from(
                $tableName,
                new Zend_Db_Expr('*')
            )
            ->limit($limit, $offset);
        $urlLists = $adapter->fetchAll($select);

        foreach ($urlLists as $item) {
            //print_r($item);
            $url = Mage::getBaseUrl().$item['request_path'];
            $url = "http://www.bilna.com/".$item['request_path'];
//echo $url;            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $output = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            // now $write is an instance of Zend_Db_Adapter_Abstract
            $url_rewrite_id = $item["url_rewrite_id"];

            $write->query("INSERT INTO core_url_rewrite_check(id, url_rewrite_id, response_code) VALUES(NULL, $url_rewrite_id, $http_status) ON DUPLICATE KEY UPDATE response_code=$http_status");
//echo $http_status;
        }

    }

    protected function getLimit() {
        $limitString = $this->getArg('limit');
        return $limitString;
    }

    protected function getOffset() {
        $offsetString = $this->getArg('offset');
        return $offsetString;
    }

    public function usageHelp() {
        return <<<USAGE
Usage:  php -f urlRewriteShell.php -- [options]
  --limit <limit>                Run specified limit
  --offset <offset>              Run specified offset

USAGE;
    }
}

$shell = new Url_Rewrite_Shell();
$shell->run();
