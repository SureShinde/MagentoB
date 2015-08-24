<?php
require_once dirname(__FILE__) . '/../abstract.php';

class RocketWeb_Netsuite_Shell_NetsuiteCron extends Mage_Shell_Abstract {
    public function run() {
        ini_set('display_errors', 1);

        $modes = $this->getModes();
        $recordtype = $this->getRecordtype(); // addition by Willy : get record type argument
        $lock_recordtype_mode = (($recordtype && $modes[0] == 'import') ? '_'.$recordtype : '');
        
        if ($modes) {
            $mutex = new RocketWeb_Netsuite_Model_Mutex('nesuite_cron_' . $modes[0] . $lock_recordtype_mode . '_lock');
            
            if (!$mutex->getLock()) {
                die ('no lock!!!');
            }

            foreach ($modes as $mode) {
                if (Mage::registry('current_run_mode')) {
                    Mage::unregister('current_run_mode');
                }

                /* addition by Willy */
                if (Mage::registry('current_run_recordtype')) {
                    Mage::unregister('current_run_recordtype');
                }
                
                Mage::register('current_run_mode',$mode);
                Mage::register('current_run_recordtype',$recordtype); // addition by Willy
                Mage::helper('rocketweb_netsuite')->loadNetsuiteNamespace();
                
                switch ($mode) {
                    case 'all':
                        Mage::getModel('rocketweb_netsuite/process')->processExport();
                        Mage::getModel('rocketweb_netsuite/process')->processImport();
                        Mage::getModel('rocketweb_netsuite/observer')->processStockImport();
                        break;
                    case 'import':
                        Mage::getModel('rocketweb_netsuite/process')->processImport($this);
                        break;
                    case 'export':
                        Mage::getModel('rocketweb_netsuite/process')->processExport($this);
                        break;
                    case 'stock':
                        Mage::getModel('rocketweb_netsuite/observer')->processStockImport();
                        break;
                }
            }

            $mutex->releaseLock();
        }
        else {
            echo $this->usageHelp();
        }
    }

    protected function getModes() {
        $possibleModes = $this->getPossibleModes();
        $modesString = $this->getArg('mode');
        
        if (!$modesString) {
            return null;
        }
        else {
            $ret = array ();
            $modes = explode(',', $modesString);
            
            foreach ($modes as $mode) {
                if (in_array(trim($mode), $possibleModes)) {
                    $ret[] = trim($mode);
                }
            }
            
            if (count($ret)) {
                //If all is one of the modes, remove the others as it will only create duplication
                if (in_array('all', $ret)) {
                    return array ('all');
                }
                else {
                    return $ret;
                }
            }
            else {
                return null;
            }
        }
    }

    /* addition by Willy: get recordtype argument
    - this will be applied if mode is "import"
    */
    protected function getRecordtype() {
        $possibleRecordtypes = $this->getPossibleRecordtypes();
        $recordtypeString = $this->getArg('recordtype');

        if (!$recordtypeString) {
            return null;
        }
        else {
            $ret = null;

            if (in_array(trim($recordtypeString), $possibleRecordtypes)) {
                $ret = trim($recordtypeString);
            }

            return $ret;
        }
    }

    protected function getPossibleModes() {
        return array ('all', 'import', 'export', 'stock');
    }

    /* addition by Willy: get possible recordtype argument */
    protected function getPossibleRecordtypes() {
        return array ('all', 'invoice', 'inventoryitem', 'order', 'order_fulfillment');
    }

    public function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }

    public function usageHelp() {
        return <<<USAGE
Usage:  php -f netsuiteCron.php -- [options]
  --verbose                     Display progress (useful for debugging)
  --mode <modes>                Run specified modes
  <modes>                       Comma separated modes (import,export,stock) or value "all" for all modes
  --recordtype <type>           value "all", "invoice", "inventoryitem", "cashsale", "order", "order_fulfillment" (applied if mode is "import")

USAGE;
    }
}

$shell = new RocketWeb_Netsuite_Shell_NetsuiteCron();
$shell->run();
