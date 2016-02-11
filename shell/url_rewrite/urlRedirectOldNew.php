<?php
require_once dirname(__FILE__) . '/../abstract.php';
//require_once(dirname(__FILE__) . "/../../app/Mage.php");
class Url_Redirect_Old_New extends Mage_Shell_Abstract {
    public function run() {
        ini_set('display_errors', 1);

        $file = $this->getFile();
        if ($file) {
            $this->process($file);
        } else {
            echo $this->usageHelp();
        }
    }

    protected function process($file) {
        // START PROCESS
        echo "START PROCESS OLD AND NEW URL REDIRECT\n";
        echo "--------------------------------------\n";

        $csvObject = new Varien_File_Csv();

        $error = false;
        $header = true;

        try 
        {
            if (file_exists($file)) 
            {
                // read CSV
                $data = $csvObject->getData($file);
                $line_no = 1;

                // read data line by line
                foreach ($data as $lines => $line) {
                    // skip header
                    if ($header) {
                        $header = false;
                        continue;
                    }

                    $this->addRedirect($line[0], $line[1], $line[2], $line[3], $line[4], $line_no);
                    $line_no++;
                }
            }
            else
                echo "ERROR : The file you mentioned does not exist\n";
        } catch (Exception $e) {
            $error = true;
            echo "ERROR : " . $e->getMessage() . "\n";
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
        }

        echo "---------------------------------------\n";
        echo "FINISH PROCESS OLD AND NEW URL REDIRECT";
    }

    protected function getFile() {
        $file = $this->getArg('file');
        return $file;
    }

    protected function addRedirect($col1, $col2, $col3, $col4, $col5, $line_no) {
        echo "$line_no : \t processing category ID - $col5\n";

        $resource = Mage::getSingleton('core/resource');
        // connection for read
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('core/url_rewrite');

        // get permanent category request path from category ID in the CSV file
        $where = "category_id = $col5 AND id_path = 'category/$col5'";
        $query = "SELECT request_path, store_id FROM $table WHERE $where";

        $results = $readConnection->fetchAll($query);
        
        if (!$results)
            return "false";

        foreach($results as $result) {
            $requestPath = $result['request_path'];
            $store_id = $result['store_id'];
            break;
        }

        // connection for write
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('core/url_rewrite');

        // delete existing old and new url first (if any)
        $writeConnection->beginTransaction();
        $query = "DELETE FROM $table WHERE id_path = 'category/$col5/old' OR id_path = 'category/$col5/new'";
        $writeConnection->query($query);
        $writeConnection->commit();

        // next, we are going to write
        $old_url_redirect = 'R';
        $new_url_redirect = 'RP';

        switch ($col3) {
            case "temporary":
                $old_url_redirect = 'R';
                break;
            case "permanent":
                $old_url_redirect = 'RP';
                break;
            default:
                $old_url_redirect = 'R';
        }

        switch ($col4) {
            case "temporary":
                $new_url_redirect = 'R';
                break;
            case "permanent":
                $new_url_redirect = 'RP';
                break;
            default:
                $new_url_redirect = 'RP';
        }

        // add old url
        $writeConnection->beginTransaction();
        $writeConnection->insert($table, 
            array("store_id" => $store_id, "id_path" => "category/$col5/old",
                "request_path" => $col1, "target_path" => $requestPath,
                "is_system" => 0, "options" => $old_url_redirect, "category_id" => $col5)
        );
        $writeConnection->commit();

        // add new url
        $writeConnection->beginTransaction();
        $writeConnection->insert($table, 
            array("store_id" => $store_id, "id_path" => "category/$col5/new",
                "request_path" => $col2, "target_path" => $requestPath,
                "is_system" => 0, "options" => $new_url_redirect, "category_id" => $col5)
        );
        $writeConnection->commit();
    }

    public function usageHelp() {
        return <<<USAGE
Usage:  php -f urlRedirectOldNew.php -- [options]
  --file <file full path>                File Full Path

USAGE;
    }
}

$shell = new Url_Redirect_Old_New();
$shell->run();
