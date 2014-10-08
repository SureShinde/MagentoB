<?php
/**
 * Description of Bilna_Cod_Sales_Order_ShipmentController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

include_once ('Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php');

class Bilna_Cod_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController {
    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment) {
    	if ($shipment->getOrder()->getStatus() == 'processing_cod') {
            //$shipment->getOrder()->setStatus('shipping_cod');
            $shipment->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'shipping_cod');
    	}
        else {
            $shipment->getOrder()->setIsInProcess(true);
    	}
    	
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }
}
