<?php
/**
 * Description of Bilna_Netsuitesync_Shell_NetsuiteExportInvoice
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Netsuitesync_Shell_ExecuteCategoryGenerator extends Mage_Shell_Abstract {
	protected $_catgenId = NULL;
 
    public function __construct() {
        parent::__construct();
 
        // Time limit to infinity
        set_time_limit(0);     
 
        if($this->getArg('catgenID')) {
            $this->_catgenId = $this->getArg('catgenID');
        }
    }
	
    public function run() {
    	if(isset($this->_catgenId) && !is_null($this->_catgenId)){
    		Mage::getSingleton('core/session')->setCategoryGeneratorId($this->_catgenId);
    	}
        Mage::getModel('categorygenerator/generator')->applyAll();
    }

	// Usage instructions
	public function usageHelp()
	{
		return <<<USAGE
Usage:  php -f executeCategoryGenerator.php -- [options]

  --catgenID <value>     Value of specific catgenID, leave it blank for all catgenID
    
USAGE;
    }
}

$shell = new Bilna_Netsuitesync_Shell_ExecuteCategoryGenerator();
$shell->run();
