<?php

require_once '../abstract.php';

class RocketWeb_Netsuite_Shell_Stockimporter extends Mage_Shell_Abstract
{
    public function run() {
        if($this->getArg('help')) {
            echo $this->usageHelp();
            exit;
        }

        Mage::helper('rocketweb_netsuite/stock')->processStockUpdates($this);

    }
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f importAllStocks.php -- [options]
  help                 This help
  verbose              Print progress information
USAGE;
    }

    public function logProgress($message) {
        if($this->getArg('verbose')) {
            echo $message;
        }
    }
}

$shell = new RocketWeb_Netsuite_Shell_Stockimporter();
$shell->run();