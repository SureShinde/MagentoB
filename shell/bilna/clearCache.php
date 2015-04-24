<?php
/**
 * Description of ClearCache
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class ClearCache extends Mage_Shell_Abstract {
    public function run() {
        if ($keys = $this->getArg('key')) {
            $cache = Mage::getSingleton('core/cache');
            
            $keyArr = explode(',', $keys);
            
            foreach ($keyArr as $key) {
                if ($cacheData = $cache->load($key)) {
                    if ($cache->remove($key)) {
                        $message = $key . ' has been removed';
                    }
                    else {
                        $message = $key . ' failed removed';
                    }
                }
                else {
                    $message = $key . ' doesnot exist';
                }
                
                $this->logProgress($message);
            }
        }
        else {
            $message = 'key is not valid';
            $this->logProgress($message);
        }
    }
    
    protected function logProgress($message) {
        echo $message . "\n";
    }
}

$shell = new ClearCache();
$shell->run();
