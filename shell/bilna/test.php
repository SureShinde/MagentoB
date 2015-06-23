<?php
/**
 * Description of test
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Test extends Mage_Shell_Abstract {
    protected $incrementId = '500000255';
    
    public function run() {
        $magentoOrder = Mage::getModel('sales/order')->loadByIncrementId($this->incrementId);
        $itemParent = array ();
        $itemShip = array ();
        
        foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
            if (!$magentoOrderItem->getParentItemId()) {
                if ($magentoOrderItem->getSku() == 'DIAP-MAMY-013C') {
                    //continue;
                }
                
                $itemParent[$magentoOrderItem->getSku()] = array (
                    'item_id' => $magentoOrderItem->getId(),
                    'qty' => (int) $magentoOrderItem->getQtyOrdered() - $magentoOrderItem->getQtyShipped()
                );
            }
        }
        
        foreach ($magentoOrder->getAllItems() as $magentoOrderItem)  {
            if ($this->checkItemParent($magentoOrderItem->getSku(), $itemParent)) {
                if ($magentoOrderItem->getQtyInvoiced() <= $itemParent[$magentoOrderItem->getSku()]['qty']) {
                    $itemShip[$magentoOrderItem->getId()] = $magentoOrderItem->getQtyOrdered();
                }
            }
        }
        
        echo "itemParent: " . json_encode($itemParent) . "\n";
        echo "itemShip: " . json_encode($itemShip) . "\n";
        exit;
    }
    
    protected function checkItemParent($sku, $itemParent) {
        foreach ($itemParent as $key => $value) {
            if ($sku == $key) {
                return true;
            }
        }
        
        return false;
    }

    protected function writeProcess($message) {
        echo "$message\n";
    }
}

$shell = new Test();
$shell->run();
