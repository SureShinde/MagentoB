<?php
/**
 * Description of Bilna_flushCacheConfig_Shell
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/abstract.php';

class Bilna_Flushcache_Shell extends Mage_Shell_Abstract {
    public function run() {
        $tags = array ('CONFIG');
        
        if (Mage::app()->cleanCache($tags)) {
            $this->logProgress('flush cache success.');
        }
        else {
            $this->logProgress('flush cache failed.');
        }
    }
    
    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
}

$shell = new Bilna_Flushcache_Shell();
$shell->run();
